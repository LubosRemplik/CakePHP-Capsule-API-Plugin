<?php
class CapsuleShell extends Shell {
	public $uses = array(
		'Capsule.Capsule'
	);
	
	public function users() {
		return $this->Capsule->users();
	} 
}
