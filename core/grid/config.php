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
        // 1 or more button links which automatically add the primary key to the link url 
        'buttons' => 'grid_column_buttons',  
    ),

    // the default table tag class 
    'table_attr' => array('class'=>'grid', 'cellspacing'=>0, 'cellpadding'=>5, 'border'=>1),
    
    'tr_classes' => array('grid_0','grid_1'),

    // if true, the grid will be split in pages
    'use_paging' => false,

    // paging and sorting URL parameter names
    'paging_var' => 'grf',
    'order_var' => 'gro',

    // default rows per page
    'rows_per_page' => 20,
);
