<!DOCTYPE html>
<html>
<head>
	<title>Stock Candlestick Plot</title>
	<script src="https://code.jquery.com/jquery-1.11.3.min.js"></script>
	<script src="https://code.highcharts.com/stock/highstock.js"></script>
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

function plotStock() {
    var company = "<?php echo $company; ?>";
    dataURL = dynamicDataURL() + "getData.php?company="+company+"&timerange=3y&chart=ohlc&dataorg=highchart";
    $.getJSON(dataURL, function (data) {
        testData = data;

        // split the data set into ohlc and volume
        var ohlc = [],
            volume = [],
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
            ohlc.push([
                data[i][0], // the date
                data[i][1], // open
                data[i][2], // high
                data[i][3], // low
                data[i][4] // close
            ]);

            volume.push([
                data[i][0], // the date
                data[i][5] // the volume
            ]);
        }


        // create the chart
        $('#container').highcharts('StockChart', {

            rangeSelector: {
                selected: 1
            },

            title: {
                text: 'SMC Historical'
            },

            yAxis: [{
                labels: {
                    align: 'right',
                    x: -3
                },
                title: {
                    text: 'OHLC'
                },
                height: '75%',
                lineWidth: 2
            }, {
                labels: {
                    align: 'right',
                    x: -3
                },
                title: {
                    text: 'Volume'
                },
                top: '80%',
                height: '20%',
                offset: 0,
                lineWidth: 2
            }],

            series: [{
                type: 'candlestick',
                name: 'SMC',
                data: ohlc
            }, {
                type: 'column',
                name: 'Volume',
                data: volume,
                yAxis: 1
            }]
        });
    });
}

function plotStock2 () {
    $.getJSON('http://localhost/analyzer/getData.php?company=smc&timerange=3y&chart=close&dataorg=highchart', function (data) {
    	testData = data;

        // Create the chart
        $('#container').highcharts('StockChart', {


            rangeSelector : {
                selected : 1
            },

            title : {
                text : 'SMC Stock Price'
            },

            series : [{
                name : 'SMC Price',
                data : data,
                tooltip: {
                    valueDecimals: 2
                }
            }]
        });
    });
}

plotStock();
</script>
</html>