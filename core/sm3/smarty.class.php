<?php
// B.H.

// preload Smarty class if it is not already exists
if (!class_exists('Smarty',false)) {
    $pkg = pkgman_manager::getp('sm3');
    $smarty_path = rtrim($pkg->config['smarty_path'],'/\\');
    if (!file_exists($smarty_path) || !file_exists($smarty_path.DIRECTORY_SEPARATOR.'Smarty.class.php')) 
        throw new Exception('sm3: smarty_path is not valid');
    require_once ($smarty_path.DIRECTORY_SEPARATOR.'Smarty.class.php');
}

class sm3_smarty extends Smarty {
    
    protected static $default_instance;
    
    /**
     * self::$default_instance
     * @return sm3_smarty
     */
    public static function get($clone=true) {
        if (!empty(self::$default_instance) && $clone) {
            $sm = clone self::$default_instance;
            $sm->start_time = $sm->_get_time();
            return $sm;
        }
        $sm = new self();
        if ($clone && empty(self::$default_instance)) self::$default_instance = $sm;
        return $sm; 
    }
    
    public function __construct() {
        parent::__construct();
        $this->import_pkg_config();
    }
    
    protected function import_pkg_config() {
        $pkg = pkgman_manager::getp('sm3');
        if (!empty($pkg->config['template_dir'])) {
            if (is_array($pkg->config['template_dir'])) {
                $this->template_dir = array_merge($this->template_dir,$pkg->config['template_dir']);
            } else $this->template_dir[] = $pkg->config['template_dir'];
        }
        if (!empty($pkg->config['compile_dir'])) $this->compile_dir = $pkg->config['compile_dir'];
        if (!empty($pkg->config['plugins_dir'])) {
            if (is_array($pkg->config['plugins_dir'])) {
                $this->plugins_dir = array_merge($this->plugins_dir,$pkg->config['plugins_dir']);
            } else $this->plugins_dir[] = $pkg->config['plugins_dir'];
        }
        if (!empty($pkg->config['cache_dir'])) $this->cache_dir = $pkg->config['cache_dir'];
        if (!empty($pkg->config['config_dir'])) $this->config_dir = $pkg->config['config_dir'];
    }
    
}