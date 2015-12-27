<?php 
	require_once('codesword_bollinger.php');

	// returns Bollinger Bands Data (timestamp, upper bollinger band, lower bollinger band)
	function getBollingerBands ($company, $from="1900-01-01 00:00:00", $to=null, $dataorg="json", $host, $db, $user, $pass) {
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
?>