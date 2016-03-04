<?php 
	require_once('codesword_sma.php');

	// get real data for the SMA
	function getSMA_sub_real ($company, $from="1900-01-01 00:00:00", $to=null, 
					$dataorg="json", $samplePeriod=15, $enSignals=false,
					$host, $db, $user, $pass) {
		// Create connection
		$con=mysqli_connect($host, $user, $pass, $db);
		
		// Check connection
		if (mysqli_connect_errno()) {
		  echo "Failed to connect to MySQL: " . mysqli_connect_error();
		  return;
		}

		$intervalPeriod = $samplePeriod * 1.5;
		//from date has to be adjusted for the bollinger bands
		$date = date_create($from);
		date_add($date,date_interval_create_from_date_string("-$intervalPeriod days"));
		$fromAdjusted =  date_format($date,"Y-m-d");

		$dataOhlc = [];

		//OHLC data format [timestamp,open,high,low,close,volume]
		if ($dataorg == "highchart") {
			$dataOhlc = getOHLC ($company, $fromAdjusted, $to, "array2", $host, $db, $user, $pass);
		} else {
			$dataOhlc = getOHLC ($company, $fromAdjusted, $to, "array", $host, $db, $user, $pass);
		}

		//Return if $dataOhlc is null
		if ( ($dataOhlc == []) || ($dataOhlc == 0) ) {
			return 0;
		}

		//Input for SMA functions should be [timestamp,close]
		$ctr = 0;
		foreach ((array)$dataOhlc as $ohlc) {
			$dbreturn[$ctr][0] = $ohlc[0];	//timestamp
			$dbreturn[$ctr++][1] = $ohlc[4];	//close
		}

		return $dbreturn;
	}

	// returns Simple Moving Average Data {(timestamp, sma), (timetsamp, signal)}
	function getSMA ($company, $from="1900-01-01 00:00:00", $to=null, 
					$dataorg="json", $samplePeriod=15, $enSignals=false,
					$enJsonEncode="false",
					$host, $db, $user, $pass) {

		$dbreturn = getSMA_sub_real ($company, $from, $to, 
					$dataorg, $samplePeriod, $enSignals,
					$host, $db, $user, $pass);

		if ($dataorg == "json") {
			$sma = codesword_sma($dbreturn, $samplePeriod);
		} 
		elseif ($dataorg == "highchart") {
			$sma = codesword_sma($dbreturn, $samplePeriod);
		}
		elseif ($dataorg == "array") {
		//TODO: create code for organizing an array data output
		}
		else { //json
			$sma = codesword_sma($dbreturn, $samplePeriod);
		}

		if ($enSignals) {
			$buysellSignals = codesword_smaBuySellSignal($dbreturn, $sma);
		} 
		else {
			$buysellSignals = 0;
		}
		
		if ($enJsonEncode) {
			$allData = [];
			$allData[0] = $sma;
			$allData[1] = $buysellSignals;
			echo json_encode($allData);
		}
		else {
			return $sma;
		}
	}

	// returns Simple Moving Average Combined Data
	// {(timestamp, smaShort, smaMedium, smaLong), (timestamp, buysell signal)}
	function getSMACombined ($company, $from="1900-01-01 00:00:00", $to=null, 
					$dataorg="json", $periodShort=20, $periodMedium=50, 
					$periodLong=120, $enSignals=false,
					$host, $db, $user, $pass) {

		$smaShort = getSMA($company, $from, $to, $dataorg, $periodShort, 
						$enSignals, false,
						$host, $db, $user, $pass);
		$smaMedium = getSMA($company, $from, $to, $dataorg, $periodMedium, 
						$enSignals, false,
						$host, $db, $user, $pass);
		$smaLong = getSMA($company, $from, $to, $dataorg, $periodLong, 
						$enSignals, false,
						$host, $db, $user, $pass);
		$real = getSMA_sub_real ($company, $from, $to, 
					$dataorg, $periodShort, $enSignals,
					$host, $db, $user, $pass);

		if ( ($smaShort == 0) || ($smaMedium == 0) || ($smaLong == 0) || ($real == 0) ) {
			return 0;
		}

		$allData = [];
		if (strcasecmp($enSignals, "latest") == 0) {
			//return only the latest signal
			// [timestamp,trade signal]
			$lastSignal = codesword_smaBuySellSignalCombinedLatests($real, $smaShort, $smaMedium, $smaLong, $dataorg);
			return $lastSignal;
		}
		elseif ($enSignals) {
			$allData = codesword_smaBuySellSignalCombined($real, $smaShort, $smaMedium, $smaLong, $dataorg);
			echo json_encode($allData);
		} 
		else {
			$allData[0] = codesword_smaConsolidate($real, $smaShort, $smaMedium, $smaLong);
			$allData[1] = 0;
			echo json_encode($allData);
		}

		
	}
?>