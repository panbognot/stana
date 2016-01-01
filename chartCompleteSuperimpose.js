var testData, testData2;
var stockQuote = null;

//var typeCharts = ['close','candlestick','macd','obv','rsi','stochastic','volume'];
var typeCharts = [
                    {chart: 'close', indicatorType: 'price', type: 'spline', pos: -1},
                    {chart: 'candlestick', indicatorType: 'price', type: 'candlestick', pos: -1},
                    {chart: 'macd', indicatorType: 'momentum', type: 'spline', pos: -1},
                    {chart: 'obv', indicatorType: 'volume', type: 'spline', pos: -1},
                    {chart: 'rsi', indicatorType: 'oscillator', type: 'spline', pos: -1},
                    {chart: 'sma5', indicatorType: 'price', type: 'spline', pos: -1},
                    {chart: 'sma15', indicatorType: 'price', type: 'spline', pos: -1},
                    {chart: 'sma20', indicatorType: 'price', type: 'spline', pos: -1},
                    {chart: 'sma50', indicatorType: 'price', type: 'spline', pos: -1},
                    {chart: 'sma120', indicatorType: 'price', type: 'spline', pos: -1},
                    {chart: 'sma150', indicatorType: 'price', type: 'spline', pos: -1},
                    {chart: 'stochastic', indicatorType: 'oscillator', type: 'spline', pos: -1},
                    {chart: 'volume', indicatorType: 'volume', type: 'column', pos: -1}
                ];

function generateCheckboxes(){
    $(typeCharts).each(function(key, data) {
        var value = data.chart;
        $('#typeCharts').append('<input type="checkbox" id="'+value+'" value="'+value+'">'+value+'');

        $('#'+value).change(function() {
            plotStock(stockQuote);
        });
    });
}

generateCheckboxes();

var seriesOptions = [];
var yAxisOptions = [];
var seriesCounter = 0;

var chartPricePresent = 0;
var chartVolumePresent = 0;
var chartMomentumPresent = 0;
var chartOscillatorPresent = 0;

var indicatorVolumePresent = 0;
var indicatorOBVPresent = 0;

var chartTotalToPlot = 0;

var chartHeight = '100%';   //default
var chartTopMargin = {
            price: '0%',
            volume: '0%',
            momentum: '0%',
            oscillator: '0%'
        };

function calculateChartHeights (pricePres, volPres, momPres, oscPres) {
    var nTypeOfChartsToPlot = 0;

    if (pricePres > 0) 
        nTypeOfChartsToPlot += 1;
    if (volPres > 0)
        nTypeOfChartsToPlot += 1;
    if (momPres > 0)
        nTypeOfChartsToPlot += 1;
    if (oscPres > 0)
        nTypeOfChartsToPlot += 1;

    // Determine Chart Height
    var tempHeight = 100 / nTypeOfChartsToPlot;
    chartHeight = tempHeight + '%';

    // Determine the Top Margins
    if ( (pricePres > 0) && (volPres <= 0) && 
        (momPres <= 0) && (oscPres <= 0) ) {
        //only price is present
        chartTopMargin.price = '0%'; 
    }
    else if((pricePres <= 0) && (volPres > 0) && 
        (momPres <= 0) && (oscPres <= 0)){
        //only volume is present
        chartTopMargin.volume = '0%';
    }
    else if((pricePres > 0) && (volPres > 0) && 
        (momPres <= 0) && (oscPres <= 0)){
        //price and volume
        chartTopMargin.price = '0%'; 
        chartTopMargin.volume = tempHeight + '%';
    }
    else if((pricePres <= 0) && (volPres <= 0) && 
        (momPres > 0) && (oscPres <= 0)){
        //only moment is present
        chartTopMargin.momentum = '0%';
    }
    else if((pricePres > 0) && (volPres <= 0) && 
        (momPres > 0) && (oscPres <= 0)){
        //price and momentum
        chartTopMargin.price = '0%'; 
        chartTopMargin.momentum = tempHeight + '%';
    }
    else if((pricePres <= 0) && (volPres > 0) && 
        (momPres > 0) && (oscPres <= 0)){
        //volume and momentum
        chartTopMargin.volume = '0%';
        chartTopMargin.momentum = tempHeight + '%';
    }
    else if((pricePres > 0) && (volPres > 0) && 
        (momPres > 0) && (oscPres <= 0)){
        //price, volume and momentum
        var tempHeight2 = tempHeight * 2;

        chartTopMargin.price = '0%'; 
        chartTopMargin.volume = tempHeight + '%';
        chartTopMargin.momentum = tempHeight2 + '%';
    }

    else if ( (pricePres <= 0) && (volPres <= 0) && 
        (momPres <= 0) && (oscPres > 0) ) {
        //only oscillator is present
        chartTopMargin.oscillator = '0%';
    }
    else if ( (pricePres > 0) && (volPres <= 0) && 
        (momPres <= 0) && (oscPres > 0) ) {
        //price and oscillator
        chartTopMargin.price = '0%'; 
        chartTopMargin.oscillator = tempHeight + '%';
    }
    else if((pricePres <= 0) && (volPres > 0) && 
        (momPres <= 0) && (oscPres > 0)){
        //volume and oscillator
        chartTopMargin.volume = '0%'; 
        chartTopMargin.oscillator = tempHeight + '%';
    }
    else if((pricePres > 0) && (volPres > 0) && 
        (momPres <= 0) && (oscPres > 0)){
        //price, volume, oscillator
        var tempHeight2 = tempHeight * 2;

        chartTopMargin.price = '0%'; 
        chartTopMargin.volume = tempHeight + '%';
        chartTopMargin.oscillator = tempHeight2 + '%';
    }
    else if((pricePres <= 0) && (volPres <= 0) && 
        (momPres > 0) && (oscPres > 0)){
        //momentum and oscillator
        chartTopMargin.momentum = '0%';
        chartTopMargin.oscillator = tempHeight + '%';
    }
    else if((pricePres > 0) && (volPres <= 0) && 
        (momPres > 0) && (oscPres > 0)){
        //price, momentum, oscillator
        var tempHeight2 = tempHeight * 2;

        chartTopMargin.price = '0%'; 
        chartTopMargin.momentum = tempHeight + '%';
        chartTopMargin.oscillator = tempHeight2 + '%';
    }
    else if((pricePres <= 0) && (volPres > 0) && 
        (momPres > 0) && (oscPres > 0)){
        //volume, momentum, oscillator
        var tempHeight2 = tempHeight * 2;

        chartTopMargin.volume = '0%'; 
        chartTopMargin.momentum = tempHeight + '%';
        chartTopMargin.oscillator = tempHeight2 + '%';
    }
    else if((pricePres > 0) && (volPres > 0) && 
        (momPres > 0) && (oscPres > 0)){
        //all
        var tempHeight2 = tempHeight * 2;
        var tempHeight3 = tempHeight * 3;

        chartTopMargin.price = '0%'; 
        chartTopMargin.volume = tempHeight + '%'; 
        chartTopMargin.momentum = tempHeight2 + '%';
        chartTopMargin.oscillator = tempHeight3 + '%'; 
    }

    //return nTypeOfChartsToPlot;
}

function plotStock (quote) {
    stockQuote = quote;
    seriesOptions = [];
    yAxisOptions = [];
    seriesCounter = 0;

    chartPricePresent = 0;
    chartVolumePresent = 0;
    chartMomentumPresent = 0;
    chartOscillatorPresent = 0;

    indicatorVolumePresent = 0;
    indicatorOBVPresent = 0;

    chartTotalToPlot = 0;

    if (stockQuote == null) {
        //alert("No Stock Quote has been selected!");
        return;
    };

    // Get the number of charts to plot first
    // the reason why its separated is due to the nature of the
    // plots being asynchronous
    $(typeCharts).each(function(key, data) {
        var chartName = data.chart;
        var chartIndicatorType = data.indicatorType;

        if ($('#'+chartName).is(':checked')) {
            switch(chartIndicatorType){
                case 'price':
                    chartPricePresent += 1;
                    break;
                case 'volume':
                    chartVolumePresent += 1;

                    if(chartName == 'obv'){
                        indicatorOBVPresent = 1;
                    }else{
                        indicatorOBVPresent = 0;
                    }

                    if(chartName == 'volume'){
                        indicatorVolumePresent = 1;
                    }else{
                        indicatorVolumePresent = 0;
                    }
                    break;
                case 'momentum':
                    if(chartName == 'macd'){
                        chartMomentumPresent += 3;
                    }else{
                        chartMomentumPresent += 1;
                    }
                    break;
                case 'oscillator':
                    if(chartName == 'stochastic'){
                        chartOscillatorPresent += 2;
                    }else{
                        chartOscillatorPresent += 1;
                    }
                    break;
                default:
                    break;
            }

            chartTotalToPlot = chartPricePresent + 
                                chartVolumePresent + 
                                chartMomentumPresent +
                                chartOscillatorPresent;

            calculateChartHeights(chartPricePresent, 
                                chartVolumePresent, 
                                chartMomentumPresent, 
                                chartOscillatorPresent);
        }

        if(chartTotalToPlot > 0){
            //there is something to plot
            $('#container').show();
        }
        else{
            $('#container').hide();
            return;
        }
    });

    var ctr = 0;
    for (var i = 0; i < typeCharts.length; i++) {
        var data = typeCharts[i];
    
        var chartName = data.chart;
        var chartIndicatorType = data.indicatorType;

        if ($('#'+chartName).is(':checked')) {
            switch(chartName){
                case 'close':
                    getClosePricesOnly(stockQuote, ctr);
                    typeCharts[i].pos = ctr;
                    ctr += 1;
                    break;
                case 'candlestick':
                    getCandlestickOnly(stockQuote, ctr);
                    typeCharts[i].pos = ctr;
                    ctr += 1;
                    break;
                case 'macd':
                    getMACDOnly(stockQuote, ctr);
                    typeCharts[i].pos = ctr;
                    ctr += 3;
                    break;
                case 'obv':
                    getOBVOnly (stockQuote, ctr);
                    typeCharts[i].pos = ctr;
                    ctr += 1;
                    break;
                case 'rsi':
                    getRSIOnly (stockQuote, ctr);
                    typeCharts[i].pos = ctr;
                    ctr += 1;
                    break;
                case 'sma5':
                    getSMAOnly (stockQuote, ctr, 5);
                    typeCharts[i].pos = ctr;
                    ctr += 1;
                    break;
                case 'sma15':
                    getSMAOnly (stockQuote, ctr, 15);
                    typeCharts[i].pos = ctr;
                    ctr += 1;
                    break;
                case 'sma20':
                    getSMAOnly (stockQuote, ctr, 20);
                    typeCharts[i].pos = ctr;
                    ctr += 1;
                    break;
                case 'sma50':
                    getSMAOnly (stockQuote, ctr, 50);
                    typeCharts[i].pos = ctr;
                    ctr += 1;
                    break;
                case 'sma120':
                    getSMAOnly (stockQuote, ctr, 120);
                    typeCharts[i].pos = ctr;
                    ctr += 1;
                    break;
                case 'sma150':
                    getSMAOnly (stockQuote, ctr, 150);
                    typeCharts[i].pos = ctr;
                    ctr += 1;
                    break;
                case 'stochastic':
                    getStochasticOnly (stockQuote, ctr);
                    typeCharts[i].pos = ctr;
                    ctr += 2;
                    break;
                case 'volume':
                    getVolumeOnly (stockQuote, ctr);
                    typeCharts[i].pos = ctr;
                    ctr += 1;
                    break;
                default:
                    alert("Please select a type of chart");
                    break;
            }
        };
    };


    if (seriesCounter == 0) {
        //alert("Please select a type of chart");
    };
}

// A boolean to check whether there is still data that is not yet done loading
var ajaxDoneLoading = false;

// Create the Y-Axis default options
var yAxisDefaults = [];
var yAxisPositions = {};
function createYAxes () {
    var ctr = 0;

    //for cases where we don't want to show a label
    emptyYAxis = {
            labels: {
                enabled: false
            },
            title: {
                text: null
            },
            lineWidth: 0
        };

    //Price Charts Y-Axis
    yAxisPrice = {
            labels: {
                align: 'right',
                x: -3
            },
            title: {
                text: 'Price'
            },
            top: chartTopMargin.price,
            height: chartHeight,
            lineWidth: 2
        };

    //Momentum Charts Y-Axis
    yAxisMomentum = {
            labels: {
                align: 'right',
                x: -3
            },
            title: {
                text: 'Momentum'
            },
            top: chartTopMargin.momentum,
            height: chartHeight,
            lineWidth: 2
        };

    //Oscillator Charts Y-Axis
    yAxisOscillator = {
            labels: {
                align: 'right',
                x: -3
            },
            title: {
                text: 'Oscillator'
            },
            top: chartTopMargin.oscillator,
            height: chartHeight,
            lineWidth: 2,
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
        };

    //Volume Charts Y-Axis
    yAxisVolume = {
            labels: {
                align: 'right',
                x: -3
            },
            title: {
                text: 'Volume'
            },
            top: chartTopMargin.volume,
            height: chartHeight,
            lineWidth: 2
        };

    //OBV Charts Y-Axis
    yAxisOBV = {
            labels: {
                align: 'right',
                x: -3
            },
            title: {
                text: 'On Balance Volume'
            },
            top: chartTopMargin.volume,
            height: chartHeight,
            lineWidth: 2
        };

    // Create y-axis for Price Charts
    if(chartPricePresent > 0){
        yAxisDefaults[ctr] = yAxisPrice;
    }else{
        yAxisDefaults[ctr] = emptyYAxis;
    }
    yAxisPositions.price = ctr;
    ctr += 1;

    // Create y-axis for Momentum Charts
    if(chartMomentumPresent > 0){
        yAxisDefaults[ctr] = yAxisMomentum;
    }else{
        yAxisDefaults[ctr] = emptyYAxis;
    }
    yAxisPositions.momentum = ctr;
    ctr += 1;

    // Create y-axis for Oscillator Charts
    if(chartOscillatorPresent > 0){
        yAxisDefaults[ctr] = yAxisOscillator;
    }else{
        yAxisDefaults[ctr] = emptyYAxis;
    }
    yAxisPositions.oscillator = ctr;
    ctr += 1;

/*    indicatorVolumePresent = 0;
    indicatorOBVPresent = 0;*/

    // Create y-axis for Volume Charts
    if(indicatorVolumePresent > 0){
        yAxisDefaults[ctr] = yAxisVolume;
    }else{
        yAxisDefaults[ctr] = emptyYAxis;
    }
    yAxisPositions.volume = ctr;
    ctr += 1;

    // Create y-axis for OBV Charts
    if(indicatorOBVPresent > 0){
        yAxisDefaults[ctr] = yAxisOBV;
    }else{
        yAxisDefaults[ctr] = emptyYAxis;
    }
    yAxisPositions.obv = ctr;
    ctr += 1;
}


// Create Chart
function createChart (quote) {
    createYAxes();

    //Test code for setting up flags
    seriesOptions[seriesCounter] = {
            type : 'flags',
            data : [{
                x: Date.UTC(2015, 9, 22),
                title: 'Buy',
                text: 'Shape: "squarepin"',
                fillColor: 'yellowgreen'
            }, {
                x: Date.UTC(2015, 11, 28),
                title: 'Buy',
                text: 'Shape: "squarepin"',
                fillColor: 'yellowgreen',
            }, {
                x: Date.UTC(2015, 11, 5),
                title: 'Sell',
                text: 'Shape: "squarepin"',
                fillColor: 'red',
            }, {
                x: Date.UTC(2015, 10, 11),
                title: 'Sell',
                text: 'Shape: "squarepin"',
                fillColor: 'red',
            }],
            onSeries: 'closeprices',
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
        };

    $('#container').highcharts('StockChart', {
        rangeSelector : {
            selected : 1
        },

        title : {
            text : quote + ' Historical'
        },

        //yAxis : yAxisOptions,
        yAxis : yAxisDefaults,

        series: seriesOptions
    });
}

// Get the Close Prices for the selected stock
function getClosePricesOnly (quote, seriesNum) {
    ajaxDoneLoading = false;
    $.getJSON('http://localhost/stana/getData.php?company='+quote+'&timerange=10y&chart=close&dataorg=highchart', function (data) {
        ajaxDoneLoading = true;

        // do some kind of pre processing if needed
        //return data;

        seriesOptions[seriesNum] = {
                name : quote + ' Price',
                data : data,
                id : 'closeprices',
                yAxis: yAxisPositions.price,
                tooltip: {
                    valueDecimals: 2
                }
            };

        seriesCounter += 1;

        if(seriesCounter === chartTotalToPlot){
            createChart(quote);
        }
    });
}

// Get the Candlestick Prices for the selected stock
function getCandlestickOnly (quote, seriesNum) {
    ajaxDoneLoading = false;
    $.getJSON('http://localhost/stana/getData.php?company='+quote+'&timerange=10y&chart=ohlc&dataorg=highchart', function (data) {
        ajaxDoneLoading = true;

        // do some kind of pre processing if needed
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
        }

        seriesOptions[seriesNum] = {
                name: quote + ' OHLC',
                type: 'candlestick',
                data: ohlc,
                yAxis: yAxisPositions.price,
                tooltip: {
                    valueDecimals: 2
                }
            };

        seriesCounter += 1;

        if(seriesCounter === chartTotalToPlot){
            createChart(quote);
        }
    });
}

// Get the MACD for the selected stock
function getMACDOnly (quote, seriesNum) {
    ajaxDoneLoading = false;
    $.getJSON('http://localhost/stana/getData.php?company='+quote+'&timerange=10y&chart=macd&dataorg=highchart', function (data) {
        ajaxDoneLoading = true;

        // do some kind of pre processing if needed
        // split the data set into macd, signal and divergence
        var macd = [],
            signal = [],
            divergence = [],
            dataLength = data.length,
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

        seriesOptions[seriesNum] = {
                name: 'MACD',
                data: macd,
                yAxis: yAxisPositions.momentum,
                tooltip: {
                    valueDecimals: 2
                }
            };

        seriesOptions[seriesNum + 1] = {
                type: 'column',
                name: 'Divergence',
                data: divergence,
                yAxis: yAxisPositions.momentum,
                tooltip: {
                    valueDecimals: 2
                }
            }

        seriesOptions[seriesNum + 2] = {
                name: 'Signal',
                data: signal,
                yAxis: yAxisPositions.momentum,
                tooltip: {
                    valueDecimals: 2
                }
            };

        seriesCounter += 3;

        if(seriesCounter === chartTotalToPlot){
            createChart(quote);
        }
    });
}

// Get the On Balance Volume for the selected stock
function getOBVOnly (quote, seriesNum) {
    ajaxDoneLoading = false;
    $.getJSON('http://localhost/stana/getData.php?company='+quote+'&timerange=10y&chart=obv&dataorg=highchart', function (data) {
        ajaxDoneLoading = true;

        // do some kind of pre processing if needed
        // split the data set into close, volume, obv
        var closePrice = [],
            volume = [],
            obv = [],
            dataLength = data.length,
            i = 0;

        for (i; i < dataLength; i += 1) {
            obv.push([
                data[i][0], // the date
                data[i][3] // obv
            ]);
        }

        seriesOptions[seriesNum] = {
                name: 'On Balance Volume',
                data: obv,
                yAxis: yAxisPositions.obv,
                tooltip: {
                    valueDecimals: 2
                }
            };

        seriesCounter += 1;

        if(seriesCounter === chartTotalToPlot){
            createChart(quote);
        }
    });
}

// Get the Relative Strength Index for the selected stock
function getRSIOnly (quote, seriesNum) {
    ajaxDoneLoading = false;
    $.getJSON('http://localhost/stana/getData.php?company='+quote+'&timerange=10y&chart=rsi&dataorg=highchart', function (data) {
        ajaxDoneLoading = true;

        // do some kind of pre processing if needed

        seriesOptions[seriesNum] = {
                name: 'RSI',
                data: data,
                threshold : null,
                yAxis: yAxisPositions.oscillator,
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
            };

        seriesCounter += 1;

        if(seriesCounter === chartTotalToPlot){
            createChart(quote);
        }
    });
}

// Get the SMA for the selected stock
function getSMAOnly (quote, seriesNum, period) {
    ajaxDoneLoading = false;
    $.getJSON('http://localhost/stana/getData.php?company='+quote+'&timerange=10y&chart=sma&period='+period+'&dataorg=highchart', function (data) {
        ajaxDoneLoading = true;

        // do some kind of pre processing if needed
        //return data;

        seriesOptions[seriesNum] = {
                name : 'SMA ' + period,
                data : data,
                yAxis: yAxisPositions.price,
                tooltip: {
                    valueDecimals: 2
                }
            };

        seriesCounter += 1;

        if(seriesCounter === chartTotalToPlot){
            createChart(quote);
        }
    });
}

// Get the Stochastic Plot for the selected stock
function getStochasticOnly (quote, seriesNum) {
    ajaxDoneLoading = false;
    $.getJSON('http://localhost/stana/getData.php?company='+quote+'&timerange=10y&chart=stoch&dataorg=highchart', function (data) {
        ajaxDoneLoading = true;

        // do some kind of pre processing if needed
        // split the data set into %K, %D
        var percentK = [],
            percentD = [],
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

        //Data arrangement for stochastic is timestamp, close, %K, %D
        for (i; i < dataLength; i += 1) {
            percentK.push([
                data[i][0], // the date
                data[i][1], // %K
            ]);

            percentD.push([
                data[i][0], // the date
                data[i][2] // %D
            ]);
        }

        seriesOptions[seriesNum] = {
                name: '%K',
                data: percentK,
                yAxis: yAxisPositions.oscillator,
                tooltip: {
                    valueDecimals: 2
                }
            };

        seriesOptions[seriesNum + 1] = {
                name: '%D',
                data: percentD,
                yAxis: yAxisPositions.oscillator,
                tooltip: {
                    valueDecimals: 2
                }
            }

        seriesCounter += 2;

        if(seriesCounter === chartTotalToPlot){
            createChart(quote);
        }
    });
}

// Get Volume for the selected stock
function getVolumeOnly (quote, seriesNum) {
    ajaxDoneLoading = false;
    $.getJSON('http://localhost/stana/getData.php?company='+quote+'&timerange=10y&chart=volume&dataorg=highchart', function (data) {
        ajaxDoneLoading = true;

        // do some kind of pre processing if needed
        
        seriesOptions[seriesNum] = {
                name: 'Volume',
                type: 'column',
                data: data,
                yAxis: yAxisPositions.volume,
                tooltip: {
                    valueDecimals: 0
                }
            };

        seriesCounter += 1;

        if(seriesCounter === chartTotalToPlot){
            createChart(quote);
        }
    });
}