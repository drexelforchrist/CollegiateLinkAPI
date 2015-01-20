<?php

if (!defined('HAS_ACURL_VERSION')) {
	require_once "libs/aCurl/aCurl.php";
}

if (!defined('HDOM_TYPE_ELEMENT')) {
	require_once "libs/php-dom-parser/php-dom-parser.php";
}

class CollegiateLink {
	private $_cookieFile;
	private $_baseUrl;
	private $curlCount = 0;


	public function __construct($baseUrl, $cookieFile) {
		/* It is assumed that the user is already authenticated. */
		$this->_baseUrl = $baseUrl;
		$this->_cookieFile = $cookieFile;
	}


	public function __destruct() { // KURTZ remove for use.
		echo "<b>Curl Request Count: " . $this->curlCount . "</b>";
	}


	public function getOrganization($orgReference) {
		include "CollegiateLinkOrg.class.php";
		return new CollegiateLinkOrg($orgReference, $this);
	}

	public function getBaseUrl() {
		return $this->_baseUrl;
	}
	public function getCookieFile() {
		return $this->_cookieFile;
	}
	public function incrementCurlCount() {
		return ++$this->curlCount;
	}
}