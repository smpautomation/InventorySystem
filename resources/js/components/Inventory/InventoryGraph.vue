<template>
    <div class="graph-wrap">

      <div class="graph-header">
        <div class="graph-eyebrow">Daily WIP Trend</div>
        <h3 class="graph-title">6AM Snapshot — {{ currentMonthLabel }}</h3>
      </div>

      <div v-if="loading" class="graph-loading">Loading graph data…</div>
      <div v-else-if="error" class="graph-error">⚠ {{ error }}</div>
      <div v-else-if="!hasData" class="graph-empty">No 6AM snapshot data available for this month.</div>

      <template v-else>
        <!-- Chart -->
        <div class="chart-container">
          <Chart type="bar" :data="chartData" :options="chartOptions" />
        </div>

        <!-- Data table -->
        <div class="table-wrap">
          <table class="data-table">
            <thead>
              <tr>
                <th class="process-col">Process</th>
                <th
                  v-for="label in dateLabels"
                  :key="label"
                  class="num-col"
                >{{ label }}</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="process in sortedProcesses" :key="process" class="data-row">
                <td class="process-cell">
                  <span class="legend-dot" :style="{ background: processColor(process) }"></span>
                  {{ process }}
                </td>
                <td
                  v-for="label in dateLabels"
                  :key="label"
                  class="num-col mono"
                >{{ getVal(label, process) }}</td>
              </tr>
            </tbody>
            <tfoot>
              <tr class="total-row">
                <td class="process-cell total-label">Total</td>
                <td
                  v-for="label in dateLabels"
                  :key="label"
                  class="num-col mono total-val"
                >{{ getDateTotal(label) }}</td>
              </tr>
            </tfoot>
          </table>
        </div>

        <template v-if="washSummary.length">
            <div class="wash-header">
                <div class="wash-title">
                <span class="wash-badge">WASH</span>
                For Wash Summary
                <span class="wash-date">{{ washDateLabel }}</span>
                </div>
                <div class="wash-totals">
                <span class="wash-stat">
                    <span class="wash-stat-label">Total Weight</span>
                    <span class="wash-stat-val">{{ washGrandTotal }}<span class="wash-stat-unit">t</span></span>
                </span>
                <span class="wash-divider"></span>
                <span class="wash-stat">
                    <span class="wash-stat-label">Total Lots</span>
                    <span class="wash-stat-val">{{ washTotalLots }}</span>
                </span>
                </div>
            </div>

            <div class="table-wrap">
                <table class="data-table">
                <thead>
                    <tr>
                    <th class="process-col">Area</th>
                    <th class="num-col">Lots</th>
                    <th class="num-col">Weight (t)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                    v-for="row in washSummary"
                    :key="row.area"
                    class="data-row"
                    >
                    <td><span class="area-badge">{{ row.area }}</span></td>
                    <td class="num-col mono">{{ row.work_order_count }}</td>
                    <td class="num-col mono">{{ Number(row.tons).toFixed(4) }}</td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr class="total-row">
                    <td class="total-label">Total</td>
                    <td class="num-col mono total-val">{{ washTotalLots }}</td>
                    <td class="num-col mono total-val">{{ washGrandTotal }}t</td>
                    </tr>
                </tfoot>
                </table>
            </div>
        </template>
      </template>

    </div>
  </template>

  <script>
  import { Chart } from 'vue-chartjs'
  import {
    Chart as ChartJS,
    BarElement, BarController,
    LineElement, LineController,
    PointElement,
    CategoryScale, LinearScale,
    Tooltip, Legend,
  } from 'chart.js'

  ChartJS.register(
    BarElement, BarController,
    LineElement, LineController,
    PointElement,
    CategoryScale, LinearScale,
    Tooltip, Legend,
  )

  // Fixed process colors — consistent across dates
  const PROCESS_COLORS = {
    CGH:    '#2b82cb',
    SL:     '#9f7aea',
    LAP:    '#38b2ac',
    SG:     '#38a169',
    HD:     '#d69e2e',
    OG:     '#ed8936',
    MC:     '#e53e3e',
    WASH:   '#667eea',
    RMP:    '#fc8181',
    RMR:    '#f6ad55',
    BAR:    '#68d391',
    CHAMFER:'#76e4f7',
    VD:     '#b794f4',
    IDG:    '#f687b3',
    CG:     '#fbd38d',
    RX:     '#90cdf4',
    IG:     '#9ae6b4',
    SCR:    '#feb2b2',
  }

  const FALLBACK_COLORS = [
    '#2b82cb','#9f7aea','#38b2ac','#38a169','#d69e2e',
    '#ed8936','#e53e3e','#667eea','#fc8181','#68d391',
  ]

  function parseDateFromFilename(filename) {
    // WO_04062026_(6AM).xlsx → extract 04062026 → Apr 6
    const match = filename.match(/(\d{8})/)
    if (!match) return filename
    const raw   = match[1]
    const month = raw.slice(0, 2)
    const day   = raw.slice(2, 4)
    return `${month}/${day}`
  }

  export default {
    name: 'InventoryGraph',
    components: { Chart },

    props: {
        plant: { type: String, required: true },
    },

    async mounted() {
        await this.fetchGraph()
    },

    data() {
        return {
            rawData: {},
            washSummary: [],
            washDate:    '',
            loading: false,
            error:   null,
        }
    },

    computed: {
        currentMonthLabel() {
        return new Date().toLocaleDateString('en-US', { month: 'long', year: 'numeric' })
        },

        hasData() {
        return Object.keys(this.rawData).length > 0
        },

        sortedFiles() {
        return Object.keys(this.rawData).sort((a, b) => {
            const ma = a.match(/(\d{8})/)
            const mb = b.match(/(\d{8})/)
            if (!ma || !mb) return 0
            // MMDDYYYY → reorder to YYYYMMDD for correct chronological sort
            const toSortable = s => s.slice(4, 8) + s.slice(0, 2) + s.slice(2, 4)
            return toSortable(ma[1]).localeCompare(toSortable(mb[1]))
        })
        },

        dateLabels() {
        return this.sortedFiles.map(parseDateFromFilename)
        },

        sortedProcesses() {
        const totals = {}
        Object.values(this.rawData).forEach(processes => {
            if (!processes || !Object.keys(processes).length) return
            Object.entries(processes).forEach(([process, tons]) => {
                totals[process] = (totals[process] ?? 0) + tons
            })
        })
        return Object.keys(totals).sort((a, b) => totals[b] - totals[a])
        },

        chartData() {
        const datasets = this.sortedProcesses.map(process => ({
            type:            'bar',
            label:           process,
            data:            this.sortedFiles.map(file => {
            const val = this.rawData[file]?.[process] ?? 0
            return parseFloat(val.toFixed(4))
            }),
            backgroundColor: this.processColor(process),
            borderColor:     'transparent',
            borderRadius:    2,
            stack:           'main',
        }))

        // Total line
        datasets.push({
            type:            'line',
            label:           'Total',
            data: this.sortedFiles.map(file => {
                const vals  = Object.values(this.rawData[file] ?? {})
                if (!vals.length) return null   // empty day — gap in line
                const total = vals.reduce((s, v) => s + v, 0)
                return parseFloat(total.toFixed(4))
            }),
            borderColor:          '#5ba3e0',
            backgroundColor:      'transparent',
            borderWidth:          2,
            pointRadius:          3,
            pointBackgroundColor: '#5ba3e0',
            tension:              0,
            stack:                'total',
            order:                -1,
            spanGaps:             false,   // don't connect across missing days
        })

        return { labels: this.dateLabels, datasets }
        },

        chartOptions() {
        return {
            responsive:          true,
            maintainAspectRatio: false,
            plugins: {
            legend: { display: false },
            tooltip: {
                backgroundColor: '#112240',
                titleColor:      '#5ba3e0',
                bodyColor:       '#c8d8e8',
                borderColor:     'rgba(43,130,203,0.3)',
                borderWidth:     1,
                padding:         12,
                callbacks: {
                label(ctx) {
                    return ` ${ctx.dataset.label}: ${ctx.parsed.y.toFixed(4)}t`
                },
                footer(items) {
                    const bars = items.filter(i => i.dataset.type === 'bar')
                    if (!bars.length) return ''
                    const total = bars.reduce((s, i) => s + i.parsed.y, 0)
                    return `Total: ${total.toFixed(4)}t`
                },
                },
            },
            },
            scales: {
            x: {
                stacked: true,
                grid:    { color: 'rgba(255,255,255,0.04)' },
                ticks:   { color: '#8899aa', font: { size: 11 } },
                border:  { color: 'rgba(255,255,255,0.08)' },
            },
            y: {
                stacked: true,
                grid:    { color: 'rgba(255,255,255,0.06)' },
                ticks: {
                color: '#8899aa',
                font:  { size: 11 },
                callback: v => `${v}t`,
                },
                border: { color: 'rgba(255,255,255,0.08)' },
                title: {
                display: true,
                text:    'Tons',
                color:   '#8899aa',
                font:    { size: 11 },
                },
            },
            },
            animation: { duration: 600, easing: 'easeInOutQuart' },
        }
        },

        washDateLabel() {
            if (!this.washDate) return ''
            const match = this.washDate.match(/(\d{8})/)
            if (!match) return this.washDate
            return parseDateFromFilename(this.washDate)
        },

        washGrandTotal() {
            return this.washSummary.reduce((s, r) => s + Number(r.tons), 0).toFixed(4)
        },

        washTotalLots() {
            return this.washSummary.reduce((s, r) => s + Number(r.work_order_count), 0)
        },
    },

    methods: {
        async fetchGraph() {
            this.loading = true
            this.error   = null
            try {
                const res = await fetch(`/api/inventory-wip/graph/${this.plant}`)
                if (!res.ok) throw new Error(`HTTP ${res.status}`)
                const json    = await res.json()
                this.rawData     = json.graph
                this.washSummary = json.wash_summary ?? []
                this.washDate    = json.wash_date    ?? ''
            } catch (e) {
                this.error = `Failed to load graph: ${e.message}`
            } finally {
                this.loading = false
            }
        },

        processColor(process) {
            return PROCESS_COLORS[process]
            ?? FALLBACK_COLORS[this.sortedProcesses.indexOf(process) % FALLBACK_COLORS.length]
        },

        getVal(dateLabel, process) {
            const file = this.sortedFiles[this.dateLabels.indexOf(dateLabel)]
            const val  = this.rawData[file]?.[process] ?? 0
            return val > 0 ? val.toFixed(4) : '—'
        },

        getDateTotal(dateLabel) {
            const file = this.sortedFiles[this.dateLabels.indexOf(dateLabel)]
            const total = Object.values(this.rawData[file] ?? {}).reduce((s, v) => s + v, 0)
            return total.toFixed(4)
        },
    },
  }
  </script>

  <style scoped>
    .graph-wrap {
        background: var(--bg-secondary);
        border: 1px solid var(--border-primary);
        border-radius: 8px;
        padding: 1.25rem 1.5rem;
        margin-bottom: 1.25rem;
        transition: background 0.3s ease;
    }
    .graph-eyebrow {
        font-size: 0.7rem; font-weight: 600; text-transform: uppercase;
        letter-spacing: 0.18em; color: var(--text-accent); margin-bottom: 0.2rem;
    }
    .graph-title {
        font-size: 1rem; font-weight: 700; color: var(--text-primary); margin: 0 0 1.25rem;
    }
    .graph-header { margin-bottom: 1rem; }

    .chart-container {
        height: 340px;
        margin-bottom: 1.25rem;
    }

    .graph-loading, .graph-empty, .graph-error {
        text-align: center; padding: 2rem;
        font-size: 0.875rem; color: var(--text-muted); letter-spacing: 0.05em;
    }
    .graph-error { color: var(--pct-low-text); }

    .table-wrap {
        overflow-x: auto;
        border: 1px solid var(--border-primary);
        border-radius: 8px;
        scrollbar-width: thin;
        scrollbar-color: var(--border-accent) transparent;
    }
    .data-table {
        width: 100%; border-collapse: collapse; font-size: 0.8rem; min-width: 500px;
    }
    .data-table thead tr {
        background: var(--bg-tertiary);
        border-bottom: 1px solid var(--border-primary);
    }
    .data-table th {
        padding: 0.6rem 1rem; text-align: right;
        font-size: 0.65rem; font-weight: 700; text-transform: uppercase;
        letter-spacing: 0.1em; color: var(--text-muted);
    }
    .data-table th.process-col { text-align: left; min-width: 120px; }
    .data-table td {
        padding: 0.5rem 1rem;
        color: var(--text-secondary);
        border-bottom: 1px solid var(--border-primary);
    }
    .data-row:last-child td { border-bottom: none; }
    .data-row:hover { background: var(--bg-tertiary); }

    .process-cell {
        display: flex; align-items: center; gap: 0.5rem;
        font-size: 0.8rem; font-weight: 600;
        color: var(--text-secondary);
    }
    .legend-dot {
        width: 10px; height: 10px;
        border-radius: 2px; flex-shrink: 0;
    }
    .num-col { text-align: right; }
    .mono    { font-family: 'Consolas', monospace; }

    /* Total row */
    .total-row td {
        background: var(--bg-tertiary);
        border-top: 2px solid var(--border-accent);
        padding: 0.5rem 1rem;
    }
    .total-label {
        font-size: 0.7rem; font-weight: 700;
        text-transform: uppercase; letter-spacing: 0.1em;
        color: var(--text-accent);
    }
    .total-val {
        font-weight: 700; color: var(--text-primary);
    }
    .wash-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 0.75rem;
        margin-top: 1.25rem;
        margin-bottom: 0.75rem;
    }
    .wash-title {
        display: flex;
        align-items: center;
        gap: 0.6rem;
        font-size: 0.875rem;
        font-weight: 700;
        color: var(--text-primary);
    }
    .wash-badge {
        display: inline-block;
        background: rgba(102, 126, 234, 0.2);
        border: 1px solid rgba(102, 126, 234, 0.4);
        border-radius: 4px;
        padding: 0.15rem 0.5rem;
        font-size: 0.7rem;
        font-weight: 700;
        color: #667eea;
        font-family: 'Consolas', monospace;
        letter-spacing: 0.05em;
    }
    .wash-date {
        font-size: 0.75rem;
        font-weight: 400;
        color: var(--text-muted);
        font-family: 'Consolas', monospace;
    }
    .wash-totals {
        display: flex;
        align-items: center;
        gap: 1rem;
        background: var(--bg-tertiary);
        border: 1px solid var(--border-accent);
        border-radius: 6px;
        padding: 0.4rem 1rem;
    }
    .wash-stat {
        display: flex;
        flex-direction: column;
        gap: 0.1rem;
    }
    .wash-stat-label {
        font-size: 0.6rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        color: var(--text-muted);
    }
    .wash-stat-val {
        font-size: 0.9rem;
        font-weight: 700;
        color: var(--text-primary);
        font-family: 'Consolas', monospace;
    }
    .wash-stat-unit {
        font-size: 0.7rem;
        color: var(--text-muted);
        font-weight: 400;
    }
    .wash-divider {
        width: 1px;
        height: 28px;
        background: var(--border-primary);
        flex-shrink: 0;
    }
    .area-badge {
        display: inline-block;
        background: rgba(43,130,203,0.1);
        border: 1px solid rgba(43,130,203,0.2);
        border-radius: 4px;
        padding: 0.1rem 0.45rem;
        font-size: 0.75rem;
        color: var(--text-accent);
    }
  </style>
