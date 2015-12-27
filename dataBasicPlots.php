<?php 
	// search for the list of companies
	function searchForCompany ($keyword, $host, $db, $user, $pass) {
		// Create connection
		$con=mysqli_connect($host, $user, $pass, $db);
		
		// Check connection
		if (mysqli_connect_errno()) {
		  echo "Failed to connect to MySQL: " . mysqli_connect_error();
		  return;
		}

		$sql = "SELECT DISTINCT(SUBSTRING_INDEX(quote, '_', 1)) AS quote FROM stock_quotes WHERE quote LIKE '".$keyword."%'";

		$result = mysqli_query($con, $sql);

		$dbreturn = "";
		$ctr = 0;
		$temp;
		while($row = mysqli_fetch_array($result)) {
			$dbreturn[$ctr] = $row['quote'];

			$ctr = $ctr + 1;
		}
		echo json_encode( $dbreturn );

		mysqli_close($con);
	}

	// returns only the closing price
	function getClose ($company, $from="1900-01-01 00:00:00", $to=null, $dataorg="json", $host, $db, $user, $pass) {
		// Create connection
		$con=mysqli_connect($host, $user, $pass, $db);
		
		// Check connection
		if (mysqli_connect_errno()) {
		  echo "Failed to connect to MySQL: " . mysqli_connect_error();
		  return;
		}

		if ($dataorg == "highchart") {
			//Added 8 hours to timestamp because of the Philippine Timezone WRT GMT
			$sql = "SELECT (UNIX_TIMESTAMP(DATE_ADD(timestamp, INTERVAL 8 HOUR)) * 1000) as timestamp, close 
					FROM $company 
					WHERE timestamp >= '".$from."' AND timestamp <= '".$to."' ORDER BY timestamp ASC";
		} else {
			$sql = "SELECT DATE_FORMAT(timestamp, '%Y-%m-%d') as timestamp, close 
					FROM $company 
					WHERE timestamp >= '".$from."' AND timestamp <= '".$to."' ORDER BY timestamp ASC";
		}

		$result = mysqli_query($con, $sql);

		$dbreturn = "";
		$ctr = 0;
		$temp;
		while($row = mysqli_fetch_array($result)) {
			  if ($dataorg == "json") {
				  $dbreturn[$ctr]['timestamp'] = $row['timestamp'];
				  $dbreturn[$ctr]['close'] = $row['close'];
			  } 
			  elseif ($dataorg == "highchart") {
			  	if ($ctr == 0) {
			  		echo "[";
			  	}
			  	echo "[".$row['timestamp'].",".$row['close']."],";
			  	$temp[0] = $row['timestamp'];
			  	$temp[1] = $row['close'];
			  }
			  elseif ($dataorg == "array") {
			  	//TODO: create code for organizing an array data output
			  }
			  else {
				  $dbreturn[$ctr]['timestamp'] = $row['timestamp'];
				  $dbreturn[$ctr]['close'] = $row['close'];
			  }

			  $ctr = $ctr + 1;
		}

		if ($dataorg == "json") {
			echo json_encode( $dbreturn );
		} 
		elseif ($dataorg == "highchart") {
			echo "[".$temp[0].",".$temp[1]."]]";
		}
		elseif ($dataorg == "array") {
		//TODO: create code for organizing an array data output
		}
		else { //json
			echo json_encode( $dbreturn );
		}

		mysqli_close($con);
	}

	// returns only the volume traded
	function getVolume ($company, $from="1900-01-01 00:00:00", $to=null, $dataorg="json", $host, $db, $user, $pass) {
		// Create connection
		$con=mysqli_connect($host, $user, $pass, $db);
		
		// Check connection
		if (mysqli_connect_errno()) {
		  echo "Failed to connect to MySQL: " . mysqli_connect_error();
		  return;
		}

		if ($dataorg == "highchart") {
			//Added 8 hours to timestamp because of the Philippine Timezone WRT GMT
			$sql = "SELECT (UNIX_TIMESTAMP(DATE_ADD(timestamp, INTERVAL 8 HOUR)) * 1000) as timestamp, volume 
					FROM $company 
					WHERE timestamp >= '".$from."' AND timestamp <= '".$to."' ORDER BY timestamp ASC";
		} else {
			$sql = "SELECT DATE_FORMAT(timestamp, '%Y-%m-%d') as timestamp, volume 
					FROM $company 
					WHERE timestamp >= '".$from."' AND timestamp <= '".$to."' ORDER BY timestamp ASC";
		}

		$result = mysqli_query($con, $sql);

		$dbreturn = "";
		$ctr = 0;
		$temp;
		while($row = mysqli_fetch_array($result)) {
			  if ($dataorg == "json") {
				  $dbreturn[$ctr]['timestamp'] = $row['timestamp'];
				  $dbreturn[$ctr]['volume'] = $row['volume'];
			  } 
			  elseif ($dataorg == "highchart") {
			  	if ($ctr == 0) {
			  		echo "[";
			  	}
			  	echo "[".$row['timestamp'].",".$row['volume']."],";
			  	$temp[0] = $row['timestamp'];
			  	$temp[1] = $row['volume'];
			  }
			  elseif ($dataorg == "array") {
			  	//TODO: create code for organizing an array data output
			  }
			  else {
				  $dbreturn[$ctr]['timestamp'] = $row['timestamp'];
				  $dbreturn[$ctr]['volume'] = $row['volume'];
			  }

			  $ctr = $ctr + 1;
		}

		if ($dataorg == "json") {
			echo json_encode( $dbreturn );
		} 
		elseif ($dataorg == "highchart") {
			echo "[".$temp[0].",".$temp[1]."]]";
		}
		elseif ($dataorg == "array") {
		//TODO: create code for organizing an array data output
		}
		else { //json
			echo json_encode( $dbreturn );
		}

	   mysqli_close($con);
	}

	// returns OHLC for a candlestick chart
	function getOHLC ($company, $from="1900-01-01 00:00:00", $to=null, $dataorg="json", $host, $db, $user, $pass) {
		// Create connection
		$con=mysqli_connect($host, $user, $pass, $db);
		
		// Check connection
		if (mysqli_connect_errno()) {
		  echo "Failed to connect to MySQL: " . mysqli_connect_error();
		  return;
		}

		if ($dataorg == "highchart") {
			//Added 8 hours to timestamp because of the Philippine Timezone WRT GMT (+8:00)
			$sql = "SELECT (UNIX_TIMESTAMP(DATE_ADD(timestamp, INTERVAL 8 HOUR)) * 1000) as timestamp, 
					open, high, low, close, volume 
					FROM $company 
					WHERE timestamp >= '".$from."' AND timestamp <= '".$to."' ORDER BY timestamp ASC";
		} else {
			$sql = "SELECT DATE_FORMAT(timestamp, '%Y-%m-%d') as timestamp, open, high, low, close, volume 
					FROM $company 
					WHERE timestamp >= '".$from."' AND timestamp <= '".$to."' ORDER BY timestamp ASC";
		}

		$result = mysqli_query($con, $sql);

		$dbreturn = "";
		$ctr = 0;
		$temp;
		while($row = mysqli_fetch_array($result)) {
			if ($dataorg == "json") {
				$dbreturn[$ctr]['timestamp'] = $row['timestamp'];
				$dbreturn[$ctr]['open'] = $row['open'];
				$dbreturn[$ctr]['high'] = $row['high'];
				$dbreturn[$ctr]['low'] = $row['low'];
				$dbreturn[$ctr]['close'] = $row['close'];
				$dbreturn[$ctr]['volume'] = $row['volume'];
			} 
			elseif ($dataorg == "highchart") {
			  	if ($ctr == 0) {
			  		echo "[";
			  	}
			  	echo "[".$row['timestamp'].",".$row['open'].",".$row['high'].",".$row['low'].",".$row['close'].",".$row['volume']."],";
			  	$temp[0] = $row['timestamp'];
			  	$temp[1] = $row['open'];
			  	$temp[2] = $row['high'];
			  	$temp[3] = $row['low'];
			  	$temp[4] = $row['close'];
			  	$temp[5] = $row['volume'];
			}
			elseif ($dataorg == "array") {
				//TODO: create code for organizing an array data output
			}
			else {
				$dbreturn[$ctr]['timestamp'] = $row['timestamp'];
				$dbreturn[$ctr]['open'] = $row['open'];
				$dbreturn[$ctr]['high'] = $row['high'];
				$dbreturn[$ctr]['low'] = $row['low'];
				$dbreturn[$ctr]['close'] = $row['close'];
				$dbreturn[$ctr]['volume'] = $row['volume'];
			}

			$ctr = $ctr + 1;
		}

		if ($dataorg == "json") {
			echo json_encode( $dbreturn );
		} 
		elseif ($dataorg == "highchart") {
			echo "[".$temp[0].",".$temp[1].",".$temp[2].",".$temp[3].",".$temp[4].",".$temp[5]."]]";
		}
		elseif ($dataorg == "array") {
		//TODO: create code for organizing an array data output
		}
		else { //json
			echo json_encode( $dbreturn );
		}

	   mysqli_close($con);
	}
?>