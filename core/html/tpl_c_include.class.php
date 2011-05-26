<?php
// B.H.

class html_tpl_c_include extends html_tpl_c_elm {

  private $tpl;

  public function __construct(html_tpl_compiled $tpl) {
    $this->tpl = $tpl;
  }
  
  public function render($block) {
    return $this->tpl->apply($block);
  }
}
