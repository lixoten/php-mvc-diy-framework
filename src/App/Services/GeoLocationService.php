<?php

declare(strict_types=1);

namespace App\Services;

use Core\Exceptions\ServiceException;

/**
 * GeoLocationService
 *
 * Provides geolocation information based on IP address.
 */
class GeoLocationService
{
    /**
     * Lookup geolocation data for a given IP address.
     *
     * @param string $ip
     * @return array<string, mixed>
     * @throws ServiceException If the lookup fails or returns invalid data.
     */
    public function getLocationFromIp(string $ip): array
    {
        $url = 'http://ip-api.com/json/' . urlencode($ip);
        $response = @file_get_contents($url);

        if ($response === false) {
            throw new ServiceException('Failed to fetch geolocation data.');
        }

        /** @var array<string, mixed>|null $data */
        $data = json_decode($response, true);

        if (!is_array($data) || !isset($data['status']) || $data['status'] !== 'success') {
            throw new ServiceException('Invalid geolocation response.');
        }

        // Return only relevant fields
        return [
            'country'    => $data['country'] ?? null,
            'countryCode'=> $data['countryCode'] ?? null,
            'region'     => $data['region'] ?? null,
            'regionName' => $data['regionName'] ?? null,
            'city'       => $data['city'] ?? null,
            'zip'        => $data['zip'] ?? null,
            'lat'        => $data['lat'] ?? null,
            'lon'        => $data['lon'] ?? null,
            'timezone'   => $data['timezone'] ?? null,
            'isp'        => $data['isp'] ?? null,
            'query'      => $data['query'] ?? null, // IP address
        ];
    }
}