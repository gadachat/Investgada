{{--
    Real-Time Crypto Price Chart Component
    Purple & Blue gradient theme
    Features: Candlestick chart, coin selector, live price ticker, OHLC stats
    Auto-refreshes every 5 seconds
--}}
<div class="card-custom" style="padding: 0; overflow: hidden;" id="cryptoChartWidget">
    <!-- HEADER -->
    <div style="padding: 18px 20px; border-bottom: 1px solid var(--border); background: linear-gradient(135deg, rgba(99,102,241,0.06), rgba(168,85,247,0.03));">
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
            <div style="display: flex; align-items: center; gap: 14px;">
                <div id="chartCoinBadge" style="width: 48px; height: 48px; border-radius: 12px; background: var(--gradient-primary); display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 16px; box-shadow: 0 4px 12px rgba(99,102,241,0.3);">
                    BTC
                </div>
                <div>
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <h5 style="margin: 0; font-size: 16px; font-weight: 700; color: var(--text-bright);" id="chartCoinName">Bitcoin</h5>
                        <span class="live-badge">LIVE</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 8px; margin-top: 2px;">
                        <span style="font-size: 22px; font-weight: 800; color: var(--text-bright); font-variant-numeric: tabular-nums;" id="chartCoinPrice">$67,500.00</span>
                        <span id="chartCoinChange" style="font-size: 13px; font-weight: 600; color: var(--green); display: inline-flex; align-items: center; gap: 4px;">
                            <i class="fas fa-caret-up"></i> <span>+0.00%</span>
                        </span>
                    </div>
                </div>
            </div>

            <!-- OHLC Quick Stats -->
            <div style="display: flex; gap: 20px; flex-wrap: wrap;">
                <div>
                    <div style="font-size: 10px; color: var(--text-dim); text-transform: uppercase; letter-spacing: 0.5px;">24h High</div>
                    <div style="font-size: 14px; font-weight: 600; color: var(--green);" id="chart24High">—</div>
                </div>
                <div>
                    <div style="font-size: 10px; color: var(--text-dim); text-transform: uppercase; letter-spacing: 0.5px;">24h Low</div>
                    <div style="font-size: 14px; font-weight: 600; color: var(--red);" id="chart24Low">—</div>
                </div>
                <div>
                    <div style="font-size: 10px; color: var(--text-dim); text-transform: uppercase; letter-spacing: 0.5px;">Volume</div>
                    <div style="font-size: 14px; font-weight: 600; color: var(--blue-1);" id="chart24Volume">—</div>
                </div>
            </div>
        </div>
    </div>

    <!-- COIN SELECTOR TABS -->
    <div style="display: flex; gap: 4px; padding: 10px 16px; overflow-x: auto; border-bottom: 1px solid var(--border); scrollbar-width: thin;" id="coinSelector">
        <!-- Loaded via JS -->
    </div>

    <!-- CHART + TIMEFRAME CONTROLS -->
    <div style="position: relative;">
        <!-- Chart type + timeframe controls -->
        <div style="position: absolute; top: 10px; right: 16px; z-index: 10; display: flex; gap: 4px; align-items: center;">
            <div style="display: flex; gap: 2px; background: var(--bg-input); border: 1px solid var(--border); border-radius: 8px; padding: 2px;">
                <button class="chart-type-btn active" data-type="candle" onclick="setChartType('candle')" title="Candlestick">
                    <i class="fas fa-chart-bar" style="font-size: 12px;"></i>
                </button>
                <button class="chart-type-btn" data-type="area" onclick="setChartType('area')" title="Area">
                    <i class="fas fa-chart-area" style="font-size: 12px;"></i>
                </button>
                <button class="chart-type-btn" data-type="line" onclick="setChartType('line')" title="Line">
                    <i class="fas fa-chart-line" style="font-size: 12px;"></i>
                </button>
            </div>
        </div>

        <div style="position: absolute; top: 10px; left: 16px; z-index: 10; display: flex; gap: 2px; background: var(--bg-input); border: 1px solid var(--border); border-radius: 8px; padding: 2px;">
            <button class="tf-btn" data-tf="15" onclick="setTimeframe(15, this)">15m</button>
            <button class="tf-btn" data-tf="60" onclick="setTimeframe(60, this)">1H</button>
            <button class="tf-btn active" data-tf="240" onclick="setTimeframe(240, this)">4H</button>
            <button class="tf-btn" data-tf="1440" onclick="setTimeframe(1440, this)">1D</button>
        </div>

        <div id="indicatorToolbarContainer"></div>
        <div id="cryptoChartMain" style="min-height: 350px; padding-top: 48px;"></div>
        <div id="indicatorSubChartsContainer"></div>
    </div>

    <!-- LIVE PRICE STRIP -->
    <div style="padding: 12px 20px; border-top: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px;">
        <div style="display: flex; align-items: center; gap: 8px; font-size: 12px; color: var(--text-dim);">
            <div style="width: 8px; height: 8px; border-radius: 50%; background: var(--green); animation: pulse 1.5s infinite;"></div>
            <span>Real-time data</span>
            <span style="color: var(--text-dim);">•</span>
            <span id="chartLastUpdate">—</span>
        </div>
        <div style="display: flex; gap: 6px;">
            <button class="icon-btn" style="width: 34px; height: 34px; font-size: 12px;" onclick="toggleAutoRefresh()" id="autoRefreshBtn" title="Auto-refresh toggle">
                <i class="fas fa-sync-alt"></i>
            </button>
            <button class="icon-btn" style="width: 34px; height: 34px; font-size: 12px;" onclick="loadChartData(currentSymbol)" title="Refresh">
                <i class="fas fa-redo"></i>
            </button>
        </div>
    </div>
</div>

<style>
    .chart-type-btn, .tf-btn {
        background: none;
        border: none;
        color: var(--text-muted);
        font-size: 11px;
        font-weight: 500;
        padding: 5px 10px;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.15s;
    }
    .chart-type-btn:hover, .tf-btn:hover {
        color: var(--text-bright);
        background: rgba(99,102,241,0.1);
    }
    .chart-type-btn.active, .tf-btn.active {
        background: var(--gradient-primary);
        color: white;
    }

    #coinSelector::-webkit-scrollbar { height: 3px; }
    #coinSelector::-webkit-scrollbar-thumb { background: var(--border); border-radius: 3px; }

    .coin-tab {
        display: flex;
        flex-direction: column;
        align-items: center;
        min-width: 70px;
        padding: 8px 10px;
        border-radius: 10px;
        cursor: pointer;
        transition: all 0.2s;
        border: 1px solid transparent;
        background: var(--bg-input);
    }
    .coin-tab:hover {
        border-color: rgba(99,102,241,0.3);
        transform: translateY(-1px);
    }
    .coin-tab.active {
        background: linear-gradient(135deg, rgba(99,102,241,0.15), rgba(168,85,247,0.1));
        border-color: rgba(99,102,241,0.4);
    }
    .coin-tab .coin-symbol {
        font-size: 12px;
        font-weight: 700;
        color: var(--text-bright);
    }
    .coin-tab .coin-price {
        font-size: 11px;
        color: var(--text-muted);
        margin-top: 2px;
        font-variant-numeric: tabular-nums;
    }
    .coin-tab .coin-trend {
        font-size: 9px;
        font-weight: 600;
        margin-top: 1px;
    }

    /* Candlestick coloring */
    .apexcharts-candlestick-series .apexcharts-candlestick-series-candlestick {
        /* ApexCharts handles bull/bear colors via options */
    }

    /* Custom tooltip */
    .crypto-chart-tooltip {
        background: var(--bg-card) !important;
        border: 1px solid var(--border) !important;
        border-radius: 10px !important;
        box-shadow: 0 8px 24px rgba(0,0,0,0.4) !important;
    }
</style>

<script>
// ========== INDICATOR INTEGRATION ==========
// Include the indicators engine
@include('dashboard.partials._indicators')

// Build indicator toolbar
IndicatorManager.buildToolbar('indicatorToolbarContainer');
IndicatorManager.resetInfoStrip();

// Override renderChart to include indicators
var _originalRenderChart = renderChart;
renderChart = function() {
    _originalRenderChart();

    // After chart renders, add indicator overlays
    if (typeof IndicatorManager !== 'undefined' && candleData.length > 0) {
        // Convert candleData to the format IndicatorEngine expects
        var formattedCandles = candleData.map(function(c) {
            return { x: new Date(c.x), y: c.y };
        });

        var overlaySeries = IndicatorManager.getOverlaySeries(formattedCandles);
        if (overlaySeries.length > 0 && cryptoChart) {
            // Add overlay series to the main chart
            overlaySeries.forEach(function(s) {
                cryptoChart.appendSeries(s);
            });
        }

        // Render sub-charts
        IndicatorManager.resetInfoStrip();
        IndicatorManager.renderSubCharts(formattedCandles);
    }
};

// Called when indicator toggle changes
function onIndicatorToggle() {
    if (candleData.length > 0) {
        renderChart();
    }
}

// ========== REAL-TIME CRYPTO CHART ==========
var currentSymbol = 'BTC';
var currentChartType = 'candle';
var currentTimeframe = 240; // minutes
var autoRefresh = true;
var refreshTimer = null;
var cryptoChart = null;
var candleData = [];
var areaData = [];
var lineData = [];

var allCoins = [
    { symbol: 'BTC',  name: 'Bitcoin',    color: '#f7931a' },
    { symbol: 'ETH',  name: 'Ethereum',   color: '#627eea' },
    { symbol: 'BNB',  name: 'BNB',         color: '#f3ba2f' },
    { symbol: 'SOL',  name: 'Solana',      color: '#14f195' },
    { symbol: 'XRP',  name: 'Ripple',      color: '#3b82f6' },
    { symbol: 'ADA',  name: 'Cardano',     color: '#0033ad' },
    { symbol: 'AVAX', name: 'Avalanche',   color: '#e84142' },
    { symbol: 'DOT',  name: 'Polkadot',    color: '#e6007a' },
    { symbol: 'LINK', name: 'Chainlink',   color: '#2a5ada' },
    { symbol: 'DOGE', name: 'Dogecoin',    color: '#c2a633' },
];

function renderCoinSelector() {
    var html = '';
    allCoins.forEach(function(coin) {
        html += '<div class="coin-tab' + (coin.symbol === currentSymbol ? ' active' : '') + '" ' +
                 'onclick="selectCoin(\'' + coin.symbol + '\', \'' + coin.name + '\', \'' + coin.color + '\')" ' +
                 'id="tab_' + coin.symbol + '">' +
                    '<div class="coin-symbol">' + coin.symbol + '</div>' +
                    '<div class="coin-price" id="tabprice_' + coin.symbol + '">—</div>' +
                    '<div class="coin-trend" id="tabtrend_' + coin.symbol + '"></div>' +
                 '</div>';
    });
    document.getElementById('coinSelector').innerHTML = html;
}

function selectCoin(symbol, name, color) {
    currentSymbol = symbol;
    document.getElementById('chartCoinBadge').textContent = symbol;
    document.getElementById('chartCoinBadge').style.background = 'linear-gradient(135deg, ' + color + ', ' + adjustColor(color, -30) + ')';
    document.getElementById('chartCoinName').textContent = name;

    // Update active tab
    document.querySelectorAll('.coin-tab').forEach(t => t.classList.remove('active'));
    document.getElementById('tab_' + symbol).classList.add('active');

    loadChartData(symbol);
}

function loadChartData(symbol) {
    var points = currentTimeframe === 15 ? 60 :
                 currentTimeframe === 60 ? 72 :
                 currentTimeframe === 240 ? 96 : 90;

    fetch("{{ route('dashboard.crypto-feed') }}?symbol=" + symbol + "&points=" + points, {
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        }
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (!data.success) return;

        candleData = data.candles;
        areaData = data.candles.map(function(c) {
            return { x: c.x, y: c.y[3] }; // close price
        });
        lineData = areaData;

        // Update price display
        updatePriceDisplay(data);

        // Render chart
        renderChart();

        // Update coin selector prices
        loadCoinSelectorPrices();
    })
    .catch(function(err) {
        console.error('Chart load failed:', err);
    });
}

function renderChart() {
    if (cryptoChart) {
        cryptoChart.destroy();
    }

    var series = [];
    var chartType = 'candlestick';

    if (currentChartType === 'candle') {
        series = [{ data: candleData }];
        chartType = 'candlestick';
    } else if (currentChartType === 'area') {
        series = [{ data: areaData, name: 'Price' }];
        chartType = 'area';
    } else {
        series = [{ data: lineData, name: 'Price' }];
        chartType = 'line';
    }

    var chartOptions = {
        series: series,
        chart: {
            type: chartType,
            height: 350,
            background: 'transparent',
            fontFamily: 'Inter, sans-serif',
            toolbar: {
                show: true,
                tools: { download: false, selection: true, zoom: true, zoomin: true, zoomout: true, pan: false, reset: true },
                autoSelected: 'zoom'
            },
            animations: {
                enabled: true,
                easing: 'easeinout',
                speed: 500
            }
        },
        theme: { mode: 'dark' },
        grid: {
            borderColor: '#334155',
            strokeDashArray: 4,
            xaxis: { lines: { show: false } },
            yaxis: { lines: { show: true } },
            padding: { right: 12 }
        },
        plotOptions: {
            candlestick: {
                colors: {
                    upward: '#10b981',
                    downward: '#ef4444'
                },
                wick: { useFillColor: true }
            }
        },
        xaxis: {
            type: 'datetime',
            labels: {
                style: { colors: '#94a3b8', fontSize: '11px' },
                datetimeFormatter: {
                    year: 'yyyy', month: "MMM 'yy", day: 'dd MMM',
                    hour: 'HH:mm', minute: 'HH:mm'
                }
            },
            axisBorder: { show: false },
            axisTicks: { show: false }
        },
        yaxis: {
            tooltip: { enabled: true },
            labels: {
                style: { colors: '#94a3b8', fontSize: '11px' },
                formatter: function(val) {
                    if (val < 1) return '$' + val.toFixed(4);
                    if (val < 100) return '$' + val.toFixed(2);
                    return '$' + val.toLocaleString('en-US', { maximumFractionDigits: 0 });
                }
            },
            opposite: false
        },
        tooltip: {
            theme: 'dark',
            style: { fontSize: '12px', background: '#1e293b' },
            custom: function(ctx) {
                var data = ctx.series[0][ctx.dataPointIndex];
                var dt = new Date(ctx.w.globals.labels[ctx.dataPointIndex] || data.x);
                var dtStr = dt.toLocaleString('en-US', { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });

                if (currentChartType === 'candle') {
                    var o = data.y[0], h = data.y[1], l = data.y[2], c = data.y[3];
                    var isUp = c >= o;
                    var color = isUp ? '#10b981' : '#ef4444';
                    return '<div style="padding: 10px 14px; background: #1e293b; border: 1px solid #334155; border-radius: 10px; min-width: 160px;">' +
                        '<div style="font-size: 11px; color: #94a3b8; margin-bottom: 6px;">' + dtStr + '</div>' +
                        '<div style="display: flex; justify-content: space-between; gap: 12px; margin-bottom: 3px;"><span style="color:#94a3b8;font-size:11px;">Open</span><span style="color:#e2e8f0;font-size:12px;font-weight:600;">$' + formatPrice(o) + '</span></div>' +
                        '<div style="display: flex; justify-content: space-between; gap: 12px; margin-bottom: 3px;"><span style="color:#94a3b8;font-size:11px;">High</span><span style="color:#10b981;font-size:12px;font-weight:600;">$' + formatPrice(h) + '</span></div>' +
                        '<div style="display: flex; justify-content: space-between; gap: 12px; margin-bottom: 3px;"><span style="color:#94a3b8;font-size:11px;">Low</span><span style="color:#ef4444;font-size:12px;font-weight:600;">$' + formatPrice(l) + '</span></div>' +
                        '<div style="display: flex; justify-content: space-between; gap: 12px; margin-top: 6px; padding-top: 6px; border-top: 1px solid #334155;"><span style="color:#94a3b8;font-size:11px;">Close</span><span style="color:' + color + ';font-size:12px;font-weight:700;">$' + formatPrice(c) + '</span></div>' +
                    '</div>';
                }
                return '<div style="padding: 10px 14px; background: #1e293b; border: 1px solid #334155; border-radius: 10px;">' +
                    '<div style="font-size: 11px; color: #94a3b8; margin-bottom: 4px;">' + dtStr + '</div>' +
                    '<div style="font-size: 14px; font-weight: 700; color: #a855f7;">$' + formatPrice(data.y) + '</div>' +
                '</div>';
            }
        }
    };

    // Apply gradient fills for area chart
    if (currentChartType === 'area') {
        chartOptions.fill = {
            type: 'gradient',
            gradient: {
                shadeIntensity: 1,
                opacityFrom: 0.35,
                opacityTo: 0.02,
                stops: [0, 90, 100],
                gradientToColors: ['#a855f7']
            }
        };
        chartOptions.colors = ['#6366f1'];
        chartOptions.stroke = { curve: 'smooth', width: 2.5, colors: ['#a855f7'] };
        chartOptions.markers = { size: 0, hover: { size: 4 } };
    } else if (currentChartType === 'line') {
        chartOptions.colors = ['#6366f1'];
        chartOptions.stroke = { curve: 'smooth', width: 2, colors: ['#3b82f6'] };
        chartOptions.markers = { size: 0, hover: { size: 4 } };
        chartOptions.fill = { type: 'solid', opacity: 0 };
    }

    cryptoChart = new ApexCharts(document.getElementById('cryptoChartMain'), chartOptions);
    cryptoChart.render();
}

function updatePriceDisplay(data) {
    var priceEl = document.getElementById('chartCoinPrice');
    var changeEl = document.getElementById('chartCoinChange');

    priceEl.textContent = '$' + formatPrice(data.current);

    var isUp = data.change_pct >= 0;
    var color = isUp ? 'var(--green)' : 'var(--red)';
    var icon = isUp ? 'caret-up' : 'caret-down';

    changeEl.style.color = color;
    changeEl.innerHTML = '<i class="fas fa-' + icon + '"></i> <span>' + (isUp ? '+' : '') + data.change_pct + '%</span>';

    document.getElementById('chart24High').textContent = '$' + formatPrice(data.high);
    document.getElementById('chart24Low').textContent = '$' + formatPrice(data.low);
    document.getElementById('chart24Volume').textContent = '$' + data.volume + 'M';

    document.getElementById('chartLastUpdate').textContent = 'Updated ' + new Date().toLocaleTimeString();
}

function loadCoinSelectorPrices() {
    allCoins.forEach(function(coin) {
        fetch("{{ route('dashboard.crypto-tick') }}?symbol=" + coin.symbol, {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            var priceEl = document.getElementById('tabprice_' + coin.symbol);
            var trendEl = document.getElementById('tabtrend_' + coin.symbol);
            if (priceEl) priceEl.textContent = '$' + formatPrice(data.price);
            if (trendEl) {
                var color = data.change_pct >= 0 ? 'var(--green)' : 'var(--red)';
                trendEl.style.color = color;
                trendEl.textContent = (data.change_pct >= 0 ? '+' : '') + data.change_pct + '%';
            }
        })
        .catch(function() {});
    });
}

function setChartType(type) {
    currentChartType = type;
    document.querySelectorAll('.chart-type-btn').forEach(b => b.classList.remove('active'));
    document.querySelector('[data-type="' + type + '"]').classList.add('active');
    renderChart();
}

function setTimeframe(tf, btn) {
    currentTimeframe = tf;
    document.querySelectorAll('.tf-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    loadChartData(currentSymbol);
}

function toggleAutoRefresh() {
    autoRefresh = !autoRefresh;
    var btn = document.getElementById('autoRefreshBtn');
    if (autoRefresh) {
        btn.style.color = 'var(--green)';
        btn.style.borderColor = 'var(--green)';
        startAutoRefresh();
    } else {
        btn.style.color = 'var(--text-dim)';
        btn.style.borderColor = 'var(--border)';
        clearInterval(refreshTimer);
    }
}

function startAutoRefresh() {
    if (refreshTimer) clearInterval(refreshTimer);
    refreshTimer = setInterval(function() {
        // Fetch a new tick and append to chart
        fetch("{{ route('dashboard.crypto-tick') }}?symbol=" + currentSymbol, {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            // Update price display
            var priceEl = document.getElementById('chartCoinPrice');
            var changeEl = document.getElementById('chartCoinChange');
            priceEl.textContent = '$' + formatPrice(data.price);

            var isUp = data.change_pct >= 0;
            changeEl.style.color = isUp ? 'var(--green)' : 'var(--red)';
            changeEl.innerHTML = '<i class="fas fa-caret-' + (isUp ? 'up' : 'down') + '"></i> <span>' + (isUp ? '+' : '') + data.change_pct + '%</span>';
            document.getElementById('chartLastUpdate').textContent = 'Updated ' + new Date().toLocaleTimeString();

            // Flash animation
            priceEl.style.transition = 'color 0.3s';
            priceEl.style.color = isUp ? 'var(--green)' : 'var(--red)';
            setTimeout(function() { priceEl.style.color = 'var(--text-bright)'; }, 400);

            // Append new candle to chart
            if (cryptoChart && candleData.length > 0) {
                var lastCandle = candleData[candleData.length - 1];
                var newTime = data.time;

                // If same interval, update last candle's close
                if (currentChartType === 'candle') {
                    lastCandle.y[3] = data.price;
                    if (data.price > lastCandle.y[1]) lastCandle.y[1] = data.price;
                    if (data.price < lastCandle.y[2]) lastCandle.y[2] = data.price;
                    cryptoChart.updateSeries([{ data: candleData }]);
                } else {
                    areaData[areaData.length - 1] = { x: newTime, y: data.price };
                    cryptoChart.updateSeries([{ data: areaData }]);
                }
            }

            // Also update coin selector prices
            loadCoinSelectorPrices();
        })
        .catch(function() {});
    }, 5000); // 5 second refresh
}

function formatPrice(price) {
    if (price < 1) return price.toFixed(4);
    if (price < 100) return price.toFixed(2);
    return price.toLocaleString('en-US', { maximumFractionDigits: 2 });
}

function adjustColor(hex, percent) {
    var r = parseInt(hex.replace('#', '').substr(0, 2), 16);
    var g = parseInt(hex.replace('#', '').substr(2, 2), 16);
    var b = parseInt(hex.replace('#', '').substr(4, 2), 16);
    r = Math.max(0, Math.min(255, r + percent));
    g = Math.max(0, Math.min(255, g + percent));
    b = Math.max(0, Math.min(255, b + percent));
    return '#' + r.toString(16).padStart(2, '0') + g.toString(16).padStart(2, '0') + b.toString(16).padStart(2, '0');
}

// Initialize
renderCoinSelector();
loadChartData('BTC');
startAutoRefresh();
</script>
