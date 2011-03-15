<?php
// B.H.

abstract class grid_element extends html_block {
    
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
    
    public $wrapper_class = 'grid_element';
    public $label_class = 'grid_label';
    public $content_class = 'grid_element_content';
    public $description_class = 'grid_element_description';
    
    public $params;
    
    /**
     * @var string
     */
    public $label;
    
    /**
     * @var html_block
     */
    public $element;
    
    
    /**
     * This is called by grid_details::load_data/save_data to interact with the data source
     * @var callback
     */
    public $binding;
    
    public function __construct(grid_pannel $pannel, $name, $params) {
        parent::__construct();
        $this->pkg = $pannel->pkg;
        $this->pannel = $pannel;
        $this->name = $name;
        $this->params = $params;
        $this->id = $pannel->details->id_prefix.$name;
        $this->binding = empty($params['binding'])?array('grid_details','default_binding'):$params['binding'];
        $lbl = null;
        if (!empty($params[0]) && is_string($params[0])) $lbl = $params[0];
        elseif (!empty($params['label'])) $lbl = $params['label'];
        if (!empty($lbl)) $this->label = $lbl;
        $this->init_element();
    }
    
    abstract public function set_value($value);
    
    /**
     * @param array $data all the data received from the user ($_POST or similar) 
     */
    public function get_value(&$data, &$err_code) {
        return $data[$this->name];
    }
    
    abstract protected function init_element();
    
    protected function render_element_wrapper($inner) {
        $out = "<div class=\"{$this->wrapper_class}\">";
        if (!empty($this->label) && empty($this->params['hide_label'])) {
            $out .= "<label class=\"{$this->label_class}\" for=\"{$this->id}\">".htmlspecialchars($this->label).'</label>'.
                "<div class=\"{$this->content_class}\">".((string)$inner);
            if (!empty($this->params['description'])) 
                $out .= "<div class=\"{$this->description_class}\">{$this->params['description']}</div>";
            $out .= '</div>';
        } else {
            $out .= ((string)$inner);
        }
        $out .= '<div class="clearfix"></div>';
        return $out.'</div>';
    }
    
    protected function render($param=null) {
        return $this->render_element_wrapper($this->element->get_html());
    }
    
}