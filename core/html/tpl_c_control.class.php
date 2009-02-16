<?php
// B.H.

class html_tpl_c_control {
  public $op; // operator name
  public $level_incr;
  public $param;
  // (html_tpl_compiled)$tpl
  public function __construct($tpl,$op,$param,&$ops) {
    $this->op = $op;
    $this->param = $param;
    switch($op) {
    case 'ifdef':
    case 'foreach':
      $this->level_incr = 1;
      $ops[] = $op;
      break;
    case 'else':
      if (end($ops)!='ifdef') throw new html_tpl_syntax_ex($tpl->name,"else not inside ifdef");
      $this->level_incr = 0;
      break;
    case 'delim':
      if (end($ops)!='foreach') throw new html_tpl_syntax_ex($tpl->name,"delim not inside foreach");
      $this->level_incr = 0;
      break;
    case 'end':
      $this->level_incr = -1;
      if (empty($ops)) throw new html_tpl_syntax_ex($tpl->name,"Unexpected token: [$op]");
      array_pop($ops);
      break;
    default:
      throw new html_tpl_syntax_ex($tpl->name,'Unrecognized operator ${:'.$op.'}');
    }
  }
}
?>
