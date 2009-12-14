<?php
// B.H.

class hcal_location_factory {
    
    private static $locations = array(
        'jerusalem' => array('lat'=>1.0, 'long'=>1.0, 'tz'=>'Asia/Jerusalem' ),
        'tzfat' => array('lat'=>1.0, 'long'=>1.0, 'tz'=>'Asia/Jerusalem' ),
        'tel_aviv' => array('lat'=>0.0, 'long'=>0.0, 'tz'=>'Asia/Jerusalem' ),
        'beer_sheva' => array('lat'=>0.0, 'long'=>0.0, 'tz'=>'Asia/Jerusalem' ),
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