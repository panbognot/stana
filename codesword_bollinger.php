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

		if ( ($real == 0) || ($real == []) || ($real == null) ) {
			//No Real Data
			return 0;
		}

		//compute the simple moving average and the standard deviation
		$std_dev = codesword_sd_ohlc($real, $period);
		//return $std_dev;

		if ( ($std_dev == 0) || ($std_dev == []) || ($std_dev == null) ) {
			//No Standard Deviation Data
			return 0;
		}

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

		$curCash = 100000;
		$curStocks = 0;
		$curEquity = 0;

		//fill the variables
		//this step is used to avoid confusion with variables
		//when comparing values over a small period of time for
		//trend detection
		for ($i=0; $i < count($input); $i++) { 
			$timestamp[$i] = isset($input[$i][0]) ? $input[$i][0] : null;
			$open[$i] = isset($input[$i][1]) ? $input[$i][1] : null;
			$high[$i] = isset($input[$i][2]) ? $input[$i][2] : null;
			$low[$i] = isset($input[$i][3]) ? $input[$i][3] : null;
			$close[$i] = isset($input[$i][4]) ? $input[$i][4] : null;
			$sma[$i] = isset($input[$i][5]) ? $input[$i][5] : null;
			$upperSD1[$i] = isset($input[$i][6]) ? $input[$i][6] : null;
			$lowerSD1[$i] = isset($input[$i][7]) ? $input[$i][7] : null;
			$upperSD2[$i] = isset($input[$i][8]) ? $input[$i][8] : null;
			$lowerSD2[$i] = isset($input[$i][9]) ? $input[$i][9] : null;
		}

		for ($i=1; $i < count($input); $i++) { 
			//Up Trend Detector
			if ($close[$i] > $upperSD1[$i]) {
				$continuity = 1;

				if ($close[$i-1] > $upperSD1[$i-1]) {
					$continuity++;
				}

				if ($continuity == 2) {
					//Buy candidate only if slope is greater than 2.0
					$slope = (($sma[$i] - $sma[$i-1]) / 2) * (1000 / $sma[$i-1]);

					if ($slope >= 2.0) {
						if ( ($ctr > 0) && ($signals[$ctr-1][1] == "sell") ) {
							$signals[$ctr][0] = $timestamp[$i];
							$signals[$ctr][1] = "buy";
							$signals[$ctr][2] = $slope;

							//trade price = price of stock when it was bought/sold
							$signals[$ctr][3] = $close[$i];

							$ctr++;
						}
						elseif ($ctr == 0) {
							$signals[$ctr][0] = $timestamp[$i];
							$signals[$ctr][1] = "buy";
							$signals[$ctr][2] = $slope;

							//trade price = price of stock when it was bought/sold
							$signals[$ctr][3] = $close[$i];

							$ctr++;
						}

						continue;
					}
				}
			}

			//Uptrend detector
			//there has to be at least 4 samples
			if ( ($i > 3) && ($close[$i] > $upperSD1[$i]) ) {
				$continuity = 1;

				for ($j=1; $j < 4; $j++) { 
					if ($close[$i-$j] > $upperSD1[$i-$j]) {
						$continuity++;
					}
				}

				if ($continuity == 4) {
					//Buy candidate only if slope is greater than 1.5
					//timeline under observation is 4 days
					$slope = (($sma[$i] - $sma[$i-3]) / 4) * (1000 / $sma[$i-3]);

					if ($slope >= 1.5) {
						if ( ($ctr > 0) && ($signals[$ctr-1][1] == "sell") ) {
							$signals[$ctr][0] = $timestamp[$i];
							$signals[$ctr][1] = "buy";
							$signals[$ctr][2] = $slope;

							//trade price = price of stock when it was bought/sold
							$signals[$ctr][3] = $close[$i];

							$ctr++;
						}
						elseif ($ctr == 0) {
							$signals[$ctr][0] = $timestamp[$i];
							$signals[$ctr][1] = "buy";
							$signals[$ctr][2] = $slope;

							//trade price = price of stock when it was bought/sold
							$signals[$ctr][3] = $close[$i];

							$ctr++;
						}

						continue;
					}
				}
			}
			
			//down trend
			if ( ($i > 3) && $close[$i] < $upperSD1[$i]) {
				$continuity = 1;

				if ($close[$i-1] < $upperSD1[$i-1]) {
					$continuity++;
				}
				if ($close[$i-2] < $upperSD1[$i-2]) {
					$continuity++;
				}

				if ($continuity == 3) {
					//Todo: add some slope calculator to filter false buy signals

					if ( ($ctr > 0) && ($signals[$ctr-1][1] == "buy") ) {
						$signals[$ctr][0] = $timestamp[$i];
						$signals[$ctr][1] = "sell";
						$signals[$ctr][2] = "two consecutive below sd1";

						//trade price = price of stock when it was bought/sold
						$signals[$ctr][3] = $close[$i];

						$ctr++;
					}
					elseif ($ctr == 0) {
						$signals[$ctr][0] = $timestamp[$i];
						$signals[$ctr][1] = "sell";
						$signals[$ctr][2] = "two consecutive below sd1";

						//trade price = price of stock when it was bought/sold
						$signals[$ctr][3] = $close[$i];

						$ctr++;
					}

					continue;
				}
			}

			//down trend
			if ( ($close[$i] > $sma[$i]) && ($open[$i] > $sma[$i]) &&
				($open[$i] - $close[$i] > 0) ) {

				$bodyAboveSD1 = $open[$i] - $upperSD1[$i];
				$bodyBelowSD1 = $upperSD1[$i] - $close[$i];

				$candleLength = $open[$i] - $close[$i];

				//sell if body is significantly below the SD1
				if ($bodyBelowSD1 > $bodyAboveSD1) {
					//Todo: add some slope calculator to filter false buy signals

					if ( ($ctr > 0) && ($signals[$ctr-1][1] == "buy") ) {
						$signals[$ctr][0] = $timestamp[$i];
						$signals[$ctr][1] = "sell";
						$signals[$ctr][2] = "body is significantly below the SD1";

						//trade price = price of stock when it was bought/sold
						$signals[$ctr][3] = $close[$i];

						$ctr++;
					}
					elseif ($ctr == 0) {
						$signals[$ctr][0] = $timestamp[$i];
						$signals[$ctr][1] = "sell";
						$signals[$ctr][2] = "body is significantly below the SD1";

						//trade price = price of stock when it was bought/sold
						$signals[$ctr][3] = $close[$i];

						$ctr++;
					}

					continue;
				}
			}

			//down trend
			if ($close[$i] < $sma[$i]) {
				//Sell candidate only if slope is less than 0
				//$slope = (($sma[$i] - $sma[$i-1]) / 2) * (1000 / $sma[$i-1]);

				//if ($slope < 0) {
					if ( ($ctr > 0) && ($signals[$ctr-1][1] == "buy") ) {
						$signals[$ctr][0] = $timestamp[$i];
						$signals[$ctr][1] = "sell";
						$signals[$ctr][2] = "strong";

						//trade price = price of stock when it was bought/sold
						$signals[$ctr][3] = $close[$i];

						$ctr++;
					}
					elseif ($ctr == 0) {
						$signals[$ctr][0] = $timestamp[$i];
						$signals[$ctr][1] = "sell";
						$signals[$ctr][2] = "strong";

						//trade price = price of stock when it was bought/sold
						$signals[$ctr][3] = $close[$i];

						$ctr++;
					}

					continue;
				//}
			}

		}

		//calculate the gains for the trades
		for ($k=0; $k < count($signals); $k++) { 
			if ($signals[$k][1] == "sell") {
				if ($k == 0) {
					//gain is zero
					$signals[$k][4] = 0;
				} 
				else {
					//gain = sell price - buying price + prev gain
					$signals[$k][4] = $signals[$k][3] - $signals[$k-1][3] + $signals[$k-1][4];
				}
				
			} 
			elseif ($signals[$k][1] == "buy") {
				if ( ($k == 0) || ($k == 1) ) {
					//gain is zero
					$signals[$k][4] = 0;
				} 
				elseif ($k > 0) {
					//gain is prev gain
					$signals[$k][4] = $signals[$k-1][4];
				}
			}
		}

		//echo json_encode($signals);
		return $signals;
	}

	// Returns the latest trade signal
	function codesword_bbTrendDetectorLatests($input) {
		$bb3 = codesword_bbTrendDetector($input);

		if ( ($bb3 == 0) || ($bb3 == []) || ($bb3 == null) ) {
			//No Signals
			return 0;
		}

		$latest = $bb3[count($bb3) - 1];

		//return $bb3 latest;
		return $latest;
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