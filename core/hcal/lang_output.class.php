<?php
// B.H.

/**
 * @author mkaganer
 * @desc Helper class - provides methods to convert numbers to a Hebrew letter representation
 * Also contains names for Hebrew month (in Hebrew and English)
 * Note: all strings are in UTF-8
 * mb_string extention is required to use if $this->gereshaim=true
 */
class hcal_lang_output {
    
    public static $heb_numbers = array(
        0=> '',
        1=> 'א',
        2=> 'ב', 
        3=> 'ג',
        4=> 'ד', 
        5=> 'ה', 
        6=> 'ו',
        7=> 'ז', 
        8=> 'ח', 
        9=> 'ט',
        15=> 'טו',
        16=> 'טז',
        10=> 'י',
        20=> 'כ', 
        30=> 'ל',
        40=> 'מ', 
        50=> 'נ', 
        60=> 'ס',
        70=> 'ע', 
        80=> 'פ', 
        90=> 'צ',
        100=> 'ק',
        200=> 'ר',
        300=> 'ש',
        400=> 'ת',
        500=> 'תק',
        600=> 'תר',
        700=> 'תש',
        800=> 'תת',
        900=> 'תתק',
    );
    
    public static $heb_months = array(
        'he' => array(
            'תשרי', 'חשוון', 'כסלו', 'טבת', 'שבט', 'אדר', 'אדר', 'ניסן',
            'אייר', 'סיון', 'תמוז', 'אב', 'אלול',
            'אדר א\'',
            'אדר ב\'',
        ),
        'en' => array(
            'Tishrey', 'Cheshvan', 'Kislev', 'Tevet', 'Shvat', 'Adar', 'Adar', 'Nisan',
            'Iyar', 'Sivan', 'Tammuz', 'Av', 'Elul',
            'Adar I', 'Adar II',
        ),
    );
    
    public static $week_days_full = array(
        'he' => array('יום ראשון', 'יום שני', 'יום שלישי', 'יום רביעי', 'יום חמישי', 'יום שישי', 'מוצאי ש"ק'),
        'en' => array('Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'),
    );
    
    /**
     * @desc Language code for output. Default is 'he'.
     * If not 'he' will output normal arabic numbers instead of hebrew notation
     * Also affects Hebrew months names output
     * @var string
     */
    public $lang;
    
    /**
     * @desc add " and ' to the numbers like תשס"ב ל' ט"ז
     * @var bool
     */
    public $gereshaim;
    
    /**
     * @desc show number of thousands before the number like ה' תש"ע=5770
     * @var bool
     */
    public $alafim;
    
    public static function get_default() {
        static $inst = null;
        if (!empty($inst)) return $inst; 
        $hcal = pkgman_manager::getp('hcal');
        $inst = new hcal_lang_output($hcal->config['lang']);
        return $inst;
    } 
    
    public function __construct($lang='he',$gereshaim=true,$alafim=false) {
        $this->lang = $lang;
        $this->gereshaim = $gereshaim;
        $this->alafim = $alafim;
    }
    
    /**
     * @param int $number
     * @return string
     */
    public function num_to_hebrew($number) {
        if ($this->lang!='he') return $number;
        $number = intval($number);
        if (empty($number)) return '';
        // convert numbers from 1 to 999. Add alafim by recursion
        $num = intval($number) % 1000;
        $res = '';
        $m100 = $num % 100;
        $m1 = intval($m100 % 10);
        $m2 = intval($m100 - $m1);
        if (isset(self::$heb_numbers[$m100])) $res = self::$heb_numbers[$m100];
        else $res = self::$heb_numbers[$m2].self::$heb_numbers[$m1];
        if ($num>=100) $res = self::$heb_numbers[intval($num - $m100)].$res;
        
        if ($this->gereshaim) {
            $len = mb_strlen($res,'utf-8');
            if ($len==1) $res.="'";
            elseif ($len>1) $res = mb_substr($res,0,$len-1,'utf-8').'"'.mb_substr($res,$len-1,1,'utf-8');
        }
        if (empty($res) && ($number>=1000)) $res = $this->num_to_hebrew(intval($number/1000))." אלפים";
        elseif (($number>=1000) && $this->alafim) {
            $res = $this->num_to_hebrew(intval($number/1000))." ".$res;
        }
        return $res;
    }
    
    /**
     * @param int $month
     * @param bool $is_leap
     * @return string
     */
    public function month_to_str($month, $is_leap) {
        $month = intval($month)-1;
        if ($is_leap && (($month==5)||($month==6))) $month += 8;
        $lang = $this->lang;
        if (!isset(self::$heb_months[$lang])) $lang = 'en';
        return self::$heb_months[$lang][$month];
    }
    
    public function weekday_full($weekday_num) {
        return self::$week_days_full[$this->lang][$weekday_num];
    }
    
    public function hebrew_date($data,$is_leap) {
        $year = $this->num_to_hebrew($data[2]);
        $month = $this->month_to_str($data[0],$is_leap);
        $day = $this->num_to_hebrew($data[1]);
        if ($this->lang=='he') return "$day ב$month $year";
        else return "$day $month $year";
    }
}