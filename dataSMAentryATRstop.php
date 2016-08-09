<?php 
	require_once('codesword_trade_signals.php');
	require_once('dataSMA.php');
	require_once('dataTR.php');

	// returns SMA Buy Signal and ATR based Sell/Stop Signal
	//		{(timestamp, sma), (timestamp, signal)}
	function getSMAentryATRstop ($company, $from="1900-01-01 00:00:00", $to=null, 
					$dataorg="json", $samplePeriod=15, $enSignals=false,
					$enJsonEncode="false",
					$host, $db, $user, $pass) {

		// TODO: Get OHLC data
		$intervalPeriod = $samplePeriod * 1.5;
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

		//Tailor data for the SMA input
		//Input for SMA functions should be [timestamp,close]
		$ctr = 0;
		$dataOhlcForSMA = [];
		foreach ((array)$dataOhlc as $ohlc) {
			$dataOhlcForSMA[$ctr][0] = $ohlc[0];	//timestamp
			$dataOhlcForSMA[$ctr++][1] = $ohlc[4];	//close
		}

		//Get SMA Signal
		$sma = codesword_sma($dataOhlcForSMA, $samplePeriod);

		//Get SMA Buy Signals
		$smaBuy = [];
		$ctrBuy = 0;
		$buysellSignals = codesword_smaBuySellSignal($dataOhlcForSMA, $sma);
		foreach ($buysellSignals as $signal) {
			if ($signal[1] == "buy") {
				$smaBuy[$ctrBuy++] = $signal;
			}
		}

		// echo json_encode($smaBuy);

		//Tailor data for the ATR input
		$ctr = 0;
		$dataOhlcForATR = [];
		foreach ((array)$dataOhlc as $ohlc) {
			$dataOhlcForATR[$ctr][0] = $ohlc[0];	//timestamp
			$dataOhlcForATR[$ctr][1] = $ohlc[2];	//high
			$dataOhlcForATR[$ctr][2] = $ohlc[3];	//low
			$dataOhlcForATR[$ctr][3] = $ohlc[4];	//close

			$ctr = $ctr + 1;
		}

		// echo json_encode($dataOhlcForATR);

		// Compute the ATR
		$atr = codesword_atr($dataOhlcForATR);

		// echo json_encode($atr);

		// TODO: Get ATR Stop/Sell Signals
		//		the stop signal is based on the entry price that we'll get from
		//		the SMA Buy Signal
		// Inputs:
		//		1. Data OHLC for ATR 	(ts,open,high,low,close)
		//		2. SMA Buy Signals 		(ts, buy)
		//		3. ATR Values 			(ts, atr)
		$smaEntryAtrStopSignals = codesword_smaEntryATRstopTradeDetector($dataOhlc, $smaBuy, $atr, 2, 4);
	}

	// // returns Simple Moving Average Data {(timestamp, sma), (timetsamp, signal)}
	// function getSMA ($company, $from="1900-01-01 00:00:00", $to=null, 
	// 				$dataorg="json", $samplePeriod=15, $enSignals=false,
	// 				$enJsonEncode="false",
	// 				$host, $db, $user, $pass) {

	// 	$dbreturn = getSMA_sub_real ($company, $from, $to, 
	// 				$dataorg, $samplePeriod, $enSignals,
	// 				$host, $db, $user, $pass);

	// 	if ($dataorg == "json") {
	// 		$sma = codesword_sma($dbreturn, $samplePeriod);
	// 	} 
	// 	elseif ($dataorg == "highchart") {
	// 		$sma = codesword_sma($dbreturn, $samplePeriod);
	// 	}
	// 	elseif ($dataorg == "array") {
	// 	//TODO: create code for organizing an array data output
	// 	}
	// 	else { //json
	// 		$sma = codesword_sma($dbreturn, $samplePeriod);
	// 	}

	// 	if ($enSignals) {
	// 		$buysellSignals = codesword_smaBuySellSignal($dbreturn, $sma);
	// 	} 
	// 	else {
	// 		$buysellSignals = 0;
	// 	}
		
	// 	if ($enJsonEncode) {
	// 		$allData = [];
	// 		$allData[0] = $sma;
	// 		$allData[1] = $buysellSignals;
	// 		echo json_encode($allData);
	// 	}
	// 	else {
	// 		return $sma;
	// 	}
	// }
?>