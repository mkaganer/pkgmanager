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
    private $jd;
    
    private $h,$m,$s;
    
    /**
     * @var hcal_location
     */
    private $location;
    
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
        $datetime = new DateTime($str);
        $datetime->setTimezone($this->location->timezone);
        $data = explode('|',$datetime->format('Y|n|j|G|i|s'));
        foreach($data as $i => $_d) $data[$i] = intval(ltrim($_d,'0'));
        $this->h = $data[3]; $this->m = $data[4]; $this->s = $data[5];
        $this->jd = gregoriantojd($data[1],$data[2],$data[0]);
    }
    
    public function get_jd() {
        return $this->jd;
    }
    
    /**
     * @return string
     */
    public function get_sql_datetime() {
        $date = explode('/',jdtogregorian($this->jd)); //m/d/y
        foreach($date as $i => $_d) $date[$i] = intval(ltrim($_d,'0'));
        //var_dump($date);
        return sprintf("%04d-%02d-%02d %02d:%02d:%02d",$date[2],$date[0],$date[1],
            $this->h,$this->m,$this->s);
    }
    
    public function set_sql_datetime($sql_datetime) {
        // AFAIK, this is OK....
        $this->set_strtotime($sql_datetime);
    }
    
    public function format_date($format) {
        $dt = new DateTime($this->get_sql_datetime());
        return $dt->format($format);
    }
    
    public function get_hebrew_date() {
        $data = explode('/',jdtojewish($this->jd));
        $is_leap = self::is_leap_year($data[2]);
        //$is_leap = (($data[2]*7+1) % 19)<7;
        return $this->lang_output->hebrew_date($data,$is_leap);
    }
    
    public function get_weekday() {
        $wd = ($this->jd+1) % 7;
        return $this->lang_output->weekday_full($wd);
    }
    
    public static function is_leap_year($y) {
        return ((intval($y)*7+1) % 19)<7;
    }
}