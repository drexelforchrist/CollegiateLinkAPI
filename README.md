CollegiateLink API
==================

This is a PHP API for interfacing with CollegiateLink, a service available to universities from CampusLabs, for managing Student Organizations.  This API is not in any way authorised, approved, or supported by CampusLabs.

## License

Don't break laws.  Don't break your institution's terms of use.  Don't attempt to use this to access data that isn't meant for you (such attempts won't work anyway).  Otherwise, use this however you want at your own risk.  The contributors take no responsibility for your behavior. 

If you expand or improve this API, you must at least consider submitting a pull request. 

## Motivation

To campus leaders, CollegiateLink is difficult to use for meaningful productivity.  For instance, there's no way to integrate it with a student organization's external website.  This API is meant to help fix that.  Essentially, this API simply mimics user actions. 

## Prerequisites

 - PHP >5.0
 - some idea of how to write PHP.

## Use

Before you can use the API in a meaningful way, you need to authenticate yourself as an authorized user.  If you're just doing this as a stand-alone project, you'll likely find it easiest to use the included aCurl and dom-parser submodules (both of which are required for this API) to emulate a user in a browser. 

### Authentication

First, you must authenticate yourself with your school's authentication system.  The maintainers of this project are affiliated with Drexel University, and for us, authentication is completely handled by the following 'authenticate' function: (which is effective, but has lots of room for improvement.)
    
    function authenticate() {
    	global $TASK;
    
    	try {
    
    		/* Request standard page, via log in if needed */
    		$c = "";
    		set_time_limit(30);
    		$c = new aCurl("https://drexel.collegiatelink.net/account/logon");
    		$c->setCookieFile($TASK['cookie']);
    		$c->includeHeader(false);
    		$c->maxRedirects(5);
    		$c->createCurl();
    
    		if ($c->getInfo()['url'] == "https://federation.campuslabs.com/?wa=wsignin1.0&wtrealm=https%3a%2f%2fdrexel.collegiatelink.net%2fexternalauthentication%2ffederationresult%2f&wctx=https%3a%2f%2fdrexel.collegiatelink.net%2faccount%2flogon") {
    			$h = new simple_html_dom((string)$c);
    			$form = $h->find('form[name="hiddenform"]', 0);
    
    			$posts = array();
    			foreach($form->find('input') as $in) {
    				$posts[$in->name] = html_entity_decode($in->value);
    			}
    
    			unset($c, $h);
    
    			$c = "";
    			$c = new aCurl($form->action);
    			$c->setCookieFile($TASK['cookie']);
    			$c->setPost(http_build_query($posts));
    			$c->maxRedirects(5);
    			$c->includeHeader(false);
    			$c->createCurl();
    			unset($posts);
    		}
    
    
    		if ($c->getInfo()['url'] == "https://login.drexel.edu/cas/login?service=https://jcf.irt.drexel.edu/authenticate") {
    			/* need to authenticate through CAS. */
    
    			/* Drexel CAS */
    			$h = new simple_html_dom((string)$c);
    			if ($a = $h->find('a',0)) {
    
    				if ($a->innerText() == "here") {
    					$c = "";
    					set_time_limit(30);
    					$c = new aCurl($a->href);
    					$c->setCookieFile($TASK['cookie']);
    					$c->maxRedirects(5);
    					$c->includeHeader(false);
    					$c->createCurl();
    
    					return 1;
    				}
    			}
    			$form = $h->find('form[id="fm1"]', 0);
    			foreach($form->find('input') as $in) {
    				$posts[$in->name] = html_entity_decode($in->value);
    			}
    
    			unset($c, $h);
    
    			$posts['username']=USERNAME;
    			$posts['password']=PASSWORD;
    
    			$c = "";
    			set_time_limit(30);
    			$c = new aCurl("https://login.drexel.edu".html_entity_decode($form->action));
    			$c->setCookieFile($TASK['cookie']);
    			$c->setPost(http_build_query($posts));
    			$c->maxRedirects(5);
    			$c->includeHeader(false);
    			$c->createCurl();
    			unset($posts);
    
    		}
    
    
    		if (substr($c->getInfo()['url'],0,38) === "https://federation.campuslabs.com/gpt?") {
    			/* cLink Federation.  This request gets us "in" */
    
    			$h = new simple_html_dom((string)$c);
    			$form = $h->find('form[name="hiddenform"]', 0);
    
    			$posts = array();
    			foreach($form->find('input') as $in) {
    				$posts[$in->name] = html_entity_decode($in->value);
    			}
    
    			unset($c, $h);
    
    			$c = "";
    			set_time_limit(30);
    			$c = new aCurl($form->action);
    			$c->setCookieFile($TASK['cookie']);
    			$c->setPost(http_build_query($posts));
    			$c->maxRedirects(5);
    			$c->includeHeader(false);
    			$c->createCurl();
    			unset($posts);
    		}
    
    		return 1;
    	} catch (Exception $e) {
    		return 0;
    	}
    }
    
    
### Sample Use Cases

For the sake of these demos, we're going to assume that you're a leader of Drexel Students for Christ, a Student Organization (org) at Drexel University.  (You see, this is exactly what I am.)  

All use cases start with am object of the CollegiateLink class, which can be recycled for other calls later in your script, if you so desire.  We'll call our instance of this object "clink".  Clink is created by something like:

    $cookie = "collegiatelinkCookie.cky";
    $baseUrl = "https://drexel.collegiatelink.net/";
    
    $clink = new CollegiateLink($baseUrl, $cookie);
  
 
#### The Org

Most of the operations of this API are meant for org leaders to be able to somewhat automate their administrative load.  For an org leader, the operations regarding the org are done through objects of the org class.  If you want to create an org object for a specific org, do so like this:

    $dfc = $clink->getOrganization("drexelforchrist");
    
where $clink is the CollegiateLink object we defined earlier, and "drexelforchrist" is the org-specific part of the URL to the org's page.  That is, https://drexel.collegiatelink.net/organizations/drexelforchrist results in "drexelforchrist". 


#### The Person

The most simple type of person we'll deal with is the "member" of an org.

To get a list of members (an array of Person objects) for a given org:

    $dfc->getProspectiveMembers()
    
where $dfc is the same as defined above.  

So, now you have an array of person objects, but what can you do with it?  Well, most of the information that appears on a user's "contact card" on CollegiateLink is programmatically accessible through this API. 

If you want to print out a list of members, complete with their community member ID (a CollegiateLink-specific user ID number), full name, email address, facebook profile (if they have it listed), and even their profile picture, this'll do: 

    echo "<table>";
    foreach ($dfc->getMembers() as $num => $mem) {
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
 
And just like that, we've solved one of the biggest gripes of CollegiateLink: easily exporting members' email addresses. 
 
 
### Everything Else
 
The entirety of the API is documented with PHPDocs, which can be easily read in the Docs directory. 