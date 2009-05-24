<?php
// B.H.

/**
 * @desc represents an html document
 * @author mkaganer
 *
 */
class html_htdoc extends html_block {
  
  /**
   * @desc Which doctype is used.
   * @see $this->set_doctype()
   * @var string
   */
  private $_doctype;
  
  /**
   * @desc true is the page is XHTML
   * @var bool
   */
  public $is_xml;
  
  /**
   * @desc If true, removes a whitespace from the output document before sending
   * to the client. This can a lot of bandwidth without dealing with stream compression.
   * <i>warning:</i> may cause problems with <code>&lt;textarea&gt;</code> default values,
   * <code>&lt;pre&gt;</code> tag and possibly inline javascript because of newline removal.
   * <b>default is false</b>
   * @var bool
   */
  public $do_shrink = false;
  
  /**
   * @desc array with html tag's attributes (as in html_element)
   * @var array
   */
  public $attr;
  
  /**
   * @desc Reference the head element
   * @var html_head
   */
  public $head;
  
  /**
   * @param string $title document's title. Default: "(untitled)"
   * @param string $doctype DOCTYPE code (see <code>$this->set_doctype()</code>). Default: "xhtml-t". 
   * @param string $encoding encoding used in the current document. Default: utf-8
   */
  public function __construct($title="(untitled)",$doctype="xhtml-t",$encoding="utf-8") {
    parent::__construct();
    $this->htdoc = $this;
    $this->set('title', $title);
    $this->set('encoding',$encoding);
    $this->attr = array(); 
    $this->set_doctype($doctype);
    $this->set('head', $this->head = new html_head($this));
    $this->set('body', new html_element('body'));
  }
  
  /**
   * @desc Get the document's body member
   * @return html_block
   */
  public function body() {
    return $this->get('body');
  }
  
  /**
   * @desc Set document's doctype.
   * possible values: <b>'xhtml-t','xhtml-s','xhtml-f','html4-t'</b>
   * note: (HTML4 is not fully supported, using xhtml is recommended)
   * <i>default constructor sets this to "xhtml-t"</i> 
   * @param string $doctype - a new doctype to set
   */
  public function set_doctype($doctype) {
    // updates $_doctype and $DOCTYPE
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
  
  /**
   * @desc Get document's doctype.
   * @see $this->set_doctype()
   * @return string
   */
  public function get_doctype() {
    return $this->_doctype;
  }
  
  /**
   * @desc Call this function to render the document and send it to the client
   * also makes sure the proper content type and encoding is sent with the headers
   * (if the headers were not sent already)  
   */
  public function send_response() {
    if (!headers_sent()) {
      $enc = $this->get('encoding');
      header('Content-type: text/html; charset='.$enc);
    }
    $res = $this->get_html();
    if ($this->do_shrink) $res = preg_replace('/[\\s]+/',' ',$res);
    echo($res);
  }
  
  /**
   * @desc output the html page as a string
   * @return string
   */
  protected function render() {
    $at = '';
    foreach($this->attr as $atk => $atv) $at .= " ${atk}=\"".htmlspecialchars($atv)."\"";
    
    $res = $this->get('DOCTYPE')."<html${at}>".
      $this->render_by_path('head').$this->render_by_path('body')."</html>";
    return $res;
  }
  
}
?>