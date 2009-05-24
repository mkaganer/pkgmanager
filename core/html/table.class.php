<?php
// B.H.

class html_table extends html_element {

  public $columns;
  public $data;
  
  public $back_url;
  public $page_param = null; // GET parameter for page numbers
  public $page_prompt = null; // text label to be put before the pageing <select>
  public $per_page = 10;
  
  // params: html_column instances for each of the table's columns
  public function __construct() {
    parent::__construct('table');
    $this->attr['border'] = 0;
    $this->attr['cellspacing'] = 0;
    $this->attr['cellpadding'] = 0;
    $args = func_get_args();
    foreach ($args as $key => $col) {
      if ($col instanceof html_column) $this->columns[$key] = $col;
      else throw new Exception("Invalid column");
    }
  }

  protected function render() {
    if (!empty($this->back_url)&&!empty($this->page_param)) {
      $paging = true;
      $cpage = isset($_GET[$this->page_param])?(int)$_GET[$this->page_param]:0;
      $per_page = max((int)$this->per_page,2);
      $rows = count($this->data);
      $pages = ceil($rows/$per_page);
      if ($cpage>=$pages) $cpage = $pages-1;
    } else {
      $paging = false;
    }
    
    $at = '';
    foreach($this->attr as $atk => $atv) $at .= " ${atk}=\"".htmlspecialchars($atv)."\"";
    $res = "<table${at}>";
    $res .= "<tr>";
    foreach ($this->columns as $col) $res .= $col->render_header();
    $res .= "</tr>";
    if (is_array($this->data)) {
      $i = -1;
      foreach ($this->data as $row_key => $drow) {
        $i++;
        if ($paging) {
          $p = (int)floor($i/$per_page);
          if ($p!=$cpage) continue;
        }
        $res .= "<tr>";
        foreach ($this->columns as $key => $col) $res .= $col->render_data($i,$row_key,$drow[$key]);
        $res .= "</tr>";
      }
    }
    if ($paging) {
      $num_cols = count($this->columns);
      $options = '';
      for($i=0;$i<$pages;$i++) {
        $url = htmlspecialchars($this->back_url->get_url(array($this->page_param => $i)));
        $sel = ($i==$cpage)?" selected=\"selected\"":"";
        $options .= "<option value=\"$url\" ${sel}>".($i+1)."</option>";
      }
      $prompt = (!empty($this->page_prompt))?$this->page_prompt.'&nbsp;':'';
      $res .= <<<EOT
<tr><td colspan="$num_cols" class="table_paging">
  ${prompt}<select onchange="document.location = this.value">$options</select>
</td></tr>
EOT;
    }
    $res .= "</table>";
    return $res;
  }
  

}