<?php
// B.H.

/**
 * @desc Represents an HTML element. Holds element's name and attributes
 * and renders all it's members inside the element. Empty elements are rendered
 * according to XML or SGML rules (i.e. &lt;img... /&gt; or &lt;img....&gt;)
 * with respect to html_htdoc::$is_xml setting   
 * @author mkaganer
 *
 */
class html_element extends html_block {

  public $attr = array();
  
  private $tag; 

  // any arguments after the 2 first are passed to html_block::__construct()
  public function __construct($tag,$attr=null) {
    $this->tag = $tag;
    if (is_array($attr)) $this->attr = $attr;

    if (func_num_args()>2) {
      $args = func_get_args(); array_shift($args); array_shift($args);
      parent::__construct($args);
    } else {
      parent::__construct();
    }
  }
  
  protected function render() {
    $at = ''; $inner = '';
    $tag = $this->tag;
    foreach($this->attr as $atk => $atv) $at .= " ${atk}=\"".htmlspecialchars($atv)."\"";
    foreach($this->members as $mbr) $inner .= $this->render_member($mbr);
    if (!empty($inner)) {
      return "<${tag}${at}>${inner}</${tag}>";
    } else {
      if (!empty($this->htdoc) && (!$this->htdoc->is_xml)) return "<${tag}${at}>"; 
      return "<${tag}${at} />";
    }
  }
  
}
?>