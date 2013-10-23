<?php

class PhotoController extends Controller {

    public $layout = 'main';
    public $request = NULL;

    public function init() {
        return parent::init();
    }

    public function beforeAction($action) {
        $this->request = Yii::app()->getRequest();
        return parent::beforeAction($action);
    }

    public function returnJSON($data) {
        header("Content-Type: application/json");
        echo CJSON::encode($data);
        Yii::app()->end();
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

    /**
     * Returns the data model based on the primary key given in the GET variable.
     * If the data model is not found, an HTTP exception will be raised.
     * @param integer $id the ID of the model to be loaded
     * @return Photo the loaded model
     * @throws CHttpException
     */
    public function loadModel($id) {
        $model = Photo::model()->findByPk($id);
        if ($model === null)
            throw new CHttpException(404, 'The requested page does not exist.');
        return $model;
    }
    
    public function actionListPhotoes() {
        print "hello world";
    }
    
    /**
     * 返回最后一个被处理的图片
     */
    public function actionLastPhoto() {
        $tmpImage = Yii::app()->session["tmp_upload_image"];
        $user = self::getLoginUser();
        // 用户已经登录后，我们自动把未保存的图片添加到数据库
        if ($user) {
            if (!$tmpImage) {
                return $this->returnJSON($this->error("no last image", 502));
            }
            // 文件上传后，保存数据库记录
            $newPhoto = array(
                "path" => $tmpImage,
                "user_id" => $user["user_id"],
                "vote" => 0,
                "datetime" => date("Y-m-d h:m:s"),
            );
            $mPhoto = new Photo();
            $mPhoto->unsetAttributes();
            $mPhoto->setIsNewRecord(true);
            $mPhoto->attributes = $newPhoto;
            $mPhoto->insert();

            // 插入新的数据后，我们要以JSON格式返回给客户端
            $newPhoto["photo_id"] = $mPhoto->getPrimaryKey();
            // 然后清除掉session离的tmp_upload_image
            Yii::app()->session["tmp_upload_image"] = "";
            return $this->returnJSON(array(
                "data" => $newPhoto,
                "error" => NULL
            ));
        }
        //如果用户没有登录，则我们返回一个错误消息给用户，提醒他登录
        else {
            return $this->returnJSON($this->error("not login", 501));
        }
    }

    public function actionUploadImage() {
        // Post image
        if ($this->request->isPostRequest) {
            $fileUpload = CUploadedFile::getInstanceByName("image");
            $mimeName = $fileUpload->getType();
            $allowMimes = array(
                "image/jpeg",
                "image/png"
            );
            if (!in_array($mimeName, $allowMimes)) {
                $this->returnJSON($this->error("wrong file type", 500));
            } else {
                if (!self::isLogin()) {
                    // 如果没有登录，那先保存文件到临时目录，然后登录后继续处理
                    $to = ROOT."/uploads/tmp/tmp".  rand(0, 100000). time().".". $fileUpload->getExtensionName();
                    if (!is_dir(ROOT."/uploads/tmp")) {
                        mkdir(ROOT."/uploads/tmp", 0777);
                    }
                    Yii::app()->session["tmp_upload_image"] = str_replace(ROOT, "", $to);
                    $fileUpload->saveAs($to);
                    
                    //返回给客户端，用户没有登录
                    return $this->returnJSON(array(
                        "data" => NULL,
                        "error" => array(
                            "message" => "not login",
                            "code" => 501
                        ),
                    ));
                }
                else {
                    $user = self::getLoginUser();
                    $to = ROOT."/uploads/".$user['nickname']. "/". time(). "_".$fileUpload->getName();
                    $fileUpload->saveAs($to);
                    
                    // 文件上传后，保存数据库记录
                    $newPhoto = array(
                        "path" => str_replace(ROOT, "", $to),
                        "user_id" => $user["user_id"],
                        "vote" => 0,
                        "datetime" => date("Y-m-d h:m:s"),
                    );
                    $mPhoto = new Photo();
                    $mPhoto->unsetAttributes();
                    $mPhoto->setIsNewRecord(true);
                    $mPhoto->attributes = $newPhoto;
                    $mPhoto->insert();
                    
                    // 插入新的数据后，我们要以JSON格式返回给客户端
                    $newPhoto["photo_id"] = $mPhoto->getPrimaryKey();
                    return $this->returnJSON(array(
                        "data" => $newPhoto,
                        "error" => NULL
                    ));
                }
            }
        } else {
            $tmpImage = Yii::app()->session['tmp_upload_image'];
            $this->render("uploadimage", array("tmpImage" => $tmpImage));
        }
    }

}
