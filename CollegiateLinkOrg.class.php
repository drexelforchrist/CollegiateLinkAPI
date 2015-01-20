<?php

if (!defined('HAS_ACURL_VERSION')) {
	require_once "libs/aCurl/aCurl.php";
}

if (!defined('HDOM_TYPE_ELEMENT')) {
	require_once "libs/php-dom-parser/php-dom-parser.php";
}

class CollegiateLinkOrg {
	private $_orgReference;
	private $_clinkObj;

	public function __construct($orgReference, &$clinkObj) {
		$this->_orgReference = $orgReference;
		$this->_clinkObj = &$clinkObj;
	}

	public function getMembers($startPage = 1, $endPage = 10) { // 15 per page.  If the org has members listed in their officer section, that's a much more efficient way to get this stuff.
		$r = array();
		include_once "CollegiateLinkPerson.class.php"; // KURTZ consider using a different class that extends CollegiateLinkPerson that allows for details like "joined"
		for ($page = intval($startPage); $page <= intval($endPage); $page++) { // loop also breaks if reached end of list.  See end of loop for that.
			set_time_limit(5);
			$c = new aCurl($this->_clinkObj->getBaseUrl() . "organization/" . $this->_orgReference . "/roster/members?Direction=Ascending&page=" . $page);
			$c->setCookieFile($this->_clinkObj->getCookieFile());
			$c->includeHeader(false);
			$c->maxRedirects(0);
			$c->createCurl();
			$this->_clinkObj->incrementCurlCount();

			$h = new simple_html_dom((string)$c);
			$people = $h->find("tr");

			foreach ($people as $person) {
				if ($person->class == "gridHeader") {
					continue;
				}

				$personArr = array();

				$personArr['HasPicture'] = false;
				$personArr['CommunityMemberProfilePicture'] = null;
				if ($img = $person->find("img",0)) {
					$personArr['HasPicture'] = true;
					$personArr['CommunityMemberProfilePicture'] = substr($img->getAttribute("src"), 35, -4);
				}
				unset($img);

				$personArr['Joined'] = $person->find("td", 3)->innertext();

				$personArr['CommunityMemberName'][] = $person->find("a", 0)->innertext();

				$lastName = $person->find("a", 1);
				$id = $lastName->getAttribute("href");
				$personArr['CommunityMemberId'] = intval(substr($id, 18));
				$personArr['CommunityMemberName'][] = $lastName->innertext();
				unset($lastName, $id);

				$r[] = new CollegiateLinkPerson($personArr,$this->_clinkObj);
			}


			/* determine if there are more pages to load */
			if ($pageSpan = $h->find('span[class="paginationLeft"]',0)) {
				// parsing "Showing 1 - 15 of 66 "
				preg_match_all('/[0-9]+/', $pageSpan->innertext(), $pagination);

				if ($pagination[0][1] === $pagination[0][2]) {
					break;
				}
			}
		}
		return $r;
	}
}