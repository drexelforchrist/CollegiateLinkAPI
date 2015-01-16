<?php

//if  (in_array  ('curl', get_loaded_extensions())) {
//	echo "cURL is installed on this server";
//}
//else {
//	echo "cURL is not installed on this server";
//}

require "CollegiateLink.class.php";

require_once "authenticate.php";

authenticate();

$cookie = "collegiatelinkCookie.cky";

$baseUrl = "https://drexel.collegiatelink.net/";

$clink = new CollegiateLink($baseUrl, $cookie);

$dsfc = $clink->getOrganization("drexelforchrist");

var_dump($dsfc->getMembers());
