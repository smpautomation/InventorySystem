<template>
    <NavBarOld />
    <div class="chart-page">
      <ChartHeader
        :title="title"
        :eyebrow="eyebrow"
        :datasets="chartData.datasets"
        :current-month-label="currentMonthLabel"
        :today="today"
        :days-in-month="daysInMonth"
        :plant="plant"
        :targets="targets"
        :daily-targets="dailyTargets"
        :areas="areas"
        :show-area-breakdown="showAreaBreakdown"
        @targets-updated="$emit('targets-updated')"
        @daily-targets-updated="$emit('daily-targets-updated')"
        @date-range-changed="onDateRangeChanged"
        @open-print="showPrint = true"
        @update:showAreaBreakdown="showAreaBreakdown = $event"
      />
      <PrintPreview
        :show="showPrint"
        :title="title"
        :eyebrow="eyebrow"
        :raw-data="rawData"
        :targets="targets"
        :all-months="allMonths"
        :daily-targets="dailyTargets"
        :summaries="monthlySummaries"
        :current-month-label="currentMonthLabel"
        @close="showPrint = false"
      />

      <div class="chart-container">
        <div v-if="isEmpty" class="chart-empty">No data available</div>
        <Chart type="bar" v-else :data="chartData" :options="chartOptions" />
      </div>



      <template v-if="showAreaBreakdown">
        <div v-for="ac in areaCharts" :key="ac.area" class="chart-container area-chart">
            <div class="area-chart-title">{{ ac.area }}</div>
            <Chart type="bar" :data="ac.data" :options="chartOptions" />
        </div>
      </template>

      <div v-if="dateRangeTotal !== null" class="date-range-summary">
        <span class="date-range-label">Total Output for Selected Range</span>
        <div class="date-range-total-wrap">
            <span class="date-range-value">
                {{ dateRangeTotal.total.toFixed(2) }}<span class="date-range-unit">t</span>
            </span>
            <span
                v-if="dateRangeTotal.pct !== null"
                class="date-range-pct"
                :class="dateRangeTotal.pct >= 100 ? 'pct-good' : 'pct-low'"
            >
                {{ dateRangeTotal.pct.toFixed(2) }}%
            </span>
            <span v-if="dateRangeTotal.targetTotal > 0" class="date-range-target-label">
                of {{ dateRangeTotal.targetTotal.toFixed(2) }}t Daily Target (Set)
            </span>
        </div>
        <div class="date-range-breakdown">
            <span v-for="ds in chartData.datasets.filter(d => d.type === 'bar')" :key="ds.label" class="breakdown-item">
                <span class="breakdown-dot" :style="{ background: ds.backgroundColor }"></span>
                <span class="breakdown-label">{{ ds.label }}</span>
                <span class="breakdown-val">{{ ds.data.reduce((s, v) => s + (Number(v) || 0), 0).toFixed(2) }}t</span>
                <span
                    v-if="getAreaRangeTarget(ds.label) > 0"
                    class="breakdown-pct"
                    :class="ds.data.reduce((s,v) => s+(Number(v)||0), 0) >= getAreaRangeTarget(ds.label) ? 'pct-good' : 'pct-low'"
                >
                    {{ ((ds.data.reduce((s,v) => s+(Number(v)||0), 0) / getAreaRangeTarget(ds.label)) * 100).toFixed(1) }}%
                </span>
                <span class="breakdown-target">/ {{ getAreaRangeTarget(ds.label).toFixed(2) }}t</span>
            </span>
        </div>
      </div>
      <SummaryCards :summaries="monthlySummaries" />
    </div>
  </template>

  <script>
  import NavBarOld from '@/components/NavBarOld.vue'
  import { Bar } from 'vue-chartjs'
  import {
      Chart as ChartJS,
      Title,
      Tooltip,
      Legend,
      BarElement,
      BarController,
      LineElement,
      LineController,
      PointElement,
      CategoryScale,
      LinearScale
  } from 'chart.js'
  import { Chart } from 'vue-chartjs'
  import ChartHeader from './ChartHeader.vue'
  import SummaryCards from './SummaryCards.vue'
  import { buildChartData, buildChartOptions, buildMonthlySummaries, buildAreaChartData } from './chartHelpers'
  import PrintPreview from './PrintPreview.vue'

  ChartJS.register(
      CategoryScale,
      LinearScale,
      BarElement,
      BarController,
      LineElement,
      LineController,
      PointElement,
      Title,
      Tooltip,
      Legend
  )

  export default {
    data() {
      return {
        dateRange: { from: null, to: null },
        showPrint: false,
        showAreaBreakdown: false,
      }
    },
    name: 'PlantOutputChart',
    components: { Chart, ChartHeader, SummaryCards, NavBarOld,  PrintPreview},
    props: {
      title:         { type: String, required: true },
      eyebrow:       { type: String, default: 'Monthly Output' },
      rawData:       { type: Object, required: true },
      targets:       { type: Object, default: () => ({}) },
      allMonths:     { type: Object, default: () => ({}) },
      plant:         { type: String, required: true },
      dailyTargets:  { type: Object, default: () => ({}) },
    },
    emits: ['targets-updated', 'daily-targets-updated'],
    computed: {
      today()             { return new Date().getDate() },
      daysInMonth()       { const n = new Date(); return new Date(n.getFullYear(), n.getMonth()+1, 0).getDate() },
      currentMonthLabel() { return new Date().toLocaleDateString('en-US', { month: 'long', year: 'numeric' }) },
      dayLabels()         { return Array.from({ length: this.today }, (_, i) => `Day ${i + 1}`) },
      currentMonthName() {
        return new Date().toLocaleDateString('en-US', { month: 'long' })
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
      chartOptions()     { return buildChartOptions() },
      monthlySummaries() {
        return buildMonthlySummaries(this.rawData, this.targets, this.allMonths, this.dailyTargets)
      },
      isEmpty() {
        if (!this.rawData || Object.keys(this.rawData).length === 0) return true
        return Object.values(this.rawData).every(
          areas => !areas || Object.keys(areas).length === 0
        )
      },
      areas() {
        return Object.keys(Object.values(this.rawData)[0] ?? {})
      },
      dateRangeTotal() {
        if (!this.dateRange.from && !this.dateRange.to) return null

        const datasets = this.chartData.datasets.filter(ds => ds.type === 'bar')
        const total = datasets.reduce((sum, ds) => {
            return sum + ds.data.reduce((s, v) => s + (Number(v) || 0), 0)
        }, 0)

        // Sum daily targets for the selected date range
        const from = new Date(this.dateRange.from || new Date(new Date().getFullYear(), new Date().getMonth(), 1))
        const to   = new Date(this.dateRange.to   || new Date())

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
      areaCharts() {
        if (!this.showAreaBreakdown || !this.areas.length) return []
        return this.areas.map(area => ({
            area,
            data: buildAreaChartData(
            area,
            this.rawData,
            this.today,
            this.targets,
            this.currentMonthName,
            this.dateRange,
            this.allMonths,
            this.dailyTargets,
            ),
        }))
      },
    },
    methods: {
      onDateRangeChanged(range) {
        this.dateRange = range
      },
      getAreaRangeTarget(areaName) {
        if (!this.dailyTargets[areaName]) return 0

        const from = new Date(this.dateRange.from || new Date(new Date().getFullYear(), new Date().getMonth(), 1))
        const to   = new Date(this.dateRange.to   || new Date())

        let total = 0
        const cursor = new Date(from)
        cursor.setHours(0, 0, 0, 0)
        to.setHours(23, 59, 59, 999)

        while (cursor <= to) {
            const day = String(cursor.getDate())
            total += this.dailyTargets[areaName][day] !== undefined
                ? Number(this.dailyTargets[areaName][day])
                : 0
            cursor.setDate(cursor.getDate() + 1)
        }

        return total
      }
    }
  }
  </script>

<style scoped>
.chart-page {
    background: var(--bg-primary);
    min-height: 100vh;
    padding: 1.5rem 2rem;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    color: var(--text-primary);
    transition: background 0.3s ease, color 0.3s ease;
}
.chart-container {
    background: var(--bg-secondary);
    border: 1px solid var(--border-primary);
    border-radius: 8px;
    padding: 1.5rem;
    height: 400px;
    margin-bottom: 1.25rem;
    transition: background 0.3s ease, border-color 0.3s ease;
}
.chart-empty {
    display: flex;
    align-items: center;
    justify-content: center;
    height: 100%;
    color: var(--text-muted);
    font-size: 0.875rem;
    letter-spacing: 0.05em;
}
.date-range-summary {
    background: var(--bg-secondary);
    border: 1px solid var(--border-accent);
    border-radius: 8px;
    padding: 1rem 1.5rem;
    margin-bottom: 1.25rem;
    display: flex;
    align-items: center;
    gap: 1.5rem;
    flex-wrap: wrap;
    transition: background 0.3s ease, border-color 0.3s ease;
}
.date-range-label {
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: var(--text-accent);
    flex-shrink: 0;
}
.date-range-value {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--text-primary);
    font-family: 'Consolas', monospace;
    flex-shrink: 0;
}
.date-range-unit {
    font-size: 1rem;
    color: var(--text-muted);
    font-weight: 400;
}
.date-range-breakdown {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
    margin-left: auto;
}
.breakdown-item {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    background: var(--bg-tertiary);
    border: 1px solid var(--border-primary);
    border-radius: 4px;
    padding: 0.3rem 0.6rem;
}
.breakdown-dot {
    width: 8px;
    height: 8px;
    border-radius: 2px;
    flex-shrink: 0;
}
.breakdown-label {
    font-size: 0.75rem;
    color: var(--text-muted);
}
.breakdown-val {
    font-size: 0.75rem;
    font-weight: 600;
    color: var(--text-secondary);
    font-family: 'Consolas', monospace;
}
.date-range-total-wrap {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    flex-shrink: 0;
}
.date-range-pct {
    font-size: 0.9rem;
    font-weight: 700;
    padding: 0.2rem 0.5rem;
    border-radius: 4px;
}
.date-range-target-label {
    font-size: 0.75rem;
    color: var(--text-muted);
}
.pct-good {
    background: var(--pct-good-bg);
    color: var(--pct-good-text);
}
.pct-low {
    background: var(--pct-low-bg);
    color: var(--pct-low-text);
}
.breakdown-target {
    font-size: 0.7rem;
    color: var(--text-muted);
    font-family: 'Consolas', monospace;
}
.breakdown-pct {
    font-size: 0.7rem;
    font-weight: 700;
    padding: 0.1rem 0.3rem;
    border-radius: 3px;
}
.area-breakdown-toggle {
    display: flex;
    align-items: center;
    margin-bottom: 1rem;
}
.toggle-label {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    cursor: pointer;
    user-select: none;
    background: var(--bg-secondary);
    border: 1px solid var(--border-primary);
    border-radius: 8px;
    padding: 0.5rem 1rem;
    transition: border-color 0.2s, background 0.2s;
}
.toggle-label:hover {
    border-color: var(--border-accent);
    background: var(--accent-hover);
}
.toggle-checkbox {
    display: none;
}
.toggle-track {
    position: relative;
    width: 36px;
    height: 20px;
    background: var(--bg-tertiary);
    border: 1px solid var(--border-primary);
    border-radius: 999px;
    flex-shrink: 0;
    transition: background 0.25s, border-color 0.25s, box-shadow 0.25s;
}
.toggle-checkbox:checked ~ .toggle-track {
    background: rgba(43, 130, 203, 0.35);
    border-color: var(--border-accent);
    box-shadow: 0 0 8px rgba(43, 130, 203, 0.4);
}
.toggle-thumb {
    position: absolute;
    top: 3px;
    left: 3px;
    width: 12px;
    height: 12px;
    background: var(--text-muted);
    border-radius: 50%;
    transition: transform 0.25s cubic-bezier(0.34, 1.56, 0.64, 1), background 0.25s;
}
.toggle-checkbox:checked ~ .toggle-track .toggle-thumb {
    transform: translateX(16px);
    background: #2b82cb;
    box-shadow: 0 0 6px rgba(43, 130, 203, 0.7);
}
.toggle-text {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    font-size: 0.8rem;
    font-weight: 600;
    letter-spacing: 0.04em;
    color: var(--text-muted);
    transition: color 0.2s;
    text-transform: uppercase;
}
.toggle-label:has(.toggle-checkbox:checked) .toggle-text {
    color: var(--text-accent);
}
.toggle-icon {
    font-size: 0.7rem;
    opacity: 0.6;
}
.area-chart {
    position: relative;
}
.area-chart-title {
    position: absolute;
    top: 0.75rem;
    left: 1.25rem;
    font-size: 0.8rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: var(--text-accent);
    z-index: 1;
}
@media (max-width: 768px) {
    .chart-page      { padding: 1rem; }
    .chart-container { height: 280px; }
}
</style>
