<?php
// B.H.

/**
 * @desc Encapsulates a CSS properties list, so it's possible to add/remove/edit properties 
 * @author mkaganer
 * @copyright GPL v2 (based on the following source):
 * @filesource http://websvn.atutor.ca/wsvn/filedetails.php?repname=atutor&path=%2Ftrunk%2Fdocs%2Finclude%2Fclasses%2Fcssparser.php
 */
class html_style implements ArrayAccess {
    
    public $css;
    
    /**
     * @param sttring $css_expression
     */
    public function __construct($css_expression=null,$remove_comments=false) {
        $this->css = array();
        if (!empty($css_expression)) $this->parse_expression($css_expression,$remove_comments);
    }
    
    /**
     * @return string CSS properties to be set as style attribute or as a CSS rule body 
     */
    public function render() {
        return $this->__toString();
    }
    
    public function __toString() {
        $result = "";
        foreach($this->css as $key => $value) $result .= "$key:$value;";
        return $result;
    }

    /* ArrayAccess implementation */
    
    public function offsetSet($offset, $value) {
        $this->css[$offset] = $value;
    }
    public function offsetExists($offset) {
        return isset($this->css[$offset]);
    }
    public function offsetUnset($offset) {
        unset($this->css[$offset]);
    }
    public function offsetGet($offset) {
        return isset($this->css[$offset]) ? $this->css[$offset] : null;
    }

    private function parse_expression($codestr, $remove_comments=false) {
        $codestr = strtolower($codestr);
        if ($remove_comments) $codestr = preg_replace("/\/\*(.*)?\*\//Usi", "", $codestr);
        $codes = explode(";",$codestr);
        foreach($codes as $code) {
            $code = trim($code);
            list($codekey, $codevalue) = explode(":",$code,2);
            if(!empty($codekey) && !empty($codevalue)) $this->css[trim($codekey)] = trim($codevalue);
        }
    }
    
}