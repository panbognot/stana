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
			//calculate the bollinger upper band (sd2)
			$bollingerBands[$ctr][2] = $sd[1] + ($sd[2] * 2);
			//calculate the bollinger lower band (sd2)
			$bollingerBands[$ctr][3] = $sd[1] - ($sd[2] * 2);

			$ctr++;
		}

		return $bollingerBands;
	}

	// This function computes the bollinger bands of the stock
	// Real - data of which structure is [timestamp,close]
	// Returns - data with structure [timestamp,sma,upper band sd 1, lower band sd 1,
	//												upper band sd 2, lower band sd 2]
	function codesword_bollinger_bands2($real, $period=20) {
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
			//calculate the bollinger upper band (sd1)
			$bollingerBands[$ctr][2] = $sd[1] + ($sd[2]);
			//calculate the bollinger lower band (sd1)
			$bollingerBands[$ctr][3] = $sd[1] - ($sd[2]);
			//calculate the bollinger upper band (sd2)
			$bollingerBands[$ctr][4] = $sd[1] + ($sd[2] * 2);
			//calculate the bollinger lower band (sd2)
			$bollingerBands[$ctr][5] = $sd[1] - ($sd[2] * 2);

			$ctr++;
		}

		return $bollingerBands;
	}

	// This function computes the bollinger bands of the stock
	// Real - data of which structure is [timestamp,open,high,low,close]
	// Returns - data with structure [timestamp,open,high,low,close,
	//									sma,upper band sd 1, lower band sd 1,
	//									upper band sd 2, lower band sd 2]
	function codesword_bollinger_bands3($real, $period=20) {
		$bollingerBands = [];

		//compute the simple moving average and the standard deviation
		$std_dev = codesword_sd_ohlc($real, $period);
		//return $std_dev;

		$diff = 0;
		for ($i=0; $i < count($real); $i++) { 
			if ($std_dev[0][0] == $real[$i][0]) {
				//the day difference between real and sd
				break;	
			}
			else {
				$diff++;
			}
		}

		$ctr = 0;
		foreach ($std_dev as $sd) {
			//get the timestamp
			$bollingerBands[$ctr][0] = $sd[0];
			//get the open
			$bollingerBands[$ctr][1] = $real[$ctr + $diff][1];
			//get the get the high
			$bollingerBands[$ctr][2] = $real[$ctr + $diff][2];
			//get the low
			$bollingerBands[$ctr][3] = $real[$ctr + $diff][3];
			//get the close
			$bollingerBands[$ctr][4] = $real[$ctr + $diff][4];
			//get the sma
			$bollingerBands[$ctr][5] = $sd[1];
			//calculate the bollinger upper band (sd1)
			$bollingerBands[$ctr][6] = $sd[1] + ($sd[2]);
			//calculate the bollinger lower band (sd1)
			$bollingerBands[$ctr][7] = $sd[1] - ($sd[2]);
			//calculate the bollinger upper band (sd2)
			$bollingerBands[$ctr][8] = $sd[1] + ($sd[2] * 2);
			//calculate the bollinger lower band (sd2)
			$bollingerBands[$ctr][9] = $sd[1] - ($sd[2] * 2);

			$ctr++;
		}

		return $bollingerBands;
	}	

	// This function identifies the trend of a stock
	// Input comes from codesword_bollinger_bands3($real, $period=20)
	// Input - data with structure [timestamp,open,high,low,close,
	//									sma,upper band sd 1, lower band sd 1,
	//									upper band sd 2, lower band sd 2]
	// Returns - data with structure [timestamp,signal,strength]
	function codesword_bbTrendDetector($input) {
		$signals = [];
		$ctr = 0;

		$timestamp = [];
		$open = [];
		$high = [];
		$low = [];
		$close = [];
		$sma = [];
		$upperSD1 = [];
		$lowerSD1 = [];
		$upperSD2 = [];
		$lowerSD2 = [];

		//fill the variables
		//this step is used to avoid confusion with variables
		//when comparing values over a small period of time for
		//trend detection
		for ($i=0; $i < count($input); $i++) { 
			$timestamp[$i] = $input[$i][0];
			$open[$i] = $input[$i][1];
			$high[$i] = $input[$i][2];
			$low[$i] = $input[$i][3];
			$close[$i] = $input[$i][4];
			$sma[$i] = $input[$i][5];
			$upperSD1[$i] = $input[$i][6];
			$lowerSD1[$i] = $input[$i][7];
			$upperSD2[$i] = $input[$i][8];
			$lowerSD2[$i] = $input[$i][9];
		
			//echo $timestamp[$i].", ".$open[$i].", ".$high[$i].", ".$low[$i].", ".$close[$i].", ";
			//echo $sma[$i].", ".$upperSD1[$i].", ".$lowerSD1[$i].", ".$upperSD2[$i].", ".$lowerSD2[$i]."<Br>";
		}

		for ($i=2; $i < count($input); $i++) { 
			//Up Trend Detector
			if ($close[$i] > $upperSD1[$i]) {
				$continuity = 1;

				if ($close[$i-1] > $upperSD1[$i-1]) {
					$continuity++;
				}
				if ($close[$i-2] > $upperSD1[$i-2]) {
					$continuity++;
				}

				if ($continuity == 3) {
					//Todo: add some slope calculator to filter false buy signals

					if ( ($ctr > 0) && ($signals[$ctr-1][1] == "sell") ) {
						$signals[$ctr][0] = $timestamp[$i];
						$signals[$ctr][1] = "buy";
						$signals[$ctr][2] = "description";
						$ctr++;
					}
					elseif ($ctr == 0) {
						$signals[$ctr][0] = $timestamp[$i];
						$signals[$ctr][1] = "buy";
						$signals[$ctr][2] = "description";
						$ctr++;
					}
				}
			}
			elseif ($close[$i] < $lowerSD1[$i]) {
				$continuity = 1;

				if ($close[$i-1] < $lowerSD1[$i-1]) {
					$continuity++;
				}
				if ($close[$i-2] < $lowerSD1[$i-2]) {
					$continuity++;
				}

				if ($continuity == 3) {
					//Todo: add some slope calculator to filter false buy signals

					if ( ($ctr > 0) && ($signals[$ctr-1][1] == "buy") ) {
						$signals[$ctr][0] = $timestamp[$i];
						$signals[$ctr][1] = "sell";
						$signals[$ctr][2] = "description";
						$ctr++;
					}
					elseif ($ctr == 0) {
						$signals[$ctr][0] = $timestamp[$i];
						$signals[$ctr][1] = "sell";
						$signals[$ctr][2] = "description";
						$ctr++;
					}
				}
			}
			else {
				# code...
			}
			
		}

		//echo json_encode($signals);
		return $signals;
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