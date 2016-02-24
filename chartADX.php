<!DOCTYPE html>
<html>
<head>
	<title>ADX Plot</title>

<?php 
    $serverName = $_SERVER['SERVER_NAME'];
?>

<?php if ($serverName == 'localhost'): ?>
    <script src="js/jquery-1.11.3.min.js"></script>
    <script src="js/highstock.js"></script>
    <script src="js/exporting.js"></script>
<?php else: ?>
    <script src="https://code.jquery.com/jquery-1.11.3.min.js"></script>
    <script src="https://code.highcharts.com/stock/highstock.js"></script>
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
    dataURL = dynamicDataURL() + "getData.php?company="+company+"&timerange=3y&chart=adx&dataorg=highchart";
    $.getJSON(dataURL, function (data) {
    	testData = data;

        // Create the chart
        $('#container').highcharts('StockChart', {
            rangeSelector : {
                selected : 1
            },

            title : {
                text : company.toUpperCase() + ' ADX Plot'
            },

            yAxis : {
                title: {
                    text: 'ADX Plot'
                },
                plotLines: [{
                    value: 20,
                    color: 'yellow',
                    dashStyle: 'shortdash',
                    width: 3,
                    label: {
                        text: 'Non-trend'
                    }
                }, {
                    value: 50,
                    color: 'orange',
                    dashStyle: 'shortdash',
                    width: 3,
                    label: {
                        text: 'Strong Trend'
                    }
                }, {
                    value: 75,
                    color: 'red',
                    dashStyle: 'shortdash',
                    width: 3,
                    label: {
                        text: 'Very Strong Trend'
                    }
                }]
            },

            series : [{
                name : 'ADX Plot',
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