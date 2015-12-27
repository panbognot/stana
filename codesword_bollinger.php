<?php  
	require_once('codesword_standard_deviation.php');

	// This function computes the bollinger bands of the stock
	// Real - data of which structure is [timestamp,close]
	// Returns - data with structure [timestamp,sma,upper band, lower band]
	function codesword_bollinger_bands($real, $period=20) {
		$bollingerBands = [];

		//compute the simple moving average and the standard deviation
		$std_dev = codesword_sd($real, $period);
		//return $std_dev;

		$ctr = 0;
		foreach ($std_dev as $sd) {
			//get the timestamp
			$bollingerBands[$ctr][0] = $sd[0];
			//get the sma
			$bollingerBands[$ctr][1] = $sd[1];
			//calculate the bollinger upper band
			$bollingerBands[$ctr][2] = $sd[1] + ($sd[2] * 2);
			//calculate the bollinger lower band
			$bollingerBands[$ctr][3] = $sd[1] - ($sd[2] * 2);

			$ctr++;
		}

		return $bollingerBands;
	}

	// This function computes the bollinger bands width of the stock
	// Real - data of which structure is [timestamp, close]
	// Returns - data with structure [timestamp,bbw]
	function codesword_bbw($real, $period=20) {
		$bbw = [];

		$std_dev = codesword_sd($real, $period);

		$ctr = 0;
		foreach ($std_dev as $sd) {
			//get the timestamp
			$bbw[$ctr][0] = $sd[0];
			//get the bandwidth which is 4 * standard deviation
			$bbw[$ctr][1] = $sd[2] * 4;

			$ctr++;
		}

		return $bbw;
	}
?>