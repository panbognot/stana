<!DOCTYPE html>
<html>
<head>
	<title>Stock On Balance Volume Plot</title>
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
    dataURL = dynamicDataURL() + "getData.php?company="+company+"&timerange=3y&chart=obv&dataorg=highchart";
    $.getJSON(dataURL, function (data) {
        testData = data;

        // split the data set into close, volume, obv
        var closePrice = [],
            volume = [],
            obv = [],
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
            closePrice.push([
                data[i][0], // the date
                data[i][1], // close
            ]);

            volume.push([
                data[i][0], // the date
                data[i][2] // the volume
            ]);

            obv.push([
                data[i][0], // the date
                data[i][3] // obv
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
                    text: 'Close Price'
                },
                height: '49%',
                lineWidth: 2
            }, {
                labels: {
                    align: 'right',
                    x: -3
                },
                title: {
                    text: 'Volume'
                },
                top: '51%',
                height: '49%',
                offset: 0,
                lineWidth: 2
            }, {
                labels: {
                    align: 'left',
                    x: 3
                },
                title: {
                    text: 'On Balance Volume'
                },
                top: '51%',
                height: '49%',
                offset: 0,
                lineWidth: 2
            }],

            series: [{
                name: 'SMC',
                data: closePrice
            }, {
                type: 'column',
                name: 'Volume',
                data: volume,
                yAxis: 1
            }, {
                name: 'On Balance Volume',
                data: obv,
                yAxis: 2
            }]
        });
    });
}

plotStock();
</script>
</html>