<?php  
	require_once('codesword_sma.php');

	// This is a momentum indicator that uses support and resistance levels
	// Real - data of which structure is [timestamp,high,low,close]
	// Returns - data with structure [timestamp,%K,%D]
	function codesword_stochastic($real, $periodLookback=14, $periodSmoothing=3) {
		//echo json_encode($real) . "<Br><Br>";
		$nHighestLowest = [];
		$ctr = 0;

		// Get the highest highs for the past $periodLookback days (nHighestLowest[i][1])
		// Get the lowest lows for the past $periodLookback days (nHighestLowest[i][2])
		for ($i=$periodLookback-1; $i < count($real) ; $i++) { 
			$currentTimestamp = $real[$i][0];
			$currentHighest = $real[$i][1];
			$currentLowest = $real[$i][2];

			
			for ($j=0; $j < $periodLookback; $j++) { 
				$index = $i - $j;

				// Get Highest High for the period
				if ($real[$index][1] > $currentHighest) {
					$currentHighest = $real[$index][1];
				}

				// Get Lowest Low for the period
				if ($real[$index][2] < $currentLowest) {
					$currentLowest = $real[$index][2];
				}
			}

			$nHighestLowest[$ctr][0] = $currentTimestamp;
			$nHighestLowest[$ctr][1] = $currentHighest;
			$nHighestLowest[$ctr][2] = $currentLowest;
			$ctr += 1;
		}
		//echo json_encode($nHighestLowest) . "<Br><Br>";

		// Get %K = [(nClose - nLowestLow) / (nHighestHigh - nLowestLow)] * 100
		$percentK = [];
		// Default offset is zero 
		$kOffset = 0;
		for ($i=0; $i < count($nHighestLowest); $i++) { 
			if ($i == 0) {
				for ($j=0; $j < $periodLookback; $j++) { 
					if ($real[$j][0] == $nHighestLowest[0][0]) {
						$kOffset = $j;
						debug_print("start of comparison: $kOffset <Br>");
						break;
					}
				}
			}

			$percentK[$i][0] = $nHighestLowest[$i][0];
			$percentK[$i][1] = (($real[$i + $kOffset][3] - $nHighestLowest[$i][2]) / 
							($nHighestLowest[$i][1] - $nHighestLowest[$i][2])) * 100;
		}
		//echo json_encode($percentK);

		// Get %D = Simple Moving Average of %K for $periodSmoothing days
		$percentD = codesword_sma($percentK, $periodSmoothing);
		//echo json_encode($percentD);

		// Organize data for returning [timestamp, %k, %d]
		$stochastic = [];
		$dOffset = $periodSmoothing - 1;
		$realOffset = $kOffset + $dOffset;
		for ($i=0; $i < count($percentD); $i++) { 
			$stochastic[$i][0] = $percentD[$i][0];	//Timestamp
			$stochastic[$i][1] = $percentK[$i + $dOffset][1];	//%K
			$stochastic[$i][2] = $percentD[$i][1];	//%D
		}
		//echo json_encode($stochastic);
		return $stochastic;
	}
?>