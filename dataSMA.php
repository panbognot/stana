<?php 
	require_once('codesword_sma.php');

	// returns Simple Moving Average Data (sma, signal, histogram)
	function getSMA ($company, $from="1900-01-01 00:00:00", $to=null, $dataorg="json", $samplePeriod=15, $host, $db, $user, $pass) {
		// Create connection
		$con=mysqli_connect($host, $user, $pass, $db);
		
		// Check connection
		if (mysqli_connect_errno()) {
		  echo "Failed to connect to MySQL: " . mysqli_connect_error();
		  return;
		}

		if ($dataorg == "highchart") {
			//Added 8 hours to timestamp because of the Philippine Timezone WRT GMT (+8:00)
			$sql = "SELECT (UNIX_TIMESTAMP(DATE_ADD(timestamp, INTERVAL 8 HOUR)) * 1000) as timestamp, close
					FROM $company 
					WHERE timestamp >= DATE_ADD('".$from."', INTERVAL -$samplePeriod DAY) AND timestamp <= '".$to."' ORDER BY timestamp ASC";
		} else {
			$sql = "SELECT DATE_FORMAT(timestamp, '%Y-%m-%d') as timestamp, close 
					FROM $company 
					WHERE timestamp >= DATE_ADD('".$from."', INTERVAL -$samplePeriod DAY) AND timestamp <= '".$to."' ORDER BY timestamp ASC";
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

		if ($dataorg == "json") {
			//echo json_encode($dbreturn);
			$sma = codesword_sma($dbreturn, $samplePeriod);
			//echo json_encode($ema);
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

		echo json_encode($sma);
		mysqli_close($con);
	}
?>