<template>
    <div class="chart-header">
      <div class="header-left">
        <div class="header-eyebrow">{{ eyebrow }}</div>
        <h2 class="header-title">
          {{ title }} <span class="header-unit">(tons)</span>
        </h2>
      </div>
      <div class="header-right">
        <div class="header-top-row">
          <button v-if="canEditTargets" class="edit-target-btn" @click="openModal">
            <span class="btn-icon">✏️</span>
            <span class="btn-text">Edit Targets</span>
          </button>
          <div class="month-badge">
            <span class="month-label">{{ currentMonthLabel }}</span>
            <span class="day-indicator">Day {{ today }} of {{ daysInMonth }}</span>
          </div>
          <div class="date-range-wrap">
            <input
              type="date"
              v-model="dateFrom"
              class="date-input"
              :max="dateTo || todayStr"
              @change="emitDateRange"
            />
            <span class="date-sep">→</span>
            <input
              type="date"
              v-model="dateTo"
              class="date-input"
              :min="dateFrom"
              :max="todayStr"
              @change="emitDateRange"
            />
            <button
              v-if="dateFrom || dateTo"
              class="date-clear-btn"
              @click="clearDateRange"
              title="Reset to default"
            >✕</button>
          </div>
        </div>
        <div class="legend-row">
          <div v-for="ds in datasets" :key="ds.label" class="legend-item">
            <span
              class="legend-dot"
              :style="ds.type === 'line'
                ? { background: 'none', borderBottom: `2px dashed ${ds.borderColor}`, width: '16px', height: '0', borderRadius: '0' }
                : { background: ds.backgroundColor }"
            ></span>
            <span class="legend-text">{{ ds.label }}</span>
          </div>
        </div>
      </div>

      <!-- ── Modal ── -->
      <Teleport to="body">
        <div v-if="showModal" class="modal-backdrop" @click.self="closeModal">
          <div class="modal">

            <div class="modal-header">
              <h3 class="modal-title">Edit Targets</h3>
              <button class="modal-close" @click="closeModal">✕</button>
            </div>

            <!-- Tabs -->
            <div class="modal-tabs">
              <button
                class="modal-tab"
                :class="{ 'modal-tab-active': activeTab === 'monthly' }"
                @click="activeTab = 'monthly'"
              >Monthly</button>
              <button
                class="modal-tab"
                :class="{ 'modal-tab-active': activeTab === 'daily' }"
                @click="activeTab = 'daily'"
              >Daily</button>
            </div>

            <!-- ── Monthly Tab ── -->
            <template v-if="activeTab === 'monthly'">
              <div class="modal-controls">
                <div class="control-group">
                  <label class="control-label">Year</label>
                  <select v-model="selectedYear" class="control-select">
                    <option v-for="y in yearOptions" :key="y" :value="y">{{ y }}</option>
                  </select>
                </div>
              </div>
              <div class="modal-body">
                <div v-for="(row, index) in monthlyForm" :key="row.month" class="target-row">
                  <span class="target-month">{{ row.month }}</span>
                  <div class="target-input-wrap">
                    <input
                      v-model.number="monthlyForm[index].target"
                      type="number" min="0" step="0.01"
                      class="target-input" placeholder="Target"
                    />
                    <span class="target-unit">t</span>
                  </div>
                  <div class="target-input-wrap">
                    <input
                      v-model.number="monthlyForm[index].working_days"
                      type="number" min="0" step="0.01"
                      class="target-input" style="width:70px" placeholder="Days"
                    />
                    <span class="target-unit">days</span>
                  </div>
                </div>
              </div>
              <div class="modal-footer">
                <span v-if="saveSuccess" class="save-success">✓ Saved successfully</span>
                <span v-if="saveError"   class="save-error">✕ {{ saveError }}</span>
                <button class="btn-cancel" @click="closeModal">Cancel</button>
                <button class="btn-save" :disabled="saving" @click="saveMonthlyTargets">
                  {{ saving ? 'Saving…' : 'Save Targets' }}
                </button>
              </div>
            </template>

            <!-- ── Daily Tab ── -->
            <template v-if="activeTab === 'daily'">
              <div class="modal-controls">
                <div class="control-group">
                  <label class="control-label">Year</label>
                  <select v-model="dailySelectedYear" class="control-select">
                    <option v-for="y in yearOptions" :key="y" :value="y">{{ y }}</option>
                  </select>
                </div>
                <div class="control-group">
                  <label class="control-label">Month</label>
                  <select v-model="dailySelectedMonth" class="control-select">
                    <option v-for="(name, idx) in MONTH_NAMES" :key="idx" :value="idx + 1">{{ name }}</option>
                  </select>
                </div>
                <div class="control-group">
                    <label class="control-label">Area</label>
                    <select v-model="dailySelectedArea" class="control-select">
                        <option v-for="a in areas" :key="a" :value="a">{{ a }}</option>
                    </select>
                </div>
              </div>
              <div class="modal-body">
                <div class="daily-grid-header">
                  <span class="daily-col-day">Day</span>
                  <span class="daily-col-target">Target (t)</span>
                </div>
                <div
                  v-for="(row, index) in dailyForm"
                  :key="row.day"
                  class="target-row daily-target-row"
                >
                  <span class="target-month daily-day-label">{{ row.day }}</span>
                  <div class="target-input-wrap">
                    <input
                      v-model.number="dailyForm[index].target"
                      type="number" min="0" step="0.01"
                      class="target-input" placeholder="0.00"
                    />
                    <span class="target-unit">t</span>
                  </div>
                </div>
              </div>
              <div class="modal-footer">
                <span v-if="dailySaveSuccess" class="save-success">✓ Saved successfully</span>
                <span v-if="dailySaveError"   class="save-error">✕ {{ dailySaveError }}</span>
                <button class="btn-cancel" @click="closeModal">Cancel</button>
                <button class="btn-save" :disabled="dailySaving" @click="saveDailyTargets">
                  {{ dailySaving ? 'Saving…' : 'Save Targets' }}
                </button>
              </div>
            </template>

          </div>
        </div>
      </Teleport>
    </div>
  </template>

  <script>
  const MONTH_NAMES = [
    'January','February','March','April','May','June',
    'July','August','September','October','November','December'
  ]

  export default {
    name: 'ChartHeader',

    props: {
      title:             { type: String,  required: true },
      eyebrow:           { type: String,  default: 'Monthly Output' },
      datasets:          { type: Array,   default: () => [] },
      currentMonthLabel: { type: String,  required: true },
      today:             { type: Number,  required: true },
      daysInMonth:       { type: Number,  required: true },
      plant:             { type: String,  required: true },
      targets:           { type: Object,  default: () => ({}) },
      dailyTargets:      { type: Object,  default: () => ({}) },
      areas:             { type: Array, default: () => [] },
    },

    emits: ['targets-updated', 'daily-targets-updated', 'date-range-changed'],

    data() {
      const now = new Date()
      return {
        MONTH_NAMES,
        showModal:   false,
        activeTab:   'monthly',

        saving:       false,
        saveSuccess:  false,
        saveError:    null,
        selectedYear: now.getFullYear(),
        monthlyForm:  [],

        dailySaving:       false,
        dailySaveSuccess:  false,
        dailySaveError:    null,
        dailySelectedYear:  now.getFullYear(),
        dailySelectedMonth: now.getMonth() + 1,
        dailyForm:          [],
        dailySelectedArea: '',

        ipData: null,

        dateFrom: '',
        dateTo:   '',
      }
    },

    async mounted() {
      try {
        const res  = await fetch('/api/ip-details')
        const data = await res.json()
        this.ipData = data
      } catch {
        this.ipData = null
      }
    },

    computed: {
      yearOptions() {
        const y = new Date().getFullYear()
        return [y - 1, y, y + 1]
      },
      canEditTargets() {
        return this.ipData?.location === 'AUTOMATION'
      },
      todayStr() {
        return new Date().toISOString().split('T')[0]
      },
    },

    watch: {
      selectedYear() {
        this.monthlyForm = this.buildMonthlyForm(this.selectedYear)
      },
      targets: {
        immediate: true,
        deep: true,
        handler() {
          this.monthlyForm = this.buildMonthlyForm(this.selectedYear)
        }
      },

      dailySelectedArea() {
        this.dailyForm = this.buildDailyForm(this.dailySelectedYear, this.dailySelectedMonth, this.dailySelectedArea)
      },
      dailySelectedMonth() {
        this.dailyForm = this.buildDailyForm(this.dailySelectedYear, this.dailySelectedMonth)
      },
      dailySelectedYear() {
        this.dailyForm = this.buildDailyForm(this.dailySelectedYear, this.dailySelectedMonth)
      },
      dailyTargets: {
        immediate: true,
        deep: true,
        handler() {
          this.dailyForm = this.buildDailyForm(this.dailySelectedYear, this.dailySelectedMonth)
        }
      },
      areas: {
        immediate: true,
        handler(val) {
            if (val.length && !this.dailySelectedArea) {
                this.dailySelectedArea = ''
                this.dailyForm = []
            }
        }
      },
    },

    methods: {
      buildMonthlyForm(year) {
        return MONTH_NAMES.map(month => {
          const entry        = this.targets?.[month]
          const target       = typeof entry === 'object' ? Number(entry?.target       ?? 0) : Number(entry ?? 0)
          const working_days = typeof entry === 'object' ? Number(entry?.working_days ?? 0) : 0
          return { month, year, target, working_days }
        })
      },

      buildDailyForm(year, monthNumber, area) {
        if (!area) return []
        const daysInMonth = new Date(year, monthNumber, 0).getDate()
        return Array.from({ length: daysInMonth }, (_, i) => {
            const day    = i + 1
            const stored = this.dailyTargets?.[area]?.[String(day)]
                        ?? this.dailyTargets?.[area]?.[day]
            return {
                day,
                month:        MONTH_NAMES[monthNumber - 1],
                month_number: monthNumber,
                year,
                area,
                target: stored !== undefined ? Number(stored) : 0,
            }
        })
      },

      // ── Modal ────────────────────────────────────────────────
      openModal() {
        this.monthlyForm      = this.buildMonthlyForm(this.selectedYear)
        this.dailySelectedArea = ''
        this.dailyForm        = []
        this.showModal        = true
        this.saveSuccess      = false
        this.saveError        = null
        this.dailySaveSuccess = false
        this.dailySaveError   = null
      },
      closeModal() {
        this.showModal = false
      },

      // ── Save monthly targets ─────────────────────────────────
      async saveMonthlyTargets() {
        this.saving      = true
        this.saveSuccess = false
        this.saveError   = null
        try {
          const payload = this.monthlyForm.filter(row => row.target > 0)
          if (!payload.length) { this.saveError = 'No targets to save'; return }

          const res = await fetch(`/api/plant-target/${this.plant}`, {
            method:  'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.csrfToken() },
            body:    JSON.stringify({ targets: payload }),
          })
          if (!res.ok) throw new Error(`HTTP ${res.status}`)

          this.saveSuccess = true
          this.$emit('targets-updated')
          setTimeout(() => { this.saveSuccess = false }, 3000)
        } catch (e) {
          this.saveError = e.message
        } finally {
          this.saving = false
        }
      },

      // ── Save daily targets ───────────────────────────────────
      async saveDailyTargets() {
        this.dailySaving      = true
        this.dailySaveSuccess = false
        this.dailySaveError   = null
        try {
          const payload = this.dailyForm
            .filter(row => row.target > 0)
            .map(row => ({
              day:          row.day,
              month:        row.month,
              month_number: row.month_number,
              year:         row.year,
              area:         row.area,
              target:       row.target,
            }))

          if (!payload.length) { this.dailySaveError = 'No targets to save'; return }

          const res = await fetch(`/api/plant-daily-target/${this.plant}`, {
            method:  'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.csrfToken() },
            body:    JSON.stringify({ targets: payload }),
          })
          if (!res.ok) throw new Error(`HTTP ${res.status}`)

          this.dailySaveSuccess = true
          this.$emit('daily-targets-updated')
          setTimeout(() => { this.dailySaveSuccess = false }, 3000)
        } catch (e) {
          this.dailySaveError = e.message
        } finally {
          this.dailySaving = false
        }
      },

      csrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? ''
      },

      emitDateRange() {
        this.$emit('date-range-changed', { from: this.dateFrom || null, to: this.dateTo || null })
      },
      clearDateRange() {
        this.dateFrom = ''
        this.dateTo   = ''
        this.$emit('date-range-changed', { from: null, to: null })
      },
    }
  }
  </script>

  <style scoped>
  .chart-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    margin-bottom: 1.5rem;
    gap: 1rem;
    flex-wrap: wrap;
  }
  .header-eyebrow {
    font-size: 0.7rem; font-weight: 600; text-transform: uppercase;
    letter-spacing: 0.18em; color: #5ba3e0; margin-bottom: 0.25rem;
  }
  .header-title  { font-size: 1.25rem; font-weight: 700; color: #e8f0f7; margin: 0; }
  .header-unit   { font-size: 0.875rem; font-weight: 400; color: #8899aa; }
  .header-right  { display: flex; flex-direction: column; align-items: flex-end; gap: 0.75rem; }
  .header-top-row { display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap; }

  .edit-target-btn {
    display: flex; align-items: center; gap: 0.5rem;
    background: rgba(43,130,203,0.15); border: 1px solid rgba(43,130,203,0.4);
    border-radius: 6px; padding: 0.4rem 0.9rem;
    color: #e8f0f7; font-size: 0.875rem; font-weight: 600;
    cursor: pointer; transition: all 0.2s ease;
  }
  .edit-target-btn:hover {
    background: rgba(43,130,203,0.25); border-color: rgba(43,130,203,0.6); transform: translateY(-1px);
  }
  .month-badge {
    display: flex; align-items: center; gap: 0.75rem;
    background: #112240; border: 1px solid rgba(43,130,203,0.25);
    border-radius: 6px; padding: 0.4rem 0.9rem;
  }
  .month-label   { font-size: 0.875rem; font-weight: 600; color: #e8f0f7; }
  .day-indicator { font-size: 0.75rem; color: #5ba3e0; font-weight: 500; }
  .legend-row    { display: flex; flex-wrap: wrap; gap: 0.6rem; justify-content: flex-end; }
  .legend-item   { display: flex; align-items: center; gap: 0.35rem; font-size: 0.75rem; color: #8899aa; }
  .legend-dot    { width: 10px; height: 10px; border-radius: 2px; flex-shrink: 0; }

  /* ── Modal ── */
  .modal-backdrop {
    position: fixed; inset: 0;
    background: rgba(0,0,0,0.6); backdrop-filter: blur(3px);
    display: flex; align-items: center; justify-content: center;
    z-index: 1000;
  }
  .modal {
    background: #112240; border: 1px solid rgba(43,130,203,0.3);
    border-radius: 10px; width: 100%; max-width: 480px; max-height: 90vh;
    display: flex; flex-direction: column;
    box-shadow: 0 20px 60px rgba(0,0,0,0.5);
  }
  .modal-header {
    display: flex; align-items: center; justify-content: space-between;
    padding: 1.25rem 1.5rem; border-bottom: 1px solid rgba(255,255,255,0.08);
  }
  .modal-title { font-size: 1rem; font-weight: 700; color: #e8f0f7; margin: 0; }
  .modal-close { background: none; border: none; color: #8899aa; font-size: 1rem; cursor: pointer; padding: 0.25rem; transition: color 0.2s; }
  .modal-close:hover { color: #e8f0f7; }

  /* Tabs */
  .modal-tabs {
    display: flex; border-bottom: 1px solid rgba(255,255,255,0.08);
  }
  .modal-tab {
    flex: 1; background: none; border: none;
    padding: 0.75rem 1rem; color: #8899aa;
    font-size: 0.875rem; font-weight: 600; cursor: pointer;
    transition: all 0.2s; border-bottom: 2px solid transparent;
    margin-bottom: -1px;
  }
  .modal-tab:hover { color: #c8d8e8; }
  .modal-tab-active { color: #5ba3e0; border-bottom-color: #5ba3e0; }

  .modal-controls {
    display: flex; gap: 1rem;
    padding: 1rem 1.5rem; border-bottom: 1px solid rgba(255,255,255,0.08);
  }
  .control-group { display: flex; flex-direction: column; gap: 0.35rem; }
  .control-label { font-size: 0.7rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.1em; color: #5ba3e0; }
  .control-select {
    background: #0d1b2a; border: 1px solid rgba(255,255,255,0.12);
    border-radius: 5px; padding: 0.35rem 0.75rem;
    color: #e8f0f7; font-size: 0.875rem; cursor: pointer;
  }
  .control-select:focus { outline: none; border-color: rgba(43,130,203,0.5); }

  .modal-body {
    overflow-y: auto; padding: 1rem 1.5rem;
    display: flex; flex-direction: column; gap: 0.6rem;
  }

  /* Daily grid header */
  .daily-grid-header {
    display: flex; align-items: center; gap: 1rem;
    padding: 0 0.75rem 0.25rem;
    font-size: 0.7rem; font-weight: 600; text-transform: uppercase;
    letter-spacing: 0.08em; color: #5ba3e0;
  }
  .daily-col-day    { min-width: 40px; }
  .daily-col-target { flex: 1; }

  .target-row {
    display: flex; align-items: center; justify-content: space-between;
    gap: 1rem; padding: 0.5rem 0.75rem;
    background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.06);
    border-radius: 6px;
  }
  .daily-target-row { justify-content: flex-start; }

  .target-month    { font-size: 0.875rem; color: #c8d8e8; font-weight: 500; min-width: 80px; }
  .daily-day-label { min-width: 40px; font-size: 0.875rem; color: #c8d8e8; font-weight: 500; }

  .target-input-wrap { display: flex; align-items: center; gap: 0.5rem; }
  .target-input {
    background: #0d1b2a; border: 1px solid rgba(255,255,255,0.12);
    border-radius: 5px; padding: 0.35rem 0.6rem;
    color: #e8f0f7; font-size: 0.875rem; width: 120px;
    text-align: right; font-family: 'Consolas', monospace;
  }
  .target-input:focus { outline: none; border-color: rgba(43,130,203,0.5); }
  .target-unit { font-size: 0.8rem; color: #8899aa; }

  .modal-footer {
    display: flex; align-items: center; justify-content: flex-end;
    gap: 0.75rem; padding: 1rem 1.5rem;
    border-top: 1px solid rgba(255,255,255,0.08); flex-wrap: wrap;
  }
  .save-success { font-size: 0.8rem; color: #68d391; margin-right: auto; }
  .save-error   { font-size: 0.8rem; color: #fc8181; margin-right: auto; }

  .btn-cancel {
    background: none; border: 1px solid rgba(255,255,255,0.15);
    border-radius: 6px; padding: 0.5rem 1rem;
    color: #8899aa; font-size: 0.875rem; cursor: pointer; transition: all 0.2s;
  }
  .btn-cancel:hover { border-color: rgba(255,255,255,0.3); color: #e8f0f7; }

  .btn-save {
    background: rgba(43,130,203,0.8); border: 1px solid rgba(43,130,203,0.6);
    border-radius: 6px; padding: 0.5rem 1.25rem;
    color: #fff; font-size: 0.875rem; font-weight: 600;
    cursor: pointer; transition: all 0.2s;
  }
  .btn-save:hover:not(:disabled) { background: rgba(43,130,203,1); }
  .btn-save:disabled { opacity: 0.5; cursor: not-allowed; }

  /* Date range */
  .date-range-wrap { display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap; }
  .date-input {
    background: #0d1b2a; border: 1px solid rgba(43,130,203,0.25);
    border-radius: 6px; padding: 0.4rem 0.6rem;
    color: #e8f0f7; font-size: 0.8rem; cursor: pointer; colorscheme: dark;
  }
  .date-input:focus { outline: none; border-color: rgba(43,130,203,0.5); }
  .date-sep { color: #8899aa; font-size: 0.8rem; }
  .date-clear-btn {
    background: rgba(229,62,62,0.15); border: 1px solid rgba(229,62,62,0.3);
    border-radius: 6px; padding: 0.35rem 0.6rem;
    color: #fc8181; font-size: 0.75rem; cursor: pointer; transition: all 0.2s;
  }
  .date-clear-btn:hover { background: rgba(229,62,62,0.25); }

  @media (max-width: 768px) {
    .chart-header    { flex-direction: column; }
    .header-right    { align-items: flex-start; width: 100%; }
    .header-top-row  { justify-content: space-between; width: 100%; }
    .legend-row      { justify-content: flex-start; }
    .modal           { max-width: 95vw; }
  }
  </style>
