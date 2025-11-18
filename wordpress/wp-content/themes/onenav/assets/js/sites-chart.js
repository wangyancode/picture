//loadFunc(function() {
//    loadChart();
//});
(function () {
    var chartTheme = IO.html.hasClass('io-black-mode') ? 'dark' : '';
    var domChart = document.getElementById("chart-container");
    var ioChart;
    var chartOption;
    function setChartTheme() {
        if (chartOption && typeof chartOption === 'object') {
            ioChart.dispose();
            ioChart = echarts.init(domChart, chartTheme);
            ioChart.setOption(chartOption);
        }
    }
    function refreshChart() {
        if (chartOption && typeof chartOption === 'object') {
            ioChart.resize();
        }
    }

    function loadChart() {
        ioChart = echarts.init(domChart, chartTheme);
        var post_data;
        ioChart.showLoading();
        jQuery.ajax({
            type: 'POST',
            url: IO.ajaxurl,
            dataType: 'json',
            data: {
                action: "get_post_ranking_data",
                data: jQuery(domChart).data(),
            },
            success: function (response) {
                ioChart.hideLoading();
                if (response.success) {
                    post_data = response.data.data.data;
                    var _series = post_data.series;
                    var Max1 = calMax(post_data.count);
                    var _yAxisData = [
                        {
                            type: 'value',
                            axisLine: {
                                show: false
                            },
                            axisLabel: {
                                formatter: '{value}'
                            },
                            max: Max1,
                            splitNumber: 4,
                            interval: Max1 / 4
                        }
                    ];
                    var _seriesData = [
                        {
                            name: _series[0],
                            type: 'bar',
                            data: post_data.desktop
                        },
                        {
                            name: _series[1],
                            type: 'bar',
                            data: post_data.mobile
                        },
                        {
                            name: _series[2],
                            type: 'line',
                            smooth: true,
                            data: post_data.count
                        }
                    ];
                    if (response.data.data.type == "down") {
                        var Max2 = calMax(post_data.download);
                        _yAxisData.push(
                            {
                                type: 'value',
                                axisLabel: {
                                    formatter: '{value}'
                                },
                                max: Max2,
                                splitNumber: 4,
                                interval: Max2 / 4
                            }
                        );
                        _seriesData.push(
                            {
                                name: _series[3],
                                type: 'line',
                                yAxisIndex: 1, itemStyle: {
                                    normal: {
                                        lineStyle: {
                                            width: 2,
                                            type: 'dotted'
                                        }
                                    }
                                },
                                data: post_data.download
                            }
                        );
                    }
                    chartOption = {
                        backgroundColor: 'rgba(0,0,0,0)',
                        tooltip: {
                            trigger: 'axis',
                            axisPointer: {
                                type: 'none',
                                crossStyle: {
                                    color: '#999'
                                }
                            }
                        },
                        grid: {
                            top: '80',
                            bottom: '60'
                        },
                        legend: {
                            top: '24',
                            data: _series
                        },
                        xAxis: [
                            {
                                type: 'category',
                                data: post_data.x_axis,
                                axisPointer: {
                                    type: 'shadow'
                                },
                                axisLabel: {
                                    formatter: function (value) {
                                        return echarts.format.formatTime("MM.dd", new Date(value));
                                    },
                                },
                            }
                        ],
                        yAxis: _yAxisData,
                        series: _seriesData
                    };
                    if (chartOption && typeof chartOption === 'object') {
                        ioChart.setOption(chartOption);
                    };
                } else {
                    showAlert(response.data);
                }
            },
            error: function () {
                showAlert(response.data);
            }
        });
    };
    function calMax(arrs) {
        var max = arrs[0];
        for (var i = 1, ilen = arrs.length; i < ilen; i++) {
            if (arrs[i] > max) {
                max = arrs[i];
            }
        }
        if (max < 4)
            return 4;
        else
            return Math.ceil(max / 4) * 4;
    }
    loadChart();

    //增加 resize 事件
    var debouncedResize = debounce(refreshChart, 100);
    window.addEventListener('resize', debouncedResize);

    // 监听自定义事件 'themeModeChanged'
    window.addEventListener('themeModeChanged', function (e) {
        // 获取当前的主题状态 (true: 黑暗模式, false: 日间模式)
        var isDarkMode = e.detail.isDarkMode;
  
        chartTheme = isDarkMode ? 'dark' : '';
        setChartTheme();
    });

    window.addEventListener('beforeunload', function () {
        window.removeEventListener('resize', debouncedResize);
        window.removeEventListener('themeModeChanged', setChartTheme);
    });

})();