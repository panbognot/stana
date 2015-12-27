<?php 
	require_once('codesword_stochastic_oscillator.php');

	// returns Stochastic Oscillator Data (timestamp, %K, %D)
	function getStochasticOscillator ($company, $from="1900-01-01 00:00:00", $to=null, $dataorg="json", $host, $db, $user, $pass) {
		// Create connection
		$con=mysqli_connect($host, $user, $pass, $db);
		
		// Check connection
		if (mysqli_connect_errno()) {
		  echo "Failed to connect to MySQL: " . mysqli_connect_error();
		  return;
		}

		$studyPeriod = 14;
		$smoothingPeriod = 3;
		$offsetPeriod = $studyPeriod + $smoothingPeriod;
		if ($dataorg == "highchart") {
			//Added 8 hours to timestamp because of the Philippine Timezone WRT GMT (+8:00)
			$sql = "SELECT (UNIX_TIMESTAMP(DATE_ADD(timestamp, INTERVAL 8 HOUR)) * 1000) as timestamp, 
					high, low, close
					FROM $company 
					WHERE timestamp >= DATE_ADD('".$from."', INTERVAL -$offsetPeriod DAY) 
					AND timestamp <= '".$to."' ORDER BY timestamp ASC";
		} else {
			$sql = "SELECT DATE_FORMAT(timestamp, '%Y-%m-%d') as timestamp, high, low, close 
					FROM $company 
					WHERE timestamp >= DATE_ADD('".$from."', INTERVAL -$offsetPeriod DAY) 
					AND timestamp <= '".$to."' ORDER BY timestamp ASC";
		}

		$result = mysqli_query($con, $sql);

		$dbreturn = "";
		$ctr = 0;
		$temp;
		$returnStochastic;
		while($row = mysqli_fetch_array($result)) {
			if ($dataorg == "json") {
			  	$dbreturn[$ctr][0] = $row['timestamp'];
				$dbreturn[$ctr][1] = floatval($row['high']);
				$dbreturn[$ctr][2] = floatval($row['low']);
				$dbreturn[$ctr][3] = floatval($row['close']);
			} 
			elseif ($dataorg == "highchart") {
			  	$dbreturn[$ctr][0] = doubleval($row['timestamp']);
				$dbreturn[$ctr][1] = floatval($row['high']);
				$dbreturn[$ctr][2] = floatval($row['low']);
				$dbreturn[$ctr][3] = floatval($row['close']);
			}
			elseif ($dataorg == "array") {
				//TODO: create code for organizing an array data output
			}
			else {
				$dbreturn[$ctr][0] = $row['timestamp'];
				$dbreturn[$ctr][1] = floatval($row['high']);
				$dbreturn[$ctr][2] = floatval($row['low']);
				$dbreturn[$ctr][3] = floatval($row['close']);
			}

			$ctr = $ctr + 1;
		}

		if ($dataorg == "json") {
			$returnStochastic = codesword_stochastic($dbreturn, $studyPeriod, $smoothingPeriod);
		} 
		elseif ($dataorg == "highchart") {
			$returnStochastic = codesword_stochastic($dbreturn, $studyPeriod, $smoothingPeriod);
		}
		elseif ($dataorg == "array") {
		//TODO: create code for organizing an array data output
		}
		else { //json
			$returnStochastic = codesword_stochastic($dbreturn, $studyPeriod, $smoothingPeriod);
		}

		echo json_encode($returnStochastic);
		mysqli_close($con);
	}
?>