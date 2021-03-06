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
    dataURL = dynamicDataURL() + "getData.php?company="+company+"&timerange=3y&chart=rsi&dataorg=highchart";
    $.getJSON(dataURL, function (data) {
    	testData = data;

        // Create the chart
        $('#container').highcharts('StockChart', {


            rangeSelector : {
                selected : 1
            },

            title : {
                text : 'SMC Relative Strength Index'
            },

            yAxis : {
                title: {
                    text: 'RSI'
                },
                plotLines: [{
                    value: 20,
                    color: 'green',
                    dashStyle: 'shortdash',
                    width: 3,
                    label: {
                        text: 'Oversold'
                    }
                }, {
                    value: 80,
                    color: 'red',
                    dashStyle: 'shortdash',
                    width: 3,
                    label: {
                        text: 'Overbought'
                    }
                }]
            },

            series : [{
                name : 'RSI',
                data : data,
                type : 'areaspline',
                threshold : null,
                tooltip: {
                    valueDecimals: 2
                },
                fillColor : {
                    linearGradient : {
                        x1: 0,
                        y1: 0,
                        x2: 0,
                        y2: 1
                    },
                    stops : [
                        [0, Highcharts.getOptions().colors[0]],
                        [1, Highcharts.Color(Highcharts.getOptions().colors[0]).setOpacity(0).get('rgba')]
                    ]
                }
            }]
        });
    });
}

plotStock();
</script>
</html>