<?php
// B.H.

abstract class grid_element extends html_element {
    
    /**
     * @var pkgman_package
     */
    public $pkg;
    
    /**
     * @var grid_pannel
     */
    public $pannel;
    
    /**
     * @var string
     */
    public $name;
    
    public $id;
    
    public $params;
    
    /**
     * @var html_element
     */
    public $label;
    
    /**
     * @var html_element
     */
    public $element;
    
    public function __construct(grid_pannel $pannel, $name, $params) {
        parent::__construct('div',array('class'=>'grid_element'));
        $this->pkg = $pannel->pkg;
        $this->pannel = $pannel;
        $this->name = $name;
        $this->params = $params;
        $this->id = $pannel->details->id_prefix.$name;
        $lbl = null;
        if (!empty($params[0]) && is_string($params[0])) $lbl = $params[0];
        elseif (!empty($params['label'])) $lbl = $params['label'];
        if (!empty($lbl)) {
            $this->label = new html_element('label',array('class'=>'grid_label','for'=>$this->id),$lbl);
            $this->add($this->label);
        }
        $this->init_element();
    }
    
    abstract protected function init_element();
    
}