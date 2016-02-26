<!DOCTYPE html>
<html>
<head>
	<title>Entry Finder Plot</title>

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

    <script src="js/dynamicpath.js"></script>
</head>
<body>
	<div id="container" style="height: 600px; min-width: 310px"></div>
</body>

<?php
    //select the company you want to view
    if(isset($_GET['company'])) {
        $company = $_GET['company']."";
    }
    else {
        //echo "ERROR: No Company was selected<Br/>";
        $company = "smc";
    }
?>

<script type="text/javascript">
var testData, testData2;

function plotStock () {
    var company = "<?php echo $company; ?>";
    var ohlc = [];

    var timerange = '3y';

    stochasticURL = dynamicDataURL() + 'getData.php?company='+company+'&timerange='+timerange+'&chart=stoch&dataorg=highchart';

    bollinger3URL = dynamicDataURL() + 'getData.php?company='+company+'&timerange='+timerange+'&chart=bollinger3&dataorg=highchart&ensig=true';

    var prices;
    var signalsData;

    // split the data set into sma, bollinger upper band, bollinger lower band
    var sma = [],
        ohlc = [],
        signals = [],
        bollingerBandsSD1 = [],
        bollingerBandsSD2 = [],
        percentK = [],
        percentD = [],
        dataLength,
        // set the allowed units for data grouping
        groupingUnits = [[
            'week',                         // unit name
            [1]                             // allowed multiples
        ], [
            'month',
            [1, 2, 3, 4, 6]
        ]],
        //general counter
        i = 0;

    $.getJSON(stochasticURL, function (data) {
        //Data arrangement for stochastic is timestamp, close, %K, %D
        for (i; i < data.length; i += 1) {
            percentK.push([
                data[i][0], // the date
                data[i][1], // %K
            ]);

            percentD.push([
                data[i][0], // the date
                data[i][2] // %D
            ]);
        }

        testData = percentK;
        testData2 = percentD;

        //get data from the bollinger bands 3
        $.getJSON(bollinger3URL, function (data) {
            prices = data[0];
            signalsData = data[1];

            // split the data set into sma, bollinger upper band, bollinger lower band
            sma = [];
            ohlc = [];
            signals = [];
            bollingerBandsSD1 = [];
            bollingerBandsSD2 = [];

            i = 0;

            for (i; i < prices.length; i += 1) {
                ohlc.push([
                    prices[i][0], // the date
                    prices[i][1], // open
                    prices[i][2], // high
                    prices[i][3], // low
                    prices[i][4] // close
                ]);

                sma.push([
                    prices[i][0], // the date
                    prices[i][5], // sma
                ]);

                bollingerBandsSD1.push([
                    prices[i][0], // the date
                    prices[i][6], // the upper band
                    prices[i][7], // the lower band
                ]);

                bollingerBandsSD2.push([
                    prices[i][0], // the date
                    prices[i][8], // the upper band
                    prices[i][9], // the lower band
                ]);
            }

            var tradeSignalsBollinger = [];
            var tempTS, tempTitle, tempFillColor;
            for (var j = 0; j < signalsData.length; j += 1) {
                tempTS = signalsData[j][0];
                tempTitle = signalsData[j][1];

                if (tempTitle == "buy") {
                    tempFillColor = "yellowgreen";
                }
                else if(tempTitle == "sell"){
                    tempFillColor = "red";
                }

                tradeSignalsBollinger[j] = {x: tempTS, title: tempTitle, fillColor: tempFillColor};
            }

            // Create the chart
            $('#container').highcharts('StockChart', {
                rangeSelector : {
                    selected : 2
                },

                title : {
                    text : company.toUpperCase() + ' Entry Finder Plots'
                },

                yAxis : [{
                    labels: {
                        align: 'right',
                        x: -3
                    },
                    title: {
                        text: 'Price'
                    },
                    height: '80%',
                    lineWidth: 2
                }, {
                    labels: {
                        align: 'right',
                        x: -3
                    },
                    title: {
                        text: 'Oscillator'
                    },
                    top: '80%',
                    height: '20%',
                    offset: 0,
                    lineWidth: 2
                }],

                series : [
                {
                    type: 'areasplinerange',
                    name: 'bb sd 2',
                    data: bollingerBandsSD2,
                    color: 'yellow'
                },
                {
                    type: 'areasplinerange',
                    name: 'bb sd 1',
                    data: bollingerBandsSD1,
                    color: 'orange'
                },
                {
                    name: 'SMA',
                    data: sma,
                    color: 'black'
                },
                {
                    type: 'candlestick',
                    name: 'Candlestick',
                    id : "candlestick",
                    data: ohlc
                },
                {
                    type : 'flags',
                    data : tradeSignalsBollinger,
                    onSeries: "candlestick",
                    shape: 'squarepin',
                    width: 16,
                    style: { // text style
                        color: 'white'
                    },
                    states: {
                        hover: {
                            fillColor: '#yellowgreen' // darker
                        }
                    }
                },
                {
                    name: '%D',
                    data: percentD,
                    yAxis: 1,
                    color: '#760E0E'
                },
                {
                    name: '%K',
                    data: percentK,
                    yAxis: 1,
                    color: '#B0CF71'
                }]
            });
        });
        //end of the Bollinger Bands 3 call

    });
    //end of the Stochastic Call
}

$("#container").css("height", (screen.height) * 0.95 );

plotStock();
</script>
</html>