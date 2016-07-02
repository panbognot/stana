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
        $company = strtolower($_GET['company']."");
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

    var timerange = '10y';

    macdURL = dynamicDataURL() + 'getData.php?company='+company+
            '&timerange='+timerange+'&chart=macd&dataorg=highchart';

    stochasticURL = dynamicDataURL() + 'getData.php?company='+company+
            '&timerange='+timerange+'&chart=stomacd&dataorg=highchart&ensig=true';

    bollinger3URL = dynamicDataURL() + 'getData.php?company='+company+
            '&timerange='+timerange+'&chart=bollinger3&dataorg=highchart&ensig=true';

    var prices;
    var signalsBollinger = [],
        signalsStoch = [];

    // split the data set into sma, bollinger upper band, bollinger lower band
    var sma = [],
        ohlc = [],
        signals = [],
        bollingerBandsSD1 = [],
        bollingerBandsSD2 = [],
        percentK = [],
        percentD = [],
        macd = [],
        signal = [],
        divergence = [],
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

    //get data for stochastic chart
    $.getJSON(stochasticURL, function (dataStoMACD) {
        var dataStoch = dataStoMACD[0];
        var dataStochSigs = dataStoMACD[1];

        //Data arrangement for stochastic is timestamp, close, %K, %D
        for (i=0; i < dataStoch.length; i += 1) {
            percentK.push([
                dataStoch[i][0], // the date
                dataStoch[i][1], // %K
            ]);

            percentD.push([
                dataStoch[i][0], // the date
                dataStoch[i][2] // %D
            ]);
        }

        var tempTS, tempTitle, tempFillColor;
        for (var j = 0; j < dataStochSigs.length; j += 1) {
            tempTS = dataStochSigs[j][0];
            tempTitle = dataStochSigs[j][1];

            if (tempTitle == "buy") {
                tempFillColor = "yellowgreen";
            }
            else if(tempTitle == "sell"){
                tempFillColor = "red";
            }

            signalsStoch[j] = {x: tempTS, title: tempTitle, fillColor: tempFillColor};
        }        
        testData = signalsStoch;

        //get data for macd chart    
        $.getJSON(macdURL, function (dataMacd) {
            //Data for MACD
            for (i=0; i < dataMacd.length; i += 1) {
                macd.push([
                    dataMacd[i][0], // the date
                    dataMacd[i][1], // macd
                ]);

                signal.push([
                    dataMacd[i][0], // the date
                    dataMacd[i][2], // signal
                ]);

                divergence.push([
                    dataMacd[i][0], // the date
                    dataMacd[i][3] // the divergence
                ]);
            }

            //get data for the bollinger bands 3
            $.getJSON(bollinger3URL, function (data) {
                prices = data[0];
                signalsBollinger = data[1];

                // split the data set into sma, bollinger upper band, bollinger lower band
                sma = [];
                ohlc = [];
                signals = [];
                bollingerBandsSD1 = [];
                bollingerBandsSD2 = [];

                for (i=0; i < prices.length; i += 1) {
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
                for (var j = 0; j < signalsBollinger.length; j += 1) {
                    tempTS = signalsBollinger[j][0];
                    tempTitle = signalsBollinger[j][1];

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
                        height: '66%',
                        lineWidth: 2
                    }, {
                        labels: {
                            align: 'right',
                            x: -3
                        },
                        title: {
                            text: 'Oscillator'
                        },
                        top: '67%',
                        height: '16%',
                        offset: 0,
                        lineWidth: 2,
                        plotLines: [{
                            value: 20,
                            color: 'green',
                            dashStyle: 'shortdash',
                            width: 1,
                            label: {
                                text: 'Oversold'
                            }
                        }, {
                            value: 80,
                            color: 'red',
                            dashStyle: 'shortdash',
                            width: 1,
                            label: {
                                text: 'Overbought'
                            }
                        }]
                    }, {
                        labels: {
                            align: 'right',
                            x: -3
                        },
                        title: {
                            text: 'macd'
                        },
                        top: '84%',
                        height: '16%',
                        offset: 0,
                        lineWidth: 2
                    }],

                    series : [
                    {
                        type: 'areasplinerange',
                        name: 'bb sd 2',
                        data: bollingerBandsSD2,
                        color: 'yellow'
                    }, {
                        type: 'areasplinerange',
                        name: 'bb sd 1',
                        data: bollingerBandsSD1,
                        color: 'orange'
                    }, {
                        name: 'SMA',
                        data: sma,
                        color: 'black'
                    }, {
                        type: 'candlestick',
                        name: 'Candlestick',
                        id : "candlestick",
                        data: ohlc
                    }, {
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
                    }, {
                        name: '%K',
                        id: 'stochastic',
                        data: percentK,
                        yAxis: 1,
                        color: '#760E0E'
                    }, {
                        name: '%D',
                        data: percentD,
                        yAxis: 1,
                        color: '#B0CF71'
                    }, {
                        type : 'flags',
                        data : signalsStoch,
                        yAxis: 1,
                        onSeries: 'stochastic',
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
                    }, {
                        //type: 'spline',
                        name: 'MACD',
                        data: macd,
                        yAxis: 2,
                    }, {
                        type: 'column',
                        name: 'Divergence',
                        data: divergence,
                        yAxis: 2,
                    }, {
                        //type: 'spline',
                        name: 'Signal',
                        data: signal,
                        yAxis: 2,
                    }]
                });
            });
            //end of the Bollinger Bands 3 call

        });
        //end of the MACD call 

    });
    //end of the Stochastic Call
}

$("#container").css("height", screen.height * 1.2 );

plotStock();
</script>
</html>