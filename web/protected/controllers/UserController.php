<?php

class UserController extends Controller
{
        const NOT_LOGIN = 0x0001;
        const UNKNOWEN = 0x0010;
        const HTTP_METHOD_ERROR = 0x0100;
        
        protected $request = NULL;

	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer $id the ID of the model to be loaded
	 * @return User the loaded model
	 * @throws CHttpException
	 */
	public function loadModel($id)
	{
		$model=User::model()->findByPk($id);
		if($model===null)
			throw new CHttpException(404,'The requested page does not exist.');
		return $model;
	}
        
        public function returnJSON($data) {
            header("Content-Type: application/json");
            echo CJSON::encode($data);
            Yii::app()->end();
        }
        
        public function errorJOSN($msg, $code) {
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


        public function actionUploadphoto() {
            if (!$this->request->isPostRequest) {
                $this->returnJSON($this->errorJOSN("only allow post method", self::HTTP_METHOD_ERROR));
            }
            else {
                //TODO:
            }
        }
        
        public function actionLogin() {
            
        }
        

	/**
	 * Performs the AJAX validation.
	 * @param User $model the model to be validated
	 */
	protected function performAjaxValidation($model)
	{
		if(isset($_POST['ajax']) && $_POST['ajax']==='user-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
	}
}
