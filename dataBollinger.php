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
					open, high, low, close
					FROM $company 
					WHERE timestamp >= DATE_ADD('".$from."', INTERVAL -$offsetPeriod DAY) 
					AND timestamp <= '".$to."' ORDER BY timestamp ASC";
		} else {
			$sql = "SELECT DATE_FORMAT(timestamp, '%Y-%m-%d') as timestamp, open, high, low, close
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
				$dbreturn[$ctr][1] = floatval($row['open']);
				$dbreturn[$ctr][2] = floatval($row['high']);
				$dbreturn[$ctr][3] = floatval($row['low']);
				$dbreturn[$ctr][4] = floatval($row['close']);
			} 
			elseif ($dataorg == "highchart") {
			  	$dbreturn[$ctr][0] = doubleval($row['timestamp']);
				$dbreturn[$ctr][1] = floatval($row['open']);
				$dbreturn[$ctr][2] = floatval($row['high']);
				$dbreturn[$ctr][3] = floatval($row['low']);
				$dbreturn[$ctr][4] = floatval($row['close']);
			}
			elseif ($dataorg == "array") {
				//TODO: create code for organizing an array data output
			}
			else {
				$dbreturn[$ctr][0] = $row['timestamp'];
				$dbreturn[$ctr][1] = floatval($row['open']);
				$dbreturn[$ctr][2] = floatval($row['high']);
				$dbreturn[$ctr][3] = floatval($row['low']);
				$dbreturn[$ctr][4] = floatval($row['close']);
			}

			$ctr = $ctr + 1;
		}

		if ($dataorg == "json") {
			$bbohlc = codesword_bollinger_bands3($dbreturn, $studyPeriod);

			// Returns - data with structure [timestamp,open,high,low,close,
			//									sma,upper band sd 1, lower band sd 1,
			//									upper band sd 2, lower band sd 2]

			$ctrBB = 0;
			foreach ($bbohlc as $bb) {
				$returnBB[$ctrBB]['timestamp'] = $bb[0];
				$returnBB[$ctrBB]['open'] = $bb[1];
				$returnBB[$ctrBB]['high'] = $bb[2];
				$returnBB[$ctrBB]['low'] = $bb[3];
				$returnBB[$ctrBB]['close'] = $bb[4];

				$returnBB[$ctrBB]['sma'] = $bb[5];

				$returnBB[$ctrBB]['ubb1'] = $bb[6];
				$returnBB[$ctrBB]['lbb1'] = $bb[7];

				$returnBB[$ctrBB]['ubb2'] = $bb[8];
				$returnBB[$ctrBB]['lbb2'] = $bb[9];
			}
		} 
		elseif ($dataorg == "highchart") {
			$returnBB = codesword_bollinger_bands3($dbreturn, $studyPeriod);
		}
		elseif ($dataorg == "array") {
		//TODO: create code for organizing an array data output
		}
		else { //json
			$returnBB = codesword_bollinger_bands3($dbreturn, $studyPeriod);
		}

		$allData = [];

		if ($enSignals) {
			$allData[0] = $returnBB;
			$allData[1] = codesword_bbTrendDetector($returnBB);
		} 
		else {
			$allData = $returnBB;
		}

		echo json_encode($allData);
		mysqli_close($con);
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