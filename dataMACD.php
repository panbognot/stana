<?php 
	require_once('codesword_macd.php');

	// returns Moving Average Convergence Divergence Data (macd, signal, histogram)
	//	- MACD is 12 day EMA - 26 day EMA
	//	- Signal is 9 day EMA of the MACD
	//	- Histogram is MACD - Signal
	function getMACD ($company, $from="1900-01-01 00:00:00", $to=null, $dataorg="json", $host, $db, $user, $pass) {
		$macd = [];

		$intervalPeriod = 55;
		//from date has to be adjusted for the bollinger bands
		$date = date_create($from);
		date_add($date,date_interval_create_from_date_string("-$intervalPeriod days"));
		$fromAdjusted =  date_format($date,"Y-m-d");

		$dataOhlc = [];

		//OHLC data format [timestamp,open,high,low,close,volume]
		if ($dataorg == "highchart") {
			$dataOhlc = getOHLC ($company, $fromAdjusted, $to, "array2", $host, $db, $user, $pass);
		} else {
			$dataOhlc = getOHLC ($company, $fromAdjusted, $to, "array", $host, $db, $user, $pass);
		}

		//Return if $dataOhlc is null
		if ( ($dataOhlc == []) || ($dataOhlc == 0) ) {
			return 0;
		}

		//Input for SMA functions should be [timestamp,close]
		$ctr = 0;
		foreach ($dataOhlc as $ohlc) {
			if ($dataorg == "json") {
			  	$dbreturn[$ctr][0] = $ohlc[0];	//timestamp
				$dbreturn[$ctr][1] = $ohlc[4];	//close
			} 
			elseif ($dataorg == "highchart") {
			  	$dbreturn[$ctr][0] = $ohlc[0];	//timestamp
				$dbreturn[$ctr][1] = $ohlc[4];	//close
			}
			elseif ($dataorg == "array") {
				//TODO: create code for organizing an array data output
			}
			else {
				$dbreturn[$ctr][0] = $ohlc[0];	//timestamp
				$dbreturn[$ctr][1] = $ohlc[4];	//close
			}

			$ctr++;
		}		

		if ($dataorg == "json") {
			//echo json_encode($dbreturn);
			$macd = codesword_macd($dbreturn);
			//echo json_encode($ema);
		} 
		elseif ($dataorg == "highchart") {
			$macd = codesword_macd($dbreturn);
		}
		elseif ($dataorg == "array") {
		//TODO: create code for organizing an array data output
		}
		else { //json
			$macd = codesword_macd($dbreturn);
		}

		echo json_encode($macd);
	}
?>