<<<<<<< HEAD
<?
if(empty($argv[1]) || empty($argv[2]) || !file_exists($argv[1])){
  die(0);
}

include __DIR__."/autoload.php";

=======
<?
if(empty($argv[1]) || empty($argv[2]) || !file_exists($argv[1])){
  die(0);
}

include __DIR__."/autoload.php";

>>>>>>> e0a0eba79 (init)
file_put_contents($MPDF_ARCH."/file_".intVal($argv[2]).".html", file_get_contents($argv[1]), FILE_APPEND);