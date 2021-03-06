<!DOCTYPE html>
<html>
<head>
	<title>Stock SMA Combined Signal</title>
<!--     
    <script src="https://code.jquery.com/jquery-1.11.3.min.js"></script>
    <script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
    <script src="https://code.highcharts.com/stock/highstock.js"></script>
    <script src="https://code.highcharts.com/stock/modules/exporting.js"></script>
     -->

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
    dataURL = dynamicDataURL() + "getData.php?company="+company+"&timerange=3y&chart=smac&dataorg=highchart&ensig=true";
    $.getJSON(dataURL, function (data) {   
    	testData = data;
        var chartValues = data[0];

        // split the data set into close and divergence
        var close = [],
            smaShort = [],
            smaMedium = [],
            smaLong = [],
            dataLength = chartValues.length,
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
            var tempVal;

            close.push([
                chartValues[i][0], // the date
                chartValues[i][1], // close
            ]);

            tempVal = chartValues[i][2]; // sma short term
            if (tempVal > 0) {
                smaShort.push([
                    chartValues[i][0], // the date
                    chartValues[i][2], // sma short term
                ]); 
            };

            tempVal = chartValues[i][3]; // sma medium term
            if (tempVal > 0) {
                smaMedium.push([
                    chartValues[i][0], // the date
                    chartValues[i][3], // sma medium term
                ]);
            };

            tempVal = chartValues[i][4]; // sma long term
            if (tempVal > 0) {
                smaLong.push([
                    chartValues[i][0], // the date
                    chartValues[i][4], // sma long term
                ]);
            };
        }

        var tradeSignals = [],
            temp = data[1],
            i = 0;

        var tempTS, tempTitle, tempFillColor;

        for (i=0; i < temp.length; i += 1) {
            tempTS = temp[i][0];
            tempTitle = temp[i][1];

            if (tempTitle == "buy") {
                tempFillColor = "yellowgreen";
            }
            else if(tempTitle == "sell"){
                tempFillColor = "red";
            }

            tradeSignals[i] = {x: tempTS, title: tempTitle, fillColor: tempFillColor};
        }
        testData2 = tradeSignals;

        // Create the chart
        $('#container').highcharts('StockChart', {

            rangeSelector : {
                selected : 1
            },

            title : {
                text : company.toUpperCase() + ' Simple Moving Average Combined'
            },

            series : [{
                name : company.toUpperCase() + ' Close',
                id : "closePrice",
                data : close,
                tooltip: {
                    valueDecimals: 2
                }
            },
            {
                name : 'SMA Short Term',
                data : smaShort,
                tooltip: {
                    valueDecimals: 2
                }
            },
            {
                name : 'SMA Medium Term',
                data : smaMedium,
                tooltip: {
                    valueDecimals: 2
                }
            },
            {
                name : 'SMA Long Term',
                data : smaLong,
                tooltip: {
                    valueDecimals: 2
                }
            },
            {
                type : 'flags',
                data : tradeSignals,
                onSeries: "closePrice",
                shape: 'squarepin',
                width: 16,
                style: { // text style
                    color: 'white'
                },
                states: {
                    hover: {
                        fillColor: '#yellowgreen' // darker
                    }
                }
            }]
        });
    });
}

plotStock();
</script>
</html>