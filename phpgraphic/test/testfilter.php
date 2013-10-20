<?php 
define("ROOT", dirname(dirname(__FILE__)));
require_once ROOT."/graphic.php";

$image_path = ROOT."/test/before.jpg";
$to = ROOT."/test/after_test.jpg";

$grapher = new Graphic($image_path, $to);

$grapher->apply_filter();