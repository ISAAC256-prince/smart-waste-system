<?php

namespace Tests\Unit;

use App\Models\Bin;
use App\Services\CollectionRoutePlanner;
use PHPUnit\Framework\TestCase;

class CollectionRoutePlannerTest extends TestCase
{
    public function test_it_orders_stops_by_the_nearest_next_bin(): void
    {
        $bins = [
            new Bin(['bin_code' => 'FAR', 'latitude' => 0.4, 'longitude' => 32.6]),
            new Bin(['bin_code' => 'NEAR', 'latitude' => 0.314, 'longitude' => 32.582]),
            new Bin(['bin_code' => 'NEXT', 'latitude' => 0.32, 'longitude' => 32.582]),
        ];

        $route = (new CollectionRoutePlanner())->plan($bins, 0.3136, 32.5811);

        $this->assertSame(['NEAR', 'NEXT', 'FAR'], array_map(
            fn (array $stop) => $stop['bin']->bin_code,
            $route['stops'],
        ));
        $this->assertGreaterThan(0, $route['total_distance_km']);
    }

    public function test_it_returns_an_empty_route_when_there_are_no_bins_to_collect(): void
    {
        $route = (new CollectionRoutePlanner())->plan([], 0.3136, 32.5811);

        $this->assertSame([], $route['stops']);
        $this->assertSame(0.0, $route['total_distance_km']);
    }
}
