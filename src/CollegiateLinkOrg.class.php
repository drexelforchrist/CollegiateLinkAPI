<?php


/**
 * The CollegiateLinkOrg class contains the information and methods related to a single student organization.
 *
 * Class CollegiateLinkOrg
 */
class CollegiateLinkOrg {
	private $_orgReference;
	private $_clinkObj;

	/**
	 * @param String $orgReference  The reference string from the url to the organization's page.
	 * @param CollegiateLink $clinkObj  The collegiateLink object that provides various resources.
	 */
	public function __construct($orgReference, &$clinkObj) {
		$this->_orgReference = $orgReference;
		$this->_clinkObj = &$clinkObj;
	}

	/**
	 * getMembers returns an array of CollegiateLinkPersons.  Wraps the members list available to non-officers.
	 *
	 * @param int $startPage  The first page of results to include.  Defaults to 1.
	 * @param int $endPage  The last page of results to include.  Defaults to 20; will not fetch more pages than there
	 * are.
	 * @return CollegiateLinkPerson[] A list of members.
	 */
	public function getMembers($startPage = 1, $endPage = 20) { // 15 per page.  If the org has members listed in their officer section, that's a much more efficient way to get this stuff.
		$r = array(); // KURTZ consider caching, at least for duration of execution
		include_once "CollegiateLinkPerson.class.php"; // KURTZ consider using a different class that extends CollegiateLinkPerson that allows for details like "joined"
		for ($page = intval($startPage); $page <= intval($endPage); $page++) { // loop also breaks if reached end of list.  See end of loop for that.
			set_time_limit(30);
			$c = new aCurl($this->_clinkObj->getBaseUrl() . "organization/" . $this->_orgReference . "/roster/members?Direction=Ascending&page=" . $page);// KURTZ consider using a different source, like https://drexel.collegiatelink.net/organization/drexelforchrist/roster/manage?Direction=Ascending&Page=1 (note, though, that this one is only available to users with management permissions)
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


	/**
	 * getProspectiveMembers returns an array of CollegiateLinkProspectiveMembers (which extends CollegiateLinkPerson)
	 * who have requested joining the organization on CollegiateLink.
	 *
	 * @param int $startPage
	 * @param int $endPage
	 * @return CollegiateLinkProspectiveMember[] An array of prospective members, which is an extension of
	 * CollegiateLinkPerson that also allows for approval or rejection.
	 */
	public function getProspectiveMembers($startPage = 1, $endPage = 10) { // 15 per page.  If the org has members listed in their officer section, that's a much more efficient way to get this stuff.
		$r = array();
		include_once "CollegiateLinkProspectiveMember.class.php"; // KURTZ consider using a different class that extends CollegiateLinkPerson that allows for more details, such as approval.
		for ($page = intval($startPage); $page <= intval($endPage); $page++) { // loop also breaks if reached end of list.  See end of loop for that.
			set_time_limit(30);
			$c = $this->_clinkObj->getACurl($this->_clinkObj->getBaseUrl() . "organization/" . $this->_orgReference . "/roster/prospective?Direction=Ascending&page=" . $page);
			$c->setCookieFile($this->_clinkObj->getCookieFile());
			$c->addRequestHeader("X-Requested-With: XMLHttpRequest");
			$c->includeHeader(false);
			$c->maxRedirects(0);
			$c->createCurl();
			$this->_clinkObj->incrementCurlCount();

			$h = new simple_html_dom((string)$c);

			$reqToken = $h->find('input[name="__RequestVerificationToken"]',0)->getAttribute("value");
			$people = array_merge($h->find('tr[class="gridrow"]'), $h->find('tr[class="gridrow_alternate"]'));

			foreach ($people as $person) {
				if ($person->class == "gridHeader") {
					continue;
				}

				$personArr = array();
				$personArr['ReqToken'] = $reqToken;

				$personArr['HasPicture'] = false;
				$personArr['CommunityMemberProfilePicture'] = null;
				if ($img = $person->find("img",0)) {
					$personArr['HasPicture'] = true;
					$personArr['CommunityMemberProfilePicture'] = substr($img->getAttribute("src"), 35, -4);
				}
				unset($img);

				$personArr['RequestDate'] = $person->find("td", 2)->innertext();

				$personName = $person->find("a", 0);
				$personArr['CommunityMemberName'] = $personName->innertext();
				$id = $personName->getAttribute("href");
				$personArr['CommunityMemberId'] = intval(substr($id, 18));

				$personArr['RelationshipId'] = $person->find("a", 1)->getAttribute("data-id");


				unset($personName, $id);

				$r[] = new CollegiateLinkProspectiveMember($personArr, $this->_clinkObj, $this->_orgReference);
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