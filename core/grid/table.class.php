<?php
// B.H.

class grid_table extends grid_base {
    
    /**
     * @var array ( grid_column )
     */
    public $columns;
    
    /**
     * @desc Basic SQL query for showing the grid (w/o limit clause!)
     * @var string
     */
    public $query;
    
    /**
     * @var string
     */
    public $default_orderby;
    
    /**
     * @desc Helper query to get the total number of rows availiable in $this->query result - used for paging
     * @var string
     */
    public $count_query;
    
    /* see config.php for explanation of theese parameters */
    
    /**
     * default table element attributes
     * @var array
     */
    public $table_attr;
    
    /**
     * @var array
     */
    public $tr_classes;
    
    /**
     * @var boolean
     */
    public $use_paging;
    
    /**
     * @var string
     */
    public $paging_var;
    
    /**
     * @var array
     */
    public $paging_icons;
    
    public $sort_icon_asc;
    public $sort_icon_desc;
    
    /**
     * @var string
     */
    public $order_var;
    
    /**
     * @var int
     */
    public $rows_per_page;
    
    /**
     * @var string
     */
    public $id_column;

    /**
     * The current row data
     * @var array
     */
    public $row;
    
    /**
     * the primary key value for the current row
     * @var mixed
     */
    public $id_value;
    
    /**
     * @var html_element
     */
    public $table;
    
    /**
     * @var html_element
     */
    public $tr;
    
    /**
     * @var html_element
     */
    public $cell;
    
    /**
     * @var grid_toolbar
     */
    public $toolbar;
    
    private $actual_orderby;
    
    private $full_sql;
    
    /**
     * @param string $query
     * @param string $count_query
     * @param html_url $base_url
     */
    public function __construct(html_url $base_url, $query, $default_orderby='') {
        parent::__construct($base_url);
        $c =& $this->pkg->config;
        $this->columns = array();
        $this->query = $query;
        $this->default_orderby = $default_orderby;
        $this->base_url = $base_url;
        $this->table_attr = $c['table_attr'];
        $this->tr_classes = (array)$c['tr_classes'];
        $this->use_paging = false;
        $this->paging_var = $c['paging_var'];
        $this->paging_icons = $c['paging_icons'];
        $this->sort_icon_asc = $c['sort_icon_asc'];
        $this->sort_icon_desc = $c['sort_icon_desc'];
        $this->order_var = $c['order_var'];
        $this->rows_per_page = $c['rows_per_page'];
        $this->id_column = $c['default_id_column'];
        $this->toolbar = new grid_toolbar();
    }
    
    public function setup_paging($count_query, $rows_per_page=null) {
        $this->count_query = $count_query;
        if ($rows_per_page) $this->rows_per_page = $rows_per_page;
        $this->use_paging = true;
    }
    
    /**
     * @desc $columns = array (
     *      'col1' => array(
     *          ['type'=>'column_type',]
     *          ['header title text',]
     *          .... custom params for the column
     *      ),
     *      .... more columns
     * )
     * @param array $columns
     * @return void
     */
    public function add_columns($columns) {
        foreach ($columns  as $name => $params) {
            if (isset($params['type'])) $type = $this->pkg->config['column_types'][$params['type']];
            else $type = $this->pkg->config['column_types'][0];
            
            if (empty($type)) throw new Exception("Column type '$params[type]' is undefined");
            if (!class_exists($type,true)) 
                throw new Exception("Invalid column type '$params[type]' - $type class not found");
            $column = new $type($this,$name, $params);
            $this->columns[$name] = $column;
        }
    }
    
    private function get_paging_url($from) {
        return htmlspecialchars($this->base_url->get_url(array($this->paging_var=>$from)));
    }
    
    private function get_paging_tag($from, $page, $disabled = false) {
        $class = "grid-button";
        $url = $this->get_paging_url($from);
        if (is_numeric($page)) {
            //if (!empty($this->paging_icons['page'])) $text = sprintf($this->paging_icons['page'],$page);
            ///else $text = $page;
            $text = $page;
        } elseif (isset($this->paging_icons[$page])) {
            if (!empty($this->paging_icons[$page][0])) 
                $class .= ' grid-button-icononly '.$this->paging_icons[$page][0];
            $text = $this->paging_icons[$page][1];
        }
        if ($disabled) $class .= " grid-button-disabled";
        return "<a class=\"$class\" href=\"$url\">$text</a>";
    }
    
    private function get_paging_block() {
        // get total rows number
        $total_rows = $this->xlink->select_s($this->count_query);
        
        $from = min($total_rows-1,max(0,intval(@$_GET[$this->paging_var])));
        $limit = max(1,$this->rows_per_page);
        $this->full_sql .= " limit $from,$limit";
        
        $paging = new html_element('div',array('class'=>'grid_paging'));
        $current_page = intval($from/$limit)+1;
        $last_page = intval(($total_rows-1)/$limit)+1;
        $prev_page = max($current_page-1,0);
        $next_page = min($current_page+1,$last_page);
        $paging->add($this->get_paging_tag(0,'first',($from==0)));
        $paging->add($this->get_paging_tag(($prev_page-1)*$limit,'prev',($from==0)));
        $paging->add($this->get_paging_tag(($current_page-1)*$limit,$current_page,true));
        $paging->add($this->get_paging_tag(($next_page-1)*$limit,'next',($from>=($last_page-1)*$limit)));
        $paging->add($this->get_paging_tag(($last_page-1)*$limit,'last',($from>=($last_page-1)*$limit)));

        return $paging;
    }
    
    private function set_actual_orderby($order_var,$order_inv) {
        if (!isset($this->columns[$order_var]) || empty($this->columns[$order_var]->params['order_by'])) return;
        $order_by = $this->columns[$order_var]->params['order_by'];
        if ($order_by===true) $order_by = $this->columns[$order_var]->name.' asc';
        if ($order_inv) $order_by = preg_replace_callback('/(asc|desc)/iu',array($this,'set_actual_orderby_cb'),$order_by);
        $this->actual_orderby = $order_by;
    }
    
    private function set_actual_orderby_cb($m) {
        if (strtolower($m[0])=='asc') return 'desc';
        else return 'asc';
    }
    
    /**
     * @return html_block
     */
    public function render() {
        $res = new html_block();
        
        if (!empty($this->toolbar->members)) $res->add($this->toolbar);
        
        $this->table = new html_element('table',$this->table_attr);
        $this->table->add($thead = new html_element('thead'));
        $this->table->add($tbody = new html_element('tbody'));
        $res->add($this->table);
        
        $order_var = stripslashes(@$_GET[$this->order_var]);
        $order_inv = false;
        if ($order_var[0]=='-') {
            $order_var = substr($order_var,1);
            $order_inv = true;
        }
        $this->actual_orderby = $this->default_orderby; 
        if (!empty($order_var)) $this->set_actual_orderby($order_var,$order_inv);
        $order_icon = (preg_match('/\\s+asc($|\\s)/iu',$this->actual_orderby))?
            $this->sort_icon_asc:$this->sort_icon_desc;
        
        $thead->add($this->tr = new html_element('tr')); 
        
        // create the header row:
        $order_base_url = clone $this->base_url;
        if (isset($order_base_url->query[$this->paging_var])) unset($order_base_url->query[$this->paging_var]);
        foreach ($this->columns as $col_name => $col) {
            if (!empty($col->params['order_by'])) {
                if (empty($this->actual_orderby)) $this->actual_orderby = $col->params['order_by'];
                if (($order_var!=$col_name) || $order_inv) {
                    $col->params['header_link'] = $order_base_url->get_url(array($this->order_var=>$col_name));
                } else {
                    $col->params['header_link'] = $order_base_url->get_url(array($this->order_var=>'-'.$col_name));
                }
                if ($order_var==$col_name) $col->params['header_icon'] = $order_icon;
            }
            $this->tr->add($this->cell = new html_element('th'));
            if ($col->width>0) $this->cell->attr['width'] = $col->width;
            
            $inner = $col->render_th();
            if (!empty($inner)) $this->cell->add($inner);
        }
        
        $this->full_sql = $this->query;
        if (!empty($this->actual_orderby)) $this->full_sql .= ' order by '.$this->actual_orderby;
        
        if ($this->use_paging) $res->add($this->get_paging_block());
        
        // fetch the data
        $query = $this->xlink->query($this->full_sql);
        $tr_class_cnt = 0; 
        while($query->arow($this->row)) {
            $this->id_value = $this->row[$this->id_column];
            
            $tbody->add($this->tr = new html_element('tr'));
            if (!empty($this->tr_classes[$tr_class_cnt])) $this->tr->attr['class'] = $this->tr_classes[$tr_class_cnt];
            if (!isset($this->tr_classes[++$tr_class_cnt])) $tr_class_cnt = 0;
            
            foreach ($this->columns as $col_name => $col) {
                $this->tr->add($this->cell = new html_element('td'));
                if ($col->width>0) $this->cell->attr['width'] = $col->width;
                
                $inner = $col->render_td($this->row[$col_name]);
                if (!empty($inner)) $this->cell->add($inner);
            }
        }
        
        return $res;
    }
    
}
