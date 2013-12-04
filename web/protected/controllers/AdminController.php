<?php

class AdminController extends Controller {

    public $layout = 'admin';
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

    public function doWhenLoginSuccess() {
        Yii::app()->session["admin_login"] = TRUE;
    }

    public function adminIsLogin() {
        return !!Yii::app()->session["admin_login"];
    }

    public function actionIndex() {
        if (!$this->adminIsLogin()) {
            return $this->redirect(array("login"));
        }
        // Find out all photo with user and vote count
        $list = Yii::app()->db->createCommand("select p.*, u.nickname, count(v.user_id) as vote_count from photo p left join user u on  p.user_id=u.user_id left join vote v on v.photo_id=p.photo_id group by p.photo_id;")
            ->queryAll();

        $this->render("index", array("list" => $list));
    }

    public function actionLogin() {
        if ($this->adminIsLogin()) {
            return $this->redirect(array("index"));
        }
        $model = new AdminLoginForm();
        if (isset($_POST["AdminLoginForm"])) {
            $model->attributes = $_POST["AdminLoginForm"];
            if ($model->validate()) {
                $this->doWhenLoginSuccess();
                return $this->redirect(array("index"));
            }
        }
        $this->render("login", array('model' => $model));
    }

    public function actionDelete() {
        if (!$this->adminIsLogin()) {
            return;
        }
        $photo_id = Yii::app()->request->getQuery("photo_id");
        if ($photo_id && is_numeric($photo_id)) {
            $ret = Yii::app()->db->createCommand()
                ->delete("photo", "photo_id = :photo_id", array(":photo_id" => $photo_id));
        }

        $user_id = Yii::app()->request->getQuery("user_id");
        if ($user_id && is_numeric($user_id)) {
            $ret = Yii::app()->db->createCommand()
                ->delete("user", "user_id = :user_id", array(":user_id" => $user_id));
        }
    }

    public function actionUser() {
        if (!$this->adminIsLogin()) {
            return $this->redirect(array("index"));
        }
        $list = Yii::app()->db->createCommand()
            ->select("*")
            ->from("user")
            ->queryAll();

        $this->render("user", array("list" => $list));
    }

}
