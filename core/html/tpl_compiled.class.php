<?php
// B.H.

class html_tpl_compiled {

  public $tplman;
  public $name;
  
  public $current_path;

  private $tpl; // compiled template (array)
  private $ops;
  
  public function __construct($tplman,$tpl_name,$tpl) {
    $this->tplman = $tplman;
    $this->name = $tpl_name;
    //$spl = preg_split('#(\\$\\{!?[a-zA-Z0-9:_=&;+\\-\\$/\\s]*\\})#u',$tpl,-1,PREG_SPLIT_DELIM_CAPTURE);
    $spl = preg_split('#(\\$\\{!?[^}]*\\})#',$tpl,-1,PREG_SPLIT_DELIM_CAPTURE);
    $this->ops = array();
    for($i=0;isset($spl[$i]);$i++) {
      if ($i%2==1) $spl[$i] = $this->compile_expr($spl[$i]);
      //else $spl[$i] = trim($spl[$i]);
    }
    if (!empty($this->ops)) throw new html_tpl_syntax_ex($this->name,"Unexpected end of file");
    $this->tpl = $spl;
    //var_dump($spl); die();
    //echo "[{$this->tpl->name}]";
  }
  
  private function compile_expr($expr) {
    if ($expr[0]!='$') throw new html_tpl_syntax_ex($this->name,"Bad template expression: $expr");
    $expr = substr($expr,2,strlen($expr)-3);
    $spl = explode(':',$expr,2);
    $type = 'var';
    if (count($spl)>1) { $type = strtolower($spl[0]); $expr = $spl[1]; }
    if (empty($type)) {
      // control expression
      $spl = explode(' ',$expr,2);
      if (count($spl)>1) { $op = strtolower($spl[0]); $param = $spl[1]; }
      else { $op = strtolower($expr); $param = null; }
      $res = new html_tpl_c_control($this,$op,$param,$this->ops);
      return $res;
    }
    $plaintext = false;
    if ($type[0]=='!') { $type = substr($type,1); $plaintext = true;}
    $res = html_tpl_c_elm::create_elm($this,$type,$expr);
    $res->plaintext = $plaintext;
    return $res;
  }
  
  // $i is a starting position
  // return end or else op position, skip nested blocks
  private function skip_to_endelse($i) {
    $level = 0;
    while(true) {
      $i++;
      if (!isset($this->tpl[$i])) throw new html_tpl_syntax_ex($this->name,"Unexpected end of file");
      $val =& $this->tpl[$i];
      if ($val instanceof html_tpl_c_control) {
        if (($level<1)&&(($val->op=='else')||($val->op=='end'))) return $i;
        $level += $val->level_incr;
      }
    }
  }
  
  public function get_realpath($path) {
    if (empty($this->current_path)||($path[0]=='/')) return $path;
    return $this->current_path.'/'.ltrim($path,'/');
  }
  
  // render the template against the given block
  public function apply(html_block $block) {
    $res = '';
    $ops = array();
    $this->current_path = null;
    for($i=0;isset($this->tpl[$i]);$i++) {
      $val = $this->tpl[$i];
      if ($val instanceof html_tpl_c_control) {
        switch($val->op) {
        case 'foreach':
          $fe_path = $this->get_realpath($val->param);
          if (!$block->valid_path($fe_path)) {
            // skip after the ${:end}
            $i = $this->skip_to_endelse($i);
            if ($this->tpl[$i]->op!='end')
              throw new html_tpl_syntax_ex($this->name,"unexpected: ".$this->tpl[$i]->op);
            continue;
          }
          $fe_subp = $block->get_subpaths($fe_path);
          if (empty($fe_subp)) $fe_subp = array($fe_path);
          $fe_newcp = array_shift($fe_subp);
          $op = array($val->op,$fe_subp,$i,$this->current_path);
          $this->current_path = rtrim($fe_newcp,'/');
          $ops[] = $op;
          break;
        case 'delim':
          $op = end($ops);
          if (empty($op[1])) {
            // last entry in foreach, skip the delim block and ${:end}
            $i = $this->skip_to_endelse($i);
            if ($this->tpl[$i]->op!='end')
              throw new html_tpl_syntax_ex($this->name,"unexpected: ".$this->tpl[$i]->op);
            $this->current_path = rtrim($op[3],'/');
            array_pop($ops);
          }
          break;
        case 'ifdef':
          $real_path = $this->get_realpath($val->param);
          $is_true = $block->valid_path($real_path);
          $op = array($val->op,$is_true);
          $ops[] = $op;
          if (!$is_true) {
            $i = $this->skip_to_endelse($i);
            if ($this->tpl[$i]->op=='end') array_pop($ops);
          }
          break;
        case 'else':
          $op = array_pop($ops);
          $i = $this->skip_to_endelse($i);
          if ($this->tpl[$i]->op=='else') throw new html_tpl_syntax_ex($this->name,"Duplicate else");
          break;
        case 'end':
          $op = array_pop($ops);
          if ($op[0]=='foreach') {
            if (!empty($op[1])) {
              $this->current_path = array_shift($op[1]);
              $i = $op[2];
              $ops[] = $op;
            } else {
              $this->current_path = rtrim($op[3],'/');
            }
          }
          if (is_null($op)) throw new html_tpl_syntax_ex($this->name,"Unexpected op: {$val->op}");
          break;
        default:
          throw new html_tpl_syntax_ex($this->name,"Unexpected op: {$val->op}");
        }
        continue;
      }
      if (is_string($val)) $res .= $val;
      elseif ($val instanceof html_tpl_c_elm) {
        // MK: this is a fix to allow HTML entities in the output
        // not sure, it's good idea ;-)
        if (!$val->plaintext) $res .= $val->render($block);
        else $res .= preg_replace('/&amp;([#a-z0-9]+);/i','&$1;',htmlspecialchars($val->render($block)));
      }
    }
    return $res;
  }
  
  
}

?>
