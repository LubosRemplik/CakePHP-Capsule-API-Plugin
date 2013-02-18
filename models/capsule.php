<?php
App::Import('Model','Capsule.CapsuleApi');
class Capsule extends CapsuleApi {

	public function deleteAllParties($request = array()) {
		$parties = $this->getParty();
		$i = 0;
		if (!empty($parties['parties']['person'])) {
			foreach ($parties['parties']['person'] as $person) {
				if ($this->deleteParty($person['id'], $request)) {
					$i++;
				}
			}
		}
		if (!empty($parties['parties']['organisation'])) {
			foreach ($parties['parties']['organisation'] as $organisation) {
				if ($this->deleteParty($organisation['id'], $request)) {
					$i++;
				}
			}
		}
		return $i;
	}

	public function deleteParty($id, $request = array()) {
		$path = sprintf('/party/%s', $id);
		$response = $this->_delete($path, $request);
		if ($response['status']['code'] != 200) {
			return false;
		}
		return true;
	}

	public function getParty($request = array()) {
		$response = $this->_get('/party', $request);
		return $response['body'];
	}

	public function getUsers($request = array()) {
		$response = $this->_get('/users', $request);
		return $response['body'];
	}

	public function setOrganisation($data, $request = array()) {
		if (!isset($data['organisation'])) {
			$data = array('organisation' => $data);
		}
		$request['body'] = json_encode($data);
		$response = $this->_post('/organisation', $request);
		if ($response['status']['code'] != 201) {
			return false;
		}
		$location = $response['header']['Location'];
		return array_pop(explode('/', $location));
	}

	public function setPerson($data, $request = array()) {
		if (!isset($data['person'])) {
			$data = array('person' => $data);
		}
		$request['body'] = json_encode($data);
		$response = $this->_post('/person', $request);
		if ($response['status']['code'] != 201) {
			return false;
		}
		$location = $response['header']['Location'];
		return array_pop(explode('/', $location));
	}

	public function setTag($type, $id, $tag, $request = array()) {
		$url = sprintf('/%s/%s/tag/%s', $type, $id, $tag);
		$response = $this->_post($url, $request);
		if (substr($response['status']['code'], 0, 1) != 2) {
			return false;
		}
		return true;
	}
}
