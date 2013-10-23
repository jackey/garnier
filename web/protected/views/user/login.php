<?php

// Weibo
$o = new SaeTOAuthV2( WB_AKEY , WB_SKEY );
$code_url = $o->getAuthorizeURL(WB_CALLBACK_URL);

// Renren
$rennClient = new RennClient ( RENREN_APP_KEY, RENREN_APP_SECRET);
$state = uniqid ( 'renren_', true );
Yii::app()->session["renren_state"] = $state;
$renren_code_url = $rennClient->getAuthorizeURL (RENREN_CALLBACK_URL, 'code', $state);

// tencent
$tencent_code_url = OAuth::getAuthorizeURL(TENCENT_CALLBACK);

?>

<p><a href="<?=$code_url?>"><img src="<?php echo Yii::app()->request->baseUrl; ?>sinasdk/weibo_login.png" title="点击进入授权页面" alt="点击进入授权页面" border="0" /></a></p>
<p><a href="<?=$renren_code_url?>"><img src="<?php echo Yii::app()->request->baseUrl; ?>renrensdk/renren.png" title="点击进入授权页面" alt="点击进入授权页面" border="0" /></a></p>
<p><a href="<?=$tencent_code_url?>"><img src="<?php echo Yii::app()->request->baseUrl; ?>tencentsdk/logo.png" title="点击进入授权页面" alt="点击进入授权页面" border="0" /></a></p>