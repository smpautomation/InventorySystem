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
        @targets-updated="$emit('targets-updated')"
        @daily-targets-updated="$emit('daily-targets-updated')"
        @date-range-changed="onDateRangeChanged"
      />
      <div class="chart-container">
        <div v-if="isEmpty" class="chart-empty">No data available</div>
        <Chart type="bar" v-else :data="chartData" :options="chartOptions" />
      </div>
      <div v-if="dateRangeTotal !== null" class="date-range-summary">
        <span class="date-range-label">Total Output for Selected Range</span>
        <span class="date-range-value">{{ dateRangeTotal.toFixed(2) }}<span class="date-range-unit">t</span></span>
        <div class="date-range-breakdown">
            <span v-for="ds in chartData.datasets.filter(d => d.type === 'bar')" :key="ds.label" class="breakdown-item">
                <span class="breakdown-dot" :style="{ background: ds.backgroundColor }"></span>
                <span class="breakdown-label">{{ ds.label }}</span>
                <span class="breakdown-val">{{ ds.data.reduce((s, v) => s + (Number(v) || 0), 0).toFixed(2) }}t</span>
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
  import { buildChartData, buildChartOptions, buildMonthlySummaries } from './chartHelpers'

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
        dateRange: { from: null, to: null }
      }
    },
    name: 'PlantOutputChart',
    components: { Chart, ChartHeader, SummaryCards, NavBarOld },
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
        return total
      }
    },
    methods: {
      onDateRangeChanged(range) {
        this.dateRange = range
      }
    }
  }
  </script>

  <style scoped>
  .chart-page {
    background: #0d1b2a;
    min-height: 100vh;
    padding: 1.5rem 2rem;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    color: #e8f0f7;
  }
  .chart-container {
    background: #112240;
    border: 1px solid rgba(255,255,255,0.08);
    border-radius: 8px;
    padding: 1.5rem;
    height: 400px;
    margin-bottom: 1.25rem;
  }
  .chart-empty {
    display: flex;
    align-items: center;
    justify-content: center;
    height: 100%;
    color: #8899aa;
    font-size: 0.875rem;
    letter-spacing: 0.05em;
  }
  .date-range-summary {
    background: #112240;
    border: 1px solid rgba(43,130,203,0.3);
    border-radius: 8px;
    padding: 1rem 1.5rem;
    margin-bottom: 1.25rem;
    display: flex;
    align-items: center;
    gap: 1.5rem;
    flex-wrap: wrap;
}
.date-range-label {
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: #5ba3e0;
    flex-shrink: 0;
}
.date-range-value {
    font-size: 1.5rem;
    font-weight: 700;
    color: #e8f0f7;
    font-family: 'Consolas', monospace;
    flex-shrink: 0;
}
.date-range-unit {
    font-size: 1rem;
    color: #8899aa;
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
    background: rgba(255,255,255,0.03);
    border: 1px solid rgba(255,255,255,0.06);
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
    color: #8899aa;
}
.breakdown-val {
    font-size: 0.75rem;
    font-weight: 600;
    color: #c8d8e8;
    font-family: 'Consolas', monospace;
}
  @media (max-width: 768px) {
    .chart-page { padding: 1rem; }
    .chart-container { height: 280px; }
  }
  </style>
