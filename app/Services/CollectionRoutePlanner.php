<?php

namespace App\Services;

class CollectionRoutePlanner
{
    /**
     * Build a nearest-neighbour sequence from the depot using straight-line distances.
     */
    public function plan(iterable $bins, float $startLatitude, float $startLongitude): array
    {
        $remaining = [];
        foreach ($bins as $bin) {
            $remaining[] = $bin;
        }

        $stops = [];
        $totalDistance = 0.0;
        $latitude = $startLatitude;
        $longitude = $startLongitude;

        while ($remaining !== []) {
            $nearestIndex = null;
            $nearestDistance = null;

            foreach ($remaining as $index => $bin) {
                $distance = $this->distance(
                    $latitude,
                    $longitude,
                    (float) $bin->latitude,
                    (float) $bin->longitude,
                );

                if ($nearestDistance === null || $distance < $nearestDistance) {
                    $nearestIndex = $index;
                    $nearestDistance = $distance;
                }
            }

            $bin = $remaining[$nearestIndex];
            $legDistance = round($nearestDistance, 2);
            $stops[] = ['bin' => $bin, 'distance_km' => $legDistance];
            $totalDistance += $nearestDistance;
            $latitude = (float) $bin->latitude;
            $longitude = (float) $bin->longitude;
            array_splice($remaining, $nearestIndex, 1);
        }

        return [
            'stops' => $stops,
            'total_distance_km' => round($totalDistance, 2),
        ];
    }

    private function distance(float $latitudeA, float $longitudeA, float $latitudeB, float $longitudeB): float
    {
        $earthRadiusKm = 6371;
        $latitudeDelta = deg2rad($latitudeB - $latitudeA);
        $longitudeDelta = deg2rad($longitudeB - $longitudeA);
        $a = sin($latitudeDelta / 2) ** 2
            + cos(deg2rad($latitudeA)) * cos(deg2rad($latitudeB)) * sin($longitudeDelta / 2) ** 2;

        return $earthRadiusKm * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
}
