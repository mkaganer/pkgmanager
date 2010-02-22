<?php
// B.H.

/**
 * @author mkaganer
 * @desc This class represents a point in time (date and time), associated with a location (see hcal_location)
 * so it is possible to calculate a hebrew date and the Halactic times for that day
 */
class hcal_datetime {

    /**
     * @var int
     */
    public $jd;
    
    // the time is stored in a local time zone
    public $h,$m,$s;
    
    /**
     * @var hcal_location
     */
    public $location;
    
    /**
     * @var hcal_lang_output
     */
    public $lang_output;
    
    /**
     * @param string $strtime
     * @param hcal_location $location
     * @param mixed $lang (optional) string or hcal_lang_output
     */
    public function __construct($strtime,$location,$lang=null) {
        if (empty($strtime)) $strtime = 'now';
        if (empty($location)) throw new Exception('Location is empty');
        if (empty($lang)) $this->lang_output = hcal_lang_output::get_default();
        elseif ($lang instanceof hcal_lang_output) $this->lang_output = $lang;
        else $this->lang_output = new hcal_lang_output($lang);
        $this->location = $location;
        $this->set_strtotime($strtime);
    }
    
    public function set_strtotime($str) {
        $datetime = new DateTime($str,$this->location->timezone);
        $data = explode('|',$datetime->format('Y|n|j|G|i|s'));
        foreach($data as $i => $_d) $data[$i] = intval(ltrim($_d,'0'));
        $this->h = $data[3]; $this->m = $data[4]; $this->s = $data[5];
        $this->jd = gregoriantojd($data[1],$data[2],$data[0]);
    }
    
    public function get_jd() {
        return $this->jd;
    }
    
    /**
     * @desc move the date in the object $dd days forward/backward
     * @param int $dd
     * @return void
     */
    public function move_days($dd) {
        $this->jd += $dd;
    }
    
    /**
     * @desc get unix timestamp for the date and time (this is NOT in GMT)
     * @return int
     */
    public function get_ts($gmt=true) {
        $ts = jdtounix($this->jd)+($this->h*3600)+($this->m*60)+$this->s;
        if ($gmt) $ts -= $this->get_timezone_offset();
        return $ts;
    }
    
    /**
     * @return string
     */
    public function get_sql_datetime() {
        $date = explode('/',jdtogregorian($this->jd)); //m/d/y
        foreach($date as $i => $_d) $date[$i] = intval(ltrim($_d,'0'));
        return sprintf("%04d-%02d-%02d %02d:%02d:%02d",$date[2],$date[0],$date[1],
            $this->h,$this->m,$this->s);
    }
    
    public function set_sql_datetime($sql_datetime) {
        // AFAIK, this is OK....
        $this->set_strtotime($sql_datetime);
    }
    
    public function format_date($format) {
        $dt = new DateTime($this->get_sql_datetime(),$this->location->timezone);
        return $dt->format($format);
    }
    
    /**
     * @desc Return a date as a Hebrew date in Jewish calendar
     * If $htimes is not null, specifies hcal_halactic_times object which is used to determine
     * if we're after the sunset and have to increase a day by 1
     * @param hcal_halactic_times $htimes
     * @param string $weekday type of weekday to add or false for no weekday
     * @return string
     */
    public function get_hebrew_date($weekday=false, $htimes=null) {
        $jd = $this->jd;
        if (!empty($htimes)) {
            if ($htimes->now >= $htimes->sunset) $jd += 1; // jewish day begins with a sunset!
        }
        $data = explode('/',jdtojewish($jd));
        $is_leap = self::is_leap_year($data[2]);
        $res = '';
        if ($weekday) {
            $res .= $this->lang_output->weekday(($jd+1) % 7,$weekday).', ';
        }
        $res .= $this->lang_output->hebrew_date($data,$is_leap);
        return $res;
    }
    
    public function get_weekday($type='full') {
        $wd = ($this->jd+1) % 7;
        return $this->lang_output->weekday($wd,$type);
    }
    
    /**
     * @return int time offset from GMT in seconds
     */
    public function get_timezone_offset() {
        $datetime = new DateTime($this->get_sql_datetime(),$this->location->timezone);
        $loc_offset = $this->location->timezone->getOffset($datetime);
        return $loc_offset;
    }
    
    public static function is_leap_year($y) {
        return ((intval($y)*7+1) % 19)<7;
    }
    
    
    /**
     * @desc Calulate sunrise and sunset for the given date and location and create an object
     * that's responsible for calculating halactic times for the given date
     * All hours calculated in the local time zone specified by $this->location
     * @return hcal_halactic_times
     */
    public function get_htimes() {
        $ts = $this->get_ts(false);
        $tz_offset = $this->get_timezone_offset();
        $ts -= $tz_offset;
        $gmt_offset = $tz_offset / 3600.0;
        
        $now = $this->h + ($this->m/60.0) + ($this->s)/3600.0;
        $jd = $this->jd;
        //if ($now >= $sunset) $jd += 1; // jewish day begins with a sunset!
        $heb_date = explode('/',jdtojewish($jd));
        
        $htimes = new hcal_halactic_times($heb_date, $ts, $gmt_offset, $this->location, $now, $this);
        return $htimes;
    }
    
}