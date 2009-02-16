<?php
// B.H.

class html_tpl_c_var extends html_tpl_c_elm {

  private $path;
  private $param;
  private $tpl; // html_tpl_compiled

  public function __construct($tpl,$path) {
    $this->tpl = $tpl;
    $l = explode('|',$path);
    $this->path = array_shift($l);
    $this->param = empty($l)?null:$l;
  }
  
  public function render($block) {
    $real_path = $this->tpl->get_realpath($this->path);
    //echo "[{$this->tpl->name}:[{$this->path}]+[{$this->tpl->current_path}]=>$real_path]\n";
    try {
      return $block->render_by_path($real_path,$this->param);
    } catch(html_block_path_ex $ex) {
      //return "";
      //$tpl_name = 
      //var_dump($ex);
      $msg = $ex->getMessage();
      return "<span dir=\"ltr\">[{$this->tpl->name}:{$real_path}:\$ex=${msg}]</span>";
    }
  }
}
