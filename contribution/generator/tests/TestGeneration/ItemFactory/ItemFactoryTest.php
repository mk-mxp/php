<?php

namespace App\Tests\TestGeneration\Group;

use App\Tests\TestGeneration\ScenarioFixture;
use App\TrackData\ItemFactory;
use App\TrackData\Item;
use App\TrackData\Unknown;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;

#[TestDox('ItemFactory (App\Tests\TestGeneration\ItemFactory\ItemFactoryTest)')]
final class ItemFactoryTest extends PHPUnitTestCase
{
    use ScenarioFixture;

    #[Test]
    public function detectsUnknown(): void
    {
        $scenario = 'empty-object';
        $input = $this->rawDataFor($scenario);

        $subject = new ItemFactory();
        $actual = $subject->from($input);

        $this->assertInstanceOf(Item::class, $actual);
        $this->assertInstanceOf(Unknown::class, $actual);
    }
}
