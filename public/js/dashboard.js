$(function () {

    //Income Gauge init
    var opts = {
        lines: 12, // The number of lines to draw
        angle: 0, // The length of each line
        lineWidth: 0.40, // The line thickness
        pointer: {
            length: 0.8, // The radius of the inner circle
            strokeWidth: 0.035, // The rotation offset
            color: '#34495e' // Fill color
        },
        limitMax: 'false',   // If true, the pointer will not go past the end of the gauge
        colorStart: '#3f51b5',   // Colors
        colorStop: '#3f51b5',    // just experiment with them
        strokeColor: '#E0E0E0',   // to see which ones work best for you
        generateGradient: true
    };
    var target = document.getElementById('income-gauge'); // your canvas element
    var gauge = new Gauge(target).setOptions(opts); // create sexy gauge!
    gauge.maxValue = parseFloat($("#income").html()) * 1.5; // set max gauge value
    gauge.animationSpeed = 100; // set animation speed (32 is default value)
    gauge.set(parseFloat($("#income").html())); // set actual value
    gauge.setTextField(document.getElementById("income"));

    //Expense gauge init
    var opts = {
        lines: 12, // The number of lines to draw
        angle: 0, // The length of each line
        lineWidth: 0.40, // The line thickness
        pointer: {
            length: 0.8, // The radius of the inner circle
            strokeWidth: 0.035, // The rotation offset
            color: '#34495e' // Fill color
        },
        limitMax: 'false',   // If true, the pointer will not go past the end of the gauge
        colorStart: '#e91e63',   // Colors
        colorStop: '#e91e63',    // just experiment with them
        strokeColor: '#E0E0E0',   // to see which ones work best for you
        generateGradient: true
    };
    var target = document.getElementById('expense-gauge'); // your canvas element
    var gauge = new Gauge(target).setOptions(opts); // create sexy gauge!
    gauge.maxValue = parseFloat($("#expense").html()) * 1.5; // set max gauge value
    gauge.animationSpeed = 100; // set animation speed (32 is default value)
    //gauge.set(parseFloat($("#expense").html())); // set actual value
    gauge.set(parseFloat($("#expense").html()));
    gauge.setTextField(document.getElementById("expense"));


    //*** Cashflow Chart ***//
    // based on prepared DOM, initialize echarts instance

    var _url = $("#_url").val() + "Dashboard/json_month_wise_income_expense/";
    $.ajax({
        method: "POST", url: _url, success: function (data) {
            var json = JSON.parse(data);
            //alert(json['Income']);
            var cashflow = echarts.init(document.getElementById('cashflow'));

            // specify chart configuration item and data
            var option = {
                /* title : {
                 text: 'Income And Expense'
                 },*/
                tooltip: {
                    trigger: 'axis'
                },
                legend: {
                    data: ['Income', 'Expense']
                },
                toolbox: {
                    show: true,
                    feature: {
                        mark: {show: true},
                        dataView: {
                            show: true,
                            readOnly: false,
                            title: 'Data View',
                            lang: ['Data View', 'Cancel', 'Reset']
                        },
                        magicType: {
                            show: true, title: {
                                line: 'Line',
                                bar: 'Bar',
                                stack: 'Stack',
                                tiled: 'Tiled',
                                force: 'Force',
                                chord: 'Chord',
                                pie: 'Pie',
                                funnel: 'Funnel'
                            }, type: ['line', 'bar', 'stack', 'tiled']
                        },
                        restore: {show: true, title: 'Reset'},
                        saveAsImage: {
                            show: true, title: 'Save as Image',
                            type: 'png',
                            lang: ['Click to Save']
                        }
                    }

                },
                calculable: true,
                xAxis: [
                    {
                        type: 'category',
                        boundaryGap: false,
                        //data : ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec']
                        data: json['Months']
                    }
                ],
                yAxis: [
                    {
                        type: 'value'
                    }
                ],
                series: [
                    {
                        name: 'Income',
                        type: 'line',
                        stack: 'stack',
                        color: [
                            '#2196f3'
                        ],
                        //data:[120, 132, 101, 134, 90, 230, 210,560,240,963,630,550]
                        data: json['Income']
                    },
                    {
                        name: 'Expense',
                        type: 'line',
                        stack: 'stack',
                        color: [
                            '#eb3c00'
                        ],
                        //data:[220, 182, 191, 234, 290, 330, 310,101, 134, 90, 230, 210]
                        data: json['Expense']
                    }
                ]
            };

            // use configuration item and data specified to show chart
            cashflow.setOption(option);

        }
    });

    //Income Vs Expense Donut Chart
    var _url = $("#_url").val() + "Dashboard/json_income_vs_expense/";
    $.ajax({
        method: "POST", url: _url, success: function (data) {
            var json = JSON.parse(data);
            var dn_income_expense = echarts.init(document.getElementById('dn_income_expense'));
            var option2 = {
                tooltip: {
                    trigger: 'item',
                    formatter: "{a} <br/>{b} : {c} ({d}%)"
                },
                legend: {
                    orient: 'vertical',
                    x: 'left',
                    data: ['Income', 'Expense']
                },

                calculable: true,
                series: [
                    {
                        name: 'Income & Expense',
                        type: 'pie',
                        radius: ['50%', '70%'],
                        color: ['#2196f3', '#eb3c00'],
                        itemStyle: {
                            normal: {
                                label: {
                                    show: true
                                },
                                labelLine: {
                                    show: true
                                }
                            },
                            emphasis: {
                                label: {
                                    show: true,
                                    position: 'center',
                                    textStyle: {
                                        fontSize: '24',
                                        fontWeight: 'bold'
                                    }
                                }
                            }
                        },
                        data: [
                            {value: json['Income'], name: 'Income'},
                            {value: json['Expense'], name: 'Expense'},
                        ]
                    }
                ]
            };


            dn_income_expense.setOption(option2);
        }
    });


    //Income Vs Expense Line Chart
    var _url = $("#_url").val() + "Dashboard/json_day_wise_income_expense/";
    $.ajax({
        method: "POST", url: _url, success: function (data) {
            var json = JSON.parse(data);
            var line_income_expense = echarts.init(document.getElementById('line_income_expense'));

            // specify chart configuration item and data
            var option3 = {
                /* title : {
                 text: 'Income And Expense'
                 },*/
                tooltip: {
                    trigger: 'axis'
                },
                legend: {
                    data: ['Income', 'Expense']
                },
                toolbox: {
                    show: true,
                    feature: {
                        mark: {show: true},
                        dataView: {
                            show: true,
                            readOnly: false,
                            title: 'Data View',
                            lang: ['Data View', 'Cancel', 'Reset']
                        },
                        magicType: {
                            show: true, title: {
                                line: 'Line',
                                bar: 'Bar',
                                stack: 'Stack',
                                tiled: 'Tiled',
                                force: 'Force',
                                chord: 'Chord',
                                pie: 'Pie',
                                funnel: 'Funnel'
                            }, type: ['line', 'bar', 'stack', 'tiled']
                        },
                        restore: {show: true, title: 'Reset'},
                        saveAsImage: {
                            show: true, title: 'Save as Image',
                            type: 'png',
                            lang: ['Click to Save']
                        }
                    }

                },
                calculable: true,
                xAxis: [
                    {
                        type: 'category',
                        boundaryGap: false,
                        data: ['01', '02', '03', '04', '05', '06',
                            '07', '08', '09', '10', '11', '12',
                            '13', '14', '15', '16', '17', '18',
                            '19', '20', '21', '22', '23', '24',
                            '25', '26', '27', '28', '29', '30', '31',
                        ]
                    }
                ],
                yAxis: [
                    {
                        type: 'value'
                    }
                ],
                series: [
                    {
                        name: 'Income',
                        type: 'line',
                        smooth: true,
                        itemStyle: {normal: {areaStyle: {type: 'default'}}},
                        color: [
                            '#2196f3'
                        ],
                        data: json['Income']
                    },
                    {
                        name: 'Expense',
                        type: 'line',
                        smooth: true,
                        itemStyle: {normal: {areaStyle: {type: 'default'}}},
                        color: [
                            '#eb3c00'
                        ],
                        data: json['Expense']
                    }
                ]
            };

            line_income_expense.setOption(option3);
        }
    });
    //End Ajax

});


$(window).on('resize', function () {
    location.reload();
});