<?php
// B.H.

class grid_pannel extends html_element {
    
    /**
     * @var pkgman_package
     */
    public $pkg;
    
    /**
     * @var grid_details
     */
    public $details;
    
    /**
     * @var string
     */
    public $name;
    
    public function __construct(grid_details $details,$name) {
        parent::__construct('div',array('class'=>'grid_pannel'));
        $this->details = $details;
        $this->name = $name;
        $this->pkg = $details->pkg;
    }
    
    public function add_elements($elements) {
        foreach ($elements  as $name => $params) {
            if (isset($params['type'])) $type = $this->pkg->config['element_types'][$params['type']];
            else $type = $this->pkg->config['element_types'][0];
            
            if (empty($type)) throw new Exception("Element type '$params[type]' is undefined");
            if (!class_exists($type,true)) 
                throw new Exception("Invalid element type '$params[type]' - $type class not found");
            $element = new $type($this, $name, $params);
            if (!($element instanceof grid_element)) throw new Exception("Invalid element type '$params[type]' - invalid type");
            $this->add($element);
        }
    }
    
}