<?php
// B.H.

class html_tpl_c_url extends html_tpl_c_elm {

  private $url;

  public function __construct($url) {
    $this->url = $url;
  }
  
  public function render($block) {
    return _url($this->url);
  }
}
