<?php
// B.H.

class grid_column_boolean extends grid_column {
    
    public function render_td($value) {
        $cell = $this->table->cell;
        $cell->attr['style'] = "vertical-align:middle;text-align:center";
        if (!empty($this->params['is_yesno'])) $value = (($value=='Y')||($value=='y'));
        $icon = empty($value)?'ui-icon-close':'ui-icon-circle-check';
        return '<span class="ui-icon '.$icon.'" style="margin:auto"></span>';
    }
    
}