<!DOCTYPE html>
<html>
<head>
	<title>Stock Stochastic Plot</title>
	<script src="https://code.jquery.com/jquery-1.11.3.min.js"></script>
	<script src="https://code.highcharts.com/stock/highstock.js"></script>
	<script src="https://code.highcharts.com/stock/modules/exporting.js"></script>

</head>
<body>
	<div id="container" style="height: 600px; min-width: 310px"></div>
</body>
<script type="text/javascript">
var testData, testData2;

function plotStock() {
    $.getJSON('http://localhost/stana/getData.php?company=smc&timerange=3y&chart=stoch&dataorg=highchart', function (data) {
        testData = data;

        // split the data set into %K, %D
        var percentK = [],
            percentD = [],
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

        //Data arrangement for stochastic is timestamp, close, %K, %D
        for (i; i < dataLength; i += 1) {
            percentK.push([
                data[i][0], // the date
                data[i][1], // %K
            ]);

            percentD.push([
                data[i][0], // the date
                data[i][2] // %D
            ]);
        }


        // create the chart
        $('#container').highcharts('StockChart', {

            rangeSelector: {
                selected: 1
            },

            title: {
                text: 'SMC Stochastic Oscillator'
            },

            yAxis: [{
                labels: {
                    align: 'right',
                    x: -3
                },
                title: {
                    text: 'Closing Price'
                },
                lineWidth: 2
            }],

            series: [{
                name: '%K',
                data: percentK
            }, {
                name: '%D',
                data: percentD
            }]
        });
    });
}

plotStock();
</script>
</html>