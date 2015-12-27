<?php
	require_once('connectDB.php');
	require_once('dataBasicPlots.php');

	if (!isset($_GET['keyword'])) {
		die();
	}

	$keyword = $_GET['keyword'];
	searchForCompany($keyword, $mysql_host, $mysql_database, $mysql_user, $mysql_password);
?>