<?php 
	require_once('codesword_bollinger.php');

	// returns Bollinger Bands Data (timestamp, upper bollinger band, lower bollinger band)
	function getBollingerBands ($company, $from="1900-01-01 00:00:00", $to=null, $dataorg="json", 
								$host, $db, $user, $pass) {
		// Create connection
		$con=mysqli_connect($host, $user, $pass, $db);
		
		// Check connection
		if (mysqli_connect_errno()) {
		  echo "Failed to connect to MySQL: " . mysqli_connect_error();
		  return;
		}

		$studyPeriod = 20;
		$offsetPeriod = $studyPeriod * 1;
		if ($dataorg == "highchart") {
			//Added 8 hours to timestamp because of the Philippine Timezone WRT GMT (+8:00)
			$sql = "SELECT (UNIX_TIMESTAMP(DATE_ADD(timestamp, INTERVAL 8 HOUR)) * 1000) as timestamp, 
					close
					FROM $company 
					WHERE timestamp >= DATE_ADD('".$from."', INTERVAL -$offsetPeriod DAY) 
					AND timestamp <= '".$to."' ORDER BY timestamp ASC";
		} else {
			$sql = "SELECT DATE_FORMAT(timestamp, '%Y-%m-%d') as timestamp, close 
					FROM $company 
					WHERE timestamp >= DATE_ADD('".$from."', INTERVAL -$offsetPeriod DAY) 
					AND timestamp <= '".$to."' ORDER BY timestamp ASC";
		}

		$result = mysqli_query($con, $sql);

		$dbreturn = "";
		$ctr = 0;
		$temp;
		$returnBB;
		while($row = mysqli_fetch_array($result)) {
			if ($dataorg == "json") {
			  	$dbreturn[$ctr][0] = $row['timestamp'];
				$dbreturn[$ctr][1] = floatval($row['close']);
			} 
			elseif ($dataorg == "highchart") {
			  	$dbreturn[$ctr][0] = doubleval($row['timestamp']);
				$dbreturn[$ctr][1] = floatval($row['close']);
			}
			elseif ($dataorg == "array") {
				//TODO: create code for organizing an array data output
			}
			else {
				$dbreturn[$ctr][0] = $row['timestamp'];
				$dbreturn[$ctr][1] = floatval($row['close']);
			}

			$ctr = $ctr + 1;
		}

		if ($dataorg == "json") {
			$returnBB = codesword_bollinger_bands($dbreturn, $studyPeriod);
		} 
		elseif ($dataorg == "highchart") {
			$returnBB = codesword_bollinger_bands($dbreturn, $studyPeriod);
		}
		elseif ($dataorg == "array") {
		//TODO: create code for organizing an array data output
		}
		else { //json
			$returnBB = codesword_bollinger_bands($dbreturn, $studyPeriod);
		}

		echo json_encode($returnBB);
		mysqli_close($con);
	}

	// returns Bollinger Bands Data (timestamp, close, ubb sd1, lbb sd1, ubb sd2, lbb sd2)
	function getBollingerBands2 ($company, $from="1900-01-01 00:00:00", $to=null, $dataorg="json", 
								$host, $db, $user, $pass) {
		// Create connection
		$con=mysqli_connect($host, $user, $pass, $db);
		
		// Check connection
		if (mysqli_connect_errno()) {
		  echo "Failed to connect to MySQL: " . mysqli_connect_error();
		  return;
		}

		$studyPeriod = 20;
		$offsetPeriod = $studyPeriod * 1;
		if ($dataorg == "highchart") {
			//Added 8 hours to timestamp because of the Philippine Timezone WRT GMT (+8:00)
			$sql = "SELECT (UNIX_TIMESTAMP(DATE_ADD(timestamp, INTERVAL 8 HOUR)) * 1000) as timestamp, 
					close
					FROM $company 
					WHERE timestamp >= DATE_ADD('".$from."', INTERVAL -$offsetPeriod DAY) 
					AND timestamp <= '".$to."' ORDER BY timestamp ASC";
		} else {
			$sql = "SELECT DATE_FORMAT(timestamp, '%Y-%m-%d') as timestamp, close 
					FROM $company 
					WHERE timestamp >= DATE_ADD('".$from."', INTERVAL -$offsetPeriod DAY) 
					AND timestamp <= '".$to."' ORDER BY timestamp ASC";
		}

		$result = mysqli_query($con, $sql);

		$dbreturn = "";
		$ctr = 0;
		$temp;
		$returnBB;
		while($row = mysqli_fetch_array($result)) {
			if ($dataorg == "json") {
			  	$dbreturn[$ctr][0] = $row['timestamp'];
				$dbreturn[$ctr][1] = floatval($row['close']);
			} 
			elseif ($dataorg == "highchart") {
			  	$dbreturn[$ctr][0] = doubleval($row['timestamp']);
				$dbreturn[$ctr][1] = floatval($row['close']);
			}
			elseif ($dataorg == "array") {
				//TODO: create code for organizing an array data output
			}
			else {
				$dbreturn[$ctr][0] = $row['timestamp'];
				$dbreturn[$ctr][1] = floatval($row['close']);
			}

			$ctr = $ctr + 1;
		}

		if ($dataorg == "json") {
			$returnBB = codesword_bollinger_bands2($dbreturn, $studyPeriod);
		} 
		elseif ($dataorg == "highchart") {
			$returnBB = codesword_bollinger_bands2($dbreturn, $studyPeriod);
		}
		elseif ($dataorg == "array") {
		//TODO: create code for organizing an array data output
		}
		else { //json
			$returnBB = codesword_bollinger_bands2($dbreturn, $studyPeriod);
		}

		echo json_encode($returnBB);
		mysqli_close($con);
	}

	// returns Bollinger Bands Data (timestamp, open, high, low, close, 
	//								ubb sd1, lbb sd1, ubb sd2, lbb sd2)
	function getBollingerBands3 ($company, $from="1900-01-01 00:00:00", $to=null, 
								$dataorg="json", $enSignals=false,
								$host, $db, $user, $pass) {
		$studyPeriod = 20;
		$offsetPeriod = $studyPeriod * 1;

		//from date has to be adjusted for the bollinger bands
		$date = date_create($from);
		date_add($date,date_interval_create_from_date_string("-$offsetPeriod days"));
		$fromAdjusted =  date_format($date,"Y-m-d");
		//echo "fromAdjusted: $fromAdjusted<Br>";

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

		//Input for bollinger bands should be [timestamp,open,high,low,close]
		$ctr = 0;
		foreach ((array)$dataOhlc as $ohlc) {
			$dbreturn[$ctr][0] = $ohlc[0];	//timestamp
			$dbreturn[$ctr][1] = $ohlc[1];	//high
			$dbreturn[$ctr][2] = $ohlc[2];	//low
			$dbreturn[$ctr][3] = $ohlc[3];	//low
			$dbreturn[$ctr++][4] = $ohlc[4];	//close
		}

		if ($dataorg == "json") {
			$bbohlc = codesword_bollinger_bands3($dbreturn, $studyPeriod);

			if (count($bbohlc) <= 1) {
				//No Data
				return 0;
			}

			$returnBB = $bbohlc;
		} 
		elseif ($dataorg == "highchart") {
			$returnBB = codesword_bollinger_bands3($dbreturn, $studyPeriod);

			if ($returnBB == 0) {
				//No Data
				return 0;
			}
		}
		elseif ($dataorg == "array") {
		//TODO: create code for organizing an array data output
		}
		else { //json
			$returnBB = codesword_bollinger_bands3($dbreturn, $studyPeriod);

			if ($returnBB == 0) {
				//No Data
				return 0;
			}
		}

		$allData = [];
		if (strcasecmp($enSignals, "latest") == 0) {
			//return only the latest signal
			// [timestamp,trade signal,... other info...]
			$lastSignal = codesword_bbTrendDetectorLatests($returnBB);
			return $lastSignal;
		}
		elseif ($enSignals) {
			$allData[0] = $returnBB;
			$allData[1] = codesword_bbTrendDetector($returnBB);
			echo json_encode($allData);
		} 
		else {
			$allData = $returnBB;
			echo json_encode($allData);
		}
	}

	// returns Bollinger Bands Data (timestamp, BBWidth)
	function getBBW ($company, $from="1900-01-01 00:00:00", $to=null, $dataorg="json", $host, $db, $user, $pass) {
		// Create connection
		$con=mysqli_connect($host, $user, $pass, $db);
		
		// Check connection
		if (mysqli_connect_errno()) {
		  echo "Failed to connect to MySQL: " . mysqli_connect_error();
		  return;
		}

		$studyPeriod = 20;
		$offsetPeriod = $studyPeriod * 1;
		if ($dataorg == "highchart") {
			//Added 8 hours to timestamp because of the Philippine Timezone WRT GMT (+8:00)
			$sql = "SELECT (UNIX_TIMESTAMP(DATE_ADD(timestamp, INTERVAL 8 HOUR)) * 1000) as timestamp, 
					close
					FROM $company 
					WHERE timestamp >= DATE_ADD('".$from."', INTERVAL -$offsetPeriod DAY) 
					AND timestamp <= '".$to."' ORDER BY timestamp ASC";
		} else {
			$sql = "SELECT DATE_FORMAT(timestamp, '%Y-%m-%d') as timestamp, close 
					FROM $company 
					WHERE timestamp >= DATE_ADD('".$from."', INTERVAL -$offsetPeriod DAY) 
					AND timestamp <= '".$to."' ORDER BY timestamp ASC";
		}

		$result = mysqli_query($con, $sql);

		$dbreturn = "";
		$ctr = 0;
		$temp;
		$returnBB;
		while($row = mysqli_fetch_array($result)) {
			if ($dataorg == "json") {
			  	$dbreturn[$ctr][0] = $row['timestamp'];
				$dbreturn[$ctr][1] = floatval($row['close']);
			} 
			elseif ($dataorg == "highchart") {
			  	$dbreturn[$ctr][0] = doubleval($row['timestamp']);
				$dbreturn[$ctr][1] = floatval($row['close']);
			}
			elseif ($dataorg == "array") {
				//TODO: create code for organizing an array data output
			}
			else {
				$dbreturn[$ctr][0] = $row['timestamp'];
				$dbreturn[$ctr][1] = floatval($row['close']);
			}

			$ctr = $ctr + 1;
		}

		if ($dataorg == "json") {
			$returnBBW = codesword_bbw($dbreturn, $studyPeriod);
		} 
		elseif ($dataorg == "highchart") {
			$returnBBW = codesword_bbw($dbreturn, $studyPeriod);
		}
		elseif ($dataorg == "array") {
		//TODO: create code for organizing an array data output
		}
		else { //json
			$returnBBW = codesword_bbw($dbreturn, $studyPeriod);
		}

		echo json_encode($returnBBW);
		mysqli_close($con);
	}	
?>