<?php

//if  (in_array  ('curl', get_loaded_extensions())) {
//	echo "cURL is installed on this server";
//}
//else {
//	echo "cURL is not installed on this server";
//}

require "src/CollegiateLink.class.php";

require_once "Tests/authenticate.php";

authenticate();

$cookie = "collegiatelinkCookie.cky";

$baseUrl = "https://drexel.collegiatelink.net/";

$clink = new CollegiateLink($baseUrl, $cookie);

$christOrgs = $clink->findOrganizations("Christ");

foreach ($christOrgs as $o) {
	$o->getMembers();

	echo "<h3>" . $o->name . "</h3>";

	echo "<table>";
	foreach ($o->getMembers() as $num => $mem) {
		echo "<tr><td>$num</td>";
		echo "<td>";
		echo $mem->CommunityMemberId;
		echo "</td>";
		echo "<td>";
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
}

die();

$dsfc = $clink->getOrganization("drexelforchrist");


echo "<table>";
foreach ($dsfc->getProspectiveMembers() as $num => $prospective) {
	echo "<tr><td>$num</td>";
	echo "<td>";
	echo $prospective->CommunityMemberId;
	echo "</td>";
	echo "<td>";
	echo $prospective->getFullName();
	echo "</td>";
	echo "<td>";
	echo $prospective->getEmailAddr();
	echo "</td>";
	echo "<td>";
	echo $prospective->FacebookProfile;
	echo "</td>";
	echo "<td>";
	$p = $prospective->getLargeProfilePicture();
	if ($p!==false) {
		echo "<img src=\"$p\" />";
	}
	echo "</td>";
	echo "</tr>";


	$prospective->approveMember();

	die();
}
echo "</table>";

