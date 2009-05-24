<?php
// B.H.

class html_tpl_c_openx extends html_tpl_c_elm {

  private $zone;

  public function __construct($zone) {
    $this->zone = $zone;
  }
  
  public function render($block) {
    return html_openx::get_zone_html($this->zone);
  }
}
