<?php 
	require_once('codesword_stochastic_oscillator.php');

	// This function identifies trade signals using stochastic analysis
	// inputStoch comes from codesword_stochastic($real, $periodLookback=14, $periodSmoothing=3)
	// inputStoch - data with structure [timestamp,%K,%D]
	// Returns - data with structure [timestamp,signal,strength]
	function codesword_stochTradeDetector($inputStoch) {
		$signals = [];
		$ctr = 0;

		//Lets segregate the values for readability
		$timestamp = [];
		$percentK = [];
		$percentD = [];

		for ($i=0; $i < count($inputStoch); $i++) { 
			//The isset is used to avoid warning messages that could mess up
			//	the system
			$timestamp[$i] = isset($inputStoch[$i][0]) ? $inputStoch[$i][0] : null;
			$percentK[$i] = isset($inputStoch[$i][1]) ? $inputStoch[$i][1] : null;
			$percentD[$i] = isset($inputStoch[$i][2]) ? $inputStoch[$i][2] : null;
		}

		for ($i=1; $i < count($inputStoch); $i++) { 
			//check if current %K is above 50 and
			//check if previous %K is less than 50
			if ( ($percentK[$i] >= 50) && ($percentK[$i-1] < 50) ) {
				//check if %D is greater than the previous %D
				//this will eliminate some false signals from a down trend
				if ($percentD[$i] > $percentD[$i-1]) {
					$signals[$ctr][0] = $timestamp[$i];
					$signals[$ctr][1] = "buy";
					$signals[$ctr][2] = "check if close or current price is above SMA";

					$ctr++;
				}
			}
		}

		return $signals;
	}

	// This function further filters the trade signals from the stochastic analysis
	//		Only trade signals with close/current price > sma are allowed
	// signalsStoch - [timestamp,signal,strength]
	// isHigherThanSMA - [timestamp,boolean]
	// Returns - [timestamp,signal,strength]
	function codesword_stochSmaTradeDetector($signalsStoch, $isHigherThanSMA) {
		$ctr = 0;
		$startDate;
		$filteredSignals = [];
		$ctrFilt = 0;

		$stochStartDate = isset($signalsStoch[0][0]) ? $signalsStoch[0][0] : null;
		$smaStartDate = isset($isHigherThanSMA[0][0]) ? $isHigherThanSMA[0][0] : null;

		//Detect if Stoch Start Date or SMA Start Date is null
		if ( !$stochStartDate || !$smaStartDate ) {
			return 0;
		}

		if ($stochStartDate > $smaStartDate) {
			$startDate = $signalsStoch[0][0];

			for ($ctr=0; $ctr < count($isHigherThanSMA); $ctr++) { 
				if ($isHigherThanSMA[$ctr][0] >= $startDate) {
					//exit from the for loop
					//echo "Counter is: $ctr<Br>";
					break;
				}
			}
		}
		else {
			$ctr = 0;
		}

		foreach ($signalsStoch as $signal) {
			while ($signal[0] > $isHigherThanSMA[$ctr][0]) {
				$ctr++;
			}

			if ($isHigherThanSMA[$ctr][1]) {
				$filteredSignals[$ctrFilt++] = $signal;
			}
		}

		//echo json_encode($filteredSignals);
		return $filteredSignals;
	}
?>