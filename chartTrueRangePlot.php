<!DOCTYPE html>
<html>
<head>
	<title>Stock Price Plot</title>
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

function plotStock () {
    var company = "<?php echo $company; ?>";
    dataURL = dynamicDataURL() + "getData.php?company="+company+"&timerange=3y&chart=atr&dataorg=highchart&ensig=true";
    $.getJSON(dataURL, function (data) {   

    //$.getJSON('https://www.highcharts.com/samples/data/jsonp.php?filename=aapl-c.json&callback=?', function (data) {
    //$.getJSON('http://localhost/stana/getData.php?company=smc&timerange=3y&chart=atr&dataorg=highchart', function (data) {
    	testData = data;

        // Create the chart
        $('#container').highcharts('StockChart', {
            rangeSelector : {
                selected : 1
            },

            title : {
                text : 'True Range'
            },

            series : [{
                name : 'True Range',
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