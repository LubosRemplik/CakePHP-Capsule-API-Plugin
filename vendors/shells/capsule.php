<?php
class CapsuleShell extends Shell {
	public $uses = array(
		'Capsule.Capsule',
	);
	
	public function deleteAll() {
		if ($count = $this->Capsule->deleteAllParties()) {
			$this->out(sprintf('%s parties deleted.', $count));
		}
	} 
}
