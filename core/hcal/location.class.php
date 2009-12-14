<?php
// B.H.

/**
 * @author mkaganer
 * @desc represents a geographic location in a context of Jewish Halactic times calculations
 * (coordinates, timezone, daylight saving on/off, is it Eretz Israel of Chul) 
 */
class hcal_location {
    
    /**
     * @desc Timezone object
     * @var DateTimeZone
     */
    public $timezone;
    
    public $latitude;
    public $longitude;

    /**
     * @param array $location_info
     */
    public function __construct($location_info) {
        if (empty($location_info['lat'])||empty($location_info['long'])||
            empty($location_info['tz'])) throw new Exception('Location info is wrong');
        $this->latitude = (float)$location_info['lat'];
        $this->longitude = (float)$location_info['long'];
        $this->timezone = new DateTimeZone($location_info['tz']);
    }
    
}