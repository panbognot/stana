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
			$sql = "SELECT (UNIX_TIMESTAMP(DATE_ADD(timestamp, INTERVAL 8 HOUR)) * 1000) as timestamp, current 
					FROM current_prices 
					WHERE company = '$company' AND timestamp > curdate() ORDER BY timestamp ASC";
		} else {
			$sql = "SELECT timestamp, current 
					FROM current_prices 
					WHERE company = '$company' AND timestamp > curdate() ORDER BY timestamp ASC";
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

	// returns all current day prices
	// $lastupdate should be of format Y-m-d+H:M:S
	function getAllCurrentDayPrices ($lastupdate, $host, $db, $user, $pass) {
		// Create connection
		$con=mysqli_connect($host, $user, $pass, $db);
		
		// Check connection
		if (mysqli_connect_errno()) {
		  echo "Failed to connect to MySQL: " . mysqli_connect_error();
		  return;
		}

		$sql = "SELECT * FROM current_prices WHERE timestamp > '$lastupdate' ORDER BY entryid ASC";
		//}

		$result = mysqli_query($con, $sql);

		$dbreturn = "";
		$ctr = 0;
		$temp;
		while($row = mysqli_fetch_array($result)) {
			//$dbreturn[$ctr]['entryid'] = $row['entryid'];
			$dbreturn[$ctr]['timestamp'] = $row['timestamp'];
			$dbreturn[$ctr]['company'] = $row['company'];
			$dbreturn[$ctr++]['current'] = $row['current'];
		}

		echo json_encode($dbreturn);

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

	// returns the OHL-Current values for the selected company
	function getOHLCurrent ($company, $dataorg="json", $host, $db, $user, $pass) {
		// Create connection
		$con=mysqli_connect($host, $user, $pass, $db);
		
		// Check connection
		if (mysqli_connect_errno()) {
		  echo "Failed to connect to MySQL: " . mysqli_connect_error();
		  return;
		}

		// reformat the company string
		$company = str_replace("_", "", $company);

		if ( ($dataorg == "highchart") || ($dataorg == "array2") ) {
			//Added 8 hours to timestamp because of the Philippine Timezone WRT GMT
			$sql = "SELECT 
						(UNIX_TIMESTAMP(DATE_ADD(timestamp, INTERVAL 8 HOUR)) * 1000) as timestamp, 
						open, high, low, close 
					FROM current_ohlc 
					WHERE company = '$company' AND timestamp > curdate() ";
		} else {
			$sql = "SELECT timestamp, open, high, low, close 
					FROM current_ohlc 
					WHERE company = '$company' AND timestamp > curdate()";
		}

		$result = mysqli_query($con, $sql);

		$dbreturn = "";
		$ctr = 0;
		$temp;
		while($row = mysqli_fetch_array($result)) {
			if ($dataorg == "json") {
				$dbreturn[$ctr]['timestamp'] = $row['timestamp'];
				$dbreturn[$ctr]['open'] = floatval($row['open']);
				$dbreturn[$ctr]['high'] = floatval($row['high']);
				$dbreturn[$ctr]['low'] = floatval($row['low']);
				$dbreturn[$ctr]['close'] = floatval($row['close']);
				$dbreturn[$ctr]['volume'] = 0;
			} 
			elseif ($dataorg == "highchart") {
				$dbreturn[$ctr][0] = doubleval($row['timestamp']);
				$dbreturn[$ctr][1] = floatval($row['open']);
				$dbreturn[$ctr][2] = floatval($row['high']);
				$dbreturn[$ctr][3] = floatval($row['low']);
				$dbreturn[$ctr][4] = floatval($row['close']);
				$dbreturn[$ctr][5] = 0;
			}
			elseif ($dataorg == "array") {
				$dbreturn[$ctr][0] = $row['timestamp'];
				$dbreturn[$ctr][1] = floatval($row['open']);
				$dbreturn[$ctr][2] = floatval($row['high']);
				$dbreturn[$ctr][3] = floatval($row['low']);
				$dbreturn[$ctr][4] = floatval($row['close']);
				$dbreturn[$ctr][5] = 0;
			}
			elseif ($dataorg == "array2") {
				$dbreturn[$ctr][0] = doubleval($row['timestamp']);
				$dbreturn[$ctr][1] = floatval($row['open']);
				$dbreturn[$ctr][2] = floatval($row['high']);
				$dbreturn[$ctr][3] = floatval($row['low']);
				$dbreturn[$ctr][4] = floatval($row['close']);
				$dbreturn[$ctr][5] = 0;
			}
			else {
				$dbreturn[$ctr]['timestamp'] = $row['timestamp'];
				$dbreturn[$ctr]['open'] = floatval($row['open']);
				$dbreturn[$ctr]['high'] = floatval($row['high']);
				$dbreturn[$ctr]['low'] = floatval($row['low']);
				$dbreturn[$ctr]['close'] = floatval($row['close']);
				$dbreturn[$ctr]['volume'] = 0;
			}

			$ctr = $ctr + 1;
		}

		mysqli_close($con);

		if ($dataorg == "json") {
			//echo json_encode( $dbreturn );
			return $dbreturn;
		} 
		elseif ($dataorg == "highchart") {
			return $dbreturn;
			//echo json_encode( $dbreturn );
		}
		elseif (($dataorg == "array") || ($dataorg == "array2")) {
			//TODO: create code for organizing an array data output
			return $dbreturn;
			//echo json_encode( $dbreturn );
		}
		else { //json
			echo json_encode( $dbreturn );
		}
	}

	// check if its a weekend
	function isWeekend($date) {
	    return (date('N', strtotime($date)) >= 6);
	}

	// returns OHLC for a candlestick chart
	function getOHLC ($company, $from="1900-01-01 00:00:00", $to=null, $dataorg="json", $host, $db, $user, $pass) {
		$ohlcur = [];	//ohl-current candlestick
		$accessOHLCur = false;	//don't read the ohlcurrent by default

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
				$dbreturn[$ctr][0] = doubleval($row['timestamp']);
				$dbreturn[$ctr][1] = floatval($row['open']);
				$dbreturn[$ctr][2] = floatval($row['high']);
				$dbreturn[$ctr][3] = floatval($row['low']);
				$dbreturn[$ctr][4] = floatval($row['close']);
				$dbreturn[$ctr][5] = intval($row['volume']);
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

		if ($ctr <= 1) {
			//no data
			return 0;
		}

		$dateToday = date('Y-m-d');
		$latestTS = 0;

		switch ($dataorg) {
			case 'json':
				$latestTS = $dbreturn[$ctr-1]['timestamp'];
				break;
			case 'highchart':
				$latestTS = $dbreturn[$ctr-1][0];
				$dateToday = date('U') * 1000;
				break;
			case 'array':
				$latestTS = $dbreturn[$ctr-1][0];
				break;
			case 'array2':
				$latestTS = $dbreturn[$ctr-1][0];
				$dateToday = date('U') * 1000;
				break;
			default:
				$latestTS = $dbreturn[$ctr-1]['timestamp'];
				break;
		}

		//echo "after switch. Date Today: $dateToday, Latest: $latestTS<Br>";
		if ( ($dateToday > $latestTS) && !isWeekend($dateToday) ) {
			$accessOHLCur = true;
			//get the candlestick for today OHLCurrent
			$ohlcur = getOHLCurrent($company, $dataorg, $host, $db, $user, $pass);
			//echo json_encode($ohlcur);

			if ($ohlcur == "") {
				$accessOHLCur = false;
				//echo "Returned OHLCurrent is empty. ";
			}
		}

		mysqli_close($con);

		if ($dataorg == "json") {
			if ($accessOHLCur) {
				$ts = date('Y-m-d', strtotime($ohlcur[0]['timestamp']));
				//echo "json ts new: " . $ts . "<Br>";

				if ($ts > $dbreturn[$ctr-1]['timestamp']) {
					//echo "json OHLCurrent > latestDB TS<Br>";
					$dbreturn[$ctr]['timestamp'] = $ts;
					$dbreturn[$ctr]['open'] = $ohlcur[0]['open'];
					$dbreturn[$ctr]['high'] = $ohlcur[0]['high'];
					$dbreturn[$ctr]['low'] = $ohlcur[0]['low'];
					$dbreturn[$ctr]['close'] = $ohlcur[0]['close'];
					$dbreturn[$ctr]['volume'] = $ohlcur[0]['volume'];
				}
			}

			echo json_encode( $dbreturn );
		} 
		elseif ($dataorg == "highchart") {
			//echo "highchart<Br>";
			if ($accessOHLCur) {
				$ts = $ohlcur[0][0] / 1000;
				$ts = ($ts - ($ts % 86400)) * 1000;
				//echo "highchart ts: $ts<Br>";

				if ($ts > $dbreturn[$ctr-1][0]) {
					//echo "highchart OHLCurrent > latestDB TS<Br>";
					$dbreturn[$ctr][0] = $ts;
					$dbreturn[$ctr][1] = $ohlcur[0][1];
					$dbreturn[$ctr][2] = $ohlcur[0][2];
					$dbreturn[$ctr][3] = $ohlcur[0][3];
					$dbreturn[$ctr][4] = $ohlcur[0][4];
					$dbreturn[$ctr][5] = $ohlcur[0][5];
				}
			}

			echo json_encode($dbreturn);
		}
		elseif ($dataorg == "array") {
			if ($accessOHLCur) {
				//echo "company: $company, ";
				$ts = date('Y-m-d', strtotime($ohlcur[0][0]));
				//echo "array ts new: " . $ts . "<Br>";

				if ($ts > $dbreturn[$ctr-1][0]) {
					//echo "array OHLCurrent > latestDB TS<Br>";
					$dbreturn[$ctr][0] = $ts;
					$dbreturn[$ctr][1] = $ohlcur[0][1];
					$dbreturn[$ctr][2] = $ohlcur[0][2];
					$dbreturn[$ctr][3] = $ohlcur[0][3];
					$dbreturn[$ctr][4] = $ohlcur[0][4];
					$dbreturn[$ctr][5] = $ohlcur[0][5];
				}
			}

			//echo json_encode($dbreturn);
			return $dbreturn;
		}
		elseif ($dataorg == "array2") {
			if ($accessOHLCur) {
				$ts = $ohlcur[0][0] / 1000;
				$ts = ($ts - ($ts % 86400)) * 1000;
				//echo "array2 ts: $ts<Br>";

				if ($ts > $dbreturn[$ctr-1][0]) {
					//echo "array2 OHLCurrent > latestDB TS<Br>";
					$dbreturn[$ctr][0] = $ts;
					$dbreturn[$ctr][1] = $ohlcur[0][1];
					$dbreturn[$ctr][2] = $ohlcur[0][2];
					$dbreturn[$ctr][3] = $ohlcur[0][3];
					$dbreturn[$ctr][4] = $ohlcur[0][4];
					$dbreturn[$ctr][5] = $ohlcur[0][5];
				}
			}

			//echo json_encode($dbreturn);
			return $dbreturn;
		}
		else { //json
			$dbreturn[$ctr]['timestamp'] = $ohlcur['timestamp'];
			$dbreturn[$ctr]['open'] = $ohlcur['open'];
			$dbreturn[$ctr]['high'] = $ohlcur['high'];
			$dbreturn[$ctr]['low'] = $ohlcur['low'];
			$dbreturn[$ctr]['close'] = $ohlcur['close'];
			$dbreturn[$ctr]['volume'] = $ohlcur['volume'];

			echo json_encode( $dbreturn );
		}
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