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
    dataURL = dynamicDataURL() + 'getData.php?company='+company+'&timerange=3y&chart=bollinger&dataorg=highchart';
    $.getJSON(dataURL, function (data) {
    	testData = data;

        // split the data set into sma, bollinger upper band, bollinger lower band
        var sma = [],
            bollingerBands = [],
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

            bollingerBands.push([
                data[i][0], // the date
                data[i][2], // the upper band
                data[i][3], // the lower band
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

            series : [{
                name: 'SMA',
                data: sma,
                color: 'black'
            }, 
            {
                type: 'areasplinerange',
                name: 'Bollinger Bands',
                data: bollingerBands,
                color: 'turquoise'
            }]
        });
    });
}

plotStock();
</script>
</html>