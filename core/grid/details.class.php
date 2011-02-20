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
    
    public function __construct(html_url $base_url,$table,$id_value=null) {
        parent::__construct($base_url);
        $c =& $this->pkg->config;
        $this->id_column = $c['default_id_column'];
        $this->form = new html_form($this->base_url,'post');
    }
    
    /**
     * @return html_block
     */
    public function render() {
        return $this->form;
    }
    
}