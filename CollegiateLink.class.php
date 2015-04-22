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


	public function getACurl($url) {
		$a = new aCurl($url);
		$a->setVerifyPeer(false);
		return $a;
	}


	public function getOrganization($orgReference) {
		include_once "CollegiateLinkOrg.class.php";
		return new CollegiateLinkOrg($orgReference, $this);
	}

	public function findOrganizations($searchTerm, $startPage = 1, $endPage = 20) {
		include_once "CollegiateLinkOrg.class.php";
		$ret = [];
		for ($page = intval($startPage); $page <= intval($endPage); $page++) { // loop also breaks if reached end of list.  See end of loop for that.
			set_time_limit(30);
			$c = new aCurl($this->getBaseUrl() . "organizations?SearchValue=" . urlencode($searchTerm) .  "&SearchType=Contains&SelectedCategoryId=0&CurrentPage=" . $page);
			$c->setCookieFile($this->getCookieFile());
			$c->includeHeader(false);
			$c->addRequestHeader("X-Requested-With: XMLHttpRequest");
			$c->maxRedirects(0);
			$c->createCurl();
			$this->incrementCurlCount();

			$h = new simple_html_dom((string)$c);
			$orgs = $h->find("h5");

			foreach ($orgs as $org) {
				try {
					$org = $org->find("a",0)->href;

					$ret[] = new CollegiateLinkOrg(substr($org, 14), $this);
				} catch (Exception $e) {
					die($e);
				}

			}


			/* determine if there are more pages to load */
			if ($pager = $h->find('div[id="pager"]',0)) {
				// if there isn't a "next" link
				if (strpos($pager, "Next") === FALSE) {
					break;
				}
			}
		}
		return $ret;
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