<?php

declare(strict_types=1);

namespace App\TrackData\CanonicalData;

use Exception;
use PhpParser\BuilderFactory;
use PhpParser\Comment\Doc;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Nop;

class TestCase
{
    /**
     * PHP_EOL is CRLF on Windows, we always want LF
     * @see https://www.php.net/manual/en/reserved.constants.php#constant.php-eol
     */
    private const LF = "\n";

    public function __construct(
        public string $uuid,
        public string $description,
        public string $property,
        public object $input,
        public mixed $expected,
        public array $comments = [],
        private ?object $unknown = null,
    ) {
    }

    public static function from(object $rawData): ?self
    {
        $requiredProperties = [
            'uuid',
            'description',
            'property',
            'input',
            'expected',
        ];
        $actualProperties = \array_keys(\get_object_vars($rawData));
        $data = [];
        foreach ($requiredProperties as $requiredProperty) {
            if (!\in_array($requiredProperty, $actualProperties)) {
                return null;
            }
            $data[$requiredProperty] = $rawData->{$requiredProperty};
            unset($rawData->{$requiredProperty});
        }

        return new static(
            ...$data,
            unknown: empty(\get_object_vars($rawData)) ? null : $rawData,
        );
    }

    public function asClassMethods(): array
    {
        $builderFactory = new BuilderFactory();

        // Renders as blank line
        $nop = new Nop();

        // This should work for all types in input and expected
        // Using a Nop() with a DocComment allows to write arbitrary strings
        // into methods (not between them). Generating code is so complicated...
        $vars = new Nop();
        $vars->setDocComment(new Doc(
            '$input = ' . var_export((array)$this->input, true) . ';' . self::LF
            . '$expected = ' . var_export($this->expected, true) . ';'
        ));

        $method = $builderFactory->method($this->testMethodName())
            ->makePublic()
            ->setReturnType('void')
            ->setDocComment($this->asDocBlock([
                ...($this->unknown !== null
                    ? ['Unknown data:', \json_encode($this->unknown), '']
                    : []
                ),
                'uuid: ' . $this->uuid,
                '@testdox ' . \ucfirst($this->description),
                '@test',
            ]))
            ->addStmt(
                $builderFactory->funcCall(
                    '$this->markTestSkipped',
                    [ 'This test has not been implemented yet.' ],
                )
            )
            ->addStmt($nop)
            ->addStmt($vars)
            ->addStmt($nop)
            ->addStmt(new Assign(
                $builderFactory->var('actual'),
                $builderFactory->funcCall('$this->subject->' . $this->property, [new Arg($builderFactory->var('input'), unpack: true)]),
            ))
            ->addStmt($nop)
            ->addStmt(
                $builderFactory->funcCall(
                    '$this->assertSame',
                    [
                        $builderFactory->var('expected'),
                        $builderFactory->var('actual'),
                    ]
                )
            )
            ;
        ;

        return [ $method->getNode() ];
    }

    private function testMethodName(): string
    {
        $sanitizedDescription = \preg_replace('/\W+/', ' ', $this->description);

        $methodNameParts = \explode(' ', $sanitizedDescription);
        $upperCasedParts = \array_map(
            fn ($part) => \ucfirst($part),
            $methodNameParts
        );

        return \lcfirst(\implode('', $upperCasedParts));
    }

    private function asDocBlock(array $lines): string
    {
        return self::LF
            . '/**' . self::LF
            . ' * ' . implode(self::LF . ' * ', $lines) . self::LF
            . ' */'
            ;
    }
}
