<?php
// B.H.

class grid_column_buttons extends grid_column {
    
    public function render_td($value) {
        $id = $this->table->id_value;
        return '['.$id.']';
    }
    
}