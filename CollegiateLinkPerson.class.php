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
	private $FacebookProfile = null;
	private $TwitterPage = null;
	private $GooglePlusProfile = null;
	private $LinkedInProfile = null;


	/* Properties whose names aren't defined by data, but should be. */
	private $emailAddresses = array();
	private $preferredEmailAddress = null;



	public function __construct($idOrArray, &$clinkObj) { // If passing an associative array, CommunityMemberId is required.
		$this->_clinkObj = &$clinkObj;

		if(is_array($idOrArray) && intval($idOrArray['CommunityMemberId'])>0) {
			$this->CommunityMemberId = intval($idOrArray['CommunityMemberId']);

			if(isset($idOrArray['CommunityMemberName'])) {
				$this->CommunityMemberName = $idOrArray['CommunityMemberName'];
			}
			if(isset($idOrArray['HasPicture'])) {
				$this->HasPicture = $idOrArray['HasPicture'];
				if ($this->HasPicture) {
					if (isset($idOrArray['CommunityMemberProfilePicture'])) {
						$this->CommunityMemberProfilePicture = $idOrArray['CommunityMemberProfilePicture'];
					}
				}
			}


		} else if (intval($idOrArray) > 0) {
			$this->CommunityMemberId = intval($idOrArray);
		} else {
			throw new Exception("Invalid argument supplied to CollegiateLinkPersonConstructor");
		}
	}

	public function __get($name) {
		switch ($name) {

			//things that all people objects have.
			case "CommunityMemberId":
				return $this->$name;
			break;

			//things loaded by Member Card
			case "CommunityMemberName": // missing break is intentional.
			case "CommunityMemberProfilePicture":
			case "ExternalWebsite":
			case "FacebookProfile":
			case "TwitterPage":
			case "GooglePlusProfile":
			case "LinkedInProfile":
			case "emailAddresses":
			case "preferredEmailAddress":
				if ($this->$name!==null && $this->$name!==array()) { // Only load if the attrib no longer has the default value.
					$this->loadMemberCard();
				}
				return $this->$name;
				break;
			default:
				return null;
		}
	}

	protected function loadMemberCard() {
		if ($this->__hasLoadedMemberCard) { // don't repeat if it's already been done.
			return true;
		}

		$c = new aCurl($this->_clinkObj->getBaseUrl() . "users/membercard/" . $this->CommunityMemberId);
		$c->setCookieFile($this->_clinkObj->getCookieFile());
		$c->addRequestHeader("X-Requested-With: XMLHttpRequest");
		$c->includeHeader(false);
		$c->maxRedirects(0);
		$c->createCurl();
		$this->_clinkObj->incrementCurlCount();

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
				if ((strpos($email->innertext(), "<span class=\"type\">pref</span>") !== false) || $this->preferredEmailAddress===null) {
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
		} else {
			$this->ExternalWebsite = false;
		}


		if ($tag = $memberCard->find('a[title="Facebook Profile"]', 0)) {
			$this->FacebookProfile = $tag->getAttribute('href');
		} else {
			$this->FacebookProfile = false;
		}


		if ($tag = $memberCard->find('a[title="Twitter Page"]', 0)) {
			$this->TwitterPage = $tag->getAttribute('href');
		} else {
			$this->TwitterPage = false;
		}


		if ($tag = $memberCard->find('a[title="Google Plus Profile"]', 0)) {
			$this->GooglePlusProfile = $tag->getAttribute('href');
		} else {
			$this->GooglePlusProfile = null;
		}


		if ($tag = $memberCard->find('a[title="LinkedIn Profile"]', 0)) {
			$this->LinkedInProfile = $tag->getAttribute('href');
		} else {
			$this->LinkedInProfile = null;
		}

		return true;
	}

	public function getEmailAddr() {
		if ($this->preferredEmailAddress===null) {
			$this->loadMemberCard();
		}
		return $this->emailAddresses[$this->preferredEmailAddress];
	}

	public function getFullName() {
		if ($this->CommunityMemberName===null) {
			$this->loadMemberCard();
		}
		if (is_array($this->CommunityMemberName)) {
			return implode(" ",$this->CommunityMemberName);
		}
		return $this->CommunityMemberName;
	}

	public function getLargeProfilePicture() {
		if ($this->HasPicture===null) {
			$this->loadMemberCard();
		}
		if ($this->HasPicture) {
			return $this->_clinkObj->getBaseUrl() . "/images/W170xL170/0/noshadow/Profile/" . $this->CommunityMemberProfilePicture . ".jpg";
		} else {
			return false;
		}
	}

}