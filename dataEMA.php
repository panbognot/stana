<?php 
	require_once('codesword_ema.php');

	// get real data for the SMA
	function getEMA_sub_real ($company, $from="1900-01-01 00:00:00", $to=null, 
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
		if ($dataorg == "highchart") {
			//Added 8 hours to timestamp because of the Philippine Timezone WRT GMT (+8:00)
			$sql = "SELECT (UNIX_TIMESTAMP(DATE_ADD(timestamp, INTERVAL 8 HOUR)) * 1000) as timestamp, close
					FROM $company 
					WHERE timestamp >= DATE_ADD('".$from."', INTERVAL -$intervalPeriod DAY) AND timestamp <= '".$to."' ORDER BY timestamp ASC";
		} else {
			$sql = "SELECT DATE_FORMAT(timestamp, '%Y-%m-%d') as timestamp, close 
					FROM $company 
					WHERE timestamp >= DATE_ADD('".$from."', INTERVAL -$intervalPeriod DAY) AND timestamp <= '".$to."' ORDER BY timestamp ASC";
		}

		$result = mysqli_query($con, $sql);

		$dbreturn = "";
		$ctr = 0;
		$temp;
		$arrayTS;
		$arrayClose;
		while($row = mysqli_fetch_array($result)) {
			if ($dataorg == "json") {
			  	$dbreturn[$ctr][0] = $arrayTS[$ctr] = $row['timestamp'];
				$dbreturn[$ctr][1] = $arrayClose[$ctr] = floatval($row['close']);
			} 
			elseif ($dataorg == "highchart") {
			  	$dbreturn[$ctr][0] = $arrayTS[$ctr] = doubleval($row['timestamp']);
				$dbreturn[$ctr][1] = $arrayClose[$ctr] = floatval($row['close']);
			}
			elseif ($dataorg == "array") {
				//TODO: create code for organizing an array data output
			}
			else {
				$dbreturn[$ctr][0] = $arrayTS[$ctr] = $row['timestamp'];
				$dbreturn[$ctr][1] = $arrayClose[$ctr] = floatval($row['close']);
			}

			$ctr = $ctr + 1;
		}

		return $dbreturn;
	}

	// returns Exponential Moving Average Data {(timestamp, ema), (timetsamp, signal)}
	function getEMA ($company, $from="1900-01-01 00:00:00", $to=null, 
					$dataorg="json", $samplePeriod=15, $enSignals=false,
					$enJsonEncode="false",
					$host, $db, $user, $pass) {
		
		$dbreturn = getEMA_sub_real ($company, $from, $to, 
					$dataorg, $samplePeriod, $enSignals,
					$host, $db, $user, $pass);

		if ($dataorg == "json") {
			$ema = codesword_ema($dbreturn, $samplePeriod);
		} 
		elseif ($dataorg == "highchart") {
			$ema = codesword_ema($dbreturn, $samplePeriod);
		}
		elseif ($dataorg == "array") {
		//TODO: create code for organizing an array data output
		}
		else { //json
			$ema = codesword_ema($dbreturn, $samplePeriod);
		}

		if ($enSignals) {
			$buysellSignals = codesword_emaBuySellSignal($dbreturn, $ema);
		} else {
			$buysellSignals = 0;
		}
		
		if ($enJsonEncode) {
			$allData = [];
			$allData[0] = $ema;
			$allData[1] = $buysellSignals;
			echo json_encode($allData);
		}
		else {
			return $ema;
		}
	}

	// returns Exponential Moving Average Data {(timestamp, wema), (timetsamp, signal)}
	function getWEMA ($company, $from="1900-01-01 00:00:00", $to=null, 
					$dataorg="json", $samplePeriod=15, $enSignals=false,
					$enJsonEncode="false",
					$host, $db, $user, $pass) {
		
		$dbreturn = getEMA_sub_real ($company, $from, $to, 
					$dataorg, $samplePeriod, $enSignals,
					$host, $db, $user, $pass);

		if ($dataorg == "json") {
			$wema = codesword_wilder_ema($dbreturn, $samplePeriod);
		} 
		elseif ($dataorg == "highchart") {
			$wema = codesword_wilder_ema($dbreturn, $samplePeriod);
		}
		elseif ($dataorg == "array") {
		//TODO: create code for organizing an array data output
		}
		else { //json
			$wema = codesword_wilder_ema($dbreturn, $samplePeriod);
		}

		if ($enSignals) {
			$buysellSignals = codesword_emaBuySellSignal($dbreturn, $wema);
		} else {
			$buysellSignals = 0;
		}
		
		if ($enJsonEncode) {
			$allData = [];
			$allData[0] = $wema;
			$allData[1] = $buysellSignals;
			echo json_encode($allData);
		}
		else {
			return $wema;
		}
	}

	// returns Simple Moving Average Combined Data
	// {(timestamp, smaShort, smaMedium, smaLong), (timestamp, buysell signal)}
	function getEMACombined ($company, $from="1900-01-01 00:00:00", $to=null, 
					$dataorg="json", $periodShort=20, $periodMedium=50, 
					$periodLong=120, $enSignals=false,
					$host, $db, $user, $pass) {

		$smaShort = getEMA($company, $from, $to, $dataorg, $periodShort, 
						$enSignals, false,
						$host, $db, $user, $pass);
		$smaMedium = getEMA($company, $from, $to, $dataorg, $periodMedium, 
						$enSignals, false,
						$host, $db, $user, $pass);
		$smaLong = getEMA($company, $from, $to, $dataorg, $periodLong, 
						$enSignals, false,
						$host, $db, $user, $pass);
		$real = getEMA_sub_real ($company, $from, $to, 
					$dataorg, $periodShort, $enSignals,
					$host, $db, $user, $pass);

		//$smaCombined = codesword_emaBuySellSignalCombined($real, $smaShort, $smaMedium, $smaLong);

		//echo json_encode($smaCombined);

		$allData = [];

		if ($enSignals) {
			$allData = codesword_emaBuySellSignalCombined($real, $smaShort, $smaMedium, $smaLong, $dataorg);
		} 
		else {
			$allData[0] = codesword_emaConsolidate($real, $smaShort, $smaMedium, $smaLong);
			$allData[1] = 0;
		}

		echo json_encode($allData);
	}
?>