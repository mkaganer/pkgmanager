<?php
// B.H.

class html_script extends html_element {

  // if $is_link is true or $script contains one of \n\r{}$; characters, this is considered inline
  // script and inserted as CDATA (if hrdoc is XHTML)
  // otherwise, it is considered a link and <script src="..."> tag is created
  // this function strips out comments from the script to speed up page loading and
  // bacause "//" comments does not work with whitespace shrinking used in html_htdoc::send_response()
  public function __construct($script,$is_link=true,$do_shrink=true) {
    $attr = array('type'=>'text/javascript');
    // check for character mask to distinguish inline script
    if (strcspn($script,"\n\r{}$;")!=strlen($script)) $is_link = false;
    if ($is_link) {
      $attr['src'] = $script;
      parent::__construct("script",$attr," ");
    } else {
      $is_xml = empty($this->htdoc)?true:$this->htdoc->is_xml;
      // strip out any comments
      if ($do_shrink) {
        $script = preg_replace("#//.*#","",$script);
        $script = preg_replace("#/\\*.*\\*/#s","",$script);
        $script = preg_replace('/[\\s]+/',' ',$script);
      }
      if ($is_xml) $script = "/* <![CDATA[ */ ${script} /* ]]> */";
      parent::__construct("script",$attr,$script);
    }
  }
  
}
?>