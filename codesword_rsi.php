<?php  
	require_once('codesword_ema.php');

	// Real - data of which structure is [timestamp,close price]
	// Returns - data with structure [timestamp,rsi]
	function codesword_rsi($real, $period=14) {
		//echo json_encode($real) . "<Br><Br>";

		// Calculate change in closing prices
		$priceChange = [];
		for ($i=0; $i < count($real) - 1; $i++) { 
			// Get timestamp
			$priceChange[$i][0] = $real[$i+1][0];

			// Get price change
			$priceChange[$i][1] = $real[$i+1][1] - $real[$i][1];
		}

		// Segregate Gains and Losses
		$listGains = [];
		$listLosses = [];
		$ctrDays = 0;
		foreach ($priceChange as $tradeDay) {
			$listGains[$ctrDays][0] = $tradeDay[0];
			$listLosses[$ctrDays][0] = $tradeDay[0];

			if ($tradeDay[1] > 0) {
				$listGains[$ctrDays][1] = $tradeDay[1];
				$listLosses[$ctrDays][1] = 0;
			} else {
				$listGains[$ctrDays][1] = 0;
				$listLosses[$ctrDays][1] = abs($tradeDay[1]);
			}

			$ctrDays += 1;
		}

		// Calculate Average Gain 14 day default period EMA
		$ema_gains = codesword_ema($listGains, $period);

		// Calculate Average Loss 14 day default period EMA
		$ema_losses = codesword_ema($listLosses, $period);

		$rsi = [];
		for ($i=0; $i < count($ema_gains); $i++) { 
			// Get timestamp
			$rsi[$i][0] = $ema_gains[$i][0];

			// Calculate RS = Ave Gain / Ave Loss
			$rs = $ema_gains[$i][1] / $ema_losses[$i][1];

			// RSI = 100 - (100 / (1 + RS))
			$rsi[$i][1] = 100 - (100 / (1 + $rs));

			// echo "date: ".$rsi[$i][0].", ave gain: ".$ema_gains[$i][1].", ave loss: ".
			// 	$ema_losses[$i][1].", rs: $rs, rsi: ".$rsi[$i][1]."<Br>";
		}
		
		return $rsi;
	}
?>