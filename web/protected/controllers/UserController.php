<?php

class UserController extends Controller {

    const NOT_LOGIN = 0x0001;
    const UNKNOWEN = 0x0010;
    const HTTP_METHOD_ERROR = 0x0100;

    protected $request = NULL;
    public $layout = "main";

    /**
     * Returns the data model based on the primary key given in the GET variable.
     * If the data model is not found, an HTTP exception will be raised.
     * @param integer $id the ID of the model to be loaded
     * @return User the loaded model
     * @throws CHttpException
     */
    public function loadModel($id) {
        $model = User::model()->findByPk($id);
        if ($model === null)
            throw new CHttpException(404, 'The requested page does not exist.');
        return $model;
    }

    public function returnJSON($data) {
        header("Content-Type: application/json");
        echo CJSON::encode($data);
        Yii::app()->end();
    }

    public static function getLoginUser() {
        return Yii::app()->session["user"];
    }

    public static function isLogin() {
        return Yii::app()->session["is_login"] == "true";
    }

    public static function isComplete() {
        $user = self::getLoginUser();
        if ($user["email"] == "" || !isset($user["email"])) {
            return FALSE;
        }
        return TRUE;
    }

    public function error($msg, $code) {
        return array(
            "data" => NULL,
            "error" => array(
                "code" => $code,
                "message" => $msg,
            ),
        );
    }

    public function beforeAction($action) {
        $this->request = Yii::app()->getRequest();
        return parent::beforeAction($action);
    }

    public function actionLogin() {
        // Normal login
        if ($this->request->isPostRequest) {
            $email = $this->request->getPost("email");
            $password = $this->request->getPost("password");
            $row = Yii::app()->db->createCommand()
                    ->select("*")
                    ->from("user")
                    ->where("email = :email AND password =:password", array(":email" => $email, ":password" => md5($password)))
                    ->queryRow();
            // 查询到后， 自动登录
            if ($row) {
                Yii::app()->session["user"] = $row;
                Yii::app()->session["is_login"] = "true";

                return $this->returnJSON(array(
                            "data" => "login success",
                            "error" => NULL
                ));
            } else {
                return $this->returnJSON($this->error("login failed", ERROR_LOGIN_FAILED));
            }
        } else {
            if (!self::isLogin()) {
                $this->render("login");
            } else {
                $this->redirect("index.php");
            }
        }
    }

    public function actionLogout() {
        Yii::app()->session->clear();
        Yii::app()->session->destroy();

        $this->redirect("index.php");
    }

    public function actionInfo() {
        $this->render("info");
    }

    public function actionTencentCallback() {
        header("Content-Type: text/html;charset=utf-8");
        if ($this->request->getParam("code")) {
            $code = $_GET['code'];
            $openid = $_GET['openid'];
            $openkey = $_GET['openkey'];
            //获取授权token
            $url = OAuth::getAccessToken($code, TENCENT_CALLBACK);
            $r = Http::request($url);
            parse_str($r, $out);
            //存储授权数据
            if (isset($out['access_token'])) {
                $access_token = Yii::app()->session["tencent_access_token"] = $out["access_token"];
                $_SESSION['t_access_token'] = $out['access_token'];
                $_SESSION['t_refresh_token'] = $out['refresh_token'];
                $_SESSION['t_expire_in'] = $out['expires_in'];
                $_SESSION['t_code'] = $code;
                $_SESSION['t_openid'] = $openid;
                $_SESSION['t_openkey'] = $openkey;

                //验证授权
                $r = OAuth::checkOAuthValid();
                if ($r) {
                    // Step1, 授权成功后，自动注册和自动登录
                    // 自动注册之前，先判断用户是否已经存在，如果存在我们只做自动登录的操作
                    $tencent_user = json_decode(Tencent::api("user/info"), TRUE);
                    $row = Yii::app()->db->createCommand()
                            ->select('*')
                            ->from("user")
                            ->where("sns_user_id = :user_id", array(":user_id" => $tencent_user["data"]["openid"]))
                            ->queryRow();
                    // 如果已经注册，我们则只自动登录
                    if ($row) {
                        Yii::app()->session["user"] = $row;
                        Yii::app()->session["is_login"] = "true";
                        $this->redirect("index.php");
                    }
                    // 没有注册的话，我们则自动创建一条用户记录，然后再实现自动登录
                    else {
                        $newUser = array(
                            "nickname" => $tencent_user['data']["nick"],
                            "sns_user_id" => $tencent_user['data']["openid"],
                            "from" => "tencent",
                            "email" => "",
                            "tel" => "",
                            "datetime" => date("Y-m-d m:h:s"),
                            "avadar" => "0",
                            "tencent_auth_code" => $access_token,
                        );
                        $mUser = new User();
                        $mUser->unsetAttributes();
                        $mUser->setIsNewRecord(true);
                        foreach ($newUser as $property => $value) {
                            $mUser->{$property} = $value;
                        }
                        $mUser->insert();
                        $newUser["user_id"] = $mUser->getPrimaryKey();

                        //自动注册后，我们还需要自动登录
                        Yii::app()->session["user"] = $newUser;
                        Yii::app()->session["is_login"] = "true";

                        // 最后跳转到注册完善页面
                        return $this->redirect("index.php?r=user/register");
                    }
                } else {
                    die("验证失败");
                }
            } else {
                die("转换数据失败");
            }
        } else {
            $this->redirect("index.php");
        }
    }

    public function actionRenrencallback() {
        // 授权后获取access token
        if ($this->request->getParam("code")) {

            $rennClient = new RennClient(RENREN_APP_KEY, RENREN_APP_SECRET);
            // 处理code -- 根据code来获得token
            $keys = array();

            // 验证state，防止伪造请求跨站攻击
            $state = $_REQUEST ['state'];
            if (empty($state) || $state !== Yii::app()->session['renren_state']) {
                echo '非法请求！';
                exit();
            }
            Yii::app()->session["renren_state"] = "";

            // 获得code
            $keys ['code'] = $_REQUEST ['code'];
            $keys ['redirect_uri'] = RENREN_CALLBACK_URL;
            $token = $rennClient->getTokenFromTokenEndpoint('code', $keys);
            ob_clean();
            Yii::app()->session["renren_access_token"] = $token->accessToken;
            $access_token = $token->accessToken;

            // 获取用户基础信息来自动注册
            $renren_user_service = $rennClient->getUserService();
            $renren_user = $renren_user_service->getUserLogin();

            // 自动注册之前需要确定用户是否已经存在数据库中
            $row = Yii::app()->db->createCommand()
                    ->select("*")
                    ->from("user")
                    ->where("sns_user_id = :sns_user_id", array(":sns_user_id" => $renren_user["id"]))
                    ->queryRow();
            // 如果用户已经注册了，那么我们实现自动登录
            if ($row) {
                Yii::app()->session["user"] = $row;
                Yii::app()->session["is_login"] = "true";
            }
            // 如果没有注册，那我们自动生成一条用户记录
            else {
                $newUser = array(
                    "nickname" => $renren_user["name"],
                    "sns_user_id" => $renren_user["id"],
                    "from" => "renren",
                    "email" => "",
                    "tel" => "",
                    "datetime" => date("Y-m-d m:h:s"),
                    "avadar" => "0",
                    "weibo_auth_code" => $access_token,
                );
                $mUser = new User();
                $mUser->unsetAttributes();
                $mUser->setIsNewRecord(true);
                foreach ($newUser as $property => $value) {
                    $mUser->{$property} = $value;
                }
                $mUser->insert();
                $newUser["user_id"] = $mUser->getPrimaryKey();

                //自动注册后，我们还需要自动登录
                Yii::app()->session["user"] = $newUser;
                Yii::app()->session["is_login"] = "true";

                // 最后跳转到注册完善页面
                return $this->redirect("index.php?r=user/register");
            }
        } else {
            $this->redirect("index.php");
        }
    }

    public function actionSinacallback() {
        $o = new SaeTOAuthV2(WB_AKEY, WB_SKEY);
        $tmpUser = NULL;
        $error = NULL;

        // Callback from Sina.
        if (isset($_REQUEST['code'])) {
            $keys = array();
            $keys['code'] = $_REQUEST['code'];
            $keys['redirect_uri'] = WB_CALLBACK_URL;
            $token = $o->getAccessToken('code', $keys);

            $access_token = Yii::app()->session["weibo_access_token"] = $token["access_token"];

            // 在这里我们要自动注册一个账户给当前的weibo用户
            // Step 1, 先从Sina获取基本账户资料
            $c = new SaeTClientV2(WB_AKEY, WB_SKEY, $access_token);
            $basic_account = $c->show_user_by_id($token["uid"]);

            // Step2, 检查下用户是否已经存在在数据库里面
            $user = Yii::app()->db->createCommand()
                    ->select("*")
                    ->from("user")
                    ->where("sns_user_id = :sns_user_id", array(":sns_user_id" => $basic_account["idstr"]))
                    ->queryRow();

            // Step 2-1, 如果已经注册，则需要自动登录
            if ($user) {
                Yii::app()->session["user"] = $user;
                Yii::app()->session["is_login"] = "true";

                // 自动登录后，返回首页
                return $this->redirect("index.php");
            }
            // Step 3, 如果没有注册，就要实现自动注册功能，然后再自动登录系统.
            else {
                // 自动注册，获取用户的screen_name
                $newUser = array(
                    "nickname" => $basic_account["screen_name"],
                    "from" => "weibo",
                    "email" => "",
                    "tel" => "",
                    "datetime" => date("Y-m-d m:h:s"),
                    "avadar" => "0",
                    "weibo_auth_code" => $access_token,
                    "sns_user_id" => $basic_account["idstr"],
                );
                $mUser = new User();
                $mUser->unsetAttributes();
                $mUser->setIsNewRecord(true);
                foreach ($newUser as $property => $value) {
                    $mUser->{$property} = $value;
                }
                $ret = $mUser->insert();
                $newUser["user_id"] = $mUser->getPrimaryKey();

                // 实现自动登录
                Yii::app()->session["is_login"] = "true";
                Yii::app()->session["user"] = $newUser;
            }

            // Step 4, 自动注册完成后，跳转到注册页面让用户完善资料。
            return $this->redirect("index.php?r=user/register");
        } else {
            $this->redirect("index.php?r=user/login");
        }
    }

    public function actionRegister() {
        //登录并且完成注册后直接跳转到首页
        if (self::isLogin() && self::isComplete()) {
            return $this->redirect("index.php");
        }

        // 输出注册表单
        if (!$this->request->isPostRequest) {
            // 这里有2种情况: a. 用户直接点击注册， b. 用户通过第三方注册
            // 这里不需要做判断
            return $this->render("register", array("user" => self::getLoginUser()));
        }
        // 处理注册表单提交
        else {
            // 用户的头像
            $uploadFile = CUploadedFile::getInstanceByName("avadar");
            $to = "";
            if ($uploadFile) {
                // 上传文件之前检查目录
                $uploadFolder = ROOT . "/uploads/" . $_POST["nickname"];
                if (!is_dir($uploadFolder)) {
                    mkdir($uploadFolder, 0777);
                }
                $newname = time() . "_avadar." . $uploadFile->getExtensionName();
                $to = $uploadFolder . "/" . $newname;
                $uploadFile->saveAs($to);
            }
            // 通过第三方注册
            if ($user = self::getLoginUser()) {
                $newUser = NULL;
                if ($user["from"] == "weibo") {
                    $newUser = array(
                        "user_id" => $user["user_id"],
                        "nickname" => $this->request->getPost("nickname"),
                        "password" => md5($this->request->getPost("password")),
                        "from" => "weibo",
                        "email" => $this->request->getPost("email"),
                        "tel" => $this->request->getPost("tel"),
                        "datetime" => date("Y-m-d m:h:s"),
                        "avadar" => str_replace(ROOT, "", $to),
                        "weibo_auth_code" => Yii::app()->session["weibo_access_token"],
                        "sns_user_id" => $user["sns_user_id"],
                    );
                } elseif ($user["from"] == "tencent") {
                    $newUser = array(
                        "user_id" => $user["user_id"],
                        "nickname" => $this->request->getPost("nickname"),
                        "password" => md5($this->request->getPost("password")),
                        "from" => "tencent",
                        "email" => $this->request->getPost("email"),
                        "tel" => $this->request->getPost("tel"),
                        "datetime" => date("Y-m-d m:h:s"),
                        "avadar" => str_replace(ROOT, "", $to),
                        "tencent_auth_code" => Yii::app()->session["tencent_access_token"],
                        "sns_user_id" => $user["sns_user_id"],
                    );
                } elseif ($user["from"] == "renren") {
                    $newUser = array(
                        "user_id" => $user["user_id"],
                        "nickname" => $this->request->getPost("nickname"),
                        "password" => md5($this->request->getPost("password")),
                        "from" => "weibo",
                        "email" => $this->request->getPost("email"),
                        "tel" => $this->request->getPost("tel"),
                        "datetime" => date("Y-m-d m:h:s"),
                        "avadar" => str_replace(ROOT, "", $to),
                        "renren_auth_code" => Yii::app()->session["renren_access_token"],
                        "sns_user_id" => $user["sns_user_id"],
                    );
                }

                if (!$newUser) {
                    return $this->returnJSON($this->error("it is not post", ERROR_UNKNOW));
                }
                
                //添加之前检查 email 是否已经注册
                $row = Yii::app()->db->createCommand()
                        ->select("*")
                        ->from("user")
                        ->where("email = :email", array(":email" => $newUser["email"]))
                        ->queryRow();
                if ($row) {
                    return $this->returnJSON($this->error("email already exits", USER_IS_EXIT_ERROR));
                }
                $user = new User();
                $user->setIsNewRecord(false);
                foreach ($newUser as $property => $value) {
                    $user->{$property} = $value;
                }
                $ret = $user->update();

                //更新session得用户数据
                Yii::app()->session["user"] = ($newUser);
            }
            // 直接注册
            else {
                $newUser = array(
                    "nickname" => $this->request->getPost("nickname"),
                    "password" => md5($this->request->getPost("password")),
                    "from" => "",
                    "email" => $this->request->getPost("email"),
                    "tel" => $this->request->getPost("tel"),
                    "datetime" => date("Y-m-d m:h:s"),
                    "avadar" => str_replace(ROOT, "", $to),
                );
                //添加之前检查 email 是否已经注册
                $row = Yii::app()->db->createCommand()
                        ->select("*")
                        ->from("user")
                        ->where("email = :email", array(":email" => $newUser["email"]))
                        ->queryRow();
                if ($row) {
                    return $this->returnJSON($this->error("email already exits", USER_IS_EXIT_ERROR));
                }
                // 添加新的用户数据
                $mUser = new User();
                $mUser->setIsNewRecord(true);
                foreach ($newUser as $property => $value) {
                    $mUser->{$property} = $value;
                }
                $mUser->insert();

                $user_id = $mUser->getPrimaryKey();
                $newUser["user_id"] = $user_id;

                // 自动登录
                Yii::app()->session["user"] = $newUser;
                Yii::app()->session["is_login"] = "true";
            }

            return $this->returnJSON(array(
                        "data" => $newUser,
                        "error" => NULL,
            ));
        }
    }

    /**
     * Performs the AJAX validation.
     * @param User $model the model to be validated
     */
    protected function performAjaxValidation($model) {
        if (isset($_POST['ajax']) && $_POST['ajax'] === 'user-form') {
            echo CActiveForm::validate($model);
            Yii::app()->end();
        }
    }

}
