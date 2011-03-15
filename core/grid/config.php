<?php
// B.H.

/**
 * @desc Package config file (optional)
 * Predefined variables: $pkg_name, $path, $prefix
 */

/**
 * @desc Package descriptor - Default config for the package
 * @var array $config
 */
$config = array(

    // which column in the query holds the primary key (grid_table::id_column can override this)
    'default_id_column' => 'id',

    'column_types' => array(
        // default column class
        0 => 'grid_column',
        'boolean' => 'grid_column_boolean',
        'datetime' => 'grid_column_datetime', 
        // 1 or more button links which automatically add the primary key to the link url
        'buttons' => 'grid_column_buttons',  
    ),
    
    'element_types' => array(
        0 => 'grid_element_text',
        'special' => 'grid_element_special',
        'hidden' => 'grid_element_hidden',
        'password' => 'grid_element_password',
        'date' => 'grid_element_date',
        'datetime' => 'grid_element_date',
        'checkbox' => 'grid_element_checkbox',
        
    ),

    // the default table tag class 
    'table_attr' => array('class'=>'grid', 'cellspacing'=>0, 'cellpadding'=>5, 'border'=>1),
    
    'tr_classes' => array('grid_0','grid_1'),

    // paging and sorting URL parameter names
    'paging_var' => 'grf',
    'order_var' => 'gro',

    // default rows per page
    'rows_per_page' => 20,
    
    'paging_icons' => array(
        'page' => 'Page #%d',
        'prev' => array('ui-icon-seek-prev','Previous page'),
        'next' => array('ui-icon-seek-next','Next page'),
        'first' => array('ui-icon-seek-first','First page'),
        'last' => array('ui-icon-seek-end','Last page'),
    ),
    
    'sort_icon_asc' => 'ui-icon-triangle-1-n',
    'sort_icon_desc' => 'ui-icon-triangle-1-s',
    
    'submit_text' => 'Save',
);
