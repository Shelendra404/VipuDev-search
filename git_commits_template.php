<?php
/**
 * Template Name: Git Commits Template
 *
 ** This template has been created by Shelendra404 purely for the purpose of demonstration. 
 ** The template provides search functionality to find information specified by the "quest-giver" using GitHub API.
 ** 
 *
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}
get_header();

?>
	<!-- Latest compiled and minified CSS -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">

	<!-- jQuery library -->
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>

	<!-- Latest compiled JavaScript -->
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>

<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/wp-content/themes/ryancv-child/git_functions.php';

	$onepage = get_field( 'onepage', 'option' );
	$sticky_menu = get_field( 'sticky_menu', 'options' );
	$simple_vcard = get_field( 'simple_vcard', 'options' );

	if ( $onepage && $sticky_menu ) {
		$menu_locations = get_nav_menu_locations();
		$menu_primary = $menu_locations['primary'];
		$frontpage_id = get_option( 'page_on_front' );
	}
?>

	<!--
		Card - Page
	-->
	<div class="card-inner blog blog-post animated active" id="card-page">
		<div class="card-wrap">			
		<!--
			Page
		-->
		<div class="content blog-page">
		<button onclick="goBack()">Go Back</button>

		<script>
		// This function makes a button that goes back one page is history exists, or back to blank search page if not.
		function goBack() {
			console.log(history.length);
			if(history.length === 1){
				window.location = 'https://niina.dev/search';
			} else {
				history.back();
			}
		}
		</script>		
			
<?php 

	if(isset($_GET['repo'])) {
		$user = $_GET['user'];
		$repo = $_GET['repo'];
		getCommits($repo, $user);
	}
	
	// This function shows the last 10 commits on MASTER branch.
	function getCommits($repo, $user) {	
		
		$url = getCommitUrl($user, $repo);
		
		$cInit = curl_init();
		curl_setopt($cInit, CURLOPT_URL, $url);
		curl_setopt($cInit, CURLOPT_RETURNTRANSFER, 1); // 1 = TRUE
		curl_setopt($cInit, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
		curl_setopt($cInit, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt($cInit, CURLOPT_USERPWD, helloThere());
		
		$output = curl_exec($cInit);
		$result = json_decode($output);

		echo '<h2>Repository: ' .$repo. '</h2>';
		// If repo has more than 10 commits, show last 10, otherwise all.
		$numResults = 0;		
		if (count($result) > 10) {
			$numResults = 10;
		}
		else {
			$numResults = count($result);
		}
		
		for ($i=0; $i<$numResults; $i++) {
			
			// Show default image if user doesn't have one.
			if (!isset($result[$i]->author->avatar_url)) {
				echo '<div class="row"><div class=col-md-3><img src="https://niina.dev/wp-content/uploads/2019/12/no_image_found.png"></img></div>';
			}
			else {
				echo '<div class="row"><div class=col-md-3><img src="'.$result[$i]->author->avatar_url.'"></img></div>';
			}

			// Commits output
			echo '<div class="col-md-9"><p>';
			echo '<b>Author: </b>' .$result[$i]->commit->author->name. '</br>';
			echo '<b>Date: </b>' .date('d. F Y', strtotime($result[$i]->commit->author->date)). '</br>';
			echo '<b>Message: </b>' .$result[$i]->commit->message. '</br>';
			echo '</p></div>';			
			echo '</div><hr>';		
		}

		curl_close($cInit);
	}

?>
			</div>
		
		</div>

	</div>

<?php
get_footer();
