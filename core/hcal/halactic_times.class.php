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
    public $rel_hr;
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
    
    /**
     * @var hcal_location
     */
    public $location;
    
    /**
     * @desc true if chutz laaretz
     * @var bool
     */
    public $is_chul;
    
    /**
     * @desc base values for times calculations in float format
     * @var array (float)
     */
    private $_base;
    
    /**
     * @desc Caculated times will be stored here in string format (HH:MM)
     * @var array
     */
    public $data;
    
    /**
     * @var hcal_datetime
     */
    public $datetime;
    
    /**
     * @desc Hebrew dates when is_kodesh will be true format: 'month/day'
     * 'eretz' is Eretz Israel (ארץ ישראל)
     * 'chul' is Chuts laaretz (חוץ לארץ)
     * @var array(string)
     */
    private static $kodesh_dates = array(
        'eretz' => array(
            '1/1', '1/2', '1/10', '1/15', '1/22', // Tishrey festivals
            '8/15', '8/21', // Pesach
            '10/6' // Shavuot
        ),
        'chul' => array(
            '1/1', '1/2', '1/10', '1/15', '1/16', '1/22', '1/23', // Tishrey festivals
            '8/15', '8/16', '8/21', '8/22', // Pesach
            '10/6', '10/7' // Shavuot
        ),
    );
    
    public function __construct($heb_date, $ts, $gmt_offset, $location, $now, $datetime =  null) {
        $zenith = 90.0 + 50.0/60;
        $this->heb_date = $heb_date;
        $this->location = $location;
        $this->now = $now;
        $this->data = array();
        $this->datetime = $datetime;
        
        $lat = $location->latitude;
        $long = $location->longitude;
        $sunrise = date_sunrise($ts, SUNFUNCS_RET_DOUBLE, $lat, $long, $zenith, $gmt_offset);
        $sunset = date_sunset($ts, SUNFUNCS_RET_DOUBLE, $lat, $long, $zenith, $gmt_offset);
        
        $this->sunrise = $sunrise;
        $this->sunset = $sunset;
        $this->rel_hr = ($sunset - $sunrise)/12.0;
        $this->is_chul = (bool)@$this->location->data['chul'];
        
        $this->calculate_base_times();
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
    public function rel_hour($hours,$d=0.0) {
        $time = $this->sunrise+$this->rel_hr*$hours+$d;
        return $this->format($time);
    }
    
    /**
     * @return void
     */
    private function calculate_base_times() {
        $this->_base = array(
            'amud72' => ($this->sunrise - (72.0/60.0)),
            'amud90' => ($this->sunrise - 1.5),
            'amud120' => ($this->sunrise - 2.0),
            'tzet18' => ($this->sunset + (18.0/60.0)),
            'tzet24' => ($this->sunset + (24.0/60.0)),
        );
    }
    
    public function get_data() {
        if (!empty($this->data)) return $this->data;
        
        // see: http://www.yeshiva.org.il/message/times.asp
        
        $mn = 1.0/60.0; // one absolute minute
        $rel_hr = $this->rel_hr;
        
        // שעה זמנית לפי מגן אברהם
        $rel_hr_ma = ($this->_base['tzet18'] - $this->_base['amud72']) / 12.0;
        
        $chatsot = ($this->sunset + $this->sunrise)/2;
        
        $data = array();
        $data['sunrise'] = $this->format($this->sunrise);
        $data['sunset'] = $this->format($this->sunset);
        $data['amud72'] = $this->format($this->_base['amud72']+3*$mn);
        $data['amud120'] = $this->format($this->_base['amud120']);
        $data['tzet24'] = $this->format($this->_base['tzet24']);
        $data['tzet18'] = $this->format($this->_base['tzet18']);
        
        $data['chatsot'] = $this->format($chatsot);
        
        // מנחה גדולה
        // חצות + 1/2 שעה לחומרה
        $mincha1 = $chatsot + max(0.5*$rel_hr,0.5);
        $data['mincha1'] = $this->format($mincha1 + 3*$mn);
        
        // מנחה קטנה
        $data['mincha2'] = $this->rel_hour(12.0 - 2.5,2*$mn);
        
        // פלג מנחה
        $data['plag'] = $this->rel_hour(10.75,3.0*$mn);
        
        // משיכיר
        // 45 דקות לפני הזריחה לחומרה
        $misheyakir = max(($this->sunrise - (45.0/60.0)),
            ($this->sunrise - $rel_hr*(45.0/60.0)));
        $data['misheyakir'] = $this->format($misheyakir + 3*$mn);
        
        // Baal HaTaniya (שעה"ר):
        $data['smah'] = $this->rel_hour(3.0,-3*$mn);
        $data['tfilah'] = $this->rel_hour(4.0,-3*$mn);
        
        // Magen Avraham:
        $shma_ma = $this->_base['amud72'] + 3.0*$rel_hr_ma;
        $tfila_ma = $this->_base['amud72'] + 4.0*$rel_hr_ma;
        $data['smah_ma'] = $this->format($shma_ma-3*$mn);
        $data['tfilah_ma'] = $this->format($tfila_ma-3*$mn);
        
        // Shabbat:
        $nerot = (float)(isset($this->location->data['nerot'])?$this->location->data['nerot']:22.0);
        $data['sh_nerot'] = $this->format($this->sunset - $nerot*$mn);
        $data['sh_tzet'] = $this->format($this->sunset + 40.0*$mn);
        $data['sh_tzet_rt'] = $this->format($this->sunset + 72.0*$mn);
        
        $this->data = $data;
        return $data;
    }
    
    private function is_jd_kodesh($jd) {
        $wd = ($jd + 1) % 7;
        if ($wd==6) return true; // Shabbes
        $d = explode('/',jdtojewish($jd));
        $d = intval($d[0]).'/'.intval($d[1]);
        $chul = $this->is_chul?'chul':'eretz';
        if (in_array($d,self::$kodesh_dates[$chul])) return true;
        return false;
    }
    
    /**
     * @desc Check if the current time is forbidden to do "melachah": Shabbat or Yom Tov
     * @return bool
     */
    public function is_kodesh() {
        $jd = $this->datetime->jd;
        if ($this->now >= $this->sunset) $jd += 1;
        //echo $this->format($this->now);
        if ($this->is_jd_kodesh($jd)) return true;
        // check "Tosefet Shabbat" 18 minutes before
        if (($this->now < $this->sunset) && ($this->now >= ($this->sunset-18.0/60.0))) {
            if ($this->is_jd_kodesh($jd+1)) return true;
        }
        // check "Tosefet Shabbat" 40 minutes after
        if (($this->now > $this->sunset) && ($this->now <= ($this->sunset+40.0/60.0))) {
            if ($this->is_jd_kodesh($jd-1)) return true;
        }
        return false;
    }
    
    /**
     * @desc Returns current Sfirat HaOmer count or 0 if not in Sfirat HaOmer
     * @return int
     */
    public function get_omer() {
        $jd = $this->datetime->jd;
        if ($this->now >= $this->sunset) $jd += 1;
        // Get the JD of 15 Nisan of the current year
        $omer_jd = jewishtojd(8,15,$this->heb_date[2]);
        $omer_cnt = $jd - $omer_jd;
        if (($omer_cnt<1) || ($omer_cnt>49)) return 0;
        return $omer_cnt;
    }
    
    /**
     * @desc Return Omer's count as Hebrew text according to Nusach Ari
     * @return string of false if not in Sfirat HaOmer 
     */
    public function get_omer_txt() {
        $omer_cnt = $this->get_omer();
        if ($omer_cnt<1) return false;
        
        $omer_cnt_mod = $omer_cnt % 7;
        $omer_week = intval($omer_cnt / 7);
        
        if ($omer_cnt==1) $omer_txt = "היום יום 1 לעומר";
        elseif ($omer_cnt<7) $omer_txt = "היום $omer_cnt ימים לעומר";
        else {
            $yom = ($omer_cnt<11)?"ימים":
                "יום";
            $shavua = ($omer_week==1)?"שבוע 1":
                "$omer_week שבועות";
            $omer_txt = "היום $omer_cnt $yom שהם $shavua";
            if ($omer_cnt_mod==1) $omer_txt .= " ויום 1";
            elseif ($omer_cnt_mod>1) $omer_txt .= " ו-$omer_cnt_mod ימים";
            $omer_txt .= " לעומר";
        }
        return $omer_txt;
    }
    
    
}
