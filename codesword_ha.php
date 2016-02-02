<?php 
	// Real - data of which structure is [timestamp,open,high,low,close,volume]
	// Heikin Ashi OHLC conversion
	// Return - data [timestamp,openha,highha,lowha,closeha,volume]
	function codesword_ha($real) {
		$ohlcha = [];

		for ($i=0; $i < count($real); $i++) {
/*			$timestamp = isset($real[$i][0]) ? $real[$i][0] : 0;
			$open = isset($real[$i][1]) ? $real[$i][1] : 0;
			$high = isset($real[$i][2]) ? $real[$i][2] : 0;
			$low = isset($real[$i][3]) ? $real[$i][3] : 0;
			$close = isset($real[$i][4]) ? $real[$i][4] : 0;
			$volume = isset($real[$i][5]) ? $real[$i][5] : 0;*/

			$timestamp = $real[$i][0];
			$open = $real[$i][1];
			$high = $real[$i][2];
			$low = $real[$i][3];
			$close = $real[$i][4];
			$volume = $real[$i][5];

			//echo "[$timestamp,$open,$high,$low,$close,$volume], " ;

			$prevOpen = isset($real[$i-1][1]) ? $real[$i-1][1] : (isset($real[0][1]) ? $real[0][1] : 0);
			$prevClose = isset($real[$i-1][4]) ? $real[$i-1][4] : (isset($real[0][4]) ? $real[0][4] : 0);

			//compute HA Close
			$closeha = ($open + $high + $low + $close) / 4;

			//compute HA Open			
			$openha = ($prevOpen + $prevClose) / 2;

			//compute HA High
			$highha = ($high > $openha) ? $high : $openha;
			$highha = ($highha > $closeha) ? $highha : $closeha; 

			//compute HA Low
			$lowha = ($low < $openha) ? $low : $openha ;
			$lowha = ($lowha < $closeha) ? $lowha : $closeha ;

			$ohlcha[$i][0] = $timestamp;
			$ohlcha[$i][1] = $openha;
			$ohlcha[$i][2] = $highha;
			$ohlcha[$i][3] = $lowha;
			$ohlcha[$i][4] = $closeha;
			$ohlcha[$i][5] = $volume;
		}

		return $ohlcha;
	}
?>