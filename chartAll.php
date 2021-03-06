<!DOCTYPE html>
<html>
<head>
	<title>Stock Analyzer Tools</title>

<?php 
    $serverName = $_SERVER['SERVER_NAME'];
?>

<?php if ($serverName == 'localhost'): ?>
    <script src="js/jquery-1.11.3.min.js"></script>
    <script src="js/highstock.js"></script>
    <script src="js/highcharts-more.js"></script>
    <script src="js/exporting.js"></script>
<?php else: ?>
    <script src="https://code.jquery.com/jquery-1.11.3.min.js"></script>
    <script src="https://code.highcharts.com/stock/highstock.js"></script>
    <script src="https://code.highcharts.com/stock/highcharts-more.js"></script>
    <script src="https://code.highcharts.com/stock/modules/exporting.js"></script>
<?php endif; ?>

    <script src="autocomplete.js"></script>

    <style type="text/css">
        #keyword {
            width: 200px;
            font-size: 1em;
        }

        #results {
            width: 204px;
            position: absolute;
            border: 1px solid #c0c0c0;
            z-index: 3;
        }

        #results .item {
            padding: 3px;
            background:rgba(255,255,255,1);
            font-family: Helvetica;
            border-bottom: 1px solid #c0c0c0;
            z-index: 3;
        }

        #results .item:last-child {
            border-bottom: 0px;
            z-index: 3;
        }

        #results .item:hover {
            background-color: #f2f2f2;
            cursor: pointer;
            z-index: 3;
        }
    </style>
</head>
<body>
    <input type="text" value="" placeholder="Enter Stock Quote..." id="keyword" list="datalist">
    <input type="checkbox" id="enBuySellSignals" value="enBuySellSignals">Enable Buy/Sell Signals
    <Br>
    <div id="typeCharts">
        <!-- Insert your checkboxes here dynamically -->
    </div>

    <div id="results">
        <!-- <div onclick="alert(this.innerHTML)">test value</div> -->
    </div>

	<div id="container" style="height: 680px; min-width: 310px"></div>
</body>
<script src="chartCompleteSuperimpose.js"></script>
</html>