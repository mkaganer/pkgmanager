<?php
// B.H.

class html_form extends html_element {
    /**
     * @desc Construct a form element, any arguments after the 3 first are passed to html_block::__construct()
     * @param string $action action URL
     * @param string $method post or get
     * @param boolean $file_upload if true, enctype="multipart/form-data"
     * @param unknown_type $attr
     */
    public function __construct($action,$method='post',$file_upload=false) {
        parent::__construct('form',$attr);
        $this->form_context = new html_form_context($method);
        $this->attr['action'] = (string)$action;
        $this->attr['method'] = $method;
        if ($file_upload) $this->set_upload();
        if (func_num_args()>4) {
            $args = func_get_args();
            array_shift($args); array_shift($args); array_shift($args);
            $this->add($args);
        }
    }
    
    /**
     * @param boolean $is_upload
     */
    public function set_upload($is_upload=true) {
        if ($is_upload) $this['enctype'] = "multipart/form-data";
        else unset($this['enctype']);
    }
    
}
