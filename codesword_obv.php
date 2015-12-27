<?php  
	// This is a volume indicator that measures buying and selling pressure
	//		as a cumulative indicator that adds volume on up days and subracts
	//		volume on down days.
	// Real - data of which structure is [timestamp,close,volume]
	// Returns - data with structure [timestamp,close,volume,obv]
	function codesword_obv($real) {
		//echo json_encode($real) . "<Br><Br>";

		$obv = [];
		$ctr = 0;
		for ($i=1; $i < count($real); $i++) { 
			// Get Timestamp
			$obv[$ctr][0] = $real[$i][0];
			// Get Close Price
			$obv[$ctr][1] = $real[$i][1];
			// Get Volume
			$obv[$ctr][2] = $real[$i][2];

			// Know the direction of price change
			$direction = $real[$i][1] - $real[$i-1][1];

			$tempObv;
			// Add volume based on direction of price change
			if ($direction > 0) {
				$tempObv = $real[$i][2];
			} 
			elseif ($direction < 0) {
				$tempObv = -$real[$i][2];
			}
			else {
				$tempObv = 0;
			}
			
			//echo "Change in Price: $direction, Temp Volume: $tempObv <Br>";
			if ($ctr == 0) {
				$obv[$ctr][3] = $tempObv;
			} else {
				$obv[$ctr][3] = $obv[$ctr-1][3] + $tempObv;
			}

			$ctr += 1;
		}

		//echo json_encode($obv) . "<Br><Br>";
		return $obv;
	}
?>