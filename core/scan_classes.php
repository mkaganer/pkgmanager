<?php
// B.H.

function process_path($path) {
  if (is_dir($path)) {
    //echo "dir: [$path]\n";
    $path = rtrim($path,"\\/")."/";
    foreach(glob($path."*",GLOB_ONLYDIR) as $subdir) process_path($subdir);
    foreach(glob($path."*.class.php") as $file) process_path($file);
  } else {
    process_file($path);
  }
}

function process_file($file) {
  global $root_path;
  $file = realpath($file);
  if (!preg_match('#\\.class\\.php$#',$file)) return; 
  $dirname = rtrim(dirname($file),"\\/");
  $basename = basename($file);
  echo "[$file]: ";
  if (strpos($dirname,$root_path)!==0) {
    echo "not under the root!\n"; return;
  }
  $pkg = trim(substr($dirname,strlen($root_path)),"\\/");
  if (!empty($pkg)) {
    $pkg = preg_split("#[\\\\/]+#",$pkg);
    $pkgpref = implode("_",$pkg);
    //echo "pkg=[$pkgpref] ";
    if (strpos($basename,$pkgpref)===0) {
      $new_b = ltrim(substr($basename,strlen($pkgpref)),"_");
      $new_p = $dirname."/".$new_b;
      echo "=>[$new_p]";
      rename($file,$new_p);
      $file = $new_p;
      $basename = $new_b;
    }
  }
  echo "\n";
}

// ********* ENTRY *******

if (!isset($argv[1])) die("Usage php $argv[0] path1 path2 ...");

$paths = $argv; array_shift($paths);
$root_path = realpath(getcwd());

while ($p = array_shift($paths)) {
  foreach(glob($p) as $pp) process_path(realpath($pp));
}

?>
