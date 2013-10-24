<?php

// change the following paths if necessary
$yii=dirname(__FILE__).'/yii/framework/yii.php';
$config=dirname(__FILE__).'/protected/config/main.php';

// require sinasdk
$sinasdk = dirname(__FILE__)."/sinasdk";
include_once( $sinasdk.'/config.php' );
include_once( $sinasdk.'/saetv2.ex.class.php' );

// require renrensdk
$renrensdk = dirname(__FILE__)."/renrensdk";
require_once $renrensdk."/config.php";
require_once $renrensdk."/rennclient/RennClient.php";

// require tencentsdk
$tencentsdk = dirname(__FILE__)."/tencentsdk";
require_once $tencentsdk."/Config.php";
require_once $tencentsdk."/Tencent.php";
OAuth::init(TENCENT_KEY, TENCENT_SECRET);
Tencent::$debug = FALSE;

define("USER_IS_EXIT_ERROR", 503);
define("NO_LAST_IMAGE_ERROR", 502);
define("NO_LOGIN_ERROR", 501);
define("WRONG_FILE_TYPE_ERROR", 500);
define("ERROR_LOGIN_FAILED", 504);
define("ERROR_VOTE_LIMIT", 505);
define("ERROR_UNKNOW", 506);


// remove the following lines when in production mode
defined('YII_DEBUG') or define('YII_DEBUG',true);
// specify how many levels of call stack should be shown in each log message
defined('YII_TRACE_LEVEL') or define('YII_TRACE_LEVEL',3);
define("ROOT", dirname(__FILE__));

require_once($yii);
Yii::createWebApplication($config)->run();
