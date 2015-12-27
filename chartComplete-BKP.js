var testData, testData2;
var stockQuote = null;

//var typeCharts = ['close','candlestick','macd','obv','rsi','stochastic','volume'];
var typeCharts = [
                    {chart: 'close', indicatorType: 'price', type: 'spline', pos: -1},
                    {chart: 'candlestick', indicatorType: 'price', type: 'candlestick', pos: -1},
                    {chart: 'macd', indicatorType: 'momentum', type: 'spline', pos: -1},
                    {chart: 'obv', indicatorType: 'volume', type: 'spline', pos: -1},
                    {chart: 'rsi', indicatorType: 'oscillator', type: 'spline', pos: -1},
                    {chart: 'stochastic', indicatorType: 'oscillator', type: 'spline', pos: -1},
                    {chart: 'volume', indicatorType: 'volume', type: 'column', pos: -1}
                ];

function generateCheckboxes(){
    $(typeCharts).each(function(key, data) {
        var value = data.chart;
        $('#typeCharts').append('<input type="checkbox" id="'+value+'" value="'+value+'">'+value+'');

        $('#'+value).change(function() {
            if($(this).is(":checked")) {
                // temporary code
/*                $(typeCharts).each(function(key, data2) {
                    var value2 = data2.chart;
                    if (value2 != value) {
                        $('#'+value2).attr('checked', false); // Unchecks it
                    }
                });*/
                //

                plotStock(stockQuote);
            }
            else{
                if(data.pos != -1){
                    if(value == 'macd'){
                        var chart = $('#container').highcharts()
                        for (var i = 0; i < 3; i++) {
                            chart.series[data.pos + i].hide();
                            chart.yAxis[data.pos + i].update({
                                labels: {
                                    enabled: false
                                },
                                title: {
                                    text: null
                                },
                                lineWidth: 0
                            });
                        };
                    }
                    else if(value == 'stochastic'){
                        var chart = $('#container').highcharts()
                        for (var i = 0; i < 2; i++) {
                            chart.series[data.pos + i].hide();
                            chart.yAxis[data.pos + i].update({
                                labels: {
                                    enabled: false
                                },
                                title: {
                                    text: null
                                },
                                lineWidth: 0
                            });
                        };
                    }
                    else{
                        var chart = $('#container').highcharts()
                        chart.series[data.pos].hide();
                        chart.yAxis[data.pos].update({
                            labels: {
                                enabled: false
                            },
                            title: {
                                text: null
                            },
                            lineWidth: 0
                        });
                    }
                }
            }
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

var chartTotalToPlot = 0;

function plotStock (quote) {
    stockQuote = quote;
    seriesOptions = [];
    yAxisOptions = [];
    seriesCounter = 0;

    chartPricePresent = 0;
    chartVolumePresent = 0;
    chartMomentumPresent = 0;
    chartOscillatorPresent = 0;

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
                    //plotOBV(stockQuote);
                    getOBVOnly (stockQuote, ctr);
                    typeCharts[i].pos = ctr;
                    ctr += 1;
                    break;
                case 'rsi':
                    //plotRSI(stockQuote);
                    getRSIOnly (stockQuote, ctr);
                    typeCharts[i].pos = ctr;
                    ctr += 1;
                    break;
                case 'stochastic':
                    //plotStochastic(stockQuote);
                    getStochasticOnly (stockQuote, ctr);
                    typeCharts[i].pos = ctr;
                    ctr += 2;
                    break;
                case 'volume':
                    //plotVolume(stockQuote);
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

// Create Chart
function CreateChart (quote) {
    $('#container').highcharts('StockChart', {
        rangeSelector : {
            selected : 1
        },

        title : {
            text : quote + ' Historical'
        },

        yAxis : yAxisOptions,
        series: seriesOptions
    });
}

// Get the Close Prices for the selected stock
function getClosePricesOnly (quote, seriesNum) {
    ajaxDoneLoading = false;
    $.getJSON('http://localhost/analyzer/getData.php?company='+quote+'&timerange=3y&chart=close&dataorg=highchart', function (data) {
        ajaxDoneLoading = true;

        // do some kind of pre processing if needed
        //return data;

        yAxisOptions[seriesNum] = {
                labels: {
                    align: 'right',
                    x: -3
                },
                title: {
                    text: 'Close Price'
                },
                height: '100%',
                lineWidth: 2
            };

        seriesOptions[seriesNum] = {
                name : quote + ' Price',
                data : data,
                yAxis: seriesNum,
                tooltip: {
                    valueDecimals: 2
                }
            };

        seriesCounter += 1;

        if(seriesCounter === chartTotalToPlot){
            CreateChart(quote);
        }
    });
}

// Displays the Close Prices for the selected stock
function plotClosePrices (quote) {
    $.getJSON('http://localhost/analyzer/getData.php?company='+quote+'&timerange=3y&chart=close&dataorg=highchart', function (data) {
        // Create the chart
        $('#container').highcharts('StockChart', {
            rangeSelector : {
                selected : 1
            },

            title : {
                text : quote + ' Stock Price'
            },

            series : [{
                name : quote + ' Price',
                data : data,
                tooltip: {
                    valueDecimals: 2
                }
            }]
        });
    });
}

// Get the Candlestick Prices for the selected stock
function getCandlestickOnly (quote, seriesNum) {
    ajaxDoneLoading = false;
    $.getJSON('http://localhost/analyzer/getData.php?company='+quote+'&timerange=3y&chart=ohlc&dataorg=highchart', function (data) {
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

        yAxisOptions[seriesNum] = {
                labels: {
                    align: 'right',
                    x: -3
                },
                title: {
                    text: 'OHLC'
                },
                height: '100%',
                lineWidth: 2
            };

        seriesOptions[seriesNum] = {
                name: quote + ' OHLC',
                type: 'candlestick',
                data: ohlc,
                yAxis: seriesNum,
                tooltip: {
                    valueDecimals: 2
                }
            };

        seriesCounter += 1;

        if(seriesCounter === chartTotalToPlot){
            CreateChart(quote);
        }
    });
}

// Displays the Candlestick Plot for the selected stock
function plotCandlestick(quote) {
    $.getJSON('http://localhost/analyzer/getData.php?company='+quote+'&timerange=3y&chart=ohlc&dataorg=highchart', function (data) {
        testData = data;

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

            volume.push([
                data[i][0], // the date
                data[i][5] // the volume
            ]);
        }


        // create the chart
        $('#container').highcharts('StockChart', {
            rangeSelector: {
                selected: 1
            },

            title: {
                text: quote + ' Historical'
            },

            yAxis: [{
                labels: {
                    align: 'right',
                    x: -3
                },
                title: {
                    text: 'OHLC'
                },
                height: '75%',
                lineWidth: 2
            }, {
                labels: {
                    align: 'right',
                    x: -3
                },
                title: {
                    text: 'Volume'
                },
                top: '80%',
                height: '20%',
                offset: 0,
                lineWidth: 2
            }],

            series: [{
                type: 'candlestick',
                name: quote,
                data: ohlc
            }, {
                type: 'column',
                name: 'Volume',
                data: volume,
                yAxis: 1
            }]
        });
    });
}

// Get the MACD for the selected stock
function getMACDOnly (quote, seriesNum) {
    ajaxDoneLoading = false;
    $.getJSON('http://localhost/analyzer/getData.php?company='+quote+'&timerange=3y&chart=macd&dataorg=highchart', function (data) {
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

        yAxisOptions[seriesNum] = {
                labels: {
                    align: 'right',
                    x: -3
                },
                title: {
                    text: 'MACD'
                },
                lineWidth: 2,
                height: '100%'
            };

        yAxisOptions[seriesNum + 1] = {
                labels: {
                    align: 'right',
                    x: -3
                },
                title: {
                    text: 'Divergence'
                },
                lineWidth: 2,
                height: '100%'
            };

        yAxisOptions[seriesNum + 2] = {
                labels: {
                    align: 'right',
                    x: -3
                },
                title: {
                    text: 'Signal'
                },
                lineWidth: 2,
                height: '100%'
            };

        seriesOptions[seriesNum] = {
                name: 'MACD',
                data: macd,
                yAxis: seriesNum,
                tooltip: {
                    valueDecimals: 2
                }
            };

        seriesOptions[seriesNum + 1] = {
                type: 'column',
                name: 'Divergence',
                data: divergence,
                yAxis: seriesNum + 1,
                tooltip: {
                    valueDecimals: 2
                }
            }

        seriesOptions[seriesNum + 2] = {
                name: 'Signal',
                data: signal,
                yAxis: seriesNum + 2,
                tooltip: {
                    valueDecimals: 2
                }
            };

        seriesCounter += 3;

        if(seriesCounter === chartTotalToPlot){
            CreateChart(quote);
        }
    });
}

function plotMACD(quote) {
    $.getJSON('http://localhost/analyzer/getData.php?company='+quote+'&timerange=3y&chart=macd&dataorg=highchart', function (data) {
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
                text: quote+' MACD'
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

// Get the On Balance Volume for the selected stock
function getOBVOnly (quote, seriesNum) {
    ajaxDoneLoading = false;
    $.getJSON('http://localhost/analyzer/getData.php?company='+quote+'&timerange=3y&chart=obv&dataorg=highchart', function (data) {
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

        yAxisOptions[seriesNum] = {
                labels: {
                    align: 'right',
                    x: -3
                },
                title: {
                    text: 'On Balance Volume'
                },
                height: '100%',
                lineWidth: 2
            };

        seriesOptions[seriesNum] = {
                name: 'On Balance Volume',
                data: obv,
                yAxis: seriesNum,
                tooltip: {
                    valueDecimals: 2
                }
            };

        seriesCounter += 1;

        if(seriesCounter === chartTotalToPlot){
            CreateChart(quote);
        }
    });
}

function plotOBV(quote) {
    $.getJSON('http://localhost/analyzer/getData.php?company='+quote+'&timerange=3y&chart=obv&dataorg=highchart', function (data) {
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
                text: quote+' Historical'
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
                name: quote,
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

// Get the Relative Strength Index for the selected stock
function getRSIOnly (quote, seriesNum) {
    ajaxDoneLoading = false;
    $.getJSON('http://localhost/analyzer/getData.php?company='+quote+'&timerange=3y&chart=rsi&dataorg=highchart', function (data) {
        ajaxDoneLoading = true;

        // do some kind of pre processing if needed

        yAxisOptions[seriesNum] = {
                labels: {
                    align: 'right',
                    x: -3
                },
                title: {
                    text: 'Relative Strength Index'
                },
                height: '100%',
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

        seriesOptions[seriesNum] = {
                name: 'RSI',
                data: data,
                threshold : null,
                yAxis: seriesNum,
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
            CreateChart(quote);
        }
    });
}

function plotRSI (quote) {
    $.getJSON('http://localhost/analyzer/getData.php?company='+quote+'&timerange=3y&chart=rsi&dataorg=highchart', function (data) {
        testData = data;

        // Create the chart
        $('#container').highcharts('StockChart', {
            rangeSelector : {
                selected : 1
            },

            title : {
                text : quote + ' Relative Strength Index'
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
                //type : 'areaspline',
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

// Get the Stochastic Plot for the selected stock
function getStochasticOnly (quote, seriesNum) {
    ajaxDoneLoading = false;
    $.getJSON('http://localhost/analyzer/getData.php?company='+quote+'&timerange=3y&chart=stoch&dataorg=highchart', function (data) {
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

        yAxisOptions[seriesNum] = {
                labels: {
                    align: 'right',
                    x: -3
                },
                title: {
                    text: 'Stochastic %K'
                },
                lineWidth: 2,
                height: '100%',
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

        yAxisOptions[seriesNum + 1] = {
                labels: {
                    align: 'right',
                    x: -3
                },
                title: {
                    text: 'Stochastic %D'
                },
                lineWidth: 2,
                height: '100%'
            };

        seriesOptions[seriesNum] = {
                name: '%K',
                data: percentK,
                yAxis: seriesNum,
                tooltip: {
                    valueDecimals: 2
                }
            };

        seriesOptions[seriesNum + 1] = {
                name: '%D',
                data: percentD,
                yAxis: seriesNum + 1,
                tooltip: {
                    valueDecimals: 2
                }
            }

        seriesCounter += 2;

        if(seriesCounter === chartTotalToPlot){
            CreateChart(quote);
        }
    });
}

function plotStochastic(quote) {
    $.getJSON('http://localhost/analyzer/getData.php?company='+quote+'&timerange=3y&chart=stoch&dataorg=highchart', function (data) {
        testData = data;

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


        // create the chart
        $('#container').highcharts('StockChart', {
            rangeSelector: {
                selected: 1
            },

            title: {
                text: quote + ' Stochastic Oscillator'
            },

            yAxis: [{
                labels: {
                    align: 'right',
                    x: -3
                },
                title: {
                    text: 'Stochastic Oscillator'
                },
                lineWidth: 2
            }],

            series: [{
                name: '%K',
                data: percentK
            }, {
                name: '%D',
                data: percentD
            }]
        });
    });
}

// Get Volume for the selected stock
function getVolumeOnly (quote, seriesNum) {
    ajaxDoneLoading = false;
    $.getJSON('http://localhost/analyzer/getData.php?company='+quote+'&timerange=3y&chart=volume&dataorg=highchart', function (data) {
        ajaxDoneLoading = true;

        // do some kind of pre processing if needed

        yAxisOptions[seriesNum] = {
                labels: {
                    align: 'right',
                    x: -3
                },
                title: {
                    text: 'Volume'
                },
                height: '100%',
                lineWidth: 2
            };

        seriesOptions[seriesNum] = {
                name: 'Volume',
                type: 'column',
                data: data,
                yAxis: seriesNum,
                tooltip: {
                    valueDecimals: 0
                }
            };

        seriesCounter += 1;

        if(seriesCounter === chartTotalToPlot){
            CreateChart(quote);
        }
    });
}

function plotVolume (quote) {
    $.getJSON('http://localhost/analyzer/getData.php?company='+quote+'&timerange=3y&chart=volume&dataorg=highchart', function (data) {
        testData = data;

        // Create the chart
        $('#container').highcharts('StockChart', {
            rangeSelector : {
                selected : 1
            },

            title : {
                text : quote + ' Volume Movement'
            },

            series : [{
                name : quote + ' Shares Traded',
                type : 'column',
                data : data,
                tooltip: {
                    valueDecimals: 0
                }
            }]
        });
    });
}