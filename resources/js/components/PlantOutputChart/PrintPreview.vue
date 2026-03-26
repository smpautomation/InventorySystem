<template>
    <Teleport to="body">
      <div v-if="show" class="print-overlay">

        <!-- Screen UI: controls bar -->
        <div class="print-toolbar no-print">
          <div class="toolbar-left">
            <span class="toolbar-title">🖨 Print Preview</span>
            <span class="toolbar-sub">A4 Landscape</span>
          </div>
          <div class="toolbar-center">
            <div class="date-range-wrap">
              <label class="range-label">From</label>
              <input type="date" v-model="printFrom" class="range-input" :max="printTo || todayStr" />
              <span class="range-sep">→</span>
              <label class="range-label">To</label>
              <input type="date" v-model="printTo" class="range-input" :min="printFrom" :max="todayStr" />
              <button class="btn-apply" @click="applyRange">Apply</button>
              <button v-if="printFrom || printTo" class="btn-clear" @click="clearRange">✕ Clear</button>
            </div>
          </div>
          <div class="toolbar-right">
            <button class="btn-close" @click="$emit('close')">✕ Close</button>
            <button class="btn-print" @click="triggerPrint">🖨 Print</button>
          </div>
        </div>

        <!-- A4 Page -->
        <div class="print-page-wrap">
          <div class="a4-page" ref="printArea">

            <!-- Page Header -->
            <div class="page-header">
              <div class="page-header-left">
                <div class="page-eyebrow">{{ eyebrow }}</div>
                <h1 class="page-title">{{ title }} <span class="page-unit">(tons)</span></h1>
                <div class="page-meta">
                  <span v-if="appliedFrom || appliedTo">
                    {{ appliedFrom || '—' }} → {{ appliedTo || '—' }}
                  </span>
                  <span v-else>{{ currentMonthLabel }} · Full Month</span>
                </div>
              </div>
              <div class="page-header-right">
                <div class="print-date">Printed: {{ printedOn }}</div>
              </div>
            </div>

            <div class="page-divider"></div>

            <!-- Chart -->
            <div class="print-chart-wrap">
              <Chart type="bar" :data="chartData" :options="printChartOptions" />
            </div>

            <div class="page-divider"></div>

            <!-- Date Range Total & Breakdown -->
            <div v-if="rangeTotal !== null" class="print-range-section">
              <div class="range-section-title">Selected Range Summary</div>
              <div class="range-summary-row">
                <div class="range-total-block">
                  <span class="range-total-label">Total Output</span>
                  <span class="range-total-val">{{ rangeTotal.total.toFixed(2) }}<span class="range-unit">t</span></span>
                  <span
                    v-if="rangeTotal.pct !== null"
                    class="range-pct"
                    :class="rangeTotal.pct >= 100 ? 'pct-good' : 'pct-low'"
                  >{{ rangeTotal.pct.toFixed(2) }}%</span>
                  <span v-if="rangeTotal.targetTotal > 0" class="range-of-target">
                    of {{ rangeTotal.targetTotal.toFixed(2) }}t target
                  </span>
                </div>
                <div class="range-breakdown">
                  <div
                    v-for="ds in barDatasets"
                    :key="ds.label"
                    class="breakdown-chip"
                  >
                    <span class="chip-dot" :style="{ background: ds.backgroundColor }"></span>
                    <span class="chip-label">{{ ds.label }}</span>
                    <span class="chip-val">{{ ds.data.reduce((s,v) => s+(Number(v)||0),0).toFixed(2) }}t</span>
                    <span
                      v-if="getAreaTarget(ds.label) > 0"
                      class="chip-pct"
                      :class="ds.data.reduce((s,v)=>s+(Number(v)||0),0) >= getAreaTarget(ds.label) ? 'pct-good' : 'pct-low'"
                    >{{ ((ds.data.reduce((s,v)=>s+(Number(v)||0),0) / getAreaTarget(ds.label))*100).toFixed(1) }}%</span>
                    <span v-if="getAreaTarget(ds.label) > 0" class="chip-target">/ {{ getAreaTarget(ds.label).toFixed(2) }}t</span>
                  </div>
                </div>
              </div>
            </div>

            <div class="page-divider"></div>

            <!-- Summary Cards -->
            <div class="print-summary-section">
              <div class="summary-section-title">Monthly Summary</div>
              <div class="print-summary-grid">
                <div
                  v-for="m in reversedSummaries"
                  :key="m.month"
                  class="print-card"
                  :class="{ 'print-card-current': m.isCurrent }"
                >
                  <div class="print-card-top">
                    <span class="print-card-month">{{ m.month }}</span>
                    <span v-if="m.isCurrent" class="print-current-badge">Current</span>
                  </div>
                  <div class="print-card-total">
                    {{ m.grandTotal.toFixed(2) }}<span class="print-card-unit">t</span>
                  </div>
                  <div v-if="m.target > 0" class="print-card-row">
                    <span class="print-card-label">Target</span>
                    <span class="print-card-val">{{ m.target }}t</span>
                    <span
                      class="print-card-pct"
                      :class="m.grandTotal >= m.target ? 'pct-good' : 'pct-low'"
                    >{{ ((m.grandTotal / m.target) * 100).toFixed(1) }}%</span>
                  </div>
                  <div v-if="m.dailyTarget > 0" class="print-card-row">
                    <span class="print-card-label">Daily Avg</span>
                    <span class="print-card-val">{{ m.dailyTarget.toFixed(2) }}t</span>
                  </div>
                  <div v-if="m.isCurrent && m.yesterdayTotal !== null" class="print-card-row">
                    <span class="print-card-label">Yesterday</span>
                    <span class="print-card-val">{{ Number(m.yesterdayTotal).toFixed(2) }}t</span>
                    <span
                      v-if="m.yesterdayDailyTarget > 0"
                      class="print-card-pct"
                      :class="m.yesterdayTotal >= m.yesterdayDailyTarget ? 'pct-good' : 'pct-low'"
                    >{{ ((m.yesterdayTotal / m.yesterdayDailyTarget) * 100).toFixed(1) }}%</span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Page Footer -->
            <div class="page-footer">
              <span>{{ title }} — Confidential</span>
              <span>Generated {{ printedOn }}</span>
            </div>

          </div>
        </div>
      </div>
    </Teleport>
  </template>

  <script>
  import { Chart } from 'vue-chartjs'
  import { buildChartData, buildChartOptions } from './chartHelpers'

  const MONTH_NAMES = [
    'January','February','March','April','May','June',
    'July','August','September','October','November','December'
  ]

  export default {
    name: 'PrintPreview',
    components: { Chart },

    props: {
      show:              { type: Boolean, required: true },
      title:             { type: String,  required: true },
      eyebrow:           { type: String,  default: 'Monthly Output' },
      rawData:           { type: Object,  required: true },
      targets:           { type: Object,  default: () => ({}) },
      allMonths:         { type: Object,  default: () => ({}) },
      dailyTargets:      { type: Object,  default: () => ({}) },
      summaries:         { type: Array,   default: () => [] },
      currentMonthLabel: { type: String,  required: true },
    },

    emits: ['close'],

    data() {
      return {
        printFrom:   '',
        printTo:     '',
        appliedFrom: '',
        appliedTo:   '',
        dateRange:   { from: null, to: null },
      }
    },

    computed: {
      todayStr() {
        return new Date().toISOString().split('T')[0]
      },
      printedOn() {
        return new Date().toLocaleDateString('en-US', {
          year: 'numeric', month: 'long', day: 'numeric',
          hour: '2-digit', minute: '2-digit'
        })
      },
      today()       { return new Date().getDate() },
      currentMonthName() {
        return new Date().toLocaleDateString('en-US', { month: 'long' })
      },
      dayLabels() {
        return Array.from({ length: this.today }, (_, i) => `Day ${i + 1}`)
      },
      chartData() {
        return buildChartData(
          this.rawData,
          this.today,
          this.dayLabels,
          this.targets,
          this.currentMonthName,
          this.dateRange,
          this.allMonths,
          this.dailyTargets,
        )
      },
      printChartOptions() {
        const base = buildChartOptions()
        return {
          ...base,
          responsive: true,
          maintainAspectRatio: false,
          animation: { duration: 0 },
          plugins: {
            ...base.plugins,
            legend: { display: false },
          },
          scales: {
            ...base.scales,
            x: {
              ...base.scales.x,
              ticks: { color: '#444', font: { size: 9 } },
              grid:  { color: 'rgba(0,0,0,0.06)' },
              border: { color: 'rgba(0,0,0,0.1)' },
            },
            y: {
              ...base.scales.y,
              ticks: { color: '#444', font: { size: 9 }, callback: v => `${v}t` },
              grid:  { color: 'rgba(0,0,0,0.06)' },
              border: { color: 'rgba(0,0,0,0.1)' },
              title: { ...base.scales.y.title, color: '#666' },
            },
            y2: {
              stacked: false,
              display: false,
              grid: { drawOnChartArea: false },
              afterDataLimits(axis) {
                const yAxis = axis.chart.scales['y']
                if (yAxis) { axis.min = yAxis.min; axis.max = yAxis.max }
              }
            }
          }
        }
      },
      barDatasets() {
        return this.chartData.datasets.filter(ds => ds.type === 'bar')
      },
      reversedSummaries() {
        return [...this.summaries].reverse()
      },
      rangeTotal() {
        if (!this.dateRange.from && !this.dateRange.to) return null

        const total = this.barDatasets.reduce((sum, ds) => {
          return sum + ds.data.reduce((s, v) => s + (Number(v) || 0), 0)
        }, 0)

        const from = new Date(this.dateRange.from)
        const to   = new Date(this.dateRange.to)
        let targetTotal = 0
        const cursor = new Date(from)
        cursor.setHours(0, 0, 0, 0)
        to.setHours(23, 59, 59, 999)

        while (cursor <= to) {
          const day = String(cursor.getDate())
          Object.entries(this.dailyTargets)
            .filter(([area]) => area !== 'ALL')
            .forEach(([, areaDays]) => {
              targetTotal += areaDays[day] !== undefined ? Number(areaDays[day]) : 0
            })
          cursor.setDate(cursor.getDate() + 1)
        }

        const pct = targetTotal > 0 ? (total / targetTotal) * 100 : null
        return { total, targetTotal, pct }
      },
    },

    methods: {
      applyRange() {
        this.appliedFrom = this.printFrom
        this.appliedTo   = this.printTo
        this.dateRange   = {
          from: this.printFrom || null,
          to:   this.printTo   || null,
        }
      },
      clearRange() {
        this.printFrom   = ''
        this.printTo     = ''
        this.appliedFrom = ''
        this.appliedTo   = ''
        this.dateRange   = { from: null, to: null }
      },
      getAreaTarget(areaName) {
        if (!this.dailyTargets[areaName]) return 0
        if (!this.dateRange.from && !this.dateRange.to) return 0

        const from = new Date(this.dateRange.from)
        const to   = new Date(this.dateRange.to)
        let total  = 0
        const cursor = new Date(from)
        cursor.setHours(0, 0, 0, 0)
        to.setHours(23, 59, 59, 999)

        while (cursor <= to) {
          const day = String(cursor.getDate())
          total += this.dailyTargets[areaName][day] !== undefined
            ? Number(this.dailyTargets[areaName][day]) : 0
          cursor.setDate(cursor.getDate() + 1)
        }
        return total
      },
      triggerPrint() {
        window.print()
      },
    },
  }
  </script>

  <style scoped>
  /* ── Overlay (screen only) ── */
  .print-overlay {
    position: fixed;
    inset: 0;
    background: #e8edf2;
    z-index: 2000;
    display: flex;
    flex-direction: column;
    overflow: hidden;
  }

  /* ── Toolbar ── */
  .print-toolbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    background: #1a2e44;
    padding: 0.75rem 1.5rem;
    flex-shrink: 0;
    flex-wrap: wrap;
  }
  .toolbar-left   { display: flex; align-items: center; gap: 0.75rem; }
  .toolbar-title  { font-size: 0.9rem; font-weight: 700; color: #e8f0f7; }
  .toolbar-sub    { font-size: 0.7rem; color: #5ba3e0; background: rgba(43,130,203,0.15); border: 1px solid rgba(43,130,203,0.3); border-radius: 3px; padding: 0.1rem 0.4rem; }
  .toolbar-center { display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap; }
  .toolbar-right  { display: flex; align-items: center; gap: 0.75rem; }

  .range-label { font-size: 0.7rem; color: #8899aa; text-transform: uppercase; letter-spacing: 0.08em; }
  .range-input {
    background: #0d1b2a; border: 1px solid rgba(43,130,203,0.3);
    border-radius: 5px; padding: 0.35rem 0.6rem;
    color: #e8f0f7; font-size: 0.8rem; colorscheme: dark;
  }
  .range-sep { color: #5ba3e0; font-size: 0.8rem; }
  .btn-apply {
    background: rgba(43,130,203,0.8); border: 1px solid rgba(43,130,203,0.6);
    border-radius: 5px; padding: 0.35rem 0.9rem;
    color: #fff; font-size: 0.8rem; font-weight: 600; cursor: pointer;
    transition: background 0.2s;
  }
  .btn-apply:hover { background: rgba(43,130,203,1); }
  .btn-clear {
    background: rgba(229,62,62,0.15); border: 1px solid rgba(229,62,62,0.3);
    border-radius: 5px; padding: 0.35rem 0.7rem;
    color: #fc8181; font-size: 0.75rem; cursor: pointer;
  }
  .btn-close {
    background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.15);
    border-radius: 5px; padding: 0.35rem 0.9rem;
    color: #c8d8e8; font-size: 0.8rem; cursor: pointer;
    transition: all 0.2s;
  }
  .btn-close:hover { background: rgba(255,255,255,0.15); }
  .btn-print {
    background: #2b82cb; border: 1px solid #3a9ae0;
    border-radius: 5px; padding: 0.35rem 1rem;
    color: #fff; font-size: 0.8rem; font-weight: 700; cursor: pointer;
    transition: background 0.2s;
  }
  .btn-print:hover { background: #3a9ae0; }

  /* ── Scrollable page area ── */
  .print-page-wrap {
    flex: 1;
    overflow-y: auto;
    display: flex;
    justify-content: center;
    padding: 2rem;
  }

  /* ── A4 Landscape page ── */
  .a4-page {
    width: 297mm;
    min-height: 210mm;
    background: #ffffff;
    box-shadow: 0 4px 32px rgba(0,0,0,0.18);
    border-radius: 3px;
    padding: 12mm 14mm;
    display: flex;
    flex-direction: column;
    gap: 6px;
    font-family: 'Georgia', 'Times New Roman', serif;
    color: #1a2e44;
    font-size: 9pt;
    box-sizing: border-box;
  }

  /* ── Page Header ── */
  .page-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
  }
  .page-eyebrow {
    font-size: 7pt;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.18em;
    color: #2b82cb;
    margin-bottom: 2px;
  }
  .page-title {
    font-size: 16pt;
    font-weight: 700;
    color: #1a2e44;
    margin: 0 0 3px;
    font-family: 'Georgia', serif;
  }
  .page-unit  { font-size: 10pt; font-weight: 400; color: #6b7a8d; }
  .page-meta  { font-size: 7.5pt; color: #6b7a8d; }
  .page-header-right { text-align: right; }
  .print-date { font-size: 7pt; color: #9aabbc; }

  .page-divider {
    height: 1px;
    background: linear-gradient(to right, #2b82cb33, #2b82cb11);
    margin: 2px 0;
  }

  /* ── Chart ── */
  .print-chart-wrap {
    height: 200px;
    width: 100%;
  }

  /* ── Range Summary ── */
  .range-section-title,
  .summary-section-title {
    font-size: 7pt;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.12em;
    color: #2b82cb;
    margin-bottom: 4px;
  }
  .range-summary-row {
    display: flex;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
  }
  .range-total-block {
    display: flex;
    align-items: baseline;
    gap: 0.5rem;
    background: #f0f6ff;
    border: 1px solid #c8dff5;
    border-radius: 5px;
    padding: 5px 10px;
    flex-shrink: 0;
  }
  .range-total-label { font-size: 7pt; color: #6b7a8d; text-transform: uppercase; letter-spacing: 0.08em; }
  .range-total-val   { font-size: 14pt; font-weight: 700; color: #1a2e44; font-family: 'Consolas', monospace; }
  .range-unit        { font-size: 9pt; color: #9aabbc; }
  .range-of-target   { font-size: 7pt; color: #9aabbc; }
  .range-breakdown   { display: flex; flex-wrap: wrap; gap: 5px; }
  .breakdown-chip {
    display: flex; align-items: center; gap: 4px;
    background: #f7f9fc; border: 1px solid #dde6ef;
    border-radius: 4px; padding: 3px 7px;
    font-size: 7.5pt;
  }
  .chip-dot    { width: 7px; height: 7px; border-radius: 2px; flex-shrink: 0; }
  .chip-label  { color: #4a5568; }
  .chip-val    { font-weight: 700; color: #1a2e44; font-family: 'Consolas', monospace; }
  .chip-target { color: #9aabbc; font-size: 7pt; }

  /* ── Summary Cards ── */
  .print-summary-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(110px, 1fr));
    gap: 5px;
  }
  .print-card {
    background: #f7f9fc;
    border: 1px solid #dde6ef;
    border-radius: 5px;
    padding: 6px 8px;
  }
  .print-card-current {
    border-color: #2b82cb;
    background: #f0f6ff;
    box-shadow: 0 0 0 1px #2b82cb22;
  }
  .print-card-top {
    display: flex; align-items: center; gap: 4px; margin-bottom: 3px;
  }
  .print-card-month {
    font-size: 6.5pt; font-weight: 700; text-transform: uppercase;
    letter-spacing: 0.1em; color: #6b7a8d;
  }
  .print-current-badge {
    font-size: 5.5pt; font-weight: 700; text-transform: uppercase;
    background: #2b82cb22; border: 1px solid #2b82cb55;
    color: #2b82cb; padding: 1px 3px; border-radius: 2px;
  }
  .print-card-total {
    font-size: 13pt; font-weight: 700; color: #1a2e44;
    font-family: 'Consolas', monospace; line-height: 1; margin-bottom: 4px;
  }
  .print-card-unit { font-size: 8pt; color: #9aabbc; font-weight: 400; }
  .print-card-row {
    display: flex; align-items: center; gap: 4px;
    margin-bottom: 2px; flex-wrap: wrap;
  }
  .print-card-label { font-size: 6.5pt; color: #9aabbc; text-transform: uppercase; letter-spacing: 0.06em; flex: 1; }
  .print-card-val   { font-size: 7pt; font-weight: 600; color: #2d3748; font-family: 'Consolas', monospace; }
  .print-card-pct   { font-size: 6.5pt; font-weight: 700; padding: 1px 3px; border-radius: 2px; }

  /* ── Shared pct colors (light theme) ── */
  .range-pct, .chip-pct, .print-card-pct {
    font-size: 7pt; font-weight: 700; padding: 1px 4px; border-radius: 3px;
  }
  .pct-good { background: #c6f6d5; color: #276749; }
  .pct-low  { background: #fed7d7; color: #9b2c2c; }

  /* ── Page Footer ── */
  .page-footer {
    display: flex;
    justify-content: space-between;
    font-size: 6.5pt;
    color: #9aabbc;
    border-top: 1px solid #dde6ef;
    padding-top: 4px;
    margin-top: auto;
  }

  /* ── Print media ── */
  @media print {
    @page {
      size: A4 landscape;
      margin: 0;
    }

    .no-print { display: none !important; }

    .print-overlay {
      position: static;
      background: white;
    }

    .print-page-wrap {
      padding: 0;
      overflow: visible;
    }

    .a4-page {
      width: 100%;
      min-height: auto;
      box-shadow: none;
      border-radius: 0;
      padding: 10mm 12mm;
      page-break-inside: avoid;
    }
  }
  </style>
