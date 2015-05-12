<?php
//require_once 'PHPUnit/Framework.php';
//require_once 'PHPUnit/Autoload.php';

require_once "/src/CollegiateLink.class.php";

class CollegiateLink_Test extends PHPUnit_Framework_TestCase {
	protected function setUp() {
//		include_once 'libs/aCurl/aCurl.php';
//		include_once 'libs/php-dom-parser/php-dom-parser.php';
		include_once 'authenticate.php';
		authenticate();
		parent::setUp();
	}


	/**
	 * @expectedException     InvalidCollegiateLinkParametersException
	 * @expectedExceptionCode 1
	 */
	public function test_constrBaseUrlNotNull() {
		new CollegiateLink(null, "collegiateLinkCookie.txt");
	}


	/**
	 * @expectedException     InvalidCollegiateLinkParametersException
	 * @expectedExceptionCode 1
	 */
	public function test_constrBaseUrlNotBlank() {
		new CollegiateLink("", "collegiateLinkCookie.txt");
	}


	/**
	 * @expectedException     InvalidCollegiateLinkParametersException
	 * @expectedExceptionCode 3
	 */
	public function test_constrBaseUrlNoSlash() {
		new CollegiateLink("https://drexel.collegiatelink.net", "collegiateLinkCookie.txt");
	}


	/**
	 * @expectedException     InvalidCollegiateLinkParametersException
	 * @expectedExceptionCode 2
	 */
	public function test_constrCookieAddressNotNull() {
		new CollegiateLink("https://drexel.collegiatelink.net/", null);
	}


	/**
	 * @expectedException     InvalidCollegiateLinkParametersException
	 * @expectedExceptionCode 2
	 */
	public function test_constrCookieAddressNotBlank() {
		new CollegiateLink("https://drexel.collegiatelink.net/", "");
	}


	public function test_getACurl() {
		$clink = new CollegiateLink("https://drexel.collegiatelink.net/", "collegiatelinkCookie.cky");
		$clink->getACurl("https://github.com/drexelforchrist/CollegiateLinkAPI");
	}


	public function test_getOrganization() {
		$clink = new CollegiateLink("https://drexel.collegiatelink.net/", "collegiatelinkCookie.cky");
		$clink->getOrganization("drexelforchrist");
	}


	public function test_findOrganizations() {
		$clink = new CollegiateLink("https://drexel.collegiatelink.net/", "collegiatelinkCookie.cky");
		if (count($clink->findOrganizations("Christ")) < 2) {
			throw new Exception("Find failed.");
		}
	}


}
 