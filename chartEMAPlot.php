<!DOCTYPE html>
<html>
<head>
	<title>Stock Price Plot</title>
<!-- 	<script src="https://code.jquery.com/jquery-1.11.3.min.js"></script>
	<script src="https://code.highcharts.com/stock/highstock.js"></script>
	<script src="https://code.highcharts.com/stock/modules/exporting.js"></script> -->

    <script src="js/jquery.min.js"></script>
    <script src="js/highstock.js"></script>
    <script src="js/exporting.js"></script>  
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
    dataURL = dynamicDataURL() + "getData.php?company="+company+"&timerange=3y&chart=ema&period=8&dataorg=highchart";
    $.getJSON(dataURL, function (data) { 
    //$.getJSON('http://localhost/stana/getData.php?company=smc&timerange=3y&chart=sma&period=50&dataorg=highchart', function (data) {
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
                name : 'SMA',
                data : data[0],
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