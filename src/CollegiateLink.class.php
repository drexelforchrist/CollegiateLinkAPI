<?php

if (!defined('HAS_ACURL_VERSION')) {
	require_once "libs/aCurl/aCurl.php";
}

if (!defined('HDOM_TYPE_ELEMENT')) {
	require_once "libs/php-dom-parser/php-dom-parser.php";
}

/**
 * The CollegiateLink class is the fundamental class you need to use this library.  Whatever you're trying to do, will
 * start with a CollegiateLink object.  Typically, you'll only need one at any given time.
 *
 * Class CollegiateLink
 */
class CollegiateLink {
	private $_cookieFile;
	private $_baseUrl;
	private $curlCount = 0;


	/**
	 * Constructor.  Creates the initial CollegiateLink class from which all connections can be made.
	 *
	 * @param String $baseUrl  The base url of the school's collegiatelink instance.
	 * Probably "https://school.collegiatelink.net/"  The Trailing slash is required.
	 * @param String $cookieFile  The file name of the file to use for cookies.
	 * @throws InvalidCollegiateLinkParametersException
	 */
	public function __construct($baseUrl, $cookieFile) {
		/* It is assumed that the user is already authenticated. */
		if ($baseUrl == null) {
			throw new InvalidCollegiateLinkParametersException("Must provide base URL to CollegiateLink constructor.", 1);
		}
		if ($cookieFile == null) {
			throw new InvalidCollegiateLinkParametersException("Must provide cookie file location to CollegiateLink constructor.", 2); // KURTZ adjust with future alternative methods of storing cookies.
		}
		if (substr_compare($baseUrl, "/", -1) !== 0) {
			throw new InvalidCollegiateLinkParametersException("Must provide a tailing \"/\" at the end of CollegeiateLink base URL", 3);
		}
		$this->_baseUrl = $baseUrl;
		$this->_cookieFile = $cookieFile;
	}


	public function __destruct() { // KURTZ remove for use.
		echo "<b>Curl Request Count: " . $this->curlCount . "</b>";
	}


	/**
	 * getACurl.  Returns a new aCurl object, with some standardized settings pre-set. Used extensively by other classes
	 * in the library.
	 *
	 * @param String $url The Url of the request.
	 * @return aCurl The new aCurl object, already prepped with initialization parameters.
	 */
	public function getACurl($url) {
		$a = new aCurl($url);
		$a->setVerifyPeer(false);
		return $a;
	}


	/**
	 * getOrganization takes an org reference (which is "safac-office" in
	 * "https://drexel.collegiatelink.net/organization/safac-office"), and returns a CollegiateLinkOrg object.
	 *
	 * @param String $orgReference The unambiguous url reference to the organization, on the CollegiateLink site.
	 * @return CollegiateLinkOrg The resulting organization object.
	 */
	public function getOrganization($orgReference) {
		include_once "CollegiateLinkOrg.class.php";
		return new CollegiateLinkOrg($orgReference, $this);
	}


	/**
	 * findOrganizations based on a given search term.  This is the API wrapper for the organization search function.
	 * CollegiateLink only returns a few results on each page, thus this function iterates through results pages to
	 * provide a more thorough collection.  However, because those requests are expensive, this request stops at
	 * $endPage or the end of the list, whichever comes first.
	 *
	 * Returns an array of CollegiateLinkOrg objects.
	 *
	 * @param String $searchTerm  The term to search for.
	 * @param int $startPage  The first page of results to include.  Defaults to 1.
	 * @param int $endPage  The last page of results to include.  Defaults to 20; will not fetch more pages than there
	 * are.
	 * @return CollegiateLinkOrg[]  An array of CollegiateLinkOrg objects.
	 */
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
				/** @var simple_html_dom_node $org */
				$org = $org->find("a",0)->href;

				$ret[] = new CollegiateLinkOrg(substr($org, 14), $this); // substr removes the /organization/

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


	/**
	 * Returns the baseUrl for the CollegiateLink instance, as it was provided in the constructor.  Used extensively by
	 * other CollegiateLink classes.
	 *
	 * @return String The base url of the collegiateLink instance.  e.g. "https://drexel.collegiatelink.net/"
	 */
	public function getBaseUrl() {
		return $this->_baseUrl;
	}


	/**
	 * Returns the cookie file name, as provided in the constructor.  Used extensively by other CollegiateLink classes.
	 *
	 * @return String The filename being used for cookies.
	 */
	public function getCookieFile() {
		return $this->_cookieFile;
	}


	/**
	 * Increments the count of the number of cUrl executions that have taken place.  Used by other CollegiateLink
	 * classes.
	 *
	 * @return int The current count, after the increment takes place.
	 */
	public function incrementCurlCount() {
		return ++$this->curlCount;
	}
}


/**
 * The InvalidCollegiateLinkParametersException is an exception class used for syntactical errors within the
 * CollegiateLink class.
 *
 * Class InvalidCollegiateLinkParametersException
 */
class InvalidCollegiateLinkParametersException extends Exception {
	public function __construct($message, $code = 0, Exception $previous = null) {
		parent::__construct($message, $code, $previous);
	}
}

