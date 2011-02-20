<?php
// B.H.

class grid_column_buttons extends grid_column {
    
    public function render_td($value) {
        $id = $this->table->id_value;
        $id_param = isset($this->params['id_param'])?$this->params['id_param']:'id';
        $out = '';
        for($i=0;isset($this->params[$i]);$i++) {
            if (!is_array($this->params[$i])) continue;
            $p =& $this->params[$i];
            if ($p['url'] instanceof html_url) {
                $url = $p['url']->get_url(array($id_param => $id)); 
            } else {
                $url = str_replace('{#ID#}',$id,$p['url']);
            }
            $out .= '<a class="grid-button';
            if ($p['class']) $out .= ' '.$p['class'];
            $out .= '" href="'.htmlspecialchars($url).'">';
            $out .= htmlspecialchars($p['title']).'</a>';
        }
        return $out;
    }
    
}