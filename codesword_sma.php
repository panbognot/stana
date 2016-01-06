<?php 
	// Real - data of which structure is [timestamp,close price]
	// Simple Moving Average
	function codesword_sma($real, $period=12) {
		$sma = [];
		$k = 2 / ($period + 1);
		$ctr = 0;

		for ($i=$period-1; $i < count($real); $i++) {
			//Reset sma to zero 
			$sum = 0;
			for ($j=0; $j < $period ; $j++) { 
				$temp = $i - $j;
				$sum += $real[$temp][1];
			}

			$sum = $sum / $period;

			//timestamp value
			$sma[$ctr][0] = $real[$i][0];
			//sma value
			$sma[$ctr][1] = $sum;
			$ctr++;
		}

		return $sma;
	}

	// Buy/Sell signals from Based from Price and SMA
	// real - [timestamp, close price]
	// sma - [timestamp, sma]
	// Returns - [timestamp, signal]
	function codesword_smaBuySellSignal($real, $sma) {
		$diff = 0;
		$ctr = 0;

		$prevPrice = 0;
		$prevSma = 0;
		$signals = [];

		//Use for instead of while(1) for safety as the
		//while(1) might cause a crash or memory leak
		for ($i=0; $i < count($sma); $i++) {
			$smaTS = $sma[0][0];
			$realTS = $real[$diff][0];

			if ($smaTS == $realTS) {
				//echo "diff: $diff";
				break;
			}

			$diff++;
		}

		for ($i=0; $i < count($sma); $i++) { 
			if ($i == 0) {
				$prevPrice = $real[$i + $diff][1];
				$prevSma = $sma[$i][1];
				continue;
			}

			$curPrice = $real[$i + $diff][1];
			$curSma = $sma[$i][1];

			//Split the comparison by trend
			//Uptrend 
			if ($curPrice > $curSma) {
				//there is a possible buy signal if there was a cross over of values
				if ($prevPrice <= $prevSma) {
					//generate buy signal only if the slope of the SMA is positive
					$slopeSma = ($curSma - $prevSma) / (2 * $curSma);

					//slope must be greater than 0.1% of the current SMA value
					//0.1% is just a rule of thumb and can still be subject to change
					if ($slopeSma > 0) {
						if ( ($ctr > 0) && ($signals[$ctr-1][1] == "sell") ) {
							$signals[$ctr][0] = $sma[$i][0];
							$signals[$ctr][1] = "buy";
							$ctr++;
						}
						elseif ($ctr == 0) {
							$signals[$ctr][0] = $sma[$i][0];
							$signals[$ctr][1] = "buy";
							$ctr++;
						}
					}
				}

				//Do this step only if there are at least 3 sma values to compare
				if ($i > 2) {
					//check if slope of sma for the past 3 days is progressively positive
					if ( ($curSma > $prevSma) && ($prevSma > $sma[$i-2][1]) ) {
						//create a buy signal only if the last signal is "sell"
						if ($ctr > 0) {
							if ($signals[$ctr-1][1] == "sell") {
								$signals[$ctr][0] = $sma[$i][0];
								$signals[$ctr][1] = "buy";
								$ctr++;
							}
						}
					}
				}
			}
			//Downtrend
			elseif ($curPrice < $curSma) {
				//there is a sell signal if there was a cross over of values
				if ($prevPrice >= $prevSma) {
					//only applicable to entries greater than the first entry
					if ($ctr > 0) {
						//generate a sell signal only if last one was a buy signal
						if ($signals[$ctr-1][1] == "buy") {
							//different thresholds depending on current price
							if ($curPrice <= 35) {
								//check if |curPrice/prevPrice - 1| > 2.5%
								if ( (($prevPrice/$curPrice) - 1) > 0.025 ) {
									$signals[$ctr][0] = $sma[$i][0];
									$signals[$ctr][1] = "sell";
									$ctr++;
								}
							}
							else {
								//check if |curPrice/prevPrice - 1| > 1%
								if ( (($prevPrice/$curPrice) - 1) > 0.01 ) {
									$signals[$ctr][0] = $sma[$i][0];
									$signals[$ctr][1] = "sell";
									$ctr++;
								}
							}
						}
					}
					else {
						$signals[$ctr][0] = $sma[$i][0];
						$signals[$ctr][1] = "sell";
						$ctr++;
					}
				}
				//Do this step only if there are at least 2 sma values to compare
				if ($i > 1) {
					//check if slope of sma for the past 3 days is progressively positive
					if ($prevPrice < $prevSma) {
						//create a buy signal only if the last signal is "sell"
						if ($ctr > 0) {
							if ($signals[$ctr-1][1] == "buy") {
								//different thresholds depending on current price
								if ($curPrice <= 35) {
									//check if |curPrice/prevPrice - 1| > 2.5%
									if ( (($prevPrice/$curPrice) - 1) > 0.025 ) {
										$signals[$ctr][0] = $sma[$i][0];
										$signals[$ctr][1] = "sell";
										$ctr++;
									}
								}
								else {
									//check if |curPrice/prevPrice - 1| > 1%
									if ( (($prevPrice/$curPrice) - 1) > 0.01 ) {
										$signals[$ctr][0] = $sma[$i][0];
										$signals[$ctr][1] = "sell";
										$ctr++;
									}
								}
							}
						}
					}
				}
				//Do this step only if there are at least 3 sma values to compare
				if ($i > 2) {
					$prev2daysPrice = $real[$i + $diff - 2][1];
					$prev2daysSma = $sma[$i - 2][1];

					//check if slope of sma for the past 3 days is progressively negative
					if ( ($prevPrice < $prevSma) && ($prev2daysPrice < $prev2daysSma) ) {
						//create a buy signal only if the last signal is "sell"
						if ($ctr > 0) {
							//check if (curSma / curPrice) - 1 > 2%
							if ( (($curSma/$curPrice) - 1) > 0.02 ) {
								if ($signals[$ctr-1][1] == "buy") {
									$signals[$ctr][0] = $sma[$i][0];
									$signals[$ctr][1] = "sell";
									$ctr++;
								}
							}
						}
					}
				}
			}
			//Sideways
			else {

			}

			$prevPrice = $real[$i + $diff][1];
			$prevSma = $sma[$i][1];
		}

		return $signals;
	}

	// real - [timestamp, close price]
	// smaShort, smaMedium, smaLong - [timestamp, sma]
	// Returns - [timestamp, close, short, medium, long]
	function codesword_smaConsolidate($real, $smaShort, $smaMedium, $smaLong) {
		$smaConsolidated = [];
		
		// get timestamp difference of Real vs SMA Short
		$diffShort = 0;
		for ($i=0; $i < count($smaShort); $i++) {
			$smaTS = $smaShort[0][0];
			$realTS = $real[$diffShort][0];

			if ($smaTS == $realTS) {
				echo "diffShort: $diffShort <Br>";
				break;
			}

			$diffShort++;
		}

		// get timestamp difference of Real vs SMA Medium
		$diffMedium = 0;
		for ($i=0; $i < count($smaMedium); $i++) {
			$smaTS = $smaMedium[0][0];
			$realTS = $real[$diffMedium][0];

			if ($smaTS == $realTS) {
				echo "diffMedium: $diffMedium <Br>";
				break;
			}

			$diffMedium++;
		}

		// get timestamp difference of Real vs SMA Long
		$diffLong = 0;
		for ($i=0; $i < count($smaLong); $i++) {
			$smaTS = $smaLong[0][0];
			$realTS = $real[$diffLong][0];

			if ($smaTS == $realTS) {
				echo "diffLong: $diffLong <Br>";
				break;
			}

			$diffLong++;
		}

		//Pad the null values with first good data
	}
?>