{{--
    Trading Indicators Toolbar + Calculation Engine
    Include after ApexCharts is loaded.
    Indicators: SMA, EMA, Bollinger Bands, RSI, MACD, Volume
--}}
<style>
.indicator-toolbar {
    display: flex;
    gap: 4px;
    flex-wrap: wrap;
    padding: 8px 16px;
    border-bottom: 1px solid var(--border);
    background: rgba(99,102,241,0.02);
}
.ind-btn {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    background: var(--bg-input);
    border: 1px solid var(--border);
    border-radius: 8px;
    padding: 5px 10px;
    font-size: 11px;
    font-weight: 600;
    color: var(--text-muted);
    cursor: pointer;
    transition: all 0.15s;
    user-select: none;
}
.ind-btn:hover {
    border-color: rgba(99,102,241,0.4);
    color: var(--text-bright);
}
.ind-btn.active {
    background: linear-gradient(135deg, rgba(99,102,241,0.15), rgba(168,85,247,0.1));
    border-color: rgba(99,102,241,0.5);
    color: var(--text-bright);
}
.ind-btn .ind-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    flex-shrink: 0;
}
.ind-settings-popup {
    position: absolute;
    top: 100%;
    left: 0;
    margin-top: 6px;
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: 10px;
    padding: 14px;
    min-width: 220px;
    box-shadow: 0 8px 28px rgba(0,0,0,0.4);
    z-index: 1000;
    display: none;
    font-size: 12px;
}
.ind-settings-popup.show { display: block; }
.ind-settings-popup label {
    font-size: 11px;
    color: var(--text-muted);
    font-weight: 600;
    margin-bottom: 4px;
    display: block;
}
.ind-settings-popup input,
.ind-settings-popup select {
    background: var(--bg-input);
    border: 1px solid var(--border);
    border-radius: 6px;
    padding: 6px 8px;
    color: var(--text);
    font-size: 12px;
    width: 100%;
}
.ind-sub-chart {
    margin-top: 4px;
}
.ind-info-strip {
    display: flex;
    gap: 14px;
    padding: 6px 16px;
    font-size: 11px;
    color: var(--text-muted);
    border-top: 1px solid var(--border);
    flex-wrap: wrap;
}
.ind-info-strip .ind-val {
    font-weight: 600;
    color: var(--text-bright);
}
</style>

<script>
// ════════════════════════════════════════════════════════════
//  TECHNICAL INDICATORS CALCULATION ENGINE
//  All functions take an array of {x: Date, y: [O,H,L,C]} candles
//  and return series data for ApexCharts
// ════════════════════════════════════════════════════════════

const IndicatorEngine = {
    // ─── SMA (Simple Moving Average) ───────────────────────
    SMA(candles, period = 20) {
        const result = [];
        for (let i = 0; i < candles.length; i++) {
            if (i < period - 1) continue;
            let sum = 0;
            for (let j = i - period + 1; j <= i; j++) {
                sum += candles[j].y[3]; // close price
            }
            result.push({ x: candles[i].x, y: +(sum / period).toFixed(2) });
        }
        return result;
    },

    // ─── EMA (Exponential Moving Average) ──────────────────
    EMA(candles, period = 12) {
        const result = [];
        const k = 2 / (period + 1);
        let ema = 0;
        // Initialize with SMA
        for (let i = 0; i < candles.length; i++) {
            if (i < period - 1) continue;
            if (result.length === 0) {
                let sum = 0;
                for (let j = i - period + 1; j <= i; j++) sum += candles[j].y[3];
                ema = sum / period;
            } else {
                ema = candles[i].y[3] * k + ema * (1 - k);
            }
            result.push({ x: candles[i].x, y: +ema.toFixed(2) });
        }
        return result;
    },

    // ─── Bollinger Bands ────────────────────────────────────
    BollingerBands(candles, period = 20, stdDev = 2) {
        const upper = [], lower = [], middle = [];
        for (let i = 0; i < candles.length; i++) {
            if (i < period - 1) continue;
            let sum = 0;
            const slice = [];
            for (let j = i - period + 1; j <= i; j++) {
                sum += candles[j].y[3];
                slice.push(candles[j].y[3]);
            }
            const sma = sum / period;
            // Standard deviation
            let variance = 0;
            for (const v of slice) variance += (v - sma) ** 2;
            const sd = Math.sqrt(variance / period);
            const x = candles[i].x;
            middle.push({ x, y: +sma.toFixed(2) });
            upper.push({ x, y: +(sma + sd * stdDev).toFixed(2) });
            lower.push({ x, y: +(sma - sd * stdDev).toFixed(2) });
        }
        return { upper, middle, lower };
    },

    // ─── RSI (Relative Strength Index) ─────────────────────
    RSI(candles, period = 14) {
        const result = [];
        const gains = [], losses = [];
        for (let i = 1; i < candles.length; i++) {
            const change = candles[i].y[3] - candles[i-1].y[3];
            gains.push(Math.max(0, change));
            losses.push(Math.max(0, -change));
            if (i >= period) {
                let avgGain, avgLoss;
                if (result.length === 0) {
                    avgGain = gains.slice(0, period).reduce((a,b) => a+b, 0) / period;
                    avgLoss = losses.slice(0, period).reduce((a,b) => a+b, 0) / period;
                } else {
                    const prev = result[result.length - 1];
                    const prevRS = prev._rs;
                    // Smoothed
                    const lastGain = gains[i];
                    const lastLoss = losses[i];
                    avgGain = (gains.slice(i - period + 1, i + 1).reduce((a,b) => a+b, 0)) / period;
                    avgLoss = (losses.slice(i - period + 1, i + 1).reduce((a,b) => a+b, 0)) / period;
                }
                const rs = avgLoss === 0 ? 100 : avgGain / avgLoss;
                const rsi = 100 - (100 / (1 + rs));
                result.push({ x: candles[i].x, y: +rsi.toFixed(2), _rs: rs });
            }
        }
        return result;
    },

    // ─── MACD (Moving Average Convergence Divergence) ───────
    MACD(candles, fast = 12, slow = 26, signal = 9) {
        const emaFast = this.EMA(candles, fast);
        const emaSlow = this.EMA(candles, slow);
        // Align by date
        const macdLine = [];
        const slowMap = {};
        emaSlow.forEach(d => slowMap[d.x.getTime()] = d.y);
        emaFast.forEach(d => {
            const slowVal = slowMap[d.x.getTime()];
            if (slowVal !== undefined) {
                macdLine.push({ x: d.x, y: +(d.y - slowVal).toFixed(2) });
            }
        });
        // Signal line = EMA of MACD
        const signalLine = [];
        const k = 2 / (signal + 1);
        let ema = 0;
        for (let i = 0; i < macdLine.length; i++) {
            if (i < signal - 1) continue;
            if (signalLine.length === 0) {
                let sum = 0;
                for (let j = i - signal + 1; j <= i; j++) sum += macdLine[j].y;
                ema = sum / signal;
            } else {
                ema = macdLine[i].y * k + ema * (1 - k);
            }
            signalLine.push({ x: macdLine[i].x, y: +ema.toFixed(2) });
        }
        // Histogram = MACD - Signal
        const histogram = [];
        const sigMap = {};
        signalLine.forEach(d => sigMap[d.x.getTime()] = d.y);
        macdLine.forEach(d => {
            const sv = sigMap[d.x.getTime()];
            if (sv !== undefined) {
                histogram.push({ x: d.x, y: +(d.y - sv).toFixed(2) });
            }
        });
        return { macdLine, signalLine, histogram };
    },

    // ─── Volume Bars ────────────────────────────────────────
    Volume(candles) {
        return candles.map(c => ({
            x: c.x,
            y: c.volume || Math.abs(c.y[3] - c.y[0]) * 1000 // approx if no volume
        }));
    },

    // ─── Stochastic Oscillator ─────────────────────────────
    Stochastic(candles, period = 14, smoothK = 3) {
        const kValues = [];
        for (let i = 0; i < candles.length; i++) {
            if (i < period - 1) continue;
            let highest = -Infinity, lowest = Infinity;
            for (let j = i - period + 1; j <= i; j++) {
                highest = Math.max(highest, candles[j].y[1]);
                lowest = Math.min(lowest, candles[j].y[2]);
            }
            const close = candles[i].y[3];
            const k = highest === lowest ? 50 : ((close - lowest) / (highest - lowest)) * 100;
            kValues.push({ x: candles[i].x, y: +k.toFixed(2) });
        }
        // Smooth K
        const result = [];
        for (let i = 0; i < kValues.length; i++) {
            if (i < smoothK - 1) continue;
            let sum = 0;
            for (let j = i - smoothK + 1; j <= i; j++) sum += kValues[j].y;
            result.push({ x: kValues[i].x, y: +(sum / smoothK).toFixed(2) });
        }
        return result;
    }
};

// ════════════════════════════════════════════════════════════
//  INDICATOR MANAGER — handles toggling and rendering
// ════════════════════════════════════════════════════════════
const IndicatorManager = {
    active: {
        sma20:  false,
        sma50:  false,
        sma200: false,
        ema12:  false,
        ema26:  false,
        bb:     false,
        rsi:    false,
        macd:   false,
        volume: false,
        stoch:  false,
    },
    colors: {
        sma20:  '#3b82f6',
        sma50:  '#a855f7',
        sma200: '#f59e0b',
        ema12:  '#10b981',
        ema26:  '#ef4444',
        bb:     '#6366f1',
        rsi:    '#7c3aed',
        macd:   '#2563eb',
        volume: '#3b82f6',
        stoch:  '#a855f7',
    },

    buildToolbar(containerId) {
        const container = document.getElementById(containerId);
        if (!container) return;
        const indicators = [
            { id: 'sma20',  label: 'SMA 20',   color: this.colors.sma20,  type: 'overlay' },
            { id: 'sma50',  label: 'SMA 50',   color: this.colors.sma50,  type: 'overlay' },
            { id: 'sma200', label: 'SMA 200',  color: this.colors.sma200, type: 'overlay' },
            { id: 'ema12',  label: 'EMA 12',   color: this.colors.ema12,  type: 'overlay' },
            { id: 'ema26',  label: 'EMA 26',   color: this.colors.ema26,  type: 'overlay' },
            { id: 'bb',     label: 'Bollinger', color: this.colors.bb,     type: 'overlay' },
            { id: 'rsi',    label: 'RSI',      color: this.colors.rsi,    type: 'sub' },
            { id: 'macd',   label: 'MACD',    color: this.colors.macd,   type: 'sub' },
            { id: 'volume', label: 'Volume',  color: this.colors.volume,  type: 'sub' },
            { id: 'stoch',  label: 'Stochastic', color: this.colors.stoch, type: 'sub' },
        ];
        let html = '<div class="indicator-toolbar">';
        indicators.forEach(ind => {
            html += `
                <button class="ind-btn" data-ind="${ind.id}" onclick="IndicatorManager.toggle('${ind.id}')" id="btn-${ind.id}">
                    <span class="ind-dot" style="background:${ind.color}"></span>
                    ${ind.label}
                </button>
            `;
        });
        html += '</div>';
        // Sub-chart container for RSI/MACD/Volume/Stochastic
        html += '<div id="indicatorSubCharts" style="display:none;"></div>';
        // Info strip
        html += '<div class="ind-info-strip" id="indicatorInfoStrip" style="display:none;"></div>';
        container.innerHTML = html;
    },

    toggle(indId) {
        this.active[indId] = !this.active[indId];
        const btn = document.getElementById(`btn-${indId}`);
        if (btn) btn.classList.toggle('active', this.active[indId]);
        // Trigger chart update — the chart page must implement this
        if (typeof onIndicatorToggle === 'function') onIndicatorToggle();
    },

    // Check if any overlay indicators are active
    hasOverlay() {
        return this.active.sma20 || this.active.sma50 || this.active.sma200 ||
               this.active.ema12 || this.active.ema26 || this.active.bb;
    },

    // Check if any sub-chart indicators are active
    hasSubChart() {
        return this.active.rsi || this.active.macd || this.active.volume || this.active.stoch;
    },

    // Build overlay series for the main chart
    getOverlaySeries(candles) {
        const series = [];
        if (this.active.sma20) {
            series.push({
                name: 'SMA 20',
                type: 'line',
                data: IndicatorEngine.SMA(candles, 20),
                color: this.colors.sma20,
                strokeWidth: 1.5,
            });
        }
        if (this.active.sma50) {
            series.push({
                name: 'SMA 50',
                type: 'line',
                data: IndicatorEngine.SMA(candles, 50),
                color: this.colors.sma50,
                strokeWidth: 1.5,
            });
        }
        if (this.active.sma200) {
            series.push({
                name: 'SMA 200',
                type: 'line',
                data: IndicatorEngine.SMA(candles, 200),
                color: this.colors.sma200,
                strokeWidth: 1.5,
            });
        }
        if (this.active.ema12) {
            series.push({
                name: 'EMA 12',
                type: 'line',
                data: IndicatorEngine.EMA(candles, 12),
                color: this.colors.ema12,
                strokeWidth: 1.5,
            });
        }
        if (this.active.ema26) {
            series.push({
                name: 'EMA 26',
                type: 'line',
                data: IndicatorEngine.EMA(candles, 26),
                color: this.colors.ema26,
                strokeWidth: 1.5,
            });
        }
        if (this.active.bb) {
            const bb = IndicatorEngine.BollingerBands(candles, 20, 2);
            series.push({ name: 'BB Upper', type: 'line', data: bb.upper, color: this.colors.bb, strokeWidth: 1, strokeDashArray: 4 });
            series.push({ name: 'BB Middle', type: 'line', data: bb.middle, color: this.colors.bb, strokeWidth: 1, opacity: 0.5 });
            series.push({ name: 'BB Lower', type: 'line', data: bb.lower, color: this.colors.bb, strokeWidth: 1, strokeDashArray: 4 });
        }
        return series;
    },

    // Render sub-charts (RSI, MACD, Volume, Stochastic)
    renderSubCharts(candles) {
        const container = document.getElementById('indicatorSubCharts');
        if (!container) return;

        if (!this.hasSubChart()) {
            container.style.display = 'none';
            container.innerHTML = '';
            return;
        }

        container.style.display = 'block';
        let html = '';

        if (this.active.rsi) {
            const rsiData = IndicatorEngine.RSI(candles, 14);
            const lastRSI = rsiData.length ? rsiData[rsiData.length-1].y : 0;
            html += `<div class="ind-sub-chart" id="rsiChart" style="height:120px;"></div>`;
            // Update info strip
            this.updateInfoStrip('RSI', lastRSI, lastRSI > 70 ? 'Overbought' : lastRSI < 30 ? 'Oversold' : 'Neutral');
            setTimeout(() => this.renderRSI(rsiData), 10);
        }

        if (this.active.macd) {
            const macdData = IndicatorEngine.MACD(candles, 12, 26, 9);
            const lastMACD = macdData.macdLine.length ? macdData.macdLine[macdData.macdLine.length-1].y : 0;
            html += `<div class="ind-sub-chart" id="macdChart" style="height:120px;"></div>`;
            this.updateInfoStrip('MACD', lastMACD, lastMACD > 0 ? 'Bullish' : 'Bearish');
            setTimeout(() => this.renderMACD(macdData), 10);
        }

        if (this.active.volume) {
            const volData = IndicatorEngine.Volume(candles);
            html += `<div class="ind-sub-chart" id="volumeChart" style="height:80px;"></div>`;
            const lastVol = volData.length ? volData[volData.length-1].y : 0;
            this.updateInfoStrip('Volume', lastVol.toLocaleString(), '');
            setTimeout(() => this.renderVolume(volData, candles), 10);
        }

        if (this.active.stoch) {
            const stochData = IndicatorEngine.Stochastic(candles, 14, 3);
            const lastK = stochData.length ? stochData[stochData.length-1].y : 0;
            html += `<div class="ind-sub-chart" id="stochChart" style="height:100px;"></div>`;
            this.updateInfoStrip('Stoch %K', lastK, lastK > 80 ? 'Overbought' : lastK < 20 ? 'Oversold' : 'Neutral');
            setTimeout(() => this.renderStoch(stochData), 10);
        }

        container.innerHTML = html;
        // Re-render since we replaced innerHTML
        if (this.active.rsi) {
            const rsiData = IndicatorEngine.RSI(candles, 14);
            this.renderRSI(rsiData);
        }
        if (this.active.macd) {
            const macdData = IndicatorEngine.MACD(candles, 12, 26, 9);
            this.renderMACD(macdData);
        }
        if (this.active.volume) {
            const volData = IndicatorEngine.Volume(candles);
            this.renderVolume(volData, candles);
        }
        if (this.active.stoch) {
            const stochData = IndicatorEngine.Stochastic(candles, 14, 3);
            this.renderStoch(stochData);
        }
    },

    infoValues: {},
    updateInfoStrip(label, value, signal) {
        this.infoValues[label] = { value, signal };
        const strip = document.getElementById('indicatorInfoStrip');
        if (!strip) return;
        strip.style.display = 'flex';
        let html = '';
        for (const [k, v] of Object.entries(this.infoValues)) {
            const signalColor = v.signal === 'Overbought' || v.signal === 'Bearish' ? '#ef4444' :
                                v.signal === 'Oversold' || v.signal === 'Bullish' ? '#10b981' :
                                'var(--text-muted)';
            html += `<span>${k}: <span class="ind-val">${typeof v.value === 'number' ? v.value.toFixed(2) : v.value}</span>`;
            if (v.signal) html += ` <span style="color:${signalColor};font-weight:600">(${v.signal})</span>`;
            html += '</span>';
        }
        strip.innerHTML = html;
    },

    resetInfoStrip() {
        this.infoValues = {};
        const strip = document.getElementById('indicatorInfoStrip');
        if (strip) { strip.style.display = 'none'; strip.innerHTML = ''; }
    },

    // ─── RSI Chart ──────────────────────────────────────────
    renderRSI(data) {
        const el = document.getElementById('rsiChart');
        if (!el) return;
        const opts = {
            chart: { type: 'line', height: 120, background: 'transparent', sparkline: { enabled: false }, toolbar: { show: false }, animations: { enabled: false } },
            series: [{ name: 'RSI (14)', data: data }],
            colors: [this.colors.rsi],
            stroke: { width: 1.5 },
            xaxis: { type: 'datetime', labels: { show: false }, axisBorder: { show: false } },
            yaxis: { min: 0, max: 100, labels: { style: { colors: '#64748b', fontSize: '10px' } } },
            annotations: {
                yaxis: [
                    { y: 70, strokeDashArray: 3, borderColor: '#ef4444', label: { text: '70', style: { color: '#ef4444', fontSize: '9px' } } },
                    { y: 30, strokeDashArray: 3, borderColor: '#10b981', label: { text: '30', style: { color: '#10b981', fontSize: '9px' } } },
                    { y: 50, strokeDashArray: 1, borderColor: '#64748b', label: { text: '50', style: { color: '#64748b', fontSize: '9px' } } },
                ]
            },
            grid: { borderColor: 'rgba(99,102,241,0.08)', strokeDashArray: 3 },
            tooltip: { theme: 'dark' },
            title: { text: 'RSI (14)', align: 'left', style: { fontSize: '11px', color: '#64748b', fontWeight: 600 } },
        };
        const ch = new ApexCharts(el, opts);
        ch.render();
    },

    // ─── MACD Chart ─────────────────────────────────────────
    renderMACD(data) {
        const el = document.getElementById('macdChart');
        if (!el) return;
        const opts = {
            chart: { type: 'bar', height: 120, background: 'transparent', sparkline: { enabled: false }, toolbar: { show: false }, animations: { enabled: false } },
            series: [
                { name: 'MACD', type: 'line', data: data.macdLine, color: '#3b82f6' },
                { name: 'Signal', type: 'line', data: data.signalLine, color: '#ef4444' },
                { name: 'Histogram', type: 'bar', data: data.histogram, color: this.colors.macd },
            ],
            stroke: { width: [1.5, 1.5, 0] },
            xaxis: { type: 'datetime', labels: { show: false }, axisBorder: { show: false } },
            yaxis: { labels: { style: { colors: '#64748b', fontSize: '10px' } } },
            grid: { borderColor: 'rgba(99,102,241,0.08)', strokeDashArray: 3 },
            plotOptions: { bar: { columnWidth: '60%' } },
            tooltip: { theme: 'dark' },
            title: { text: 'MACD (12, 26, 9)', align: 'left', style: { fontSize: '11px', color: '#64748b', fontWeight: 600 } },
        };
        const ch = new ApexCharts(el, opts);
        ch.render();
    },

    // ─── Volume Chart ──────────────────────────────────────
    renderVolume(volData, candles) {
        const el = document.getElementById('volumeChart');
        if (!el) return;
        // Color bars based on candle direction
        const colors = volData.map((v, i) => {
            const c = candles[i];
            return c && c.y[3] >= c.y[0] ? '#10b981' : '#ef4444';
        });
        const opts = {
            chart: { type: 'bar', height: 80, background: 'transparent', sparkline: { enabled: true }, toolbar: { show: false }, animations: { enabled: false } },
            series: [{ name: 'Volume', data: volData.map((v, i) => ({ x: v.x, y: v.y, fillColor: colors[i] })) }],
            colors: ['#3b82f6'],
            stroke: { width: 0 },
            xaxis: { type: 'datetime', labels: { show: false }, axisBorder: { show: false } },
            yaxis: { labels: { show: false } },
            grid: { show: false },
            plotOptions: { bar: { columnWidth: '70%', colors: { ranges: [{ from: 0, to: Infinity, color: '#3b82f6' }] } } },
            tooltip: { theme: 'dark' },
            title: { text: 'Volume', align: 'left', style: { fontSize: '11px', color: '#64748b', fontWeight: 600 } },
        };
        const ch = new ApexCharts(el, opts);
        ch.render();
    },

    // ─── Stochastic Chart ───────────────────────────────────
    renderStoch(data) {
        const el = document.getElementById('stochChart');
        if (!el) return;
        const opts = {
            chart: { type: 'line', height: 100, background: 'transparent', sparkline: { enabled: false }, toolbar: { show: false }, animations: { enabled: false } },
            series: [{ name: '%K (14,3)', data: data }],
            colors: [this.colors.stoch],
            stroke: { width: 1.5 },
            xaxis: { type: 'datetime', labels: { show: false }, axisBorder: { show: false } },
            yaxis: { min: 0, max: 100, labels: { style: { colors: '#64748b', fontSize: '10px' } } },
            annotations: {
                yaxis: [
                    { y: 80, strokeDashArray: 3, borderColor: '#ef4444', label: { text: '80', style: { color: '#ef4444', fontSize: '9px' } } },
                    { y: 20, strokeDashArray: 3, borderColor: '#10b981', label: { text: '20', style: { color: '#10b981', fontSize: '9px' } } },
                ]
            },
            grid: { borderColor: 'rgba(99,102,241,0.08)', strokeDashArray: 3 },
            tooltip: { theme: 'dark' },
            title: { text: 'Stochastic (14, 3)', align: 'left', style: { fontSize: '11px', color: '#64748b', fontWeight: 600 } },
        };
        const ch = new ApexCharts(el, opts);
        ch.render();
    },
};
</script>
