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
?>