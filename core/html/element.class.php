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
class html_element extends html_block implements ArrayAccess{
    
    /**
     * the following elements will never be "self-closing" (mozilla browsers fail to parse theese elements
     * correctly as self-closed by XML convetions 
     * @var array  
     */
    public static $force_no_self_close = array(
        'div', 'script', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'p', 'div', 'span', 'b', 'i',
        'strong', 'em', 'a', 'select',
    );
    
    
    public $attr = array();
    
    public $tag;
    
    /**
    * @var if true, and there's no inner content, the tag will be self-closed (in XML mode only)
    */
    public $self_closing = true;
    
    // any arguments after the 2 first are passed to html_block::__construct()
    public function __construct($tag,$attr=null) {
        $this->tag = $tag;
        if (in_array(strtolower($tag),self::$force_no_self_close)) $this->self_closing = false;
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
        $inner = '';
        foreach($this->members as $mbr) $inner .= (string)$this->render_member($mbr);
        if (($inner!=='') || !$this->self_closing) {
            return "<${tag}${at}>${inner}</${tag}>";
        } else {
            if (!empty($this->htdoc) && (!$this->htdoc->is_xml)) return "<${tag}${at}>"; 
            return "<${tag}${at} />";
        }
    }
    
    /* ArrayAccess implementation */
    
    public function offsetSet($offset, $value) {
        $this->attr[$offset] = $value;
    }
    public function offsetExists($offset) {
        return isset($this->attr[$offset]);
    }
    public function offsetUnset($offset) {
        unset($this->attr[$offset]);
    }
    public function offsetGet($offset) {
        return isset($this->attr[$offset]) ? $this->attr[$offset] : null;
    }
    
}
