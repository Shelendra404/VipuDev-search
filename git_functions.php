<?php 
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}

	// variables
		$baseUrl = "https://api.github.com/users/" .$user. "/repos";
		$initialUrl = $baseUrl ."?per_page=1";	
		$urlRepos = $baseUrl ."/repos?per_page=30";	
		$urlCommits = "https://api.github.com/repos/" .$user;
		
	// Obviously have removed user/oauth token here, otherwise everything is intact.
	function helloThere() {
		$user = "****";
		$token = "****";
		$authString= $user.":".$token;
		return $authString;	
		
	}

	function getCommitUrl($user, $repo) {
		$url = "https://api.github.com/repos/" .$user. "/" .$repo. "/commits";
		return $url;
	}

	function getBaseUrl($user) {
		$baseUrl = "https://api.github.com/users/" .$user. "/repos";
		return $baseUrl;
	}

	function getInitialUrl($user) {
		$initialUrl = getBaseUrl($user)."?per_page=1";	;
		return $initialUrl;
	}

	function getUrlCommits($user) {
		$urlCommits = "https://api.github.com/repos/" .$user;
		return $urlCommits;
	}
?>
