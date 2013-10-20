<?php 
define("ROOT", dirname(dirname(__FILE__)));
require_once ROOT."/graphic.php";

$image_need_to_process = ROOT."/test/before.jpg";

$grapher = new Graphic();
$processed_image = $grapher->apply_filter($image_need_to_process);

$grapher->mergeImages(array($image_need_to_process, $processed_image));