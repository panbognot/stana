<!DOCTYPE html>
<html>
<head>
	<title>Bollinger Bands Plot</title>
	<script src="https://code.jquery.com/jquery-1.11.3.min.js"></script>
	<script src="https://code.highcharts.com/stock/highstock.js"></script>
    <script src="https://code.highcharts.com/stock/highcharts-more.js"></script>
	<script src="https://code.highcharts.com/stock/modules/exporting.js"></script>
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

    ohlcURL = dynamicDataURL() + 'getData.php?company='+company+'&timerange=3y&chart=ohlc&dataorg=highchart';
    $.getJSON(ohlcURL, function (dataOHLC) {
        var i = 0;

        for (i; i < dataOHLC.length; i += 1) {
            ohlc.push([
                dataOHLC[i][0], // the date
                dataOHLC[i][1], // open
                dataOHLC[i][2], // high
                dataOHLC[i][3], // low
                dataOHLC[i][4] // close
            ]);
        }        

        bollinger2URL = dynamicDataURL() + 'getData.php?company='+company+'&timerange=3y&chart=bollinger2&dataorg=highchart';
        $.getJSON(bollinger2URL, function (data) {
        	testData = data;

            // split the data set into sma, bollinger upper band, bollinger lower band
            var sma = [],
                bollingerBandsSD1 = [],
                bollingerBandsSD2 = [],
                dataLength = data.length,
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
                sma.push([
                    data[i][0], // the date
                    data[i][1], // sma
                ]);

                bollingerBandsSD1.push([
                    data[i][0], // the date
                    data[i][2], // the upper band
                    data[i][3], // the lower band
                ]);

                bollingerBandsSD2.push([
                    data[i][0], // the date
                    data[i][4], // the upper band
                    data[i][5], // the lower band
                ]);
            }

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
                    data: ohlc
                }]
            });
        });
    });
}

plotStock();
</script>
</html>