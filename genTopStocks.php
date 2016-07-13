<?php 
	require_once('connectDB.php');
	require_once('dataBasicPlots.php');

	$topGainers = getTopGainers($mysql_host, $mysql_database, $mysql_user, $mysql_password);
	$fullData['recommendations'] = $topGainers;
	echo json_encode($fullData);

?>