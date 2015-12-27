<?php 
	// Real - data of which structure is [timestamp, value]
	function codesword_ema($real, $period=12) {
		$ema = [];
		$ctr = 0;
		$k = 2 / ($period + 1);

		for ($i=$period-1; $i < count($real); $i++) {
			//Reset ema to zero 
			$sum = 0;
			for ($j=0; $j < $period ; $j++) { 
				$temp = $i - $j;
				$sum += $real[$temp][1];
			}

			if ($ctr == 0) {
				$sum = $sum / $period;
			} else {
				$sum = ( $real[$i][1] * $k ) + ( $ema[$ctr-1][1] * (1 - $k) );
			}

			//timestamp value
			$ema[$ctr][0] = $real[$i][0];
			//ema value
			$ema[$ctr][1] = $sum;
			$ctr++;
		}

		return $ema;
	}

	// Real - data of which structure is [timestamp, value]
	function codesword_wilder_ema($real, $period=14) {
		$wema = [];
		$ctr = 0;

		//Compute wilder's exponential moving average
		for ($i=$period-1; $i < count($real) ; $i++) { 
			//get timestamp
			$wema[$ctr][0] = $real[$i][0];

			if ($ctr == 0) {
				$sum = 0;
				for ($j=0; $j < $period ; $j++) { 
					$temp = $i - $j;
					$sum += $real[$temp][1];
				}

				$wema[$ctr][1] = $sum / $period;
			} 
			else {
				//current wema = [(Prior wema x 13) + Current TR] / 14
				$wema[$ctr][1] = (($wema[$ctr-1][1] * ($period - 1)) + $real[$i][1]) / $period;
			}
			$ctr += 1;
		}

		return $wema;
	}

	// Real - data of which structure is [timestamp, value1, value2]
	// For 2 value elements
	// TODO: Create a wilder ema function that automatically detects how many elements
	// are present in an array row
	function codesword_wilder_ema2($real, $period=14) {
		$wema = [];
		$ctr = 0;

		//Compute wilder's exponential moving average
		for ($i=$period-1; $i < count($real) ; $i++) { 
			//get timestamp
			$wema[$ctr][0] = $real[$i][0];

			if ($ctr == 0) {
				$sum1 = 0;
				$sum2 = 0;

				for ($j=0; $j < $period ; $j++) { 
					$temp = $i - $j;

					//Just do a check if the array elements have value to avoid the "notice"
					//message that can cause a bug in the system
					if (isset($real[$temp][1])) {
						$value1 = $real[$temp][1];
					} else {
						$value1 = 0;
					}
					
					if (isset($real[$temp][2])) {
						$value2 = $real[$temp][2];
					} else {
						$value2 = 0;
					}
					
					$sum1 += $value1;
					$sum2 += $value2;
				}

				$wema[$ctr][1] = $sum1 / $period;
				$wema[$ctr][2] = $sum2 / $period;
			} 
			else {
				//Just do a check if the array elements have value to avoid the "notice"
				//message that can cause a bug in the system
				if (isset($real[$i][1])) {
					$value1 = $real[$i][1];
				} else {
					$value1 = 0;
				}
				
				if (isset($real[$i][2])) {
					$value2 = $real[$i][2];
				} else {
					$value2 = 0;
				}

				//current wema = [(Prior wema x 13) + Current TR] / 14
				$wema[$ctr][1] = (($wema[$ctr-1][1] * ($period - 1)) + $value1) / $period;
				$wema[$ctr][2] = (($wema[$ctr-1][2] * ($period - 1)) + $value2) / $period;
			}
			$ctr += 1;
		}

		return $wema;
	}
?>