<?php 
	require_once('connectDB.php');
	require_once('dataBasicPlots.php');
	require_once('dataSMA.php');
	require_once('dataBollinger.php');
	require_once('dataAccountManagement.php');

	set_time_limit(120);

	$toDate;
	$fromDate;
	$dataorg = "json";
	$ensig = "latest";
	$delta = "1 year";

	//initialize variables
	$debug_mode = false;

	function debug_print($string) {
		global $debug_mode;

		if ($debug_mode) {
			echo "$string";
		}
	}

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
		        $delta = "3 months";
		        break;
		    case "stomacd":
		        echo "STOCHASTIC and MACD COMBINED <Br><Br>";
		        $delta = "3 months";
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
	//echo json_encode($companyList) . "<Br><Br>";

	$latestSignals = [];
	$ctr = 0;
	foreach ($companyList as $company) {
		// echo "$company:<Br>";
		$latest = 0;
		switch ($type) {
		    case "smac":
		    	// echo "    smac<Br>";
				$latest = getSMACombined($company, $fromDate, $toDate, $dataorg, 
							20, 50, 120, $ensig, 
							$mysql_host, $mysql_database, $mysql_user, $mysql_password);
		        break;
		    case "bb3":
		    	// echo "    bb3<Br>";
		    	$latest = getBollingerBands3($company, $fromDate, $toDate, $dataorg, 
							$ensig, 
							$mysql_host, $mysql_database, $mysql_user, $mysql_password);
		        break;
		    case "stomacd":
		    	// echo "    stomacd<Br>";
		    	$latest = getStoMACD($company, $fromDate, $toDate, $dataorg, 
							$ensig, 
							$mysql_host, $mysql_database, $mysql_user, $mysql_password);
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

	//segregate the signals into buy and sell categories
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

	//Sort according to date
	function date_compare($a, $b)
	{
		//The timestamp is the zeroth element
	    $t1 = strtotime($a[0]);
	    $t2 = strtotime($b[0]);
	    return $t2 - $t1;
	}    
	usort($filteredBuys, 'date_compare');
	usort($filteredSells, 'date_compare');

	//Display the Buy Recommendations
	echo "Buy Signals for the Past $days days :<Br><Br>";
	foreach ($filteredBuys as $buys) {
		$name = str_replace("_", "", $buys[count($buys) - 1]);
		echo "Company: ".strtoupper($name).", date: ".$buys[0]."<Br>";
	}

	echo "<Br><Br>";

	//Display the Sell Recommendations
	echo "Sell Signals for the Past month:<Br><Br>";
	foreach ($filteredSells as $sells) {
		$name = str_replace("_", "", $sells[count($sells) - 1]);
		echo "Company: ".strtoupper($name).", date: ".$sells[0]."<Br>";
	}
?>