<template>
  <div class="summary-grid">
    <div
      v-for="m in reversedSummaries"
      :key="m.month"
      class="summary-card"
      :class="{ 'summary-card-current': m.isCurrent }"
    >
      <div class="summary-top">
        <span class="summary-name">{{ m.month }}</span>
        <span v-if="m.isCurrent" class="current-badge">Current</span>
      </div>

      <div class="summary-total">
        {{ m.grandTotal.toFixed(2) }}<span class="summary-unit">t</span>
      </div>

      <div class="target-row">
        <span class="target-label">Target</span>
        <span class="target-val">{{ m.target > 0 ? m.target + ' t' : '—' }}</span>
        <span
          v-if="m.target > 0"
          class="target-pct"
          :class="m.grandTotal >= m.target ? 'pct-good' : 'pct-low'"
        >
          {{ ((m.grandTotal / m.target) * 100).toFixed(2) }}%
        </span>
      </div>

      <div v-if="m.isCurrent && m.dailyTarget > 0" class="target-row">
        <span class="target-label">Daily Target</span>
        <span class="target-val">{{ m.dailyTarget.toFixed(2) }} t
            <span class="target-label">/ {{ m.working_days }} days</span>
        </span>
      </div>
      <div v-if="m.isCurrent && m.yesterdayTotal !== null" class="target-row">
        <span class="target-label">Yesterday's Output</span>
        <span class="target-val">
            {{ Number(m.yesterdayTotal).toFixed(2) }} t
            <span class="target-label">÷ {{ m.yesterdayDailyTarget }} t</span>
        </span>
        <span
            v-if="m.yesterdayDailyTarget > 0"
            class="target-pct"
            :class="m.yesterdayTotal >= m.yesterdayDailyTarget ? 'pct-good' : 'pct-low'"
        >
            {{ ((m.yesterdayTotal / m.yesterdayDailyTarget) * 100).toFixed(2) }}%
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
  },
  computed: {
    reversedSummaries() {
      return [...this.summaries].reverse()
    }
  }
}
</script>

<style scoped>
.summary-grid {
    display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem;
}
.summary-card {
    background: var(--card-bg);
    border: 1px solid var(--card-border);
    border-radius: 8px;
    padding: 1rem 1.25rem;
    transition: transform 0.2s, box-shadow 0.2s, background 0.3s ease;
}
.summary-card:hover {
    transform: translateY(-3px); box-shadow: 0 8px 24px var(--shadow);
}
.summary-card-current {
    border-color: var(--border-accent); box-shadow: 0 0 0 1px rgba(43,130,203,0.2);
}
.summary-top {
    display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;
}
.summary-name {
    font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em; color: var(--text-muted);
}
.summary-total {
    font-size: 1.75rem; font-weight: 700; color: var(--text-primary); font-family: 'Consolas', monospace; margin-bottom: 0.75rem; line-height: 1;
}
.summary-unit {
    font-size: 1rem; color: var(--text-muted); font-weight: 400;
}
.current-badge {
    font-size: 0.65rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em;
    background: var(--accent-hover); border: 1px solid var(--border-accent);
    color: var(--text-accent); padding: 0.15rem 0.45rem; border-radius: 3px;
}
.target-row {
    display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.75rem;
    padding: 0.4rem 0.6rem; background: var(--bg-tertiary);
    border-radius: 4px; border: 1px solid var(--border-primary);
    transition: background 0.3s ease;
}
.target-label {
    font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.08em; flex: 1;
}
.target-val {
    font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); font-family: 'Consolas', monospace;
}
.target-pct {
    font-size: 0.75rem; font-weight: 700; padding: 0.1rem 0.35rem; border-radius: 3px;
}
.pct-good {
    background: var(--pct-good-bg); color: var(--pct-good-text);
}
.pct-low {
    background: var(--pct-low-bg); color: var(--pct-low-text);
}
</style>
