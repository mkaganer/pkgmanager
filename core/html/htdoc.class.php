<?php
// B.H.

// represents an html document
class html_htdoc extends html_block {
  
  // string: which doctype is used
  // values: 'xhtml-t','xhtml-s','xhtml-f','html4-t'
  // note: (HTML4 is not fully supported, using xhtml is recommended)
  // default constructor sets this to "xhtml-t"
  private $_doctype;
  
  // bool: true is the page is XHTML
  public $is_xml;
  
  // if true, removes a whitespace from the output document before sending to the client
  // warning: may cause problems with <textarea> default values e.t.c.
  public $do_shrink = false;
  
  // array with html tag's attributes (as in html_element)
  public $attr;
  
  public function __construct($title="(untitled)",$doctype="xhtml-t",$encoding="utf-8") {
    parent::__construct();
    $this->htdoc = $this;
    $this->set('title', $title);
    $this->set('encoding',$encoding);
    $this->attr = array(); 
    $this->set_doctype($doctype);
    $this->set('head', new html_head($this));
    $this->set('body', new html_element('body'));
  }
  
  public function body() {
    return $this->get('body');
  }
  
  // updates $_doctype and $DOCTYPE
  public function set_doctype($doctype) {
    switch($doctype) {
      case 'xhtml-t':
        $this->set('DOCTYPE', '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">');
        $this->is_xml = true;
        break;
      case 'xhtml-s':
        $this->set('DOCTYPE', '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">');
        $this->is_xml = true;
        break;
      case 'xhtml-f':
        $this->set('DOCTYPE', '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN" "http://www.w3.org/TR/html4/frameset.dtd">');
        $this->is_xml = true;
        break;
      case 'html4-t':
        $this->set('DOCTYPE', '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">');
        $this->is_xml = false;
        break;
      default:
        throw new Exception("DOCTYPE value not supported!");
    }
    $this->_doctype = $doctype;
    // if XHTML, add xmlns attribute to the <html> tag
    if ($this->is_xml) $this->attr['xmlns'] = "http://www.w3.org/1999/xhtml";
    elseif (isset($this->attr['xmlns'])) unset($this->attr['xmlns']);
  }
  
  public function get_doctype() {
    return $this->_doctype;
  }
  
  // call this function to render the document and send it to the client
  // also makes sure the proper content type and encoding is sent with the headers
  // (is the headers were not sent already)  
  public function send_response() {
    if (!headers_sent()) {
      $enc = $this->get('encoding');
      header('Content-type: text/html; charset='.$enc);
    }
    $res = $this->get_html();
    if ($this->do_shrink) $res = preg_replace('/[\\s]+/',' ',$res);
    echo($res);
  }
  
  // output the html page as a string  
  protected function render() {
    $at = '';
    foreach($this->attr as $atk => $atv) $at .= " ${atk}=\"".htmlspecialchars($atv)."\"";
    
    $res = $this->get('DOCTYPE')."<html${at}>".
      $this->render_by_path('head').$this->render_by_path('body')."</html>";
    return $res;
  }
  
}
?>