<!DOCTYPE html>
<html>
<head>
	<title>Multiview Stock Recommendations</title>

<?php 
    $serverName = $_SERVER['SERVER_NAME'];
?>

<?php if ($serverName == 'localhost'): ?>
    <script src="js/jquery-1.11.3.min.js"></script>
    <script src="js/highstock.js"></script>
    <script src="js/highcharts-more.js"></script>
    <script src="js/exporting.js"></script>
    <script src="js/handlebars.min.js"></script>
<?php else: ?>
    <script src="https://code.jquery.com/jquery-1.11.3.min.js"></script>
    <script src="https://code.highcharts.com/stock/highstock.js"></script>
    <script src="https://code.highcharts.com/stock/highcharts-more.js"></script>
    <script src="https://code.highcharts.com/stock/modules/exporting.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/handlebars.js/4.0.5/handlebars.min.js"></script>
<?php endif; ?>

	<script src="js/dynamicpath.js"></script>

	<!-- Bootstrap JS -->
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>

	<!-- Bootstrap CSS -->
	<link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css">
</head>
<body>
	<div class="container-fluid">
	  <div class="row">
	    <p><b>Multiview Stock Recommendations</b></p> 
	  </div>
	  <div id="multi-view-recommendations" class="row">
<!-- 	    <div id="chart1" class="col-sm-3">
	      <h3>Column 1</h3>
	      <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit...</p>
	      <p>Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris...</p>
	    </div>
	    <div class="col-sm-3">
	      <h3>Column 2</h3>
	      <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit...</p>
	      <p>Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris...</p>
	    </div>
	    <div class="col-sm-3">
	      <h3>Column 3</h3> 
	      <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit...</p>
	      <p>Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris...</p>
	    </div>
	    <div class="col-sm-3">
	      <h3>Column 4</h3> 
	      <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit...</p>
	      <p>Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris...</p>
	    </div> -->
	  </div>
	</div>
</body>

<?php
    //select the company you want to view
    if(isset($_GET['company'])) {
        $company = strtolower($_GET['company']."");
    }
    else {
        //echo "ERROR: No Company was selected<Br/>";
        $company = "smc";
    }
?>

<script id="chart-template" type="text/x-handlebars-template">
	{{#each recommendations}}
	    <div id="chart-{{company}}" class="col-sm-3">
			<h3>{{#uppercase}} {{company}} {{/uppercase}}</h3> 
			
			{{#if isbuy}}
				<p class="bg-success">BUY Recommendation</p>
			{{else}}
				<p class="bg-danger">SELL Recommendation</p>
			{{/if}}
			
			<div id="chart-{{company}}-canvas">
				<p>
					Lorem ipsum dolor sit amet, consectetur adipiscing elit. Morbi 
					faucibus semper interdum. Morbi pharetra sed dolor sed finibus. 
					Quisque tincidunt luctus mauris, at bibendum nisi elementum 
					nec. Interdum et malesuada fames ac ante ipsum primis in 
					faucibus. Morbi sit amet erat nec nunc efficitur rutrum. Donec 
					cursus nec lectus sit amet euismod. Cras et eleifend libero.

					Nulla in nunc sed eros lobortis tempor. Nam leo erat, 
					scelerisque ut euismod sit amet, pretium in risus. Aenean 
					elementum, turpis sed imperdiet eleifend, mi felis blandit 
					ligula, ut aliquam orci ligula ac enim. Nunc pretium lacus non 
					lobortis faucibus. Aenean nec diam et quam euismod iaculis vitae 
					non risus.
				</p>
			</div>
	    </div>
	{{/each}}
</script>

<script type="text/javascript">
	//Grab the template script
	var chartTemplateScript = $("#chart-template").html();

	// This is our block helper
	// The name of our helper is provided as the first parameter - in this case 'uppercase'
	Handlebars.registerHelper('uppercase', function(options) {
		return options.fn(this).toUpperCase();
	});

	// Compile the template
	var chartTemplate = Handlebars.compile(chartTemplateScript);

	// Define our data object
	var context = {
		recommendations: [
			{
				"company":"smc",
				"timestamp":"2016-07-01 18:00",
				"isbuy":true
			},
			{
				"company":"ltg",
				"timestamp":"2016-07-01 18:00",
				"isbuy":true
			},
			{
				"company":"tel",
				"timestamp":"2016-07-01 18:00",
				"isbuy":false
			},
			{
				"company":"glo",
				"timestamp":"2016-07-01 18:00",
				"isbuy":false
			},
			{
				"company":"ceb",
				"timestamp":"2016-07-01 18:00",
				"isbuy":true
			},
			{
				"company":"ssi",
				"timestamp":"2016-07-01 18:00",
				"isbuy":false
			}
		]
	};

	// Pass our data to the template
	var chartCompiledHtml = chartTemplate(context);

	// Add the compiled html to the page
	$('#multi-view-recommendations').html(chartCompiledHtml);
</script>
<script type="text/javascript">
	var testData, testData2;

	function plotStock (targetCompany) {
	    var company = targetCompany;
	    var targetID = "#chart-" + company + "-canvas";
	    var ohlc = [];      

	    bollinger2URL = dynamicDataURL() + 'getData.php?company='+company+'&timerange=3y&chart=bollinger3&dataorg=highchart&ensig=true';
	    $.getJSON(bollinger2URL, function (data) {
	    	testData = data;
	        var allData = data;
	        var prices = data[0];
	        var signalsData = data[1];

	        // split the data set into sma, bollinger upper band, bollinger lower band
	        var sma = [],
	            ohlc = [],
	            signals = [];

	            bollingerBandsSD1 = [],
	            bollingerBandsSD2 = [],
	            dataLength = prices.length,
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
	                prices[i][0], // the date
	                prices[i][1], // open
	                prices[i][2], // high
	                prices[i][3], // low
	                prices[i][4] // close
	            ]);

	            sma.push([
	                prices[i][0], // the date
	                prices[i][5], // sma
	            ]);

	            bollingerBandsSD1.push([
	                prices[i][0], // the date
	                prices[i][6], // the upper band
	                prices[i][7], // the lower band
	            ]);

	            bollingerBandsSD2.push([
	                prices[i][0], // the date
	                prices[i][8], // the upper band
	                prices[i][9], // the lower band
	            ]);
	        }

	        var tradeSignals = [];
	        var tempTS, tempTitle, tempFillColor;
	        for (var j = 0; j < signalsData.length; j += 1) {
	            tempTS = signalsData[j][0];
	            tempTitle = signalsData[j][1];

	            if (tempTitle == "buy") {
	                tempFillColor = "yellowgreen";
	            }
	            else if(tempTitle == "sell"){
	                tempFillColor = "red";
	            }

	            tradeSignals[j] = {x: tempTS, title: tempTitle, fillColor: tempFillColor};

	/*            signals.push([
	                signalsData[j][0],  // the date
	                signalsData[j][1],  // the signal
	                signalsData[j][2],  // the description
	            ]);*/
	        }
	        testData2 = tradeSignals;

	        // Create the chart
	        $(targetID).highcharts('StockChart', {
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

	            series : [
	            {
	                type: 'areasplinerange',
	                name: 'bb sd 2',
	                data: bollingerBandsSD2,
	                color: 'yellow'
	            },
	            {
	                type: 'areasplinerange',
	                name: 'bb sd 1',
	                data: bollingerBandsSD1,
	                color: 'orange'
	            },
	            {
	                name: 'SMA',
	                data: sma,
	                color: 'black'
	            },
	            {
	                type: 'candlestick',
	                name: 'Candlestick',
	                id : "candlestick",
	                data: ohlc
	            },
	            {
	                type : 'flags',
	                data : tradeSignals,
	                onSeries: "candlestick",
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