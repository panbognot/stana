<!DOCTYPE html>
<html>
<head>
	<title>Stock Moving Average Convergence Divergence Plot</title>
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
    dataURL = dynamicDataURL() + "getData.php?company="+company+"&timerange=3y&chart=macd&dataorg=highchart";
    $.getJSON(dataURL, function (data) {
        testData = data;

        // split the data set into macd and divergence
        var macd = [],
            signal = [],
            divergence = [],
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
            macd.push([
                data[i][0], // the date
                data[i][1], // macd
            ]);

            signal.push([
                data[i][0], // the date
                data[i][2], // signal
            ]);

            divergence.push([
                data[i][0], // the date
                data[i][3] // the divergence
            ]);
        }


        // create the chart
        $('#container').highcharts('StockChart', {

            rangeSelector: {
                selected: 1
            },

            title: {
                text: 'SMC MACD'
            },

            yAxis: [{
                labels: {
                    align: 'right',
                    x: -3
                },
                title: {
                    text: 'macd'
                },
                lineWidth: 2
            }],

            series: [{
                //type: 'spline',
                name: 'MACD',
                data: macd
            }, {
                type: 'column',
                name: 'Divergence',
                data: divergence
            }, {
                //type: 'spline',
                name: 'Signal',
                data: signal
            }]
        });
    });
}

plotStock();
</script>
</html>