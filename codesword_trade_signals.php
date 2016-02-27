<?php 
	require_once('codesword_stochastic_oscillator.php');

	// This function identifies trade signals using stochastic analysis
	// Input comes from codesword_stochastic($real, $periodLookback=14, $periodSmoothing=3)
	// Input - data with structure [timestamp,%K,%D]
	// Returns - data with structure [timestamp,signal,strength]
	function codesword_stochTradeDetector($input) {
		$signals = [];
		$ctr = 0;

		//Lets segregate the values for readability
		$timestamp = [];
		$percentK = [];
		$percentD = [];

		for ($i=0; $i < count($input); $i++) { 
			//The isset is used to avoid warning messages that could mess up
			//	the system
			$timestamp[$i] = isset($input[$i][0]) ? $input[$i][0] : null;
			$percentK[$i] = isset($input[$i][1]) ? $input[$i][1] : null;
			$percentD[$i] = isset($input[$i][2]) ? $input[$i][2] : null;
		}

		for ($i=1; $i < count($input); $i++) { 
			//check if current percentK is above 50
			if ( ($percentK[$i] >= 50) && ($percentK[$i-1] < 50) ) {
				$signals[$ctr][0] = $timestamp[$i];
				$signals[$ctr][1] = "buy";
				$signals[$ctr][2] = "check if close or current price is above SMA";

				$ctr++;
			}
		}

		return $signals;
	}
?>