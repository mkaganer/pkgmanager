<?php
// B.H.

class grid_element_text extends grid_element {

    private $elm_style;
    
    public function init_element() {
        $this->element = new html_element('input',array('type'=>'text','id'=>$this->id,
            'name'=>$this->name,'class'=>'grid-element-text'));
        $this->add($this->element);
        $this->elm_style = $this->element->attr['style'] = new html_style();
        if (!empty($this->params['width'])) $this->elm_style['width'] = $this->params['width'];
        if (!empty($this->params['direction'])) $this->elm_style['direction'] = $this->params['direction'];
    }
    
    public function set_value($value) {
        $this->element->attr['value'] = $value;
    }
    
}