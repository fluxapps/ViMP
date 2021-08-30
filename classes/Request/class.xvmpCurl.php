<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

use srag\Plugins\ViMP\Database\Config\ConfigAR;

/**
 * Class xvmpCurl
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class xvmpCurl {

	const FORMAT_JSON = 'json';

	/**
	 * xvmpCurl constructor.
	 *
	 * @param string $url
	 * @param string $request_type
	 */
	public function __construct($url = '') {
		global $DIC;
		$lng = $DIC['lng'];
		self::$api_key = ConfigAR::getConfig(ConfigAR::F_API_KEY);
		if (strpos($url, 'http') === false) {
			$url = rtrim(ConfigAR::getConfig(ConfigAR::F_API_URL), '/') . '/' . ltrim($url, '/');
		}
		$this->url = $url;
		$this->addPostField('apikey', ConfigAR::getConfig(ConfigAR::F_API_KEY));
		$this->addPostField('format', self::FORMAT_JSON);
		$this->addPostField('language', $lng->getLangKey());
	}


	/**
	 * init password and username from config
	 */
	public static function init() {
		self::$api_key = ConfigAR::getConfig(ConfigAR::F_API_KEY);
	}

    /**
     * @throws xvmpException
     */
    public function get() {
		$this->setRequestType(self::REQ_TYPE_GET);
		$this->execute();
	}

    /**
     * @throws xvmpException
     */
    public function put() {
		$this->setRequestType(self::REQ_TYPE_PUT);
		$this->execute();
	}

    /**
     * @throws xvmpException
     */
	public function post() {
		$this->setRequestType(self::REQ_TYPE_POST);
		$this->execute();
	}

    /**
     * @throws xvmpException
     */
    public function delete() {
		$this->setRequestType(self::REQ_TYPE_DELETE);
		$this->execute();
	}

    /**
     * @throws xvmpException
     */
    protected function execute() {
		static $ch;
		if (!isset($ch)) {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			if (self::$ip_v4) {
				curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
			}

			if (self::$ssl_version) {
				curl_setopt($ch, CURLOPT_SSLVERSION, self::$ssl_version);
			}
			if ($this->getUsername() AND $this->getPassword()) {
				curl_setopt($ch, CURLOPT_USERPWD, $this->getUsername() . ':' . $this->getPassword());
			}

			if (!$this->isVerifyHost()) {
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
			}
			if (!$this->isVerifyPeer()) {
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			}
		}

		if ($this->getTimeoutMS()) {
			curl_setopt($ch, CURLOPT_TIMEOUT_MS, $this->getTimeoutMS());
		}

		curl_setopt($ch, CURLOPT_URL, $this->getUrl());
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $this->getRequestType());

        curl_setopt($ch, CURLOPT_COOKIESESSION, true);
        curl_setopt($ch, CURLOPT_COOKIEJAR, CLIENT_DATA_DIR . "/temp/vimp_cookie.txt");
        curl_setopt($ch, CURLOPT_COOKIEFILE, CLIENT_DATA_DIR . "/temp/vimp_cookie.txt");



        $this->addHeader('X-Forwarded-For: ' . $_SERVER['REMOTE_ADDR']);
        
		$this->prepare($ch);

		if ($this->getRequestContentType()) {
			$this->addHeader('Content-Type: ' . $this->getRequestContentType());
		}
		curl_setopt($ch, CURLOPT_HTTPHEADER, $this->getHeaders());
		$this->debug($ch);
		$resp_orig = curl_exec($ch);
		if ($resp_orig === false) {
			$this->setResponseError(new xvmpCurlError($ch));
		}
		$this->setResponseBody($resp_orig);
		$this->setResponseMimeType(curl_getinfo($ch, CURLINFO_CONTENT_TYPE));
		$this->setResponseContentSize(curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD));
		$this->setResponseStatus(curl_getinfo($ch, CURLINFO_HTTP_CODE));

		$i = 1000;

		xvmpCurlLog::getInstance()->write('CURLINFO_CONNECT_TIME: ' . round(curl_getinfo($ch, CURLINFO_CONNECT_TIME) * $i, 2) . ' ms', xvmpCurlLog::DEBUG_LEVEL_1);
		xvmpCurlLog::getInstance()->write('CURLINFO_NAMELOOKUP_TIME: ' . round(curl_getinfo($ch, CURLINFO_NAMELOOKUP_TIME) * $i, 2) . ' ms', xvmpCurlLog::DEBUG_LEVEL_1);
		xvmpCurlLog::getInstance()->write('CURLINFO_REDIRECT_TIME: ' . round(curl_getinfo($ch, CURLINFO_REDIRECT_TIME) * $i, 2) . ' ms', xvmpCurlLog::DEBUG_LEVEL_1);
		xvmpCurlLog::getInstance()->write('CURLINFO_STARTTRANSFER_TIME: ' . round(curl_getinfo($ch, CURLINFO_STARTTRANSFER_TIME) * $i, 2) . ' ms', xvmpCurlLog::DEBUG_LEVEL_1);
		xvmpCurlLog::getInstance()->write('CURLINFO_PRETRANSFER_TIME: ' . round(curl_getinfo($ch, CURLINFO_PRETRANSFER_TIME) * $i, 2) . ' ms', xvmpCurlLog::DEBUG_LEVEL_1);
		xvmpCurlLog::getInstance()->write('CURLINFO_TOTAL_TIME: ' . round(curl_getinfo($ch, CURLINFO_TOTAL_TIME) * $i, 2) . ' ms', xvmpCurlLog::DEBUG_LEVEL_1);

		if ($this->getResponseStatus() > 299 || is_array($this->getResponseArray()['errors'])) {
			xvmpCurlLog::getInstance()->write('ERROR ' . $this->getResponseStatus(), xvmpCurlLog::DEBUG_LEVEL_1);
			xvmpCurlLog::getInstance()->write('Response:' . $resp_orig, xvmpCurlLog::DEBUG_LEVEL_3);

			$error_msg = $this->getResponseArray()['errors']['error'];
			$error_msg = is_array($error_msg) ? implode(".\n", $error_msg) : $error_msg;

			if ($error_msg == "Medium doesn't exist") {
                throw new xvmpException(xvmpException::API_CALL_STATUS_404, $error_msg);
            }

			switch ($this->getResponseStatus()) {
				case 403:
					throw new xvmpException(xvmpException::API_CALL_STATUS_403, $error_msg);
					break;
				case 401:
					throw new xvmpException(xvmpException::API_CALL_BAD_CREDENTIALS);
					break;
				case 404:
					throw new xvmpException(xvmpException::API_CALL_STATUS_404, $error_msg);
					break;
				default:
					throw new xvmpException(xvmpException::API_CALL_STATUS_500, $error_msg);
					break;
			}
		}

		if (($this->getResponseStatus() == 0) && $this->getResponseError()->getErrorNr()) {
			$error = $this->getResponseError();
			throw new xvmpException(xvmpException::API_CALL_STATUS_500, $error->getMessage());
		}
		//		curl_close($ch);
	}

	/**
	 * @param $ch
	 *
	 * @throws xvmpException
	 */
	protected function preparePut($ch) {
		if ($this->getPostFields()) {
			$this->preparePost($ch);
		}
	}


	/**
	 * @param $ch
	 */
	protected function preparePost($ch) {
		curl_getinfo($ch, CURLINFO_HEADER_OUT);
		if (count($this->getFiles()) > 0) {
			curl_getinfo($ch, CURLOPT_SAFE_UPLOAD);
			foreach ($this->getFiles() as $file) {
				$this->addPostField($file->getPostVar(), $file->getCURLFile());
			}
		}

		curl_setopt($ch, CURLOPT_POSTFIELDS, $this->getPostFields());

		xvmpCurlLog::getInstance()->write('POST-Body', xvmpCurlLog::DEBUG_LEVEL_3);
		xvmpCurlLog::getInstance()->write(print_r($this->getPostFields(), true), xvmpCurlLog::DEBUG_LEVEL_3);
	}


	/**
	 * @param $ch
	 */
	protected function debug($ch) {
		$xvmpCurlLog = xvmpCurlLog::getInstance();
		$xvmpCurlLog->write('execute *************************************************', xvmpCurlLog::DEBUG_LEVEL_1);
		$xvmpCurlLog->write($this->getUrl(), xvmpCurlLog::DEBUG_LEVEL_1);
		$xvmpCurlLog->write($this->getRequestType(), xvmpCurlLog::DEBUG_LEVEL_1);
		if ($this->getRequestType() == self::REQ_TYPE_POST) {
			$xvmpCurlLog->write(print_r($this->post_fields, true), xvmpCurlLog::DEBUG_LEVEL_1);
		}
		$backtrace = "Backtrace: \n";
		foreach (debug_backtrace() as $b) {
			$backtrace .= $b['file'] . ': ' . $b["function"] . "\n";
		}
		$xvmpCurlLog->write($backtrace, xvmpCurlLog::DEBUG_LEVEL_4);
		if (xvmpCurlLog::getLogLevel() >= xvmpCurlLog::DEBUG_LEVEL_3) {
			curl_setopt($ch, CURLOPT_VERBOSE, true);
			curl_setopt($ch, CURLOPT_STDERR, fopen(xvmpCurlLog::getFullPath(), 'a'));
		}
	}


	/**
	 * @param $ch
	 */
	protected function prepare($ch) {
		switch ($this->getRequestType()) {
			case self::REQ_TYPE_PUT:
				$this->preparePut($ch);
				break;
			case self::REQ_TYPE_POST:
				$this->preparePost($ch);
				break;
		}
	}


	const REQ_TYPE_GET = 'GET';
	const REQ_TYPE_POST = 'POST';
	const REQ_TYPE_DELETE = 'DELETE';
	const REQ_TYPE_PUT = 'PUT';
	/**
	 * @var array
	 */
	protected $post_fields = array();
	/**
	 * @var int
	 */
	protected static $ssl_version = CURL_SSLVERSION_DEFAULT;
	/**
	 * @var bool
	 */
	protected static $ip_v4 = false;
	/**
	 * @var string
	 */
	protected $url = '';
	/**
	 * @var string
	 */
	protected $request_type = self::REQ_TYPE_GET;
	/**
	 * @var array
	 */
	protected $headers = array();
	/**
	 * @var string
	 */
	protected $response_body = '';
	/**
	 * @var string
	 */
	protected $response_mime_type = '';
	/**
	 * @var string
	 */
	protected $response_content_size = '';
	/**
	 * @var int
	 */
	protected $response_status = 200;
	/**
	 * @var xvmpCurlError
	 */
	protected $response_error = NULL;
	/**
	 * @var string
	 */
	protected $put_file_path = '';
	/**
	 * @var string
	 */
	protected $post_body = '';
	/**
	 * @var string
	 */
	protected static $api_key = '';
	/**
	 * @var string
	 */
	protected static $username = '';
	/**
	 * @var string
	 */
	protected static $password = '';
	/**
	 * @var bool
	 */
	protected static $verify_peer = true;
	/**
	 * @var bool
	 */
	protected static $verify_host = true;
	/**
	 * @var string
	 */
	protected $request_content_type = '';
	/**
	 * @var
	 */
	protected $files = array();
	/**
	 * @var integer
	 */
	protected $timeout_MS;


	/**
	 * @return int
	 */
	public function getTimeoutMS() {
		return $this->timeout_MS;
	}


	/**
	 * @param int $timeout_MS
	 */
	public function setTimeoutMS($timeout_MS) {
		$this->timeout_MS = $timeout_MS;
	}


	/**
	 * @return array
	 */
	public function getPostFields() {
		return $this->post_fields;
	}


	/**
	 * @param array $post_fields
	 */
	public function setPostFields($post_fields) {
		$this->post_fields = $post_fields;
	}

	/**
	 * @param $key
	 * @param $value
	 */
	public function addPostField($key, $value) {
		$this->post_fields[$key] = $value;
	}


	/**
	 * @return int
	 */
	public static function getSslVersion() {
		return self::$ssl_version;
	}


	/**
	 * @param int $ssl_version
	 */
	public static function setSslVersion($ssl_version) {
		self::$ssl_version = $ssl_version;
	}


	/**
	 * @return bool
	 */
	public static function isIpV4() {
		return self::$ip_v4;
	}


	/**
	 * @param bool $ip_v4
	 */
	public static function setIpV4($ip_v4) {
		self::$ip_v4 = $ip_v4;
	}


	/**
	 * @return string
	 */
	public function getUrl() {
		return $this->url;
	}


	/**
	 * @param string $url
	 */
	public function setUrl($url) {
		$this->url = $url;
	}


	/**
	 * @return string
	 */
	public function getRequestType() {
		return $this->request_type;
	}


	/**
	 * @param string $request_type
	 */
	public function setRequestType($request_type) {
		$this->request_type = $request_type;
	}


	/**
	 * @return array
	 */
	public function getHeaders() {
		return $this->headers;
	}


	/**
	 * @param array $headers
	 */
	public function setHeaders($headers) {
		$this->headers = $headers;
	}

	/**
	 * @param $string
	 */
	public function addHeader($string) {
		$this->headers[] = $string;
	}

	/**
	 * @return string
	 */
	public function getResponseBody() {
		return $this->response_body;
	}


	/**
	 * @param string $response_body
	 */
	public function setResponseBody($response_body) {
		$this->response_body = $response_body;
	}

	public function getResponseArray() {
		return json_decode($this->response_body, true);
	}

	/**
	 * @return string
	 */
	public function getResponseMimeType() {
		return $this->response_mime_type;
	}


	/**
	 * @param string $response_mime_type
	 */
	public function setResponseMimeType($response_mime_type) {
		$this->response_mime_type = $response_mime_type;
	}


	/**
	 * @return string
	 */
	public function getResponseContentSize() {
		return $this->response_content_size;
	}


	/**
	 * @param string $response_content_size
	 */
	public function setResponseContentSize($response_content_size) {
		$this->response_content_size = $response_content_size;
	}


	/**
	 * @return int
	 */
	public function getResponseStatus() {
		return $this->response_status;
	}


	/**
	 * @param int $response_status
	 */
	public function setResponseStatus($response_status) {
		$this->response_status = $response_status;
	}


	/**
	 * @return xvmpCurlError
	 */
	public function getResponseError() {
		return $this->response_error;
	}


	/**
	 * @param xvmpCurlError $response_error
	 */
	public function setResponseError($response_error) {
		$this->response_error = $response_error;
	}


	/**
	 * @return string
	 */
	public function getPutFilePath() {
		return $this->put_file_path;
	}


	/**
	 * @param string $put_file_path
	 */
	public function setPutFilePath($put_file_path) {
		$this->put_file_path = $put_file_path;
	}


	/**
	 * @return string
	 */
	public function getPostBody() {
		return $this->post_body;
	}


	/**
	 * @param string $post_body
	 */
	public function setPostBody($post_body) {
		$this->post_body = $post_body;
	}


	/**
	 * @return string
	 */
	public static function getUsername() {
		return self::$username;
	}


	/**
	 * @param string $username
	 */
	public static function setUsername($username) {
		self::$username = $username;
	}


	/**
	 * @return string
	 */
	public static function getPassword() {
		return self::$password;
	}


	/**
	 * @param string $password
	 */
	public static function setPassword($password) {
		self::$password = $password;
	}


	/**
	 * @return bool
	 */
	public static function isVerifyPeer() {
		return !ConfigAR::getConfig(ConfigAR::F_DISABLE_VERIFY_PEER);
	}


	/**
	 * @return bool
	 */
	public static function isVerifyHost() {
		return self::$verify_host;
	}


	/**
	 * @param bool $verify_host
	 */
	public static function setVerifyHost($verify_host) {
		self::$verify_host = $verify_host;
	}


	/**
	 * @return string
	 */
	public function getRequestContentType() {
		return $this->request_content_type;
	}


	/**
	 * @param string $request_content_type
	 */
	public function setRequestContentType($request_content_type) {
		$this->request_content_type = $request_content_type;
	}


	/**
	 * @return xvmpUploadFile[]
	 */
	public function getFiles() {
		return $this->files;
	}


	/**
	 * @param xvmpUploadFile[] $files
	 */
	public function setFiles($files) {
		$this->files = $files;
	}


}
