<?php 
define("ROOT", dirname(dirname(__FILE__)));
require_once ROOT."/graphic.php";

$image_path = ROOT."/test/test.png";
$to = ROOT."/test/test_o.png";

$grapher = Instagraph::factory($image_path, $to);

$grapher->kelvin();