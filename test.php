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

echo "<table>";
foreach ($dsfc->getMembers() as $num => $mem) {
	echo "<tr><td>$num</td><td>";
	echo $mem->getFullName();
	echo "</td>";
	echo "<td>";
	echo $mem->getEmailAddr();
	echo "</td>";
	echo "<td>";
	echo $mem->FacebookProfile;
	echo "</td>";
	echo "<td>";
	$p = $mem->getLargeProfilePicture();
	if ($p!==false) {
		echo "<img src=\"$p\" />";
	}
	echo "</td>";
	echo "</tr>";
}
echo "</table>";

