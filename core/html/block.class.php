<?php
// B.H.

/**
 * @desc Base class for all the html stuff. Holds a list of sub-elements that may be accessed
 * using "path" syntax.
 * Elements may be:
 * <ul><li>classes (instanceof <code>html_block</code> or <code>html_url</code>)</li>
 * <li>strings (that will be rendered to the output as-is)</li>
 * <li>arrays (or nested arrays) of the above.</li></ul>
 * Access to the block's members is done using "path" language (@see html_block::add_p)
 *
 * @author mkaganer
 */
class html_block {
    
  /**
   * @desc If false, the block will not be rendered to the output.
   * @var bool
   */
  public $visible = true;
  
  /**
   * @desc References the "form_context" instance associated with the current
   * object. Must be not null when using data-binding controls like html_form_elm.
   * Note: when <code>html_block</code> is added to another block using 
   * <code>add/add_p/set</code> methods and the child's form_context is null,
   * it is set to the form context of the parent control in order to allow
   * "nesting" inside the form blocks. 
   * @var html_form_context
   */
  public $form_context = null;
  
  /**
   * @desc reference the block's parent page
   * @var html_htdoc
   */
  public $htdoc = null;

  /**
   * @desc block's members array
   * @var array
   */
  public $members = array();

  /**
   * @desc Parent block. Set by $this->add/add_p/set
   * @var html_block
   */
  protected $parent = null;
  
  /**
   * @desc If not null, the template will be used in rendering process
   * If false, rendering will just output all the members onr after another.
   * @var html_tpl_compiled
   */
  private $_tpl = null;

  /**
   * @return pkgmanager_package
   */
  public static function get_pkg() {
      static $_pkg;
      if (empty($_pkg)) return $_pkg = pkgman_manager::getp('html');
      return $_pkg;
  }
  
  
  /**
   * Create a basic html_block instance, and optionally add
   * some inner members (a shortcut to calling $block->add(...)) 
   * @param mixed $elm,... - elements to add to a new instance
   */
  public function __construct() {
      if (func_num_args()>0) {
          $args = func_get_args();
          $this->add($args);
      }
  }
  
 
  /**
   * @desc Adds elements to the block w/o explicit path 
   * @param mixed $element,... - elements to add 
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
   * @param string $path - path to add
   * @param mixed $elm,... elements to add
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
  
  /**
   * @param $path
   * @return html_block|string|mixed
   */
  public function get($path) {
    $mbr =& $this->_get_path($path);
    while (is_array($mbr)&&(count($mbr)==1)&&isset($mbr[0])) $mbr =& $mbr[0];
    return $mbr;
  }
  
  /**
   * @desc Number of top-level members in the block
   * @return int
   */
  public function count() {
    return count($this->members);
  }
  
  /**
   * @desc Creates a new instance of the appropriate form element.
   * Note: this method does not add this element to the block,
   * so itmust be added explicitly
   * @param string $type - one of <code>config['form_element_map']</code> 
   * @param string $name - name attribute
   * @param array $param - additional parameters (@see appropriate element class for details)
   * @return unknown_type
   */
  public function form_element($type,$name,$param=null) {
      self::create_form_element($type,$name,$param);
  }
  
  /**
   * @desc Creates a new instance of the appropriate form element. Static version
   * @param string $type - one of <code>config['form_element_map']</code> 
   * @param string $name - name attribute
   * @param array $param - additional parameters (@see appropriate element class for details)
   * @return unknown_type
   */
  public static function create_form_element($type,$name,$param=null) {
    $elm_class = pkgman_manager::getp('html')->config['form_element_map'][$type];
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
        $arr = self::create_form_element($arg,$name,$arr);
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
  
  /**
   * @desc To be overriden in the inheriting classes
   * @param mixed $param - this will be passed to the nested blocks
   * @return string
   */
  protected function render($param=null) {
    $res = '';
    foreach($this->members as $mbr) $res .= $this->render_member($mbr,$param);
    return $res;
  }
  
  /**
   * @desc (moved from html_template)
   * associate template with the block
   * @param string $tpl_name - template's name
   */
  public function load_template($tpl_name) {
    $pkg = self::get_pkg();
    if (!isset($pkg->config['tpl_manager']))
      $tplman = $pkg->config['tpl_manager'] = new html_tpl_manager();
      else $tplman = $pkg->config['tpl_manager'];
    $this->_tpl = empty($tpl_name)?null:$tplman->get_tpl($tpl_name);
  }
  
  public function get_template() {
    return $this->_tpl;
  }
  
  
  /**
   * @desc Parse a raw HTML or XHTML source and split it into "DOM" tree that will be added to the current block
   * Any HTML tags become html_element instances. This function is tolerant to missing or mismatched
   * HTML tags like most HTML rendering engines. This funciton is also aware of tags that must or must not
   * be self-closed (as it whould happen by XML conventions), so the output of $this->get_html() 
   * should be always suitable as (X)HTML.
   * Note: currently, there's no support for CDATA blocks (to be added soon...), 
   * and HTML comments are currentrly stripped out
   * @param string $html
   */
  public function parse_html($html, $strip_whitespace=false) {
      // Theese tags will always be theated as "autoclosed" like <br />, no children tags allowed
      $force_self_close = array(
          'img', 'br', 'hr', 'input',
      );
      // Theese tags will never be rendered as "self-closing", even if they are empty. Some rendering
      // engines (like Mozilla's Gecko) will refuse to parse some self-closing tags properly even if 
      // DOCTYPE of the document is set to XHTML DTD 
      // (this happens if HTTP Content-type is set to text/html and not to XML doctype) 
      $force_no_self_close = array(
          'div', 'script', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'p', 'div', 'span', 'b', 'i',
          'strong', 'em', 'a',
      );
      // first of all, strip html comments
      $html = preg_replace('#<!--.*-->#uUs','',$html);
      $split = preg_split('#<([^>]+)>#u',$html,null,PREG_SPLIT_DELIM_CAPTURE);
      
      // this will track the current parent element
      $parent = $this; 
      foreach ($split as $i => $val) {
          if ($i % 2) {
              $val = trim($val);
              $close = ($val[0]=='/');
              $self_close = ($val[strlen($val)-1]=='/');
              $val = trim($val,'/');
              // if we cannot parse the tag name, something is really wrong. Just skip it for safety :-)
              if (!preg_match('#^([a-z][a-z0-9\\.:_\\-]*)(.*)$#usi',$val,$tm)) continue;
              $tag = strtolower($tm[1]);
              if (!$close) {
                  $attr = $this->parse_html_tag_attr($tm[2]);
                  $new_tag = new html_element($tag,$attr);
                  if (in_array($tag, $force_no_self_close)) $new_tag->self_closing = false;
                  if (!$self_close && in_array($tag, $force_self_close)) $self_close = true;
                  $parent->add($new_tag);
                  if (!$self_close) $parent = $new_tag;
              } else {
                  // look up the matching closing tag
                  $open_tag = $parent;
                  while (($open_tag->tag!=$tag) && $open_tag!==$this) $open_tag = $open_tag->parent;
                  if ($open_tag!==$this) $parent = $open_tag->parent;
              }
          } else {
              // this is a plain text entry. simply add it to the "DOM" in the current level
              if ($strip_whitespace) $val = preg_replace('#\\s+#u',' ',$val);
              $parent->add($val);
          }
      }
  }
  
  private function parse_html_tag_attr($str) {
      $attr = array();
      while (preg_match('#^\\s*([a-z][a-z0-9\\.:_\\-]*)(.*)$#usi',$str,$m)) {
          $key = strtolower($m[1]);
          $str = ltrim($m[2]);
          if ($str[0]!='=') $attr[$key] = $key;
          else {
              if (preg_match('#^="([^"]*)"(.*)$#us',$str,$m) || preg_match("#^='([^']*)'(.*)$#us",$str,$m) ||
                  preg_match('#^=([^\\s]*)(.*)$#us',$str,$m)) 
              {
                  $attr[$key] = html_entity_decode($m[1]);
                  $str = $m[2];
              } else return $attr;
          }
      }
      return $attr;
  }
  
  /**
   * Recursively scan the members and get the stripped text (with out the HTML tags). 
   * Works like striptags() on strings
   * @return string
   */
  public function get_inner_text() {
      $out = '';
      foreach($this->members as $mbr) {
          if (is_string($mbr) || is_numeric($mbr)) $out .= html_entity_decode((string)$mbr,ENT_QUOTES,'UTF-8');
          elseif ($mbr instanceof html_block) $out .= $mbr->get_inner_text();
      }
      return $out;
  }
  
}