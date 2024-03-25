<?php

declare(strict_types=1);

namespace App\TrackData;

use App\TrackData\CanonicalData;
use App\TrackData\InnerGroup;
use App\TrackData\Item;
use App\TrackData\Unknown;

/**
 * Produces Item instances of whatever type is possible
 */
class ItemFactory
{
    public function from(mixed $rawData): Item
    {
        $case = CanonicalData::from($rawData);
        // $case = TestCase::from($rawData);
        if ($case === null)
            $case = Group::from($rawData);
        if ($case === null)
            $case = InnerGroup::from($rawData);
        if ($case === null)
            $case = Unknown::from($rawData);
        return $case;
    }
}
