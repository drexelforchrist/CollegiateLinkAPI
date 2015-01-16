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

	/* Properties defined by CollegiateLink terminology */
	private $CommunityMemberId; // required in constructor

	public function __construct($idOrArray, &$clinkObj) { // If passing an associative array, CommunityMemberId is required.
		$this->_clinkObj = &$clinkObj;

		if(is_array($idOrArray) && intval($idOrArray['CommunityMemberId'])>0) {
			$this->CommunityMemberId = intval($idOrArray['CommunityMemberId']);

			// KURTZ build from array

		} else if (intval($idOrArray) > 0) {
			$this->CommunityMemberId = intval($idOrArray);
		}
	}


	public function loadMemberCard() { // KURTZ make private or protected
		$c = new aCurl($this->_clinkObj->getBaseUrl() . "users/membercard/" . $this->CommunityMemberId);
		$c->setCookieFile($this->_clinkObj->getCookieFile());
		$c->addRequestHeader("X-Requested-With: XMLHttpRequest");
		$c->includeHeader(false);
		$c->maxRedirects(0);
		$c->createCurl();

		echo $c;
	}

}