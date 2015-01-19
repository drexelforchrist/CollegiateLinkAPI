<?php

if (!defined('HAS_ACURL_VERSION')) {
	require_once "libs/aCurl/aCurl.php";
}

if (!defined('HDOM_TYPE_ELEMENT')) {
	require_once "libs/php-dom-parser/php-dom-parser.php";
}

class CollegiateLinkPerson {
	private $__hasLoadedMemberCard = false;

	private $_clinkObj;
	private $_loadedAttribs = array();

	/* Properties defined by CollegiateLink terminology */
	private $CommunityMemberId; // required in constructor
	private $CommunityMemberName = null;
	private $CommunityMemberProfilePicture = null;
	private $HasPicture = null;
	private $ExternalWebsite = null;
	private $HasExternalWebsite = null;
	private $FacebookProfile = null;
	private $HasFacebookProfile = null;
	private $TwitterPage = null;
	private $HasTwitterPage = null;

	/* Properties whose names aren't defined by data, but should be. */
	private $emailAddresses = array();
	private $preferredEmailAddress = null;



	public function __construct($idOrArray, &$clinkObj) { // If passing an associative array, CommunityMemberId is required.
		$this->_clinkObj = &$clinkObj;

		if(is_array($idOrArray) && intval($idOrArray['CommunityMemberId'])>0) {
			$this->CommunityMemberId = intval($idOrArray['CommunityMemberId']);

			// KURTZ build from array

		} else if (intval($idOrArray) > 0) {
			$this->CommunityMemberId = intval($idOrArray);
		}
	}

	public function __get($name) {
		switch ($name) {
			case "CommunityMemberName": // missing break is intentional.
				if (!$this->_loadedAttribs[$name]) {
// KURTZ revise everything here.
				}
			case "CommunityMemberId":
				return $this->$name;
			break;
			default:
				return null;
		}
	}

	public function loadMemberCard() { // KURTZ make private or protected
		if ($this->__hasLoadedMemberCard) { // don't repeat if it's already been done.
			return true;
		}

		$c = new aCurl($this->_clinkObj->getBaseUrl() . "users/membercard/" . $this->CommunityMemberId);
		$c->setCookieFile($this->_clinkObj->getCookieFile());
		$c->addRequestHeader("X-Requested-With: XMLHttpRequest");
		$c->includeHeader(false);
		$c->maxRedirects(0);
		$c->createCurl();

		if($c->getInfo()['http_code'] != 200) { // stop the nonsense if it didn't load.
			return false;
		}

		$memberCard = new simple_html_dom((string)$c);
		$this->__hasLoadedMemberCard = true;

		if (!is_array($this->CommunityMemberName) && ($tag = $memberCard->find('span[class="fn"]', 0))) {
			$this->CommunityMemberName = $tag->innertext();
		}

		if ($tag = $memberCard->find('a[class="email"]')) {
			foreach ($tag as $email) {
				$this->emailAddresses[] = substr($email->getAttribute("href"),7);
				if (strpos($email->innertext(), "<span class=\"type\">pref</span>") !== false) {
					$this->preferredEmailAddress = count($this->emailAddresses)-1;
				}
				echo "\n\n";
			}
		} // KURTZ: else?

		if ($tag = $memberCard->find("img",0)) {
			$this->CommunityMemberProfilePicture = substr($tag->getAttribute("src"),37,-4);
			$this->HasPicture = true;
		} else {
			$this->CommunityMemberProfilePicture = null;
			$this->HasPicture = false;
		}


		if ($tag = $memberCard->find('a[title="External Website"]', 0)) {
			$this->ExternalWebsite = $tag->getAttribute('href');
			$this->HasExternalWebsite = true;
		} else {
			$this->ExternalWebsite = null;
			$this->HasExternalWebsite = false;
		}


		if ($tag = $memberCard->find('a[title="Facebook Profile"]', 0)) {
			$this->FacebookProfile = $tag->getAttribute('href');
			$this->HasFacebookProfile = true;
		} else {
			$this->FacebookProfile = null;
			$this->HasFacebookProfile = false;
		}


		if ($tag = $memberCard->find('a[title="Twitter Page"]', 0)) {
			$this->TwitterPage = $tag->getAttribute('href');
			$this->HasTwitterPage = true;
		} else {
			$this->TwitterPage = null;
			$this->HasTwitterPage = false;
		}


		if ($tag = $memberCard->find('a[title="Google Plus Profile"]', 0)) {
			$this->GooglePlusProfile = $tag->getAttribute('href');
			$this->HasGooglePlusProfile = true;
		} else {
			$this->GooglePlusProfile = null;
			$this->HasGooglePlusProfile = false;
		}


		if ($tag = $memberCard->find('a[title="LinkedIn Profile"]', 0)) {
			$this->LinkedInProfile = $tag->getAttribute('href');
			$this->HasLinkedInProfile = true;
		} else {
			$this->LinkedInProfile = null;
			$this->HasLinkedInProfile = false;
		}


		var_dump($this);

		echo $memberCard;

		return true;
	}

}