<?php 
	require_once('codesword_stochastic_oscillator.php');

	// This function identifies trade signals using stochastic analysis
	// inputStoch comes from codesword_stochastic($real, $periodLookback=14, $periodSmoothing=3)
	// inputStoch - data with structure [timestamp,%K,%D]
	// Returns - data with structure [timestamp,signal,strength]
	function codesword_stochTradeDetector($inputStoch) {
		$signals = [];
		$ctr = 0;

		//Lets segregate the values for readability
		$timestamp = [];
		$percentK = [];
		$percentD = [];

		for ($i=0; $i < count($inputStoch); $i++) { 
			//The isset is used to avoid warning messages that could mess up
			//	the system
			$timestamp[$i] = isset($inputStoch[$i][0]) ? $inputStoch[$i][0] : null;
			$percentK[$i] = isset($inputStoch[$i][1]) ? $inputStoch[$i][1] : null;
			$percentD[$i] = isset($inputStoch[$i][2]) ? $inputStoch[$i][2] : null;
		}

		for ($i=1; $i < count($inputStoch); $i++) { 
			//check if current %K is above 50 and
			//check if previous %K is less than 50
			if ( ($percentK[$i] >= 50) && ($percentK[$i-1] < 50) ) {
				//check if %D is greater than the previous %D
				//this will eliminate some false signals from a down trend
				if ($percentD[$i] > $percentD[$i-1]) {
					$signals[$ctr][0] = $timestamp[$i];
					$signals[$ctr][1] = "buy";
					$signals[$ctr][2] = "check if close or current price is above SMA";

					$ctr++;
				}
			}
		}

		return $signals;
	}

	// This function further filters the trade signals from the stochastic analysis
	//		Only trade signals with close/current price > sma are allowed
	// signalsStoch - [timestamp,signal,strength]
	// isHigherThanSMA - [timestamp,boolean]
	// Returns - [timestamp,signal,strength]
	function codesword_stochSmaTradeDetector($signalsStoch, $isHigherThanSMA) {
		$ctr = 0;
		$startDate;
		$filteredSignals = [];
		$ctrFilt = 0;

		$stochStartDate = isset($signalsStoch[0][0]) ? $signalsStoch[0][0] : null;
		$smaStartDate = isset($isHigherThanSMA[0][0]) ? $isHigherThanSMA[0][0] : null;

		//Detect if Stoch Start Date or SMA Start Date is null
		if ( !$stochStartDate || !$smaStartDate ) {
			return 0;
		}

		if ($stochStartDate > $smaStartDate) {
			$startDate = $signalsStoch[0][0];

			for ($ctr=0; $ctr < count($isHigherThanSMA); $ctr++) { 
				if ($isHigherThanSMA[$ctr][0] >= $startDate) {
					//exit from the for loop
					//echo "Counter is: $ctr<Br>";
					break;
				}
			}
		}
		else {
			$ctr = 0;
		}

		foreach ($signalsStoch as $signal) {
			while ($signal[0] > $isHigherThanSMA[$ctr][0]) {
				$ctr++;
			}

			if ($isHigherThanSMA[$ctr][1]) {
				$filteredSignals[$ctrFilt++] = $signal;
			}
		}

		//echo json_encode($filteredSignals);
		return $filteredSignals;
	}

	// This function further filters the trade signals from the simple moving average
	// Inputs:
	//	- $ohlc -> The Open, High, Low, Close prices
	//	- $smaBuy -> The Buy Signals from the SMA which will be used as the basis for
	//			the computation of the stop values
	//	- $atr -> Average True Range values. Used to calculate the Stop Values at any
	//			given time of the trade.
	function codesword_smaEntryATRstopTradeDetector($ohlc, $smaBuy, $atr, 
					$riskFactor = 2, $profitFactor = 4, $enProfitComputation = true) {
		// echo "OHLC:" . json_encode($ohlc) . "<Br/><Br/>";
		// echo "SMA Buy:" . json_encode($smaBuy) . "<Br/><Br/>";
		// echo "ATR:" . json_encode($atr) . "<Br/><Br/>";

		$allSignals = [];
		$numBuySignals = count($smaBuy);
		$numOhlc = count($ohlc);

		if ($enProfitComputation) {
			echo "buy signals: $numBuySignals, ohlc: $numOhlc <Br/>";
		}
		else {
			
		}

		$stopValues = [];

		//The sell signals will include information why a sell should be done:
		//	1. Stop Loss
		//	2. Take Profits
		$sellSignals = [];
		$posOhlc = 0;
		$posStop = 0;

		for ($i=0; $i < $numBuySignals; $i++) { 
			$entryTS = $smaBuy[$i][0];
			$atrDeductible = 0;
			$curStop = 0;
			$targetProfitablePrice = 0;

			// Find the ATR value for the entry timestamp
			foreach ($atr as $curAtr) {
				if ($curAtr[0] == $entryTS) {
					$atrDeductible = round($curAtr[1], 3);
					// echo "ATR Deductible: $atrDeductible <Br/>";
					break;		
				}
			}

			// Find the close value for the entry timestamp and compute the
			//	current stop value
			foreach ($ohlc as $curOhlc) {
				if ($curOhlc[0] == $entryTS) {
					$curStop = round($curOhlc[4] - $riskFactor * $atrDeductible, 3);
					$targetProfitablePrice = round($curOhlc[4] + $profitFactor * $atrDeductible, 3);
					// echo "Start Stop Loss Value: $curStop, Profitable Target Price: $targetProfitablePrice <Br/>";
					break;		
				}
			}

			// Initial value assignment of stop values based from entry price
			$stopValues[$posStop][0] = $entryTS;
			$stopValues[$posStop++][1] = $curStop;

			if ($i == $numBuySignals - 1) {
				// enter code here where the buy signal timestamp will no longer be
				//	compared to the "next" buy signal timestamp
				for ($j = $posOhlc; $j < $numOhlc; $j++) {
					// Is the current ohlc timestamp > sma buy timestamp?
					//	if yes, continue with the process
					if ($ohlc[$j][0] > $entryTS) {
						$stopValues[$posStop][0] = $ohlc[$j][0];

						//If the current close is higher than 
						//	the target profitable price (entry + 4 * ATR) then, 
						//	reduce risk to 1 ATR only (close - 1 * ATR)
						if ($ohlc[$j][1] > $targetProfitablePrice) {
							$computeStop = round($ohlc[$j][4] - ($riskFactor * $atrDeductible) / 2, 3);
							//$stopValues[$posStop][2] = "Take Profits";
						} 
						else {
							$computeStop = round($ohlc[$j][4] - $riskFactor * $atrDeductible, 3);
						}

						//Compare if computed Stop Value is higher than the curStop
						if ($computeStop > $curStop) {
							$stopValues[$posStop][1] = $computeStop;
							$curStop = $computeStop;
						} 
						else {
							$stopValues[$posStop][1] = $curStop;
						}

						if ($ohlc[$j][1] > $targetProfitablePrice) {
							$stopValues[$posStop][2] = "Take Profits";
						}

						$posOhlc++;
						$posStop++;
					}
				}
			}
			else {
				$nextTS = $smaBuy[$i+1][0];

				for ($j = $posOhlc; $j < $numOhlc; $j++) {
					// Is the current ohlc timestamp > sma buy timestamp?
					//	if yes, continue with the process
					if ( ($ohlc[$j][0] > $entryTS) && ($ohlc[$j][0] < $nextTS) ) {
						$stopValues[$posStop][0] = $ohlc[$j][0];

						//If the current close is higher than 
						//	the target profitable price (entry + 4 * ATR) then, 
						//	reduce risk to 1 ATR only (close - 1 * ATR)
						if ($ohlc[$j][1] > $targetProfitablePrice) {
							$computeStop = round($ohlc[$j][4] - ($riskFactor * $atrDeductible) / 2, 3);
						} 
						else {
							$computeStop = round($ohlc[$j][4] - $riskFactor * $atrDeductible, 3);
						}

						//Compare if computed Stop Value is higher than the curStop
						if ($computeStop > $curStop) {
							$stopValues[$posStop][1] = $computeStop;
							$curStop = $computeStop;
						} 
						else {
							$stopValues[$posStop][1] = $curStop;
						}

						if ($ohlc[$j][1] > $targetProfitablePrice) {
							$stopValues[$posStop][2] = "Take Profits";
						}

						$posOhlc++;
						$posStop++;
					}
					// Is the current ohlc timestamp = next sma buy timestamp?
					//	if yes, break
					elseif ($ohlc[$j][0] >= $nextTS) {
						// echo "Calculate STOP signal for next entry timestamp: $nextTS <Br/>";
						break;
					}
				}
			}
		}

		$allSignals["buy"] = $smaBuy;
		if ($enProfitComputation) {
			echo "<Br/>Stop Values: " . json_encode($stopValues) . "<Br/><Br/>";
			echo "SMA Buy:" . json_encode($smaBuy) . "<Br/><Br/>";
		}

		//Determine the Sell Signals
		// Find the offset between stop values and ohlc
		$offsetOhlc = 0;
		foreach ($ohlc as $curOhlc) {
			if ($curOhlc[0] == $stopValues[0][0]) {
				if ($enProfitComputation) {
					echo "Offset between OHLC and Stop Values: $offsetOhlc <Br/><Br/>";
				}

				break;
			} 
			else {
				$offsetOhlc++;
			}
		}

		$ctrSellSignals = 0;
		for ($i=0; $i < count($stopValues); $i++) { 
			//	Compare the Stop Values and the Low for the Day
			if ($ohlc[$i + $offsetOhlc][3] <= $stopValues[$i][1]) {
				//Bullish Candlestick, positive value for Close/Current - Open
				//	and close/current price that is higher than yesterday's close
				if ( ($ohlc[$i + $offsetOhlc][4] - $ohlc[$i + $offsetOhlc][1]) &&
					($ohlc[$i + $offsetOhlc][4] >= $ohlc[$i + $offsetOhlc - 1][4]) ) {
					continue;
				}
				else {
					$sellSignals[$ctrSellSignals][0] = $stopValues[$i][0];
					$sellSignals[$ctrSellSignals][1] = "sell: cut losses";

					$ctrSellSignals++; 
				}
			} 
			else {
				try {
					$tempSellMessage = isset($stopValues[$i][2]) ? $stopValues[$i][2] : null;
					if ($tempSellMessage != null) {
						$sellSignals[$ctrSellSignals][0] = $stopValues[$i][0];
						$sellSignals[$ctrSellSignals][1] = "sell: profit taking";

						$ctrSellSignals++; 
					}
				} 
				catch (Exception $e) {
					continue;
				}
			}
			
		}

		$allSignals["sell"] = $sellSignals;
		if ($enProfitComputation) {
			echo "ATR Sells: " . json_encode($sellSignals) . "<Br/><Br/>";
		}

		if ($enProfitComputation) {
			//Calculate the profit based on the buy and sell signals gathered
			$startingCapital = 100000;
			$capital = $startingCapital;
			$remainingCapital = 0;
			$ohlcPos = 0;
			$sellPos = 0;
			$priceBuy = 0;
			$volBought = 0;

			//Sell match signal
			$lastTradeAction = null;

			foreach ($smaBuy as $sBuy) {
				//Date of buy
				$dateBuy = $sBuy[0];

				//Find the price of Buy Date from OHLC
				for ($i = $ohlcPos; $i < count($ohlc); $i++) { 
					if ($ohlc[$i][0] == $dateBuy) {
						//calculate how much volume you are able to buy
						$priceBuy = $ohlc[$i][4];
						$lotSize = 0;

						//calculate lot size depending on price
						if ($priceBuy < 1) {
							$lotSize = 10000;
						} 
						elseif ($priceBuy < 10) {
							$lotSize = 1000;
						}
						elseif ($priceBuy < 100) {
							$lotSize = 100;
						}
						elseif ($priceBuy < 1000) {
							$lotSize = 10;
						}
						else {
							$lotSize = 5;
						}
						
						$priceBuyLot = $lotSize * $priceBuy;
						// $remainingCapital = $capital % $priceBuyLot;
						$remainingCapital = fmod($capital, $priceBuyLot);
						$volBought = ($capital - $remainingCapital) * $lotSize / $priceBuyLot;

						// $volBought = (int)($capital / $priceBuy);
						// $remainingCapital = $capital - ($priceBuy * $volBought);

						echo "Price Bought: $priceBuy, Volume Bought: $volBought, 
								Remaining Capital: $remainingCapital, Lot Size: $lotSize <Br/>";

						$ohlcPos = $i;
						$lastTradeAction = "buy";
						break;
					}
				}

				//run through the sell signals and sell stock on first "cut losses"
				for ($i = $sellPos; $i < count($sellSignals); $i++) { 
					if ($lastTradeAction == "sell") {
						break;
					}

					//Date of sell
					$dateSell = $sellSignals[$i][0];
					$statusSell = $sellSignals[$i][1];

					//Sell if date sell is greater than date buy
					//and if sell status is "cut losses"
					if ( ($dateSell > $dateBuy) && ($statusSell == "sell: cut losses") ) {
						//Find the price of Sell Date from OHLC
						for ($i = $ohlcPos; $i < count($ohlc); $i++) { 
							if ($ohlc[$i][0] == $dateSell) {
								//calculate how much volume you are able to buy
								$priceSell = $ohlc[$i][4];
								//Calculate Capital gained from selling
								$sellGains = ($priceSell * $volBought) * (1 - 0.0109);
								$volBought = 0;

								$capital = $remainingCapital + $sellGains;
								// $volBought = (int)($capital / $priceBuy);
								// $remainingCapital = $capital - ($priceBuy * $volBought);

								echo "Price Sold: $priceSell, Sell Capital: $sellGains, Total Capital: $capital<Br/>";

								$ohlcPos = $i;
								$lastTradeAction = "sell";
								break;
							}
						}
					}
				}
			}

			//You've already sold your positions
			if ($volBought == 0) {
				$totalEquity = $capital;

				//Percent Gain computation
				$percGain = (($totalEquity / $startingCapital) - 1) * 100;

				//Remaining Volume you are currently holding
				echo "<Br/>Total Equity: $totalEquity, Percent Gain: $percGain%<Br/>";
			} 
			//You are currently holding some stocks
			else {
				//Get current price of stock
				$curPrice = $ohlc[count($ohlc) - 1][4];
				//Total Equity
				$totalEquity = $remainingCapital + ($volBought * $curPrice);

				//Percent Gain computation
				$percGain = (($totalEquity / $startingCapital) - 1) * 100;

				//Remaining Volume you are currently holding
				echo "<Br/>Volume Holding: $volBought, Cur Price: $curPrice, 
					Total Equity: $totalEquity, Percent Gain: $percGain%<Br/>";
			}
		}
		else {
			//TODO: Output Signal Generated from SMA and ATR
			echo json_encode($allSignals);

			return $allSignals;
		}
	}	
?>