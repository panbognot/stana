<?php 
	require_once('connectDB.php');
	require_once('dataBasicPlots.php');
	require_once('dataMACD.php');
	require_once('dataRSI.php');
	require_once('dataStochasticOscillator.php');
	require_once('dataOBV.php');
	require_once('dataSMA.php');
	require_once('dataEMA.php');
	require_once('dataTR.php');
	require_once('dataADX.php');
	require_once('dataBollinger.php');
	require_once('dataAccountManagement.php');

	//combined from SMA and ATR
	require_once('dataSMAentryATRstop.php');

	//initialize variables
	$debug_mode = false;
	$toDate;
	$fromDate;
	$dataorg;

	$isCmd = false;
	$cmdCompany;
	$cmdChart;
	$cmdPeriod;
	$cmdTimerange;
	$cmdEnProfit;

	//Only Useful for the command line calls
	if (isset($argv)) {
		if (count($argv) > 1) {
			$isCmd = true;

			try {
				$cmdCompany = isset($argv[1]) ? $argv[1] : "smc";
				$cmdChart = isset($argv[2]) ? $argv[2] : "smaentryatrstop";
				$cmdPeriod = isset($argv[3]) ? $argv[3] : 20;
				$cmdTimerange = isset($argv[4]) ? $argv[4] : "6m";
				$cmdEnProfit = isset($argv[5]) ? $argv[5] : "false";
			} 
			catch (Exception $e) {
				echo "ERROR: There are missing variables from command line...";
				return;
			}
		}
	}

	function debug_print($string) {
		global $debug_mode;

		if ($debug_mode) {
			echo "$string";
		}
	}

	function getTimeRange($timerange) {
		global $toDate, $fromDate;

		$toDate = date_create(date("Y-m-d"));
		//echo "current date: ".date("Y-m-d")."<Br>";
		debug_print("to date: " . date_format($toDate,"Y-m-d") . "<Br>");

		$deltaTime = "0 days";

		switch ($timerange) {
		    case "2w":
		        debug_print("2 weeks");
		        $deltaTime = "15 days";
		        break;
		    case "1m":
		        debug_print("1 month");
		        $deltaTime = "1 month";
		        break;
		    case "2m":
		        debug_print("2 months");
		        $deltaTime = "2 months";
		        break; 
		    case "3m":
		        debug_print("3 months");
		        $deltaTime = "3 months";
		        break;
		    case "6m":
		        debug_print("6 months");
		        $deltaTime = "6 months";
		        break;
		    case "1y":
		        debug_print("1 year");
		        $deltaTime = "1 year";
		        break;
		    case "3y":
		        debug_print("3 years");
		        $deltaTime = "3 years";
		        break;
		    case "5y":
		        debug_print("5 years");
		        $deltaTime = "5 years";
		        break;
		    case "10y":
		        debug_print("10 years");
		        $deltaTime = "10 years";
		        break;
		    case "all":
		        debug_print("all data");
		        //lol, this is assuming that there is no company older than 100 years old
		        $deltaTime = "100 years";
		        break;
		    default:
		        echo "no data";
		}

		$fromDate = date_create(date("Y-m-d"));
		date_sub($fromDate, date_interval_create_from_date_string($deltaTime));
		debug_print("<Br>from date: " . date_format($fromDate,"Y-m-d") . "<Br>");

		$fromDate = date_format($fromDate,"Y-m-d");
		$toDate = date_format($toDate,"Y-m-d");
	}

	//get all current price
	if (isset($_GET['allcur'])) {
		if (isset($_GET['lastupdate'])) {
			$lastupdate = $_GET['lastupdate'];

			getAllCurrentDayPrices($lastupdate, $mysql_host, $mysql_database, $mysql_user, $mysql_password);
			return;
		}
		else {
			echo "ERROR: No data for lastupdate<Br>";
			return;
		}
	}
	//select the company you want to view
	elseif(isset($_GET['company']) || $isCmd) {
		$temp = isset($_GET['company']) ? $_GET['company'] : $cmdCompany;
		$company = $temp . "_";
		debug_print("selected company: ".$company."<Br/>");	

		//$company = $_GET['company']."_";
		//debug_print("selected company: ".$_GET['company']."<Br/>");	
	}
	elseif (isset($_GET['keyword'])) {
		$keyword = $_GET['keyword'];
		searchForCompany($keyword, $mysql_host, $mysql_database, $mysql_user, $mysql_password);
	}
	else {
		echo "ERROR: No Company was selected<Br/>";
		return;
	}

	//select the data organization that you want depending on the chart you plan on
	// plotting your data
	if(isset($_GET['dataorg'])) {
		$temp = $_GET['dataorg'];

		switch ($temp) {
			case '1':
			case 'json':
				$dataorg = "json";
				break;
			case '2':
			case 'array':
				$dataorg = "array";
				break;
			case 'array2':
				$dataorg = "array2";
				break;
			case '3':
			case 'highchart':
			case 'highcharts':
				$dataorg = "highchart";
				break;
			
			default:
				$dataorg = "json";
				break;
		}

		debug_print("data organization: ".$dataorg."<Br/>");	
	}
	else {
		$dataorg = "json";
	}

	//select the time range of the company
	if (isset($_GET['timerange']) || $isCmd) {
		$tempTime = isset($_GET['timerange']) ? $_GET['timerange'] : $cmdTimerange;
		//$tempTime = $_GET['timerange'];

		getTimeRange($tempTime);
	}
	//get start & end time only if time range is not selected
	else {
		debug_print("alternative date setting...<Br>");
		if (isset($_GET['start'])) {
			$fromDate = $_GET['start'];
		}
		else {
			//default start date is 2 weeks or 15 days ago
			$fromDate = date_create(date("Y-m-d"));
			date_sub($fromDate, date_interval_create_from_date_string("15 days"));
			$fromDate = date_format($fromDate,"Y-m-d");
		}

		if (isset($_GET['end'])) { 
			$toDate = $_GET['end'];
		}
		else {
			//default end date is current date
			$toDate = date_format(date_create(date("Y-m-d")),"Y-m-d");
		}
	}

	//enable/disable trading signals
	if(isset($_GET['ensig'])) {
		$ensig = $_GET['ensig'];

		if ($ensig == "true") {
			$ensig = true;
		}
		elseif ($ensig == "latest") {
			//get the latest buy/sell signal only
			$ensig = "latest";
		}
		else {
			$ensig = false;
		}

		debug_print("Production of Trade Signals: ".$_GET['ensig']."<Br/>");	
	}
	else {
		$ensig = false;
		debug_print("Production of Trade Signals: false<Br/>");	
	}

	//Enable/disable the profits computation
	if(isset($_GET['enprofit']) || $isCmd) {
		$tempEnprofit = isset($_GET['enprofit']) ? $_GET['enprofit'] : $cmdEnProfit;
		//$enprofit = $_GET['enprofit'];

		if ($tempEnprofit == "true") {
			$enprofit = true;
		}
		else {
			$enprofit = false;
		}

		debug_print("Enable the computation of profits: $tempEnprofit<Br/>");	
	}
	else {
		$enprofit = false;
		debug_print("Enable the computation of profits: false<Br/>");	
	}

	if(isset($_GET['chart']) || $isCmd) {
		$chartDataType = isset($_GET['chart']) ? $_GET['chart'] : $cmdChart;

		//$chartDataType = $_GET['chart'];
		debug_print("Date range: from $fromDate to $toDate<Br>");

		switch ($chartDataType) {
			case 'close':
				getClose($company, $fromDate, $toDate, $dataorg, 
					$mysql_host, $mysql_database, $mysql_user, $mysql_password);
				break;
			case 'current':
				getCurrentDayPrices($company, $dataorg, 
					$mysql_host, $mysql_database, $mysql_user, $mysql_password);
				break;
			case 'volume':
				getVolume($company, $fromDate, $toDate, $dataorg, 
					$mysql_host, $mysql_database, $mysql_user, $mysql_password);
				break;
			case 'ohlcur':
				getOHLCurrent($company, $dataorg, 
					$mysql_host, $mysql_database, $mysql_user, $mysql_password);
				break;
			case 'ohlc':
				getOHLC($company, $fromDate, $toDate, $dataorg, 
					$mysql_host, $mysql_database, $mysql_user, $mysql_password);
				break;
			case 'ohlcha':
				getOHLCHA($company, $fromDate, $toDate, $dataorg, 
					$mysql_host, $mysql_database, $mysql_user, $mysql_password);
				break;
			case 'sohlcavv':
				$targetDate = null;
				if (isset($_GET['targetdate'])) {
					$targetDate = isset($_GET['targetdate']) ? $_GET['targetdate'] : null;
				}

				getSingleOHLCAVV($company, $targetDate, $dataorg, 
					$mysql_host, $mysql_database, $mysql_user, $mysql_password);
				break;
			case 'macd':
				getMACD($company, $fromDate, $toDate, $dataorg, 
					$mysql_host, $mysql_database, $mysql_user, $mysql_password);
				break;
			case 'rsi':
				getRSI($company, $fromDate, $toDate, $dataorg, 
					$mysql_host, $mysql_database, $mysql_user, $mysql_password);
				break;
			case 'stoch':
				getStochasticOscillator($company, $fromDate, $toDate, $dataorg, 
					$mysql_host, $mysql_database, $mysql_user, $mysql_password);
				break;
			case 'obv':
				getOnBalanceVolume($company, $fromDate, $toDate, $dataorg, 
					$mysql_host, $mysql_database, $mysql_user, $mysql_password);
				break;
			case 'sma':
				if(isset($_GET['period'])) {
					$period = $_GET['period'];
					getSMA($company, $fromDate, $toDate, $dataorg, $period, $ensig,
						true,
						$mysql_host, $mysql_database, $mysql_user, $mysql_password);
				}
				else {
					echo "Error: No period selected";
				}
				break;
			case 'smaentryatrstop':
				if(isset($_GET['period']) || $isCmd) {
					$period = isset($_GET['period']) ? $_GET['period'] : $cmdPeriod; 

					// $period = $_GET['period'];
					getSMAentryATRstop($company, $fromDate, $toDate, $dataorg, $period, $ensig,
						true, $enprofit,
						$mysql_host, $mysql_database, $mysql_user, $mysql_password);
				}
				else {
					echo "Error: No period selected";
				}
				break;
			case 'ema':
				if(isset($_GET['period'])) {
					$period = $_GET['period'];
					getEMA($company, $fromDate, $toDate, $dataorg, $period, $ensig,
						true,
						$mysql_host, $mysql_database, $mysql_user, $mysql_password);
				}
				else {
					echo "Error: No period selected";
				}
				break;
			case 'wema':
				if(isset($_GET['period'])) {
					$period = $_GET['period'];
					getWEMA($company, $fromDate, $toDate, $dataorg, $period, $ensig,
						true,
						$mysql_host, $mysql_database, $mysql_user, $mysql_password);
				}
				else {
					echo "Error: No period selected";
				}
				break;
			case 'smac':
				getSMACombined($company, $fromDate, $toDate, $dataorg, 
					20, 50, 120, $ensig, 
					$mysql_host, $mysql_database, $mysql_user, $mysql_password);
				break;
			case 'tr':
				getTrueRange($company, $fromDate, $toDate, $dataorg, 
					$mysql_host, $mysql_database, $mysql_user, $mysql_password);
				break;
			case 'atr':
				getAverageTrueRange($company, $fromDate, $toDate, $dataorg, 
					$mysql_host, $mysql_database, $mysql_user, $mysql_password);
				break;
			case 'utr':
				getUndividedTrueRange($company, $fromDate, $toDate, $dataorg, 
					$mysql_host, $mysql_database, $mysql_user, $mysql_password);
				break;
			case 'adx':
				getADX($company, $fromDate, $toDate, $dataorg, 
					$mysql_host, $mysql_database, $mysql_user, $mysql_password);
				break;
			case 'bollinger':
				getBollingerBands($company, $fromDate, $toDate, $dataorg, 
					$mysql_host, $mysql_database, $mysql_user, $mysql_password);
				break;
			case 'bollinger2':
				getBollingerBands2($company, $fromDate, $toDate, $dataorg, 
					$mysql_host, $mysql_database, $mysql_user, $mysql_password);
				break;
			case 'bollinger3':
				getBollingerBands3($company, $fromDate, $toDate, $dataorg, 
					$ensig, 
					$mysql_host, $mysql_database, $mysql_user, $mysql_password);
				break;
			case 'bbw':
				getBBW($company, $fromDate, $toDate, $dataorg, 
					$mysql_host, $mysql_database, $mysql_user, $mysql_password);
				break;
			case 'stomacd':
				getStoMACD($company, $fromDate, $toDate, $dataorg,
					$ensig, 
					$mysql_host, $mysql_database, $mysql_user, $mysql_password);
				break;
			default:
				echo "Chart Type Does Not Exist Yet!!!";
				break;
		}
	}
	else {
		echo "Error: No chart was selected!";
	}
?>