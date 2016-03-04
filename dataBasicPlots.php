<?php 
	require_once("codesword_ha.php");	//for the heikin-ashi candlestick

	// generate the names of all the companies
	function readStockQuotes ($host, $db, $user, $pass) {
		// Create connection
		$con=mysqli_connect($host, $user, $pass, $db);
		
		// Check connection
		if (mysqli_connect_errno()) {
		  echo "Failed to connect to MySQL: " . mysqli_connect_error();
		  return;
		}

		$sql = "SELECT DISTINCT quote FROM stock_quotes ORDER BY quote ASC";
		$result = mysqli_query($con, $sql);

		$dbreturn = "";
		$ctr = 0;
		while($row = mysqli_fetch_array($result)) {
			$dbreturn[$ctr++] = $row['quote'];
		}
		//echo json_encode( $dbreturn );

		mysqli_close($con);

		return $dbreturn;
	}

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

	// returns the current day prices
	function getCurrentDayPrices ($company, $dataorg="json", $host, $db, $user, $pass) {
		// Create connection
		$con=mysqli_connect($host, $user, $pass, $db);
		
		// Check connection
		if (mysqli_connect_errno()) {
		  echo "Failed to connect to MySQL: " . mysqli_connect_error();
		  return;
		}

		// reformat the company string
		$company = str_replace("_", "", $company);

		if ($dataorg == "highchart") {
			//Added 8 hours to timestamp because of the Philippine Timezone WRT GMT
/*			$sql = "SELECT (UNIX_TIMESTAMP(DATE_ADD(timestamp, INTERVAL 8 HOUR)) * 1000) as timestamp, close 
					FROM $company 
					WHERE timestamp >= '".$from."' AND timestamp <= '".$to."' ORDER BY timestamp ASC";*/

			$sql = "SELECT (UNIX_TIMESTAMP(DATE_ADD(timestamp, INTERVAL 8 HOUR)) * 1000) as timestamp, current 
					FROM current_prices 
					WHERE company = '".$company."' AND timestamp > curdate() ORDER BY timestamp ASC";
		} else {
/*			$sql = "SELECT DATE_FORMAT(timestamp, '%Y-%m-%d') as timestamp, close 
					FROM $company 
					WHERE timestamp >= '".$from."' AND timestamp <= '".$to."' ORDER BY timestamp ASC";*/

			$sql = "SELECT timestamp, current 
					FROM current_prices 
					WHERE company = '".$company."' AND timestamp > curdate() ORDER BY timestamp ASC";
		}

		$result = mysqli_query($con, $sql);

		$dbreturn = "";
		$ctr = 0;
		$temp;
		while($row = mysqli_fetch_array($result)) {
			  if ($dataorg == "json") {
				  $dbreturn[$ctr]['timestamp'] = $row['timestamp'];
				  $dbreturn[$ctr]['current'] = $row['current'];
			  } 
			  elseif ($dataorg == "highchart") {
			  	if ($ctr == 0) {
			  		echo "[";
			  	}
			  	echo "[".$row['timestamp'].",".$row['current']."],";
			  	$temp[0] = $row['timestamp'];
			  	$temp[1] = $row['current'];
			  }
			  elseif ($dataorg == "array") {
			  	//TODO: create code for organizing an array data output
			  }
			  else {
				  $dbreturn[$ctr]['timestamp'] = $row['timestamp'];
				  $dbreturn[$ctr]['current'] = $row['current'];
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

		if ( ($dataorg == "highchart") || ($dataorg == "array2") ) {
			//Added 8 hours to timestamp because of the Philippine Timezone WRT GMT (+8:00)
			$sql = "SELECT (UNIX_TIMESTAMP(DATE_ADD(timestamp, INTERVAL 8 HOUR)) * 1000) as timestamp, 
					open, high, low, close, volume 
					FROM $company 
					WHERE timestamp >= '".$from."' AND timestamp <= '".$to."' ORDER BY timestamp ASC";
		}
		else {
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
				$dbreturn[$ctr][0] = $row['timestamp'];
				$dbreturn[$ctr][1] = floatval($row['open']);
				$dbreturn[$ctr][2] = floatval($row['high']);
				$dbreturn[$ctr][3] = floatval($row['low']);
				$dbreturn[$ctr][4] = floatval($row['close']);
				$dbreturn[$ctr][5] = intval($row['volume']);
			}
			elseif ($dataorg == "array2") {
				$dbreturn[$ctr][0] = doubleval($row['timestamp']);
				$dbreturn[$ctr][1] = floatval($row['open']);
				$dbreturn[$ctr][2] = floatval($row['high']);
				$dbreturn[$ctr][3] = floatval($row['low']);
				$dbreturn[$ctr][4] = floatval($row['close']);
				$dbreturn[$ctr][5] = intval($row['volume']);
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
		elseif (($dataorg == "array") || ($dataorg == "array2")) {
			mysqli_close($con);
			return $dbreturn;
		}
		else { //json
			echo json_encode( $dbreturn );
		}

	    mysqli_close($con);
	}

	// returns OHLC for a heikin-ashi candlestick chart
	function getOHLCHA ($company, $from="1900-01-01 00:00:00", $to=null, $dataorg="json", $host, $db, $user, $pass) {
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
			  	$dbreturn[$ctr][0] = $row['timestamp'];
			  	$dbreturn[$ctr][1] = floatval($row['open']);
			  	$dbreturn[$ctr][2] = floatval($row['high']);
			  	$dbreturn[$ctr][3] = floatval($row['low']);
			  	$dbreturn[$ctr][4] = floatval($row['close']);
			  	$dbreturn[$ctr][5] = floatval($row['volume']);
			} 
			elseif ($dataorg == "highchart") {
			  	if ($ctr == 0) {
			  		//echo "[";
			  	}
			  	//echo "[".$row['timestamp'].",".$row['open'].",".$row['high'].",".$row['low'].",".$row['close'].",".$row['volume']."],";
			  	$dbreturn[$ctr][0] = doubleval($row['timestamp']);
			  	$dbreturn[$ctr][1] = floatval($row['open']);
			  	$dbreturn[$ctr][2] = floatval($row['high']);
			  	$dbreturn[$ctr][3] = floatval($row['low']);
			  	$dbreturn[$ctr][4] = floatval($row['close']);
			  	$dbreturn[$ctr][5] = floatval($row['volume']);
			}
			elseif ($dataorg == "array") {
				//TODO: create code for organizing an array data output
			}
			else {
				$dbreturn[$ctr]['timestamp'] = $row['timestamp'];
				$dbreturn[$ctr]['open'] = floatval($row['open']);
				$dbreturn[$ctr]['high'] = floatval($row['high']);
				$dbreturn[$ctr]['low'] = floatval($row['low']);
				$dbreturn[$ctr]['close'] = floatval($row['close']);
				$dbreturn[$ctr]['volume'] = floatval($row['volume']);
			}

			$ctr = $ctr + 1;
		}

		//echo "ohlc regular: ".json_encode($dbreturn)."<Br><Br>";

		//Apply the heikin-ashi conversion
		$ohlcha = codesword_ha($dbreturn);

		if ($dataorg == "json") {
			echo json_encode( $ohlcha );
		} 
		elseif ($dataorg == "highchart") {
			//echo "[".$temp[0].",".$temp[1].",".$temp[2].",".$temp[3].",".$temp[4].",".$temp[5]."]]";
			echo json_encode( $ohlcha );
		}
		elseif ($dataorg == "array") {
		//TODO: create code for organizing an array data output
		}
		else { //json
			echo json_encode( $ohlcha );
		}

	    mysqli_close($con);
	}	
?>