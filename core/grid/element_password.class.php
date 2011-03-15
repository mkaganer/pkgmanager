<?php
// B.H.

class grid_element_password extends grid_element {
    
    private $elm_style;
    
    private $element_1;
    private $element_2;
    
    public function init_element() {
        $this->element = new html_block();
        $this->element_1 = new html_element('input',array('type'=>'password','id'=>$this->id,
            'name'=>$this->name,'class'=>'grid-element-password'));
        $this->element_2 = new html_element('input',array('type'=>'password','id'=>$this->id.'__2',
            'name'=>$this->name.'__2','class'=>'grid-element-password'));
        $this->element->add($this->element_1,'<br />');
        $this->element->add($this->element_2);
        $this->elm_style = $this->element->attr['style'] = new html_style();
        $this->element_2->attr['style'] = $this->elm_style;
        if (!empty($this->params['width'])) $this->elm_style['width'] = $this->params['width'];
        if (!empty($this->params['direction'])) $this->elm_style['direction'] = $this->params['direction'];
    }
    
    public function set_value($value) {
        //$this->element_1->attr['value'] = $value;
        //$this->element_2->attr['value'] = $value;
    }
    
    public function get_value(&$data, &$err_code) {
        $val = $data[$this->name];
        if ($val!=@$data[$this->name.'__2']) {
            $err_code = 'password_not_match';
            return null;
        }
        if (!empty($val)) return $val;
        else return null;
    }
   
}