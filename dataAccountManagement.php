<?php 
	require_once('dataBasicPlots.php');
	require_once('codesword_trade_signals.php');

	function tableExists($con, $table) {
	    $sql = "SHOW TABLES LIKE '$table'";
	    $result = mysqli_query($con, $sql);

	    if(mysqli_num_rows($result) > 0) {
	        return true;
	    } 
	    else {
	    	return false;
	    }
	}

	// generate the names of all the companies
	function importHoldingsData ($host, $db, $user, $pass, $data) {
		// Create connection
		$con=mysqli_connect($host, $user, $pass, $db);
		
		// Check connection
		if (mysqli_connect_errno()) {
		  echo "Failed to connect to MySQL: " . mysqli_connect_error();
		  return;
		}

		//create table if it doesn't exist yet
		$tableExists = tableExists($con, "current_holdings");
		if (!$tableExists) {
			$createTable = "CREATE TABLE `pse_data`.`current_holdings` (
							  `quote` VARCHAR(16) NOT NULL,
							  `datebuy` DATE NOT NULL,
							  `pricebuy` FLOAT NULL,
							  `volume` INT NULL,
							  `pricestoploss` FLOAT NULL,
							  PRIMARY KEY (`quote`))
							COMMENT = 'contains the current stocks being held and the stop loss selling price'";

			$result = mysqli_query($con, $createTable);

			echo "importHoldingsData: Created table 'current_holdings' <Br><Br>";
		}

		$sql = "REPLACE INTO current_holdings (datebuy, quote, pricebuy, volume)";

		$holdingValues = " VALUES";
		
		$dataSize = count($data);
		$ctr = 0;

		foreach ($data as $holdings) {
			$holdingValues = $holdingValues . "('".$holdings['date']."','".$holdings['company']."','".$holdings['pricebuy']."','".$holdings['volume']."')";

			$ctr++;
			if ($ctr < $dataSize) {
				$holdingValues = $holdingValues . ", ";
			}
		}

		$sql = $sql . $holdingValues;

		echo "$sql";

		$result = mysqli_query($con, $sql);

		if (mysqli_affected_rows($con) < 1) {
			echo "importHoldingsData: Failed Value Insertion!<Br>";
		}

		mysqli_close($con);
	}

	// returns OHLC for a candlestick chart
	function getStoMACD ($company, $from="1900-01-01 00:00:00", $to=null, $dataorg="json", $host, $db, $user, $pass) {
		$thlc = [];			//[timestamp,high,low,close]
		$stoch = [];		//[timestamp,%K,%D]
		$stochSignals = [];	//[timestamp,signal,desc]
		$stochSmaSignals = [];	//[timestamp,signal,desc]
		$isCloseHigherThanSMA = [];	//[timestamp,boolean]

		//OHLC data format [timestamp,open,high,low,close,volume]
		if ($dataorg == "highchart") {
			$dataOhlc = getOHLC ($company, $from, $to, "array2", $host, $db, $user, $pass);
		} else {
			$dataOhlc = getOHLC ($company, $from, $to, "array", $host, $db, $user, $pass);
		}
		
		//echo json_encode($dataOhlc);

		//Input for stochastic convestion should be [timestamp,high,low,close]
		$ctr = 0;
		foreach ($dataOhlc as $ohlc) {
			$thlc[$ctr][0] = $ohlc[0];	//timestamp
			$thlc[$ctr][1] = $ohlc[2];	//high
			$thlc[$ctr][2] = $ohlc[3];	//low
			$thlc[$ctr++][3] = $ohlc[4];	//close
		}

		//echo json_encode($thlc);

		//generate stochastic values
		$stoch = codesword_stochastic($thlc);
		//echo json_encode($stoch);

		//find out if the close/current price > sma
		$isCloseHigherThanSMA = codesword_isHigherThanSMA($thlc);
		//echo json_encode($isCloseHigherThanSMA);

		//generate trade signals from stochastic values
		$stochSignals = codesword_stochTradeDetector($stoch);

		//do some more filtering
		$stochSmaSignals = codesword_stochSmaTradeDetector($stochSignals, 
														$isCloseHigherThanSMA);

		//echo json_encode($stochSignals);
		$allData[0] = $stoch;
		$allData[1] = $stochSmaSignals;

		echo json_encode($allData);
	}
?>