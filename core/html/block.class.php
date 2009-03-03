<?php
// B.H.

/**
 * @desc Base class for all html template stuff
 * holds a list of sub-elements that may be accessed using "path" syntax
 * elements may be classes (instanceof html_block or html_url)
 * or strings (that will be rendered to the output as-is)
 * or arrays of the above.
 * <i>This class does not support templates so the members are simply
 * rendered one after another</i>
 *
 * @author mkaganer
 */
class html_block {

  public $visible = true;
  public $form_context = null;
  
  // reference the block's parent page (html_htdoc)
  public $htdoc = null;

  public $members = array();

  protected $parent = null;
  
  // holds a tpl_compiled instance if template was associated
  private $_tpl = null;

  public function __construct() {
    if (func_num_args()>0) {
      $args = func_get_args();
      $this->add($args);
    }
  }
  
  // args: add($elm,...)
  /**
   * Adds elements to the block w/o explicit path 
   * @param $element,...
   * @return unknown_type
   */
  public function add() {
    $arr = func_get_args();
    while(is_array($arr)&&(count($arr)==1)&&isset($arr[0])) $arr = $arr[0];

    $this->arr_translate($arr);
    if (is_array($arr)) $this->init_member_array($arr);
    else if ($arr instanceof html_block) $arr->init($this);

    if (is_array($arr)) $this->members = array_merge($this->members,$arr); else $this->members[] = $arr;
  }  
  
  /**
   * @param $path - path to add
   * @param $elm,.... elements to add
   * @return unknown_type
   */
  public function add_p($path) {
    $arr = func_get_args(); array_shift($arr);
    while(is_array($arr)&&(count($arr)==1)&&isset($arr[0])) $arr = $arr[0];

    $this->arr_translate($arr);
    if (is_array($arr)) $this->init_member_array($arr);
    else if ($arr instanceof html_block) $arr->init($this);

    $mbr =& $this->_get_path($path,true);
    if (!is_array($mbr)) $mbr = array($mbr);
    $do_merge = $path[strlen($path)-1]!='/';

    if (is_array($arr)&&$do_merge) $mbr = array_merge($mbr,$arr); else $mbr[] = $arr;
  }
  
  public function set($path,$value) {
    $path = trim($path,'/');
    if (empty($path)) return;
    $mbr =& $this->_get_path($path,true);

    $this->arr_translate($value);
    if (is_array($value)) $this->init_member_array($value);
    else if ($value instanceof html_block) $value->init($this);
    
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
    }
    foreach($arr as $key => $val) if (is_array($val)) $this->arr_translate($arr[$key]);
  }
  
  public function init($parent=null) {
    $this->parent = $parent;
    if (!empty($parent)) {
      $this->htdoc = $parent->htdoc;
      if (empty($this->form_context)) $this->set_form_context($parent->form_context);
    }
    $this->init_member_array($this->members);
  }
  
  public function set_form_context($context) {
    $this->form_context = $context;
    $this->set_fc_members($context,$this->members);
  }
  
  private function init_member_array(&$arr) {
    foreach($arr as $mbr) {
      if ($mbr instanceof html_block) $mbr->init($this);
      elseif (is_array($mbr)) $this->init_member_array($mbr);
    }
  }

  private function set_fc_members($context,&$arr) {
    foreach($arr as $mbr) {
      if ($mbr instanceof html_block) $mbr->set_form_context($context);
      elseif (is_array($mbr)) $this->set_fc_members($context,$mbr);
    }
  }

  public final function get_html($param=null) {
    if (!empty($this->_tpl)) return $this->_tpl->apply($this);
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
  
  // (moved from html_template:)
  // associate template with the block
  public function load_template($tpl_name) {
    global $_pkgman;
    $pkg = $_pkgman->get("html");
    if (!isset($pkg->config['tpl_manager']))
      $tplman = $pkg->config['tpl_manager'] = new html_tpl_manager();
      else $tplman = $pkg->config['tpl_manager'];
    $this->_tpl = empty($tpl_name)?null:$tplman->get_tpl($tpl_name);
    //echo "[load_tpl:$tpl_name]";var_dump($this->_tpl);
  }
  
  public function get_template() {
    return $this->_tpl;
  }
  
  
}
?>