<?php

class PhotoController extends Controller {

    public $layout = 'main';
    /**
     *
     * @var CHttpRequest
     */
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
        $allowOrderby = array("vote", "datetime");
        
        $num = $this->request->getParam("num");
        $page = $this->request->getParam("page"); // from 1 - x
        $orderby = $this->request->getParam("orderby");
        if (!in_array($orderby, $allowOrderby)) {
            $orderby = "datetime";
        }
        $order = strtolower($this->request->getParam("order"));
        if (!in_array($order, array("desc", "asc"))) {
            // 最新的照片在上面
            $order = "desc";
        }
        if (!is_numeric($page)) {
            $page = 1;
        }
        if (!is_numeric($num)) {
            $num = 50;
        }
        $offset = ( $page - 1 ) * $num;
        
        if ($orderby == "datetime") {
            $orderby = "photo.datetime";
        }
        else {
            $orderby = "vote";
        }
        
        $sql = 'select '
                . 'user.user_id as user_id, '
                . 'count(vote.vote_id) as vote, '
                . 'concat("url" ,photo.path) as path, '
                . 'user.nickname as nickname, '
                . 'photo.photo_id as photo_id '
                . 'from photo '
                . 'left join user on user.user_id=photo.user_id '
                . 'left join vote on vote.photo_id=photo.photo_id '
                . 'group by photo.photo_id '
                . 'order by '. $orderby. ' '. $order.' '
                . 'limit '. $offset. ', '. $num;
        $rows = Yii::app()->db->createCommand($sql)
                ->queryAll(TRUE, array(":num" => $num, ":offset" => $offset));
                
        $arrayPhotoes = array();
        
        return $this->returnJSON(array(
            "data" => $rows,
            "error" => null
        ));
    }
    
    public function actionVote() {
        if ($this->request->isPostRequest) {
            $photo_id = $this->request->getPost("photo_id");
            $user_id = $this->request->getPost("user_id");
            $datetime = date("Y-m-d h:m:s");
            // Step1, 先判断用户是否一天投了10次票
            $rows = Yii::app()->db->createCommand()
                    ->select("*")
                    ->from("vote")
                    ->where("date(datetime) = :date AND photo_id = :photo_id AND user_id = :user_id", array(":date" => date("Y-m-d"), ":photo_id" => $photo_id, ":user_id" => $user_id))
                    ->queryAll();
            if (count($rows) >= 10) {
                return $this->returnJSON($this->error("10 times vote already", ERROR_VOTE_LIMIT));
            }
            // 如果没有超过限制, 则添加一条记录
            else {
                $newVote = array(
                    "photo_id" => $photo_id,
                    "user_id" => $user_id,
                    "datetime" => $datetime,
                );
                $mVote = new Vote();
                $mVote->setIsNewRecord(true);
                $mVote->unsetAttributes();
                $mVote->attributes = $newVote;
                $mVote->insert();
                $newVote['vote_id'] = $mVote->getPrimaryKey();
                
                return $this->returnJSON(array(
                    "data" => $newVote,
                    "error" => NULL
                ));
            }
        }
        else {
            $mPhoto = Photo::model();
            $photo = $mPhoto->findByPk("7");
            $this->render("votephoto", array("photo" => $photo, "user" => $this->getLoginUser()));
        }
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
                return $this->returnJSON($this->error("no last image", NO_LAST_IMAGE_ERROR));
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
            return $this->returnJSON($this->error("not login", NO_LOGIN_ERROR));
        }
    }

    public function actionUploadImage() {
        // Post image
        if ($this->request->isPostRequest) {
            $fileUpload = CUploadedFile::getInstanceByName("image");
            
            // 图片处理参数
            // 每一个都是必须
            $params = array();
            $params['width'] = $this->request->getPost('width');
            $params['height'] = $this->request->getPost('height');
            $params['x'] = $this->request->getPost('x');
            $params['y'] = $this->request->getPost('y');
            $params['rotate'] = $this->request->getPost('rotate');
            $mimeName = $fileUpload->getType();
            $allowMimes = array(
                "image/jpeg",
                "image/png"
            );
            if (!in_array($mimeName, $allowMimes)) {
                $this->returnJSON($this->error("wrong file type", WRONG_FILE_TYPE_ERROR));
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
                            "code" => NO_LOGIN_ERROR
                        ),
                    ));
                }
                else {
                    $user = self::getLoginUser();
                    $to = ROOT."/uploads/".$user['nickname']. "/". time(). "_".$fileUpload->getName();
                    // 保存之前，先处理图片
                    // 1. 裁剪和旋转
                    $this->_processImage($fileUpload->getTempName(), $params);
                    // 2. 美白
                    $grapher = new Graphic();
                    $grapher->apply_filter($fileUpload->getTempName(), $fileUpload->getTempName());
                    
                    // 3. 美白后， 还需要和王力宏的照片合并

                    // 然后再保存
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
    
    public function _processImage($path, $params) {
        // 第一步，裁剪图片
        $image = new Imagick($path);
        $image->resizeImage($params['width'], $params['height'], Imagick::FILTER_LANCZOS, 1);
        
        // 第二步 旋转图片
        $bg = ROOT.'/uploads/background.png';
        $image->rotateimage(new ImagickPixel('none'), $params['rotate']);
        $image->writeimage();
        
        // 清理资源
        $image->clear();
        $image->destroy();
    }

}
