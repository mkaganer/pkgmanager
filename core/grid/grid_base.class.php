<?php
// B.H.

abstract class grid_base {
    
    /**
     * @var pkgman_package
     */
    public $pkg;
    
        
    /**
     * @var sql_connection
     */
    public $xlink;
    
    /**
     * @var html_url
     */
    public $base_url;
    
    public function __construct(html_url $base_url=null) {
        $this->pkg = pkgman_manager::getp('grid');
        if (!empty($xlink)) $this->xlink = $xlink;
        elseif (is_object($this->pkg->config['xlink'])) $this->xlink = $this->pkg->config['xlink'];
        else $this->xlink = sql_connection::get_connection();
        if (empty($this->xlink)) throw new Exception('No SQL connection');
        $this->base_url = $base_url;
    }
    
    public abstract function render();
    
}