<?php
// B.H.

class hcal_halactic_times {
    
    /**
     * @var float
     */
    public $sunrise;
    /**
     * @var float
     */
    public $sunset;
    /**
     * @var float
     */
    public $rel_hour;
    /**
     * @desc Current time (set by hcal_datetime's h,m,s properties) in float hours
     * @var float
     */
    public $now;

    /**
     * @desc array(m,d,y) - hebrew date in _numeric_ values of month, day, year
     * @var array
     */
    public $heb_date;
    
    public function __construct($heb_date,$sunrise, $sunset, $rel_hour, $now) {
        $this->heb_date = $heb_date; 
        $this->sunrise = $sunrise;
        $this->sunset = $sunset;
        $this->rel_hour = $rel_hour;
        $this->now = $now;
    }
    
    /**
     * @param float $fl_time
     * @return string
     */
    public function format($fl_time) {
        $hr = floor($fl_time);
        $min = round(($fl_time - $hr)*60.0);
        return sprintf('%02d:%02d',intval($hr),intval($min));
    }
    
    /**
     * @desc Calcualte a time by the "shaa zmanit" measure. 0.0 is a sunrise, 12.0 is a sunset
     * 6.0 is "chatzot" and 3.0 is "sof kriat shmah"
     * @param float $hours "shot zmaniyuot"
     * @return string
     */
    public function rel_hour($hours) {
        $time = $this->sunrise+$this->rel_hour*$hours;
        return $this->format($time);
    }
    
    
}
