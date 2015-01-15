<?php

if (!defined('HAS_ACURL_VERSION')) {
	require_once "libs/aCurl/aCurl.php";
}

if (!defined('HDOM_TYPE_ELEMENT')) {
	require_once "libs/php-dom-parser/php-dom-parser.php";
}

class CollegiateLink {
	private $_status = 0;
	private $_cookieFile;
	private $_domain;


	public function __construct($domain, $cookieFile) {
		/* It is assumed that the user is already authenticated. */
		$this->_domain = $domain;
		$this->_cookieFile = $cookieFile;
	}


	public function getStatus() {
		return $this->_status;
	}

	public function getOrganization($orgReference) {
		include "CollegiateLinkOrganization.class.php";
		return new CollegiateLinkOrganization($orgReference);
	}
}