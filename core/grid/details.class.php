<?php
// B.H.

class grid_details extends grid_base {
    
    // binding callback modes (the first argument)
    const BINDING_FROMDB = 0; // get the data from the data source and return the element value
    const BINDING_TODB = 1; // get the element value and set the data in the data source
    const BINDING_PASS2 = 2; // like BINDING_TODB, but called when $this->id_value has a value (after insert is done) 
    
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
     * @var html_block
     */
    public $buttons_bar;
    
    /**
     * @var grid_form_button
     */
    public $submit_button;
    
    /**
     * @var array(grid_element)
     */
    public $elements;
    
    
    protected $postback_values;
    
    /**
     * @param html_url $base_url
     * @param string $table
     * @param string $id_value null (default) means insert mode, not-null means update mode
     */
    public function __construct(html_url $base_url,$table,$id_value=null) {
        parent::__construct($base_url);
        $c =& $this->pkg->config;
        $this->table = $table;
        $this->id_column = $c['default_id_column'];
        $this->id_value = $id_value;
        $this->is_update = !is_null($id_value);
        $this->id_prefix = 'det'.rand(1000,1000000).'_';
        $this->elements = array();
        
        $this->form = new html_form($this->base_url,'post');
        $this->form->attr['class'] = 'grid_form';
        $this->content = new html_block();
        $this->form->add($this->content);
        $this->buttons_bar = new html_block($this->submit_button = new grid_form_submit());
        $this->buttons_bar->add('<span class="grid-buttons-spacer">&nbsp;</span>');
        $this->form->add($this->buttons_bar);
    }
    
    public function set_width($width) {
        $old_style = trim($this->form->attr['style'],"; \t");
        if (!empty($old_style)) $old_style .= ';';
        if (is_numeric($width)) $width .= 'px';
        $this->form->attr['style'] .= $old_style."width:$width";
    }
    
    public function add_pannel($name=null,$title=null) {
        $pannel = new grid_pannel($this, $name,$title);
        $this->pannel = $pannel;
        $this->content->add($pannel);
        return $pannel;
    }
    
    public function add_elements($elements) {
        if (empty($this->pannel)) $this->add_pannel();
        $this->pannel->add_elements($elements);
    }
    
    /**
     * @param array $data postback data returned from the user (can be $_POST for example)
     * @param unknown_type $strip_slashes
     */
    public function save_data($data, $strip_slashes=true) {
        if ($strip_slashes) $data = array_map('stripslashes',$data);

        // Step 1: get values from the elements and valdate
        $values = array();
        $errors = array();
        foreach ($this->elements as $name => $elm) {
            $err_code = null;
            $values[$name] = $elm->get_value($data,$err_code);
            if (!empty($err_code)) $errors[$name] = $err_code;
        }
        if (!empty($errors)) {
            $this->postback_values = $values;
            return $errors;
        }
        
        // Step 2: bind the $values data to the database columns array (using binding callback)
        $db_data = array();
        foreach ($this->elements as $name => $elm) {
            call_user_func_array($elm->binding,array(
                self::BINDING_TODB,
                $this, // grid_details
                $elm, // the element
                &$values[$name], // element value
                &$db_data, // database row buffer
            ));
        }

        // Step 3: insert/update the DB
        $filter = $this->xlink->filter(array(
            $this->id_column => $this->id_value
        ));
        if (!empty($db_data)) {
            if (is_null($this->id_value)) {
                // insert
                $this->xlink->insert($this->table,$db_data);
                $this->id_value = $this->xlink->insert_id();
                $filter->filters[$this->id_column] = $this->id_value;
            } else {
                // update
                $this->xlink->update($this->table,$db_data,$filter);
            }
        }
        
        // Step 4: execute "pass 2" updates if needed
        $db_data = array();
        foreach ($this->elements as $name => $elm) {
            call_user_func_array($elm->binding,array(
                self::BINDING_PASS2,
                $this, // grid_details
                $elm, // the element
                &$values[$name], // element value
                &$db_data, // database row buffer
            ));
        }
        if (!empty($db_data)) {
            $this->xlink->update($this->table,$db_data,$filter);
        }
        
        return null;
    }
    
    protected function load_data() {
        if (is_null($this->id_value)) {
            // input row does not exists, we are in 'insert' mode probably...
            $db_data = array();
        } else {
            // load the input data
            $sql = 'select tbl.* from `'.$this->table.'` tbl where ';
            $filter = $this->xlink->filter(array(
                $this->id_column => $this->id_value
            ),'and','tbl');
            $sql .= $filter->render();
            $db_data = $this->xlink->select_r($sql);
            if (empty($db_data)) throw new Exception("Row {$this->id_column}='{$this->id_value}' in {$this->table} not found");
        }
        foreach ($this->elements as $name => $elm) {
            $value = null;
            call_user_func_array($elm->binding,array(
                self::BINDING_FROMDB,
                $this, // grid_details
                $elm, // the element
                &$value, // display data
                &$db_data, // database row
            ));
            if (is_null($value) && !empty($elm->params['default_value'])) $value = $elm->params['default_value'];
            if (!empty($this->postback_values[$name])) $value = $this->postback_values[$name];
            $elm->set_value($value);
        }
    }
    
    /**
     * @return html_block
     */
    public function render() {
        $this->load_data();
        return $this->form;
    }
    
    
    /**
     * The default binding callback implementation
     * @param boolean $to_db direction true: $data => $db_data, false: $db_data => $data
     * @param grid_details $details
     * @param grid_element $elm
     * @param array &$data data array formated for display
     * @param array &$db_data database data row
     */
    public static function default_binding($binding_mode,grid_details $details, grid_element $elm, &$value, &$db_data) {
        $column_name = empty($elm->params['column_name'])?$elm->name:$elm->params['column_name'];
        if ($binding_mode==self::BINDING_TODB) {
            // $value => $db_data
            if (!is_null($value)) $db_data[$column_name] = $value;
        } elseif ($binding_mode==self::BINDING_FROMDB) {
            // $db_data => $value
            $value = @$db_data[$column_name];
        }
    } 
    
}