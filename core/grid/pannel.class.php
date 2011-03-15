<?php
// B.H.

class grid_pannel extends html_block {
    
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
    
    /**
     * @var string
     */
    public $title;
    
    public $wrapper_class = 'grid_pannel';
    public $header_class = 'grid_pannel_header';
    public $content_class = 'grid_pannel_content';
    
    public $title_visible = true;
    
    public function __construct(grid_details $details,$name=null,$title=null) {
        $this->pkg = $details->pkg;
        parent::__construct();
        $this->details = $details;
        $this->name = $name;
        $this->title = $title;
    }
    
    public function add_elements($elements) {
        foreach ($elements  as $name => $params) {
            if (isset($params['type'])) $class = $this->pkg->config['element_types'][$params['type']];
            else $class = $this->pkg->config['element_types'][0];
            
            if (empty($class)) throw new Exception("Element type '$params[type]' is undefined");
            if (!class_exists($class,true)) 
                throw new Exception("Invalid element type '$params[type]' - $class class not found");
            if (isset($this->details->elements[$name])) throw new Exception("Duplicate element name '$name'");
            $element = new $class($this, $name, $params);
            if (!($element instanceof grid_element)) throw new Exception("Invalid element type '$params[type]' - invalid type");
            $this->add($element);
            $this->details->elements[$name] = $element;
        }
    }
    
    protected function render_pannel_wrapper($inner) {
        $out = "<div class=\"{$this->wrapper_class}\">";
        if ($this->title_visible && !empty($this->title)) {
            $out .= '<h2 class="'.$this->header_class.'">'.htmlspecialchars($this->title).'</h2>'.
                "<div class=\"{$this->content_class}\">".((string)$inner).'</div>';
        } else {
            $out .= ((string)$inner);
        }
        $out .= '<div class="clearfix"></div></div>';
        return $out;
    }
    
    protected function render($param=null) {
        return $this->render_pannel_wrapper(parent::render($param));
    } 
    
}
