<?php

namespace App\Services;

use Illuminate\Support\Collection;

class RoutePlannerService
{
    public function normalizeCoordinates(array $decodedCoordinates): Collection
    {
        return collect($decodedCoordinates)
            ->map(function ($point): ?array {
                if (!is_array($point) || !isset($point['lat'], $point['lng'])) {
                    return null;
                }

                $lat = (float) $point['lat'];
                $lng = (float) $point['lng'];

                if ($lat < -90 || $lat > 90 || $lng < -180 || $lng > 180) {
                    return null;
                }

                return [
                    'lat' => round($lat, 7),
                    'lng' => round($lng, 7),
                ];
            })
            ->filter()
            ->values();
    }

    public function calculateRouteDistance(array $coordinates): float
    {
        $distance = 0.0;

        for ($index = 1; $index < count($coordinates); $index++) {
            $lat1 = deg2rad((float) $coordinates[$index - 1]['lat']);
            $lon1 = deg2rad((float) $coordinates[$index - 1]['lng']);
            $lat2 = deg2rad((float) $coordinates[$index]['lat']);
            $lon2 = deg2rad((float) $coordinates[$index]['lng']);

            $deltaLat = $lat2 - $lat1;
            $deltaLon = $lon2 - $lon1;

            $a = sin($deltaLat / 2) ** 2
                + cos($lat1) * cos($lat2) * sin($deltaLon / 2) ** 2;
            $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

            $distance += 6371 * $c;
        }

        return round($distance, 2);
    }
}
