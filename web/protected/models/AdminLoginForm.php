<?php

class AdminLoginForm extends CFormModel {
	public $username;
	public $password;

	public function rules() {
		return array(
			array("username, password", 'required'),
			array("password", "authenticate"),
		);
	}

	public function authenticate($attrs, $params) {
		if ($this->password != "aDm#n_!assword" || $this->username != "admin") {
			return $this->addError("password", "user name or password is not right");
		}
	}

}