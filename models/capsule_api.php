<?php
class CapsuleApi extends AppModel {

	public $useTable = false;

	protected $_config = array();

	protected $_request = array(
		'uri' => array(
			'scheme' => 'https',
			'path' => '/api',
		),
		'auth' => array(
			'method' => 'Basic',
			'pass' => 'x'
		)
	);

	protected $_get = array(
		'method' => 'GET',
		'header' => array(
			'Accept' => 'application/json'
		),
	);

	protected $_post = array(
		'method' => 'POST',
		'header' => array(
			'Content-type' => 'application/json'
		),
	);

	protected $_put = array(
		'method' => 'PUT',
		'header' => array(
			'Content-type' => 'application/json'
		),
	);

	protected $_delete = array(
		'method' => 'DELETE',
	);

	public function __construct($id = false, $table = null, $ds = null) {
		parent::__construct($id, $table, $ds);
		$this->_config = Configure::read('Capsule');
	}

	protected function _generateCacheKey() {
		$backtrace = debug_backtrace();
		$cacheKey = array();
		$cacheKey[] = $this->alias;
		if (!empty($backtrace[2]['function'])) {
			$cacheKey[] = $backtrace[2]['function'];
		}
		if ($backtrace[2]['args']) {
			$cacheKey[] = md5(serialize($backtrace[2]['args']));	
		}
		return implode('_', $cacheKey);
	}

	protected function _parseResponse($response) {
		$results = $response;
		$body = json_decode($response['body']);
		$body = Set::reverse($body);
		$results['body'] = $body;
		return $results;
	}

	protected function _get($path, $request = array(), $options = array()) {
		if (!isset($options['cache'])) {
			$options['cache'] = false;
		}
		$request = Set::merge($this->_request, $this->_get, $request);
		return $this->_request($path, $request, $options);
	}

	protected function _post($path, $request = array()) {
		$request = Set::merge($this->_request, $this->_post, $request);
		$response = $this->_request($path, $request);
		return $response;
	}

	protected function _put($path, $request = array()) {
		$request = Set::merge($this->_request, $this->_put, $request);
		$response = $this->_request($path, $request);
		return $response;
	}

	protected function _delete($path, $request = array()) {
		$request = Set::merge($this->_request, $this->_delete, $request);
		$response = $this->_request($path, $request);
		return $response;
	}

	protected function _request($path, $request = array(), $options = array()) {
		// preparing request
		$request['uri']['host'] = $this->_config['host'];
		$request['auth']['user'] = $this->_config['token'];
		$request['uri']['path'] .= $path;

		// Read cached GET results
		if ($request['method'] == 'GET' 
		&& (isset($options['cache']) && $options['cache'] === true)) {
			$cacheKey = $this->_generateCacheKey();
			$results = Cache::read($cacheKey);
			if ($results !== false) {
				return $results;
			}
		}

		// createding http socket object with auth configuration
		App::Import('Core','HttpSocket');
		$HttpSocket = new HttpSocket();

		// issuing request
		$HttpSocket->request($request);

		// parsing response
		$results = $this->_parseResponse($HttpSocket->response);

		// cache and return results
		if ($request['method'] == 'GET' && !empty($results)
		&& (isset($options['cache']) && $options['cache'] === true)) {
			Cache::write($cacheKey, $results);
		}
		return $results;
	}
}
