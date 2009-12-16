<?php
// B.H.

class hcal_location_factory {
    
    // lat = +N/-S = קו רוחב
    // long = -W/+E = קו אורך 
    
    private static $locations = array(
        'jerusalem' => array('lat' => 31.766666667 , 'long' => 35.23333333, 'tz'=>'Asia/Jerusalem'),
        'tzfat' => array('lat'=> 32.9833333333, 'long'=>35.483333333, 'tz'=>'Asia/Jerusalem' ),
        'tel_aviv' => array('lat' => 32.05, 'long' => 34.75, 'tz'=>'Asia/Jerusalem' ),
        'beer_sheva' => array('lat' => 31.25, 'long' => 34.783333333, 'tz'=>'Asia/Jerusalem' ),
        'new_york' => array('lat' => 40.66909166667, 'long' => -73.94282222222, 'tz'=>'America/New_York' ),
    );
    
    public static function get($loc_name=null) {
        if (empty($loc_name)) {
            $hcal = pkgman_manager::getp('hcal');
            $loc_name = $hcal->config['location'];
        }
        if (empty(self::$locations[$loc_name])) return false;
        return new hcal_location(self::$locations[$loc_name]);
    }
    
}