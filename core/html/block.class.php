<?php
// B.H.

// base class for all html template stuff
// holds a list of sub-elements that may be accessed using "path" syntax
// elements may be classes (instanceof html_block or html_url)
// or strings (that will be rendered to the output as-is)
// or arrays of the above
//
// this class does not support templates so the members are simply rendered one after another
class html_block {

  public $visible = true;
  public $form_context = null;

  public $members = array();

  protected $parent = null;

  public function __construct() {
    if (func_num_args()>0) {
      $args = func_get_args();
      $this->add($args);
    }
  }
  
  // args: add($elm,...)
  public function add() {
    $arr = func_get_args();
    while(is_array($arr)&&(count($arr)==1)&&isset($arr[0])) $arr = $arr[0];
    //if (empty($arr)) return;
    $this->arr_translate($arr);
    $this->init_member_array($arr);
    if (is_array($arr)) $this->members = array_merge($this->members,$arr); else $this->members[] = $arr;
  }  
  
  // args: add_p($path,$elm,...)
  public function add_p($path) {
    $arr = func_get_args(); array_shift($arr);
    while(is_array($arr)&&(count($arr)==1)&&isset($arr[0])) $arr = $arr[0];
    //if (empty($arr)) return;
    $this->arr_translate($arr);
    $mbr =& $this->_get_path($path,true);
    if (!is_array($mbr)) $mbr = array($mbr);
    $do_merge = $path[strlen($path)-1]!='/';
    $this->init_member_array($arr);
    if (is_array($arr)&&$do_merge) $mbr = array_merge($mbr,$arr); else $mbr[] = $arr;
  }
  
  public function set($path,$value) {
    $path = trim($path,'/');
    if (empty($path)) return;
    $mbr =& $this->_get_path($path,true);
    if (is_array($value)) $this->arr_translate($value);
    $this->init_member_array($value);
    $mbr = $value;
  }
  
  public function unset_p($path) {
    $path = trim($path,'/');
    if (empty($path)) return;
    $mbr =& $this->_get_path($path,true);
    $mbr = null;
  }
  
  public function get($path) {
    $mbr =& $this->_get_path($path);
    while (is_array($mbr)&&(count($mbr)==1)&&isset($mbr[0])) $mbr =& $mbr[0];
    return $mbr;
  }
  
  public function count() {
    return count($this->members);
  }
  
  // creates a new instance of the appropriate form element
  // does not add this element, must be added explicitly
  public function form_element($type,$name,$param=null) {
    global $_pkgman;
    $elm_class = $_pkgman->get('html')->config['form_element_map'][$type];
    if (empty($elm_class)) throw new Exception("Unmapped element type [$type]");
    
    return new $elm_class($type,$name,$param);
  }
  private function arr_translate(&$arr) {
    if (!is_array($arr)) return;
    if (isset($arr[0])&&is_string($arr[0])&&(strlen($arr[0])>1)) {
      $cmd = $arr[0][0]; $arg = substr($arr[0],1);
      if ($cmd=='#') {
        if (count($arr)<2) throw new Exception("Bad array('#xxx',...) syntax!");
        array_shift($arr); $name = array_shift($arr);
        $arr = $this->form_element($arg,$name,$arr);
        return;
      }
      if ($cmd=='~') {
        array_shift($arr);
        $arr = new html_template($arg,$arr);
        return;
      }
    }
    foreach($arr as $key => $val) if (is_array($val)) $this->arr_translate($arr[$key]);
  }
  
  public function init($parent=null) {
    if (is_null($parent)||($parent instanceof html_block)) $this->parent = $parent;
    else throw new Exception("Invalid parent object");
    if (is_null($this->form_context)&&(!is_null($parent))) $this->form_context = $parent->form_context;
    $this->init_member_array($this->members);
    $this->init_done = true;
  }
  private function init_member_array($arr) {
    if (is_array($arr)) {
      foreach($arr as $mbr) {
        if ($mbr instanceof html_block) $mbr->init($this);
        elseif (is_array($mbr)) $this->init_member_array($mbr);
      }
    } elseif ($arr instanceof html_block) {
      $arr->init($this);
    }
  }

  public final function get_html($param=null) {
    return $this->visible?$this->render($param):'';
  }
  
  public function __toString() {
    return $this->get_html();
  }
  
  public function valid_path($path) {
    $path = trim($path,'/');
    if (strlen($path)==0) return true;
    $p = explode('/',$path);
    $mbr =& $this->members;
    foreach($p as $val) {
      if (!is_array($mbr)) return false;
      if (!isset($mbr[$val])) return false;
      $mbr =& $mbr[$val];
    }
    while (is_array($mbr)&&(count($mbr)==1)&&isset($mbr[0])) $mbr =& $mbr[0];
    return !empty($mbr);
  }
  
  public function get_subpaths($path) {
    $path = rtrim($path,'/');
    $mbr =& $this->_get_path($path);
    if (!is_array($mbr)) return null;
    $res = array();
    foreach($mbr as $key => $val) $res[] = $path.'/'.$key;
    return $res;
  }
  
  private function &_get_path($path,$create=false) {
    //echo "<pre>_get_path($path)</pre>";
    $path = trim($path,'/');
    $p = explode('/',$path);
    $mbr =& $this->members;
    if (strlen($path)==0) return $mbr;
    foreach($p as $val) {
      if (!is_array($mbr)) throw new html_block_path_ex($path);
      if (!isset($mbr[$val])) {
        if (!$create) {
          throw new html_block_path_ex($path);
        }
        $mbr[$val] = array();
      }
      $mbr =& $mbr[$val];
    }
    return $mbr;
  }
  
  // used by the template engine. $path format is [/]member/member...[/]
  public function render_by_path($path,$param=null) {
    $mbr =& $this->_get_path($path);
    return $this->render_member($mbr,$param);
  }
  
  protected final function render_member($mbr,$param=null) {
    if ($mbr instanceof html_block) return $mbr->get_html($param);
    if ($mbr instanceof html_url) return $mbr->get_url();
    if (is_array($mbr)) {
      $res = '';
      foreach($mbr as $val) $res .= $this->render_member($val,$param);
      return $res;
    }
    return (string)$mbr;
  }
  
  // to be overriden in the inheriting classes
  protected function render($param=null) {
    $res = '';
    foreach($this->members as $mbr) $res .= $this->render_member($mbr,$param);
    return $res;
  }
  
}
?>