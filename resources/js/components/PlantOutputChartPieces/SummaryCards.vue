<template>
  <div class="summary-grid">
    <div
      v-for="m in summaries"
      :key="m.month"
      class="summary-card"
      :class="{ 'summary-card-current': m.isCurrent }"
    >
      <div class="summary-top">
        <span class="summary-name">{{ m.month }}</span>
        <span v-if="m.isCurrent" class="current-badge">Current</span>
      </div>

      <div class="summary-total">
        {{ m.grandTotal.toFixed(0) }}<span class="summary-unit">pcs</span>
      </div>

      <div class="target-row">
        <span class="target-label">Target</span>
        <span class="target-val">{{ m.target > 0 ? m.target + ' pcs' : '—' }}</span>
        <span
          v-if="m.target > 0"
          class="target-pct"
          :class="m.grandTotal >= m.target ? 'pct-good' : 'pct-low'"
        >
          {{ ((m.grandTotal / m.target) * 100).toFixed(2) }}%
        </span>
      </div>

      <div v-if="m.dailyTarget > 0" class="target-row">
        <span class="target-label">Daily Target</span>
        <span class="target-val">{{ m.dailyTarget.toFixed(0) }} pcs
            <span class="target-label">/ {{ m.working_days }} days</span>
        </span>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'SummaryCards',
  props: {
    summaries: { type: Array, required: true }
  }
}
</script>

<style scoped>
.summary-grid {
    display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem;
}
.summary-card {
  background: #112240;
  border: 1px solid rgba(255,255,255,0.08);
  border-radius: 8px;
  padding: 1rem 1.25rem;
  transition: transform 0.2s, box-shadow 0.2s;
}
.summary-card:hover {
    transform: translateY(-3px); box-shadow: 0 8px 24px rgba(0,0,0,0.3);
}
.summary-card-current {
    border-color: rgba(43,130,203,0.4); box-shadow: 0 0 0 1px rgba(43,130,203,0.2);
}
.summary-top {
    display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;
}
.summary-name {
    font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em; color: #8899aa;
}
.summary-total {
    font-size: 1.75rem; font-weight: 700; color: #e8f0f7; font-family: 'Consolas', monospace; margin-bottom: 0.75rem; line-height: 1;
}
.summary-unit {
    font-size: 1rem; color: #8899aa; font-weight: 400;
}
.current-badge {
  font-size: 0.65rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em;
  background: rgba(43,130,203,0.2); border: 1px solid rgba(43,130,203,0.4);
  color: #5ba3e0; padding: 0.15rem 0.45rem; border-radius: 3px;
}
.target-row {
  display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.75rem;
  padding: 0.4rem 0.6rem; background: rgba(255,255,255,0.03);
  border-radius: 4px; border: 1px solid rgba(255,255,255,0.06);
}
.target-label {
    font-size: 0.7rem; color: #8899aa; text-transform: uppercase; letter-spacing: 0.08em; flex: 1;
}
.target-val {
    font-size: 0.8rem; font-weight: 600; color: #c8d8e8; font-family: 'Consolas', monospace;
}
.target-pct {
    font-size: 0.75rem; font-weight: 700; padding: 0.1rem 0.35rem; border-radius: 3px;
}
.pct-good {
    background: rgba(56,161,105,0.15); color: #68d391;
}
.pct-low  {
    background: rgba(229,62,62,0.12);  color: #fc8181;
}
</style>
