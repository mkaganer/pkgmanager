<?php
// B.H.

/**
 * @desc This class is responsible of looking up and loading model classes, that extend sql_model class
 * and provide info about particular data table or query such as columns, keys e.t.c.
 *
 * This is a default class to be used by sql_connection::get_model() to load model class.
 * The class that will be used as "model provider" is set by "model_provider" config value
 * of the connection object, and the default value is
 * <code>$GLOBALS['_pkgman']->get('sql')->config["default_model_provider"]</code>
 *
 * This implementation of model provider looks for model classes in packages stored in "models" config
 * value of sql_connection which may be string or array of strings. It will try to look for a class named
 * pkgname_modelname with all model package names, and will create an instance of a class if it's found.
 *
 * Model class must inherit sql_model class.
 *
 * @author mkaganer
 */
class sql_model_provider {

	/**
	 * @var sql_connection
	 */
	public $link;

	public function __construct($link) {
        $this->link = $link;
	}

	/**
	 * @desc Load model by name, if not found, null will be returned
	 * @param string $model_name
	 * @return sql_model
	 */
	public function get_model($model_name) {
        $pkgman = pkgman_manager::get_instance();
        $models = $this->link->config['models'];
        if (is_string($models)) {
        	$class_name = "${models}_${model_name}";
        	if (!$pkgman->load_class($class_name)) return null;
        	return new $class_name($this->link,$model_name);
        } else {
        	foreach ($models as $pkg) {
	            $class_name = "${models}_${model_name}";
	            if (!$pkgman->load_class($class_name)) continue;
	            return new $class_name($this->link,$model_name);
        	}
        	return null;
        }
	}

}

?>