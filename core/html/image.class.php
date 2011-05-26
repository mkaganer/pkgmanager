<?php
// B.H.

class html_image extends html_element {

  // one of config['thumb_sizes'] values or null
  public $size;
   
  // phisical image dimentions (of the file) to be used in ratio calculations
  // note: this is not width/height attributes, use attr array 
  public $width,$height;

  // if true, on image rendering, will set width and height attributes
  // according to the resulting thumbnail's dimentions (to preserve the ratio)
  // 
  // (must set $this->width and $this->height for this to work)
  public $set_thumb_size = false;
  
  // $size is a code from 
  public function __construct($src,$alt="",$size=null) {
    parent::__construct('img');
    $this->attr['src'] = $src;
    $this->attr['alt'] = $alt;
    $this->attr['border'] = 0;
    $this->size = $size;
  }
  
  private function render_flash($pkg,$size) {
    if (!isset($pkg->config['show_flash'])) return '';
    $src = $this->attr['src'];
    $w = (int)$this->attr['width'];
    $h = (int)$this->attr['height'];
    if (isset($pkg->config['thumb_sizes'][$size])) {
      $sz = $pkg->config['thumb_sizes'][$size];
      if (isset($sz['w'])) $w = $sz['w'];
      if (isset($sz['h'])) $h = $sz['h'];
    }
    $bg_color = isset($this->attr['flash_bg'])?$this->attr['flash_bg']:'#ffffff';
    $bg_mode = isset($this->attr['flash_mode'])?$this->attr['flash_mode']:'transparent';
    return sprintf($pkg->config['show_flash'],$src,$w,$h,$bg_color,$bg_mode);
  }
  
  protected function render($param=null) {
    $pkg = self::get_pkg();
    $size = $this->size;
    if (isset($param[0])) $size = $param[0];
    if (preg_match('/\\.swf$/i',$this->attr['src'])) return $this->render_flash($pkg,$size);
    if (isset($pkg->config['thumb_sizes'][$size])) {
      $attr_save = $this->attr;
      $sz = $pkg->config['thumb_sizes'][$size];
      unset($this->attr['width']);
      unset($this->attr['height']);
      if (isset($sz['w'])) $this->attr['width'] = $sz['w'];
      if (isset($sz['h'])) $this->attr['height'] = $sz['h'];
      if ($this->set_thumb_size&&isset($this->width)&&isset($this->height)) {
        if ((!isset($this->attr['width']))&&(isset($sz['h']))) {
          $thumb_w = floor($this->width*($sz['h']/$this->height));
          $this->attr['width'] = $thumb_w;
        }
        if ((!isset($this->attr['height']))&&(isset($sz['w']))) {
          $thumb_h = floor($this->height*($sz['w']/$this->width));
          $this->attr['height'] = $thumb_h;
        }
      }
      $phpthumb = $pkg->config['phpthumb_url'];
      // display thumbnails using phpThumb if availiable
      if ((function_exists('phpThumbURL') || (!empty($phpthumb)))&&(!preg_match('/\\.gif$/i',$this->attr['src']))) {
        $pt_param = ''; foreach($sz as $k => $v) $pt_param .= "&${k}=${v}";
        if (function_exists('phpThumbURL')) {
          $this->attr['src'] = phpThumbURL('src='.urlencode($this->attr['src']).$pt_param);
        } else {
          $this->attr['src'] = $phpthumb.'?src='.urlencode($this->attr['src']).$pt_param;
        }
      }
      $res = parent::render();
      $this->attr = $attr_save;
      return $res;
    }
    return parent::render();
  }
}
?>