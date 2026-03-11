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
      @targets-updated="$emit('targets-updated')"
    />

    <div class="chart-container">
      <div v-if="isEmpty" class="chart-empty">No data available</div>
      <Chart type="bar" v-else :data="chartData" :options="chartOptions" />
    </div>
    <SummaryCards :summaries="monthlySummaries" />
  </div>
</template>

<script>
import NavBarOld from '@/components/NavBarOld.vue'
import { Chart as ChartJS, Title, Tooltip, Legend, BarElement, LineElement, PointElement, CategoryScale, LinearScale } from 'chart.js'
import { Chart } from 'vue-chartjs'
import ChartHeader from './ChartHeader.vue'
import SummaryCards from './SummaryCards.vue'
import { buildChartData, buildChartOptions, buildMonthlySummaries } from './chartHelpers'

ChartJS.register(CategoryScale, LinearScale, BarElement, LineElement, PointElement, Title, Tooltip, Legend)

export default {
  name: 'PlantOutputChart',
  components: { Chart, ChartHeader, SummaryCards, NavBarOld },
  props: {
    title:     { type: String, required: true },
    eyebrow:   { type: String, default: 'Monthly Output' },
    rawData:   { type: Object, required: true },
    targets:   { type: Object, default: () => ({}) },
    allMonths: { type: Object, default: () => ({}) },
    plant:     { type: String, required: true },
  },
  emits: ['targets-updated'],
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
            this.currentMonthName
        )
    },
    chartOptions()      { return buildChartOptions() },
    monthlySummaries() {
      return buildMonthlySummaries(this.rawData, this.targets, this.allMonths)
    },
    isEmpty() {
        if (!this.rawData || Object.keys(this.rawData).length === 0) return true

        return Object.values(this.rawData).every(
            areas => !areas || Object.keys(areas).length === 0
        )
    },
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

@media (max-width: 768px) {
  .chart-page { padding: 1rem; }
  .chart-container { height: 280px; }
}
</style>
