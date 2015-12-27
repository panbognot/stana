<?php  
	require_once('codesword_ema.php');

	// This is a volatility indicator that uses high, low and close values
	// Real - data of which structure is [timestamp,high,low,close]
	// Returns - data with structure [timestamp, TR]
	function codesword_tr($real) {
		$ctr = 0;
		$trueValue = [];

		for ($i=1; $i < count($real) ; $i++) { 
			//get timestamp
			$trueValue[$ctr][0] = $real[$i][0];

			//calculate the ff:
			//	1. Today's High - Today's Low (TRHL)
			//	2. Today's High - Yesterday's Close (TRHC)
			//	3. Yesterday's Close - Today's Low (TRCL)
			$TRHL = abs($real[$i][1] - $real[$i][2]);
			$TRHC = abs($real[$i][1] - $real[$i-1][3]);
			$TRCL = abs($real[$i-1][3] - $real[$i][2]);

			$tempTR = ($TRHL >= $TRHC) ? $TRHL : $TRHC;
			$trueValue[$ctr][1] = ($tempTR >= $TRCL) ? $tempTR : $TRCL;

			$ctr += 1;
		}
		return $trueValue;
	}

	// This is a volatility indicator that uses high, low and close values
	// Real - data of which structure is [timestamp,high,low,close]
	// Returns - data with structure [timestamp, ATR]
	function codesword_atr($real, $periodLookback=14) {
		$tr = codesword_tr($real);
		
		//Compute the Average True Range by running WEMA on TR
		$atr = codesword_wilder_ema($tr, $periodLookback);

		return $atr;
	}

	// This is a volatility indicator that uses high, low and close values
	// Real - data of which structure is [timestamp,high,low,close]
	// Returns - data with structure [timestamp, UTR]
	// This is the undivided version of the TR smoothening
	function codesword_utr($real, $periodLookback=14) {
		$atr = codesword_atr($real, $periodLookback);

		for ($i=0; $i < count($atr) ; $i++) { 
			//multiply by period
			$atr[$i][1] = $atr[$i][1] * $periodLookback;
		}

		return $atr;
	}
?>