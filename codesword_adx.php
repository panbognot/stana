<?php  
	require_once('codesword_tr.php');
	require_once('codesword_ema.php');

	// This function computes the directional movement (DM) of the stock
	// Real - data of which structure is [timestamp,high,low,close]
	// Returns - data with structure [timestamp, +DM, -DM]
	function codesword_dm($real) {
		$dm = [];
		$ctr = 0;

		for ($i=1; $i < count($real) ; $i++) { 
			//get timestamp
			$dm[$ctr][0] = $real[$i][0];

			//calculate the difference in Highs
			//Today's High - Yesterday's High
			$diffHighs = $real[$i][1] - $real[$i-1][1];

			//calculate the difference in Lows
			//Yesterday's Low - Today's Low
			$diffLows = $real[$i-1][2] - $real[$i][2];

			if ( ($diffHighs <= 0) && ($diffLows <= 0) ) {
				//+DM
				$dm[$ctr][1] = 0;
				//-DM
				$dm[$ctr][2] = 0;
			}
			elseif ($diffHighs > $diffLows) {
				//+DM
				$dm[$ctr][1] = $diffHighs;
				//-DM
				$dm[$ctr][2] = 0;
			}
			elseif ($diffHighs < $diffLows) {
				//+DM
				$dm[$ctr][1] = 0;
				//-DM
				$dm[$ctr][2] = $diffLows;
			}
			
			$ctr++;
		}

		return $dm;
	}

	// This function computes the directional movement (DM) of the stock
	// 		while applying a Wilder's EMA for a set period
	// Real - data of which structure is [timestamp,high,low,close]
	// Returns - data with structure [timestamp, +DM14, -DM14]
	function codesword_dm_wema($real, $periodLookback=14) {
		$dm = codesword_dm($real);
		//return $dm;

		//Use Wilder's EMA on the resultant Directional Movement
		$dmNDays = codesword_wilder_ema2($dm, $periodLookback);

		for ($i=0; $i < count($dmNDays); $i++) { 
			$dmNDays[$i][1] = $dmNDays[$i][1] * $periodLookback;
			$dmNDays[$i][2] = $dmNDays[$i][2] * $periodLookback;
		}

		return $dmNDays;
	}

	// This is a momentum indicator that uses support and resistance levels
	// Real - data of which structure is [timestamp,high,low,close]
	// Returns - data with structure [timestamp, DX]
	function codesword_dx($real, $periodLookback=14) {
		//compute the True Range Undivided Average for $periodLookback days
		$TR14 = codesword_utr($real, $periodLookback);
		//return $TR14;

		//compute the Directional Movement for $periodLookback days
		$DM14 = codesword_dm_wema($real, $periodLookback);
		//return $DM14;

		//compute the Directional Indicators and the Directional Index
		$DI14 = [];
		$DI14_diff = [];
		$DI14_sum = [];
		$DX = [];
		for ($i=0; $i < count($DM14); $i++) { 
			//get timestamp
			$DI14[$i][0] = $DM14[$i][0];

			//+DI14 = +DM14 / TR14
			$DI14[$i][1] = $DM14[$i][1] * 100 / $TR14[$i][1];

			//-DI14 = -DM14 / TR14
			$DI14[$i][2] = $DM14[$i][2] * 100 / $TR14[$i][1];

			//calculate the difference and sum of the DI14
			$DI14_diff[$i] = abs($DI14[$i][1] - $DI14[$i][2]);
			$DI14_sum[$i] = abs($DI14[$i][1] + $DI14[$i][2]);

			//calculate the directional index
			//DX = (DI14 diff / DI14 sum) * 100
			$DX[$i][0] = $DM14[$i][0];
			$DX[$i][1] = ($DI14_diff[$i] / $DI14_sum[$i]) * 100;
		}
		//return $DI14;
		return $DX;

		//$ADX = codesword_wilder_ema($DX, $periodLookback);
		//return $ADX;
	}

	// This is a momentum indicator that uses support and resistance levels
	// Real - data of which structure is [timestamp,high,low,close]
	// Returns - data with structure [timestamp, ADX]
	function codesword_adx($real, $periodLookback=14) {
		//compute the True Range Undivided Average for $periodLookback days
		$DX = codesword_dx($real, $periodLookback);

		//run the DX through a wilder ema to smoothen the curve
		$ADX = codesword_wilder_ema($DX, $periodLookback);
		return $ADX;
	}
?>