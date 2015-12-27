<?php 
	require_once('codesword_rsi.php');

	// returns Relative Strength Index Data (timestamp, rsi)
	//	- RSI
	function getRSI ($company, $from="1900-01-01 00:00:00", $to=null, $dataorg="json", $host, $db, $user, $pass) {
		// Create connection
		$con=mysqli_connect($host, $user, $pass, $db);
		
		// Check connection
		if (mysqli_connect_errno()) {
		  echo "Failed to connect to MySQL: " . mysqli_connect_error();
		  return;
		}

		//Bloomberg seems to be using 27 instead... I guess they use this to prevent fear from spreading
		$studyPeriod = 14;
		if ($dataorg == "highchart") {
			//Added 8 hours to timestamp because of the Philippine Timezone WRT GMT (+8:00)
			$sql = "SELECT (UNIX_TIMESTAMP(DATE_ADD(timestamp, INTERVAL 8 HOUR)) * 1000) as timestamp, 
					close
					FROM $company 
					WHERE timestamp >= DATE_ADD('".$from."', INTERVAL -$studyPeriod DAY) 
					AND timestamp <= '".$to."' ORDER BY timestamp ASC";
		} else {
			$sql = "SELECT DATE_FORMAT(timestamp, '%Y-%m-%d') as timestamp, close 
					FROM $company 
					WHERE timestamp >= DATE_ADD('".$from."', INTERVAL -$studyPeriod DAY) 
					AND timestamp <= '".$to."' ORDER BY timestamp ASC";
		}

		$result = mysqli_query($con, $sql);

		$dbreturn = "";
		$ctr = 0;
		$temp;
		$arrayTS;
		$arrayClose;
		$returnRsi;
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
			$returnRsi = codesword_rsi($dbreturn, $studyPeriod);
			//echo json_encode($ema);
		} 
		elseif ($dataorg == "highchart") {
			$returnRsi = codesword_rsi($dbreturn, $studyPeriod);
		}
		elseif ($dataorg == "array") {
		//TODO: create code for organizing an array data output
		}
		else { //json
			$returnRsi = codesword_rsi($dbreturn, $studyPeriod);
		}

		echo json_encode($returnRsi);
		mysqli_close($con);
	}
?>