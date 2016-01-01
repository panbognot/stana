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
				//there is a buy signal if there was a cross over of values
				if ($prevPrice <= $prevSma) {
					$signals[$ctr][0] = $sma[$i][0];
					$signals[$ctr][1] = "buy";
					$ctr++;
				}
			}
			//Downtrend
			elseif ($curPrice < $curSma) {
				//there is a sell signal if there was a cross over of values
				if ($prevPrice >= $prevSma) {
					$signals[$ctr][0] = $sma[$i][0];
					$signals[$ctr][1] = "sell";
					$ctr++;
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
?>