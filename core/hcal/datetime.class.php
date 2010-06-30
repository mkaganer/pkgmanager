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
    
    public function set_lang($lang=null) {
        if (empty($lang)) $this->lang_output = hcal_lang_output::get_default();
        elseif ($lang instanceof hcal_lang_output) $this->lang_output = $lang;
        else $this->lang_output = new hcal_lang_output($lang);
    } 
    
    public function set_strtotime($str) {
        $datetime = new DateTime($str,$this->location->timezone);
        $data = explode('|',$datetime->format('Y|n|j|G|i|s'));
        foreach($data as $i => $_d) $data[$i] = intval(ltrim($_d,'0'));
        $this->h = intval($data[3]); $this->m = intval($data[4]); $this->s = intval($data[5]);
        $this->jd = gregoriantojd($data[1],$data[2],$data[0]);
    }
    
    public function set_civil_date($m,$d,$y) {
        $this->jd = gregoriantojd($m,$d,$y);
    }
    
    public function set_hebrew_date_numeric($hd) {
        $is_leap = self::is_leap_year($hd[2]);
        if ((($hd[0]==6)||($hd[0]==7))||($hd[0]==14)||($hd[0]==15)) {
            if (!$is_leap) $hd[0] = 6;
            elseif ($hd[0]==15) $hd[0] = 7;
            else $hd[0] = 6;
        }
        $jd = jewishtojd($hd[0],$hd[1],$hd[2]);
        if ($jd>0) $this->jd = $jd; 
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
    
    
    public function get_hebrew_date_numeric($htimes=null) {
        $jd = $this->jd;
        if (!empty($htimes)) {
            if ($htimes->now >= $htimes->sunset) $jd += 1; // jewish day begins with a sunset!
        }
        $data = explode('/',jdtojewish($jd));
        $is_leap = self::is_leap_year($data[2]);
        if ($is_leap && (($data[0]==6)||($data[0]==7))) $data[0] += 8;
        return $data;
    }
    
    /**
     * @desc Return a date as a Hebrew date in Jewish calendar
     * If $htimes is not null, specifies hcal_halachic_times object which is used to determine
     * if we're after the sunset and have to increase a day by 1
     * @param hcal_halachic_times $htimes
     * @param string $weekday type of weekday to add or null for no weekday ('full'|'short')
     * @return string
     */
    public function get_hebrew_date($weekday=null, $htimes=null) {
        $data = $this->get_hebrew_date_numeric($htimes);
        $res = '';
        if ($weekday) {
            $res .= $this->lang_output->weekday(($this->jd+1) % 7,$weekday).', ';
        }
        $res .= $this->lang_output->hebrew_date($data);
        return $res;
    }
    
    public function get_weekday($type='full') {
        $wd = ($this->jd+1) % 7;
        return $this->lang_output->weekday($wd,$type);
    }
    
    /**
     * @return int time offset from GMT in seconds
     */
    public function get_timezone_offset(&$is_dst=null) {
        $tz = $this->location->timezone;
        $datetime = new DateTime($this->get_sql_datetime(),$tz);
        $loc_offset = $tz->getOffset($datetime);
        if (!is_null($is_dst)) {
            $y = intval($datetime->format('Y'));
            $datetime->setDate($y,1,1);
            $loc_off_1jan = $tz->getOffset($datetime);
            $is_dst = ($loc_offset!=$loc_off_1jan);
        }
        return $loc_offset;
    }
    
    /**
     * @desc Return true if the given Hebrew year is "leap" (has 13 months)
     * @param int $y
     */
    public static function is_leap_year($y) {
        // This formula is working, trust me 8-)
        return ((intval($y)*7+1) % 19)<7;
    }
    
    
    /**
     * @desc Calulate sunrise and sunset for the given date and location and create an object
     * that's responsible for calculating halactic times for the given date
     * All hours calculated in the local time zone specified by $this->location
     * @return hcal_halachic_times
     */
    public function get_htimes() {
        return new hcal_halachic_times($this);
    }
    
}