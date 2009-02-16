<?php
// B.H.

class html_template extends html_block {

  private $tpl = null;
  // any arguments after the first are passed to html_block::__construct()
  public function __construct($tpl_name=null) {
    if (func_num_args()>1) {
      $args = func_get_args(); array_shift($args);
      parent::__construct($args);
    } else {
      parent::__construct();
    }
    if (!empty($tpl_name)) $this->load_template($tpl_name);
  }
  
  public function load_template($tpl_name) {
    global $_pkgman;
    $pkg = $_pkgman->get("html");
    if (!isset($pkg->config['tpl_manager']))
      $tplman = $pkg->config['tpl_manager'] = new html_tpl_manager();
      else $tplman = $pkg->config['tpl_manager'];
    $this->tpl = $tplman->get_tpl($tpl_name);
  }
  
  public function get_template() {
    return $this->tpl;
  }
  
  protected function render() {
    if (is_null($this->tpl)) throw new Exception("No template specified!");
    return $this->tpl->apply($this);
  }
  
}
?>