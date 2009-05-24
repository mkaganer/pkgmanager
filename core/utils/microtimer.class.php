<?php
// B.H.

/**
 * @desc This class is used for profiling and debuging 
 * @author mkaganer
 *
 */
class utils_microtimer {

  public $snapshots = array(); // diff from $base
  
  private $base = array(); // (int)seconds (float)microseconds
  
  public function __construct($base=null) {
    $this->base = explode(" ",is_null($base)?microtime():$base);
    if (!is_null($base)) $this->snapshot("__construct",$base);
  }
  
  public function snapshot($name='snapshot',$base=null) {
    $snap = explode(" ",microtime());
    $base = is_null($base)?null:explode(" ",$base);
    // relative to the object's base
    $incr = (((float)($snap[0] - $this->base[0])) + $snap[1] - $this->base[1]);
    // relative to $base
    $diff = is_null($base)?null:(((float)($snap[0] - $base[0])) + $snap[1] - $base[1]);
    $this->snapshots[] = array(
      $incr,$diff,$name,
    );
  }
  
  public function out() {
    $res = <<<EOT
<table dir="ltr" style="border-collapse:collapse;" border="1" cellpadding="2">
<tr>
  <th>#</th>
  <th>INCR</th>
  <th>DIFF</th>
  <th>DESCR</th>
</tr>
EOT;
    foreach ($this->snapshots as $i => $snap) {
      $descr = htmlspecialchars(wordwrap($snap[2],150));
      $incr = sprintf("%.1f",$snap[0]*1e3);
      $diff = empty($snap[1])?"N/A":sprintf("%.1f",$snap[1]*1e3);
      $res .= <<<EOT
<tr>
  <td valign="top">$i</td>
  <td valign="top" align="right">$incr</td>
  <td valign="top" align="right">$diff</td>
  <td valign="top"><pre style="margin:0;">$descr</pre></td></tr>
EOT;
    }
    $res .= "</table>";
    echo $res;
  }
}