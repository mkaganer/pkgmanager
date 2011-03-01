<?php
// B.H.

class grid_details extends grid_base {
    
    /**
     * @var html_form
     */
    public $form;
    
    /**
     * @var string
     */
    public $table;
    
    /**
     * Table's primary key column name
     * @var string
     */
    public $id_column;
    
    public $id_value;
    
    /**
     * @var boolean true for update mode, false for insert mode
     */
    public $is_update;
    
    public $id_prefix;
    
    /**
     * @var html_block
     */
    public $content;
    
    /**
     * Currently active pannel
     * @var grid_pannel
     */
    public $pannel;
    
    /**
     * @var grid_form_button
     */
    public $submit_button;
    
    public function __construct(html_url $base_url,$table,$id_value=null) {
        parent::__construct($base_url);
        $c =& $this->pkg->config;
        $this->id_column = $c['default_id_column'];
        $this->id_value = $id_value;
        $this->is_update = !is_null($id_value);
        $this->id_prefix = 'det'.rand(1000,1000000).'_';
        
        $this->form = new html_form($this->base_url,'post');
        $this->form->attr['class'] = 'grid_form';
        $this->content = new html_block();
        $this->form->add($this->content);
        $this->submit_button = new grid_form_submit();
        $this->form->add($this->submit_button);
    }
    
    public function set_width($width) {
        $old_style = trim($this->form->attr['style'],"; \t");
        if (!empty($old_style)) $old_style .= ';';
        if (is_numeric($width)) $width .= 'px';
        $this->form->attr['style'] .= $old_style."width:$width";
    }
    
    public function add_pannel($name=null) {
        $pannel = new grid_pannel($this, $name);
        $this->pannel = $pannel;
        $this->content->add($pannel);
    }
    
    public function add_elements($elements) {
        if (empty($this->pannel)) $this->add_pannel();
        $this->pannel->add_elements($elements);
    }
    
    /**
     * @return html_block
     */
    public function render() {
        return $this->form;
    }
    
}