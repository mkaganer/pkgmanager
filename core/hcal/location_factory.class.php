<?php
// B.H.

class hcal_location_factory {
    
    // lat = +N/-S = קו רוחב
    // long = -W/+E = קו אורך 
    
    private static $locations = array(
        // Eretz HaKodesh:
        'jerusalem' => array('lat' => 31.766666667 , 'long' => 35.23333333, 'tz'=>'Asia/Jerusalem', 
            'nerot' => 40 ),
        'tzfat' => array('lat'=> 32.9833333333, 'long'=>35.483333333, 'tz'=>'Asia/Jerusalem' ),
        'kiryat_shmona' => array('lat'=>33.206667, 'long'=>35.570000, 'tz'=>'Asia/Jerusalem' ),
        'natzrat_ilit' => array('lat'=>32.711111, 'long'=>35.324722, 'tz'=>'Asia/Jerusalem' ),
        'mitzpe_ramon' => array('lat'=>30.610000, 'long'=>34.801944, 'tz'=>'Asia/Jerusalem' ),
        'dimona' => array('lat'=>31.069166, 'long'=>35.033333, 'tz'=>'Asia/Jerusalem' ),
        'sderot' => array('lat'=>31.522777, 'long'=> 34.595277, 'tz'=>'Asia/Jerusalem' ),
        'kiryat_gat' => array('lat'=>31.611111, 'long'=>34.768333, 'tz'=>'Asia/Jerusalem' ),
        'rechovot' => array('lat'=>31.8925, 'long'=>34.811111, 'tz'=>'Asia/Jerusalem' ),
        'hebron' => array('lat'=>31.524444, 'long'=>35.1111111, 'tz'=>'Asia/Jerusalem' ),
        'chadera' => array('lat'=>32.442777, 'long'=>34.920833, 'tz'=>'Asia/Jerusalem' ),
        'afula' => array('lat'=>32.610277, 'long'=>35.287777, 'tz'=>'Asia/Jerusalem' ),
        'netanya' => array('lat'=>32.321388, 'long'=>34.853055, 'tz'=>'Asia/Jerusalem' ),
        'ashkelon' => array('lat'=>31.665833, 'long'=>34.559444, 'tz'=>'Asia/Jerusalem' ),
        'jericho' => array('lat'=>31.856944, 'long'=>35.460555, 'tz'=>'Asia/Jerusalem' ),
        'beit_shemesh' => array('lat'=>31.745555, 'long'=>34.986388, 'tz'=>'Asia/Jerusalem' ),
        'tel_aviv' => array('lat' => 32.05, 'long' => 34.75, 'tz'=>'Asia/Jerusalem' ),
        'bney_brak' => array('lat' => 32.0783333, 'long' => 34.8422222, 'tz'=>'Asia/Jerusalem' ),
        'beer_sheva' => array('lat' => 31.25, 'long' => 34.783333333, 'tz'=>'Asia/Jerusalem' ),
        'eilat' => array('lat' => 29.554167, 'long' => 34.948056, 'tz'=>'Asia/Jerusalem',
            'chul' => false ),
        // (see http://www.adathisraelshul.org/rabbis-study/chagim/78-passover/211-the-halachic-status-of-eilat.html)

        'beitar_elit' => array('lat' => 31.697222, 'long' => 35.1225, 'tz'=>'Asia/Jerusalem' ),
        'gush_etzion' => array('lat' => 31.697222, 'long' => 35.1225, 'tz'=>'Asia/Jerusalem' ),
        'modiin_ilit' => array('lat' => 31.931667, 'long' => 35.043056, 'tz'=>'Asia/Jerusalem' ),
        'ashdod' => array('lat' => 31.7880556, 'long' => 34.64277778, 'tz'=>'Asia/Jerusalem' ),
        'tiberias' => array('lat' => 32.7863889, 'long' => 35.5425, 'tz'=>'Asia/Jerusalem' ),
        'haifa' => array('lat' => 32.826945, 'long' => 34.97694, 'tz'=>'Asia/Jerusalem' ),
        'ariel' => array('lat' => 32.094692, 'long' => 35.162799, 'tz'=>'Asia/Jerusalem' ),
    
        // US
        'new_york' => array('lat' => 40.66909166667, 'long' => -73.94282222222, 'tz'=>'America/New_York',
            'chul' => true ),
    );
    
    /**
     * @param string $loc_name
     * @return hcal_location
     */
    public static function get($loc_name=null) {
        if (empty($loc_name)) {
            $hcal = pkgman_manager::getp('hcal');
            $loc_name = $hcal->config['location'];
        }
        if (empty(self::$locations[$loc_name])) return false;
        return new hcal_location(self::$locations[$loc_name],$loc_name);
    }
    
}