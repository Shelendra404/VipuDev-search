	<?php
	/**
	 * Template Name: Git Search Template
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

		// Setting up the page as per theme options, so it looks and functions like the original theme templates.
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
					<form method="post" action="">
						<h2>GitHub Search</h2>
						<hr>	
						<p><label for="username">Search for user:</label>
						<input type="text" id="username" name="username" value="<?=($_POST['username'] ? htmlentities($_POST['username']) : '')?>" /></p>
						<p><input type="submit" name="submit" value="Submit" /></p>
					</form>					
				</div>

	<?php 
	require_once $_SERVER['DOCUMENT_ROOT'] . '/wp-content/themes/ryancv-child/git_functions.php';

		// Setting up the variables:
		$user = htmlentities($_POST["username"]);
		
		// URLs
		// All base URLs are located in functions file 
		// so that for updating, they only need to be changed in one place.
		$baseUrl = getBaseUrl($user);
		$initialUrl = getInitialUrl($user);		
		$urlCommits = getUrlCommits($user);
		$urlRepos = $baseUrl ."/repos?per_page=30";	

		// The intention of this separate function was originally to just get the amount of pages required for pagination.
		// Since I failed making the pagination buttons, I instead attempted to use this function to set "per_page" parameter based on results.
		// This worked, but GitHub API still has max limit of 100 per page, so my attempt to get around my pagination-fail also failed.
		function getAmountOfRepos($initialUrl, $baseUrl, $user) {

			
			$cInit = curl_init();
			curl_setopt($cInit, CURLOPT_URL, $initialUrl);
			curl_setopt($cInit, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($cInit, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
			curl_setopt($cInit, CURLOPT_HEADER, 1);
			curl_setopt($cInit, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
			
			// This function calls for encrypted OAUTH token from another file with no direct access to it.
			curl_setopt($cInit, CURLOPT_USERPWD, helloThere());

			// echo getInitialUrl($user);
			// echo getBaseUrl($user);

			$result = curl_exec($cInit);		
			$result = str_replace("<",'"',$result);
			$result = str_replace(">",'"',$result);
			
			// Separate information into chunks	based on header size	
			$header_size = curl_getinfo($cInit, CURLINFO_HEADER_SIZE);
			$header = substr($result, 0, $header_size);
			$body = substr($result, $header_size);
			$headers = explode("\n", $header);
			$headers = get_headers_from_curl_response($header);

			// If headers are returned, check for following errors:
			// User doesn't exist / RateLimit reached / User doesn't have public repo's.
			if (isset($headers)) {
				if (isset($headers['http_code']) && ($headers['http_code'] == "HTTP/1.1 404 Not Found")) {
					echo "This user doesn't exist.";
				}
				else if (isset($headers['X-RateLimit-Remaining']) && ($headers['X-RateLimit-Remaining'] = 0)) {
					echo "You have somehow managed to exceed the 5000 requests per hour -limit of authenticated requests. Please do something else for a while!";
				}
				else if (isset($headers['Link'])) {			
				$num = moreResults($headers['Link']);			
					if ($num <100) {
					$url = $baseUrl."?per_page=".$num;
					}
					else {
						$url = $baseUrl."?per_page=100";
					}
					getRepos($url, $urlCommits, $user, $num);
				}
				else {
				echo "This user doesn't have any public repositories.";
				}	
			}
			
			curl_close($cInit);

		}
		
		// This function remains here as means to demonstrate the pagination attempt.
		// The function goes through header link, explodes, makes array with key/value, uses this to check the last page.
		function moreResults($data) {

				$urls = explode(",", $data);
				$a=array();

				foreach ( $urls as $item ) {
					list($k, $v) = explode(';', $item);
					$result[ $k ] = $v;
					$a[$v]=$k;
				}	

				$last = $a[' rel="last"'];

				if (isset($last)) {
					
						$url_components = parse_url($last); 
						parse_str($url_components['query'], $params); 
						$num = rtrim($params['page'], "\"");
						return $num;
						  
				}
		}
		
		// This function is NOT mine. It was simple and it worked though, so I saw no need to change it.
		// It is made by c.hill https://stackoverflow.com/users/1157309/c-hill
		// and found on https://stackoverflow.com/questions/10589889/returning-header-as-array-using-curl
		function get_headers_from_curl_response($response){
					$headers = array();

					$header_text = substr($response, 0, strpos($response, "\r\n\r\n"));

					foreach (explode("\r\n", $header_text) as $i => $line)
						if ($i === 0)
							$headers['http_code'] = $line;
						else
						{
							list ($key, $value) = explode(': ', $line);

							$headers[$key] = $value;
						}

					return $headers;
				}


		function getRepos($urlRepos, $urlCommits, $user, $num) {

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $urlRepos);
			
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
			curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);	
			curl_setopt($ch, CURLOPT_USERPWD, helloThere());		

			$result = curl_exec($ch);	
			$repoArray = json_decode($result, true);

			echo '<br>';
			
			// Again, with the failed pagination, we can now only check the amount of public repo's a user has, just not do anything with them.
			if ($num > 100) {	
				echo "This user has " .$num. " public repositories. Due to limitations, we are showing the first 100.";
			}
			else {
				echo "<h4>User ".$user." has ".count($repoArray)." public repositories: </h4><br>";
			}

			echo '<div class="repoResults">';
			for ($i=0; $i<count($repoArray); $i++) {

				echo '<div class="flex-container">';
				echo '<div><b>' .$repoArray[$i]['name'].'</b></div>';
				echo '<div><a href="/commits?user='.htmlentities($_POST["username"]).'&repo=' .$repoArray[$i]['name']. '">Show commits</a></div>';
				echo '</div><hr>';
			}
			echo '</div>';
			curl_close($ch);
		}		

		if(isset($_POST['submit'])) {
			
			$numberOfResults = getAmountOfRepos($initialUrl, $baseUrl, $user);

		}

		?>

			</div>
			
		</div>

	</div>

	<?php
	get_footer();
