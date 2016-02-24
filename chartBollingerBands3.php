<!DOCTYPE html>
<html>
<head>
	<title>Bollinger Bands Plot</title>

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

    bollinger2URL = dynamicDataURL() + 'getData.php?company='+company+'&timerange=3y&chart=bollinger3&dataorg=highchart&ensig=true';
    $.getJSON(bollinger2URL, function (data) {
    	testData = data;
        var allData = data;
        var prices = data[0];
        var signalsData = data[1];

        // split the data set into sma, bollinger upper band, bollinger lower band
        var sma = [],
            ohlc = [],
            signals = [];

            bollingerBandsSD1 = [],
            bollingerBandsSD2 = [],
            dataLength = prices.length,
            // set the allowed units for data grouping
            groupingUnits = [[
                'week',                         // unit name
                [1]                             // allowed multiples
            ], [
                'month',
                [1, 2, 3, 4, 6]
            ]],

            i = 0;

        for (i; i < dataLength; i += 1) {
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

        var tradeSignals = [];
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

            tradeSignals[j] = {x: tempTS, title: tempTitle, fillColor: tempFillColor};

/*            signals.push([
                signalsData[j][0],  // the date
                signalsData[j][1],  // the signal
                signalsData[j][2],  // the description
            ]);*/
        }
        testData2 = tradeSignals;

        // Create the chart
        $('#container').highcharts('StockChart', {
            rangeSelector : {
                selected : 2
            },

            title : {
                text : company.toUpperCase() + ' Bollinger Bands Plot'
            },

            yAxis : [{
                labels: {
                    align: 'right',
                    x: -3
                },
                title: {
                    text: 'Price'
                },
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
                data : tradeSignals,
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
            }]
        });
    });

}

plotStock();
</script>
</html>