<?php

include_once "CollegiateLinkPerson.class.php";

class CollegiateLinkProspectiveMember extends CollegiateLinkPerson {

	private $RelationshipId;
	private $RequestDate;

	private $__RequestVerificationToken = null;

	private $_orgReference;


	public function __construct($array, &$clinkObj, &$orgReference) {
		$this->_orgReference = &$orgReference;

		parent::__construct($array, $clinkObj);

		if(isset($array['RelationshipId'])) {
			$this->RelationshipId = $array['RelationshipId'];
		}

		if(isset($array['RequestDate'])) {
			$this->RequestDate = $array['RequestDate'];
		}

		if(isset($array['ReqToken'])) {
			$this->__RequestVerificationToken = $array['ReqToken'];
		}
	}


	public function approveMember() {
		$this->approveOrDenyMember(true);
	}

	public function denyMember() {
		$this->approveOrDenyMember(false);
	}


	private function approveOrDenyMember($approve) {
		$approve = !!$approve;
		if ($approve) {
			$c = new aCurl($this->_clinkObj->getBaseUrl() . "organization/" . $this->_orgReference . "/roster/approvemember");
		} else {
			$c = new aCurl($this->_clinkObj->getBaseUrl() . "organization/" . $this->_orgReference . "/roster/denymember");
		}
		//$c = $this->_clinkObj->getACurl("http://");
		$c->setCookieFile($this->_clinkObj->getCookieFile());
		$c->addRequestHeader("X-Requested-With: XMLHttpRequest");
		$c->includeHeader(false);
		$c->setPost(array("id" => $this->RelationshipId,
							"__RequestVerificationToken" => $this->__RequestVerificationToken));
		$c->maxRedirects(4);
		$c->createCurl();
		$this->_clinkObj->incrementCurlCount();
	}
}