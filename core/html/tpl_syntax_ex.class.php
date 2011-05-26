<?php
// B.H.

class html_tpl_syntax_ex extends Exception {
  public $tpl;
  public function __construct($tpl,$message) {
    $this->tpl = $tpl;
    parent::__construct("$message [template:$tpl]");
  }
}
?>
