<?php
// B.H.

class html_head extends html_element {

  // reference to meta content-type element (used to set apropriate encoding)
  private $meta_content_type;
  // reference to the title element
  private $title_elm;

  public function __construct() {
    parent::__construct('head');
    $this->add("<!-- B.H. -->");
    $this->add($this->meta_content_type = new html_element('meta'));
    $this->add($this->title_elm = new html_element('title'));
  }
  
  public function add_stylesheet($href,$media=null) {
    $attr = array('rel'=>'stylesheet', 'type'=>'text/css', 'href'=>$href);
    if (!empty($media)) $attr['media'] = $media;
    $this->add(new html_element("link",$attr));
  }
  
  public function add_css($css,$media=null) {
    $attr = array('type'=>'text/css'); 
    if (!empty($media)) $attr['media'] = $media;
    $this->add(new html_element("style",$attr,$css));
  }
  
  protected function render($param=null) {
    $encoding = $this->htdoc->get('encoding');
    $title = htmlspecialchars($this->htdoc->get('title'));
    $this->meta_content_type->attr = array('http-equiv'=>'Content-Type',
      'content'=>'text/html; charset='.$encoding);
    $this->title_elm->members = array($title);
    return parent::render($param);
  }
   

}

?>