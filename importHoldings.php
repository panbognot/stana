<?php
require_once('connectDB.php');
require_once('dataAccountManagement.php');

function importHoldingsFromCSV($filepath)
{
	$file = fopen($filepath,"r");
	$isFirst = true;
	$ctr = 0;
	$labels = [];

	while(! feof($file))
	{
		if ($isFirst) {
			$labels = fgetcsv($file);
			$isFirst = !$isFirst;

			continue;
		}

		$temp = fgetcsv($file);

		for ($i=0; $i < count($labels); $i++) { 
			$holdings[$ctr][$labels[$i]] = $temp[$i];
		}

		$ctr++;
	}

	fclose($file);

	//echo json_encode($holdings);
	return $holdings;
}

$buys = importHoldingsFromCSV("currentHoldings.csv");

echo json_encode($buys) . "<Br><Br>";

//Generate the Stop Loss Price (slp)
//SLP = Buy Price - 1 ATR

importHoldingsData ($mysql_host, $mysql_database, $mysql_user, $mysql_password, $buys);

?>