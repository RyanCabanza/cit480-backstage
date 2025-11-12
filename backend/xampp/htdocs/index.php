<?php
	/** info for sql */
	$user = 'root';
	$password = '9wE!l@vnydz2sJ*Z';
	$database = 'backstage-db';
	$servername='localhost:3306';
	$$mysqli = new mysqli($servername, $user,
                $password, $database);

	/** checks connection */
	if (!empty($_SERVER['HTTPS']) && ('on' == $_SERVER['HTTPS'])) {
		$uri = 'https://';
	} else {
		$uri = 'http://';
	}
	$uri .= $_SERVER['HTTP_HOST'];
	header('Location: '.$uri.'/dashboard/');
	exit;
?>
Something is wrong with the XAMPP installation :-(
