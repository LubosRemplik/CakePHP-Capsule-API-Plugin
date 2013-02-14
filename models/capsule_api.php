<?php
class CapsuleApi extends AppModel {

	public $useTable = false;

	protected $_config = array();

	protected $_request = array(
		'method' => 'GET',
		'uri' => array(
			'scheme' => 'https',
			'host' => 'api.twitter.com',
			'path' => '/api',
		),
		'header' => array(
			'Accept' => 'application/json'
		),
		'auth' => array(
			'method' => 'Basic',
			'pass' => 'x'
		)
	);

	public function __construct($id = false, $table = null, $ds = null) {
		parent::__construct($id, $table, $ds);
		$this->_config = Configure::read('Capsule');
		$this->_request['uri']['host'] = $this->_config['host'];
		$this->_request['auth']['user'] = $this->_config['token'];
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
		$results = json_decode($response);
		$results = Set::reverse($results);
		return $results;
	}

	protected function _request($path, $request = array()) {
		// preparing request
		$request = Set::merge($this->_request, $request);
		$request['uri']['path'] .= $path;

		// Read cached GET results
		if ($request['method'] == 'GET') {
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
		$response = $HttpSocket->request($request);

		// parsing response
		$results = $this->_parseResponse($response);

		// cache and return results
		if ($request['method'] == 'GET') {
			Cache::write($cacheKey, $results);
		}
		return $results;
	}
}
