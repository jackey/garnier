<?php 
define("ROOT", dirname(dirname(__FILE__)));
require_once ROOT."/graphic.php";

$to = ROOT."/test/testcreate.png";

$grapher = new Graphic($to, $to);

$grapher->recreate_to_diamond($to, 200, 200, 5);