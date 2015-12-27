<?php 
	// Real - data of which structure is [timestamp,close price]
	// Standard Deviation - returns [timestamp,sma,std_dev]
	function codesword_sd($real, $period=20) {
		$std_dev = [];
		$k = 2;	//the standard deviation multiplier
		$ctr = 0;

		for ($i=$period-1; $i < count($real); $i++) {
			//get the mean/average
			$sum = 0;
			for ($j=0; $j < $period ; $j++) { 
				$temp = $i - $j;
				$sum += $real[$temp][1];
			}
			$fMean = $sum / $period;

			//compute the variance
			$fVariance = 0;
			for ($j=0; $j < $period ; $j++) { 
				$temp = $i - $j;
				//$sum += $real[$temp][1];
				$fVariance += pow($real[$temp][1] - $fMean, 2);
			}
			$fVariance = $fVariance / $period;

			//timestamp value
			$std_dev[$ctr][0] = $real[$i][0];
			//mean value
			$std_dev[$ctr][1] = $fMean;
			//standard deviation value
			$std_dev[$ctr][2] = round(sqrt($fVariance), 4);
			$ctr++;
		}

		return $std_dev;
	}
?>