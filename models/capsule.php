<?php
App::Import('Model','Capsule.CapsuleApi');
class Capsule extends CapsuleApi {

	public function users() {
		return $this->_request('/users');
	}
}
