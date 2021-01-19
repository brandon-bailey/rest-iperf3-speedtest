<?php

defined('BASEPATH') or exit('No direct script access allowed');
use GeoIp2\Database\Reader;

class Lookup_model extends CI_Model
{
    private $reader;
    public function __construct()
    {
        parent::__construct();
        $this->reader = new Reader($this->config->item('geoip_database'));
    }
    
    public function getLocationByAddress($address)
    {
        $record = $this->reader->city($address);
        $location = new StdClass();
        $location->city = $record->city->name;
        $location->state = $record->mostSpecificSubdivision->isoCode;
        $location->country = $record->country->isoCode;
        $location->zip = $record->postal->code;
        $location->longitude = $record->location->longitude;
        $location->latitude = $record->location->latitude;
        return $location;
    }

/**
 * Calculates the great-circle distance between two points, with
 * the Vincenty formula.
 * @param float $latitudeFrom Latitude of start point in [deg decimal]
 * @param float $longitudeFrom Longitude of start point in [deg decimal]
 * @param float $latitudeTo Latitude of target point in [deg decimal]
 * @param float $longitudeTo Longitude of target point in [deg decimal]
 * @param float $earthRadius Mean earth radius in [m]
 * @return float Distance between points in [m] (same as earthRadius)
 */
    public static function vincentyGreatCircleDistance(
        $latitudeFrom,
        $longitudeFrom,
        $latitudeTo,
        $longitudeTo,
        $earthRadius = 6371000
    ) {
    
        // convert from degrees to radians
          $latFrom = deg2rad($latitudeFrom);
        $lonFrom = deg2rad($longitudeFrom);
        $latTo = deg2rad($latitudeTo);
        $lonTo = deg2rad($longitudeTo);

        $lonDelta = $lonTo - $lonFrom;
        $a = pow(cos($latTo) * sin($lonDelta), 2) +
        pow(cos($latFrom) * sin($latTo) - sin($latFrom) * cos($latTo) * cos($lonDelta), 2);
        $b = sin($latFrom) * sin($latTo) + cos($latFrom) * cos($latTo) * cos($lonDelta);

        $angle = atan2(sqrt($a), $b);
        return $angle * $earthRadius;
    }
}