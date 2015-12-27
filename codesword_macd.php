<?php  
	require_once('codesword_ema.php');

	// Real - data of which structure is [timestamp,close price]
	// Returns - data with structure [timestamp,macd,signal,divergence]
	function codesword_macd($real, $fastPeriod=12, $slowPeriod=26) {
		$fastEma = codesword_ema($real, $fastPeriod);
		$slowEma = codesword_ema($real, $slowPeriod);

		$ctrFastOffset = 0;
		$macd = [];

		for ($i=0; $i < count($slowEma) ; $i++) { 
			if ($i == 0) {
				for ($j=0; $j < $slowPeriod; $j++) { 
					if ($fastEma[$j][0] == $slowEma[0][0]) {
						$ctrFastOffset = $j;
						debug_print("start of comparison: $ctrFastOffset <Br>");
						break;
					}
				}
			}

			$macd[$i][0] = $slowEma[$i][0];
			$macd[$i][1] = $fastEma[$i + $ctrFastOffset][1] - $slowEma[$i][1];
		}

		// The signal is a 9-day EMA
		$signal = codesword_ema($macd, 9);
		$ctrMacdOffset = 0;
		$histogram = [];
		$macdFull = [];

		for ($i=0; $i < count($signal) ; $i++) { 
			if ($i == 0) {
				for ($j=0; $j < $slowPeriod; $j++) { 
					if ($macd[$j][0] == $signal[0][0]) {
						$ctrMacdOffset = $j;
						debug_print("start of comparison: $ctrMacdOffset <Br>");
						break;
					}
				}
			}

			$histogram[$i][0] = $signal[$i][0];
			$histogram[$i][1] = $macd[$i + $ctrMacdOffset][1] - $signal[$i][1];

			$macdFull[$i][0] = $signal[$i][0];
			$macdFull[$i][1] = $macd[$i + $ctrMacdOffset][1];
			$macdFull[$i][2] = $signal[$i][1];
			$macdFull[$i][3] = $macd[$i + $ctrMacdOffset][1] - $signal[$i][1];
		}

		return $macdFull;
	}
?>