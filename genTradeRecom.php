<?php 
	require_once('connectDB.php');
	require_once('dataBasicPlots.php');
	require_once('dataSMA.php');

	if(isset($_GET['days'])) {
		$days = (int)($_GET['days']);
	}
	else {
		$days = 5;
	}

	if(isset($_GET['type'])) {
		$type = $_GET['type'];

		switch ($type) {
		    case "smac":
		        echo "SIMPLE MOVING AVERAGE COMBINED <Br><Br>";
		        break;
		    case "bb3":
		        echo "BOLLINGER BANDS 3 <Br><Br>";
		        break;
		    default:
		        $type = "smac";
		        echo "SIMPLE MOVING AVERAGE COMBINED <Br><Br>";
		}
	}
	else {
		echo "SIMPLE MOVING AVERAGE COMBINED <Br><Br>";
		$type = "smac";
	}

	$toDate;
	$fromDate;
	$dataorg = "json";
	$ensig = "latest";
	$delta = "1 year";

	function getTimeRange($deltaTime) {
		global $toDate, $fromDate;

		$toDate = date_create(date("Y-m-d"));

		$fromDate = date_create(date("Y-m-d"));
		date_sub($fromDate, date_interval_create_from_date_string($deltaTime));

		$fromDate = date_format($fromDate,"Y-m-d");
		$toDate = date_format($toDate,"Y-m-d");
	}

	function getDateDiffFromPresent($signalDate) {
		$now = time(); // or your date as well
		$your_date = strtotime($signalDate);
		$datediff = $now - $your_date;

		return floor($datediff/(60*60*24));
	}

	//get the time range
	getTimeRange($delta);

	//generate stock quotes
	$companyList = readStockQuotes($mysql_host, $mysql_database, $mysql_user, $mysql_password);
	//echo json_encode($companyList);

	$latestSignals = [];
	$ctr = 0;
	foreach ($companyList as $company) {
		switch ($type) {
		    case "smac":
				$latest = getSMACombined($company, $fromDate, $toDate, $dataorg, 
							20, 50, 120, $ensig, 
							$mysql_host, $mysql_database, $mysql_user, $mysql_password);
		        break;
		    case "bb3":
		    	echo "using bollinger bands 3 <Br>";
		    	return;
		        break;
		    default:
		        echo "Error: No selected Type of Signal Generator <Br><Br>";
		        return;
		}

		if ( ($latest == 0) || ($latest == []) ) {
			continue;
		}

		$latest[count($latest)] = $company;
		$latestSignals[$ctr++] = $latest;
	}

	//echo json_encode($latestSignals);

	//filter out dates that aren't within the target time window
	$curMonthSignals = [];
	$ctr = 0;
	foreach ($latestSignals as $signal) {
		if (getDateDiffFromPresent($signal[0]) < $days) {
			$curMonthSignals[$ctr++] = $signal;
		}
	}

	//echo json_encode($curMonthSignals);

	//segrate the signals into buy and sell categories
	$filteredBuys = [];
	$filteredSells = [];
	$ctrBuy = $ctrSell = 0;
	foreach ($curMonthSignals as $signal) {
		if ($signal[1] == "buy") {
			$filteredBuys[$ctrBuy++] = $signal;
		}
		elseif ($signal[1] == "sell") {
			$filteredSells[$ctrSell++] = $signal;
		}
	}

	//Display the Buy Recommendations
	echo "Buy Signals for the Past $days days :<Br><Br>";
	foreach ($filteredBuys as $buys) {
		echo "Company: ".$buys[3].", date: ".$buys[0]."<Br>";
	}

	echo "<Br><Br>";

	//Display the Sell Recommendations
	echo "Sell Signals for the Past month:<Br><Br>";
	foreach ($filteredSells as $sells) {
		echo "Company: ".$sells[3].", date: ".$sells[0]."<Br>";
	}
?>