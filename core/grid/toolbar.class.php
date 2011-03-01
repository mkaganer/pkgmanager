<?php
// B.H.

class grid_toolbar extends html_element {
    
    public $is_empty;
    
    public function __construct() {
        parent::__construct('div',array('class'=>'grid_toolbar'));
    }
    
    public function add_button($url,$text,$icon=null, $icon_only=false,$disabled=false) {
        $button_class = 'grid-button';
        if (!empty($icon)) $button_class .= ' '.$icon;
        if ($icon_only) $button_class .= ' grid-button-icononly';
        if ($disabled) $button_class .= ' grid-button-disabled';
        $this->add('<a href="'.htmlspecialchars($url).'" class="'.$button_class.'">'.
            htmlspecialchars($text).'</a>');
    }
    
}