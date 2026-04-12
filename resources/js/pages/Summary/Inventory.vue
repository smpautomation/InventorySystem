<template>
    <NavBarOld />
    <div class="page">

      <div class="page-header">
        <div class="header-left">
          <div class="eyebrow">WIP Inventory</div>
          <h2 class="title">Inventory Query <span class="title-unit">(tons)</span></h2>
        </div>
      </div>

      <!-- Filter Form -->
      <div class="filter-card">
        <div class="filter-row">

          <div class="filter-group">
            <label class="filter-label">Daily Check File</label>
            <div class="select-wrap">
              <select v-model="selectedDailyCheck" class="filter-select" :disabled="initLoading">
                <option value="" disabled>{{ initLoading ? 'Loading…' : 'Select a file' }}</option>
                <option v-for="f in dailyCheckFiles" :key="f" :value="f">{{ f }}</option>
              </select>
              <span class="select-arrow">▾</span>
            </div>
          </div>

          <div class="filter-group">
            <label class="filter-label">
                Areas
                <span v-if="selectedAreas.length" class="filter-count">{{ selectedAreas.length }} selected</span>
            </label>
            <div class="area-dropdown" :class="{ disabled: initLoading }">
                <div
                v-for="area in availableAreas"
                :key="area"
                class="area-option"
                :class="{ selected: selectedAreas.includes(area) }"
                @click="toggleArea(area)"
                >
                <span class="area-option-tick">✓</span>
                {{ area }}
                </div>
            </div>
          </div>

          <div class="filter-actions">
            <button
              class="btn-query"
              :disabled="!canQuery || queryLoading"
              @click="runQuery"
            >
              <span v-if="queryLoading" class="btn-spinner">⟳</span>
              <span v-else>⬡</span>
              {{ queryLoading ? 'Querying…' : 'Run Query' }}
            </button>
            <button
              v-if="results.length"
              class="btn-clear"
              @click="clearResults"
            >
              ✕ Clear
            </button>
          </div>

        </div>

        <div v-if="initError" class="error-banner">⚠ {{ initError }}</div>
        <div v-if="queryError" class="error-banner">⚠ {{ queryError }}</div>
      </div>

      <!-- Results -->
      <template v-if="results.length">

        <!-- Summary bar -->
        <div class="results-summary-bar">
          <div class="rsb-item">
            <span class="rsb-label">Work Orders</span>
            <span class="rsb-value">{{ results.length }}</span>
          </div>
          <div class="rsb-divider"></div>
          <div class="rsb-item">
            <span class="rsb-label">Total Weight</span>
            <span class="rsb-value">{{ grandTotal }}<span class="rsb-unit">t</span></span>
          </div>
          <div class="rsb-divider"></div>
          <div class="rsb-item">
            <span class="rsb-label">Areas</span>
            <span class="rsb-value">{{ selectedAreas.join(', ') }}</span>
          </div>
          <div class="rsb-spacer"></div>
          <div class="rsb-item">
            <label class="toggle-label">
              <input type="checkbox" v-model="groupByRouting" class="toggle-checkbox" />
              <span class="toggle-track"><span class="toggle-thumb"></span></span>
              <span class="toggle-text">Group by Routing</span>
            </label>
          </div>
        </div>

        <!-- Grouped view -->
        <template v-if="groupByRouting">
          <div v-for="(rows, routing) in groupedResults" :key="routing" class="group-block">
            <div class="group-header">
              <span class="group-routing">{{ routing }}</span>
              <span class="group-count">{{ rows.length }} orders</span>
              <span class="group-total">
                {{ rows.reduce((s, r) => s + r.Tons, 0).toFixed(6) }}
                <span class="group-unit">t</span>
              </span>
            </div>
            <div class="table-wrap">
              <table class="results-table">
                <thead>
                  <tr>
                    <th>Work Order</th>
                    <th>Process</th>
                    <th>Area</th>
                    <th class="num-col">Weight (t)</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="row in rows" :key="row.Work_Order">
                    <td class="mono">{{ row.Work_Order }}</td>
                    <td>{{ row.Process }}</td>
                    <td><span class="area-badge">{{ row.Area }}</span></td>
                    <td class="mono num-col">{{ Number(row.Tons).toFixed(6) }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </template>

        <!-- Flat view -->
        <div v-else class="table-wrap">
          <table class="results-table">
            <thead>
              <tr>
                <th>Work Order</th>
                <th>Process</th>
                <th>Area</th>
                <th>Routing Code</th>
                <th class="num-col">Weight (t)</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="row in results" :key="row.Work_Order">
                <td class="mono">{{ row.Work_Order }}</td>
                <td>{{ row.Process }}</td>
                <td><span class="area-badge">{{ row.Area }}</span></td>
                <td class="mono">{{ row.RoutingCode }}</td>
                <td class="mono num-col">{{ Number(row.Tons).toFixed(6) }}</td>
              </tr>
            </tbody>
          </table>
        </div>

      </template>

      <div v-else-if="queried && !queryLoading" class="empty-state">
        No results found for the selected filters.
      </div>

    </div>
  </template>

  <script>
  import NavBarOld from '@/components/NavBarOld.vue'

  const API_INIT  = '/api/inventory-wip/init'
  const API_QUERY = '/api/inventory-wip/getWIP'

  const CACHE_TTL_INIT_MS  = 60 * 60 * 1000        // 1 hour — matches server
    const CACHE_TTL_QUERY_MS = 15 * 60 * 1000        // 15 min — avoids repeat network hits within a session

    function readCache(key) {
        try {
            const raw = localStorage.getItem(key)
            if (!raw) return null
            const { timestamp, payload } = JSON.parse(raw)
            if (Date.now() - timestamp > payload.ttl) {
                localStorage.removeItem(key)
                return null
            }
            return payload.data
        } catch {
            return null
        }
    }

    function writeCache(key, data, ttl) {
        try {
            localStorage.setItem(key, JSON.stringify({
                timestamp: Date.now(),
                payload:   { data, ttl },
            }))
        } catch {
            // localStorage full — skip
        }
    }

    function makeQueryCacheKey(dailyCheckFile, areas) {
        const sorted = [...areas].sort().join(',')
        return `inventory_wip:${dailyCheckFile}:${sorted}`
    }

  export default {
    name: 'Inventory',
    components: { NavBarOld },

    data() {
      return {
        dailyCheckFiles: [],
        availableAreas:  [],

        selectedDailyCheck: '',
        selectedAreas:      [],

        results:        [],
        queried:        false,
        groupByRouting: false,

        initLoading:  false,
        initError:    null,
        queryLoading: false,
        queryError:   null,
      }
    },

    computed: {
      canQuery() {
        return this.selectedDailyCheck && this.selectedAreas.length > 0
      },
      grandTotal() {
        return this.results.reduce((s, r) => s + Number(r.Tons), 0).toFixed(6)
      },
      groupedResults() {
        return this.results.reduce((groups, row) => {
          const key = row.RoutingCode || '—'
          if (!groups[key]) groups[key] = []
          groups[key].push(row)
          return groups
        }, {})
      },
    },

    async mounted() {
      await this.loadInit()
    },

    methods: {
      async loadInit() {
        const cacheKey = 'inventory_wip:init'
        const cached   = readCache(cacheKey)
        if (cached) {
            this.dailyCheckFiles = cached.Daily_Check_file ?? []
            this.availableAreas  = cached.Area ?? []
            return
        }

        this.initLoading = true
        this.initError   = null
        try {
            const res  = await fetch(API_INIT)
            if (!res.ok) throw new Error(`HTTP ${res.status}`)
            const data = await res.json()
            this.dailyCheckFiles = data.Daily_Check_file ?? []
            this.availableAreas  = data.Area ?? []
            writeCache(cacheKey, data, CACHE_TTL_INIT_MS)
        } catch (e) {
            this.initError = `Failed to load filters: ${e.message}`
        } finally {
            this.initLoading = false
        }
      },

      toggleArea(area) {
        const idx = this.selectedAreas.indexOf(area)
        if (idx === -1) this.selectedAreas.push(area)
        else            this.selectedAreas.splice(idx, 1)
      },

      async runQuery() {
        const cacheKey = makeQueryCacheKey(this.selectedDailyCheck, this.selectedAreas)
        const cached   = readCache(cacheKey)
        if (cached) {
            this.results = cached
            this.queried = true
            return
        }

        this.queryLoading = true
        this.queryError   = null
        this.queried      = false
        try {
            const res = await fetch(API_QUERY, {
                method:  'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.csrfToken() },
                body:    JSON.stringify({
                    daily_check_file: this.selectedDailyCheck,
                    areas:            this.selectedAreas,
                }),
            })
            if (!res.ok) throw new Error(`HTTP ${res.status}`)
            const data   = await res.json()
            this.results = data
            this.queried = true
            writeCache(cacheKey, data, CACHE_TTL_QUERY_MS)
        } catch (e) {
            this.queryError = `Query failed: ${e.message}`
        } finally {
            this.queryLoading = false
        }
      },

      clearResults() {
        this.results        = []
        this.queried        = false
        this.groupByRouting = false
      },

      csrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? ''
      },
    },
  }
  </script>

  <style scoped>
  .page {
    background: var(--bg-primary);
    min-height: 100vh;
    padding: 1.5rem 2rem;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    color: var(--text-primary);
    transition: background 0.3s ease;
  }

  /* ── Header ── */
  .page-header   { margin-bottom: 1.5rem; }
  .eyebrow {
    font-size: 0.7rem; font-weight: 600; text-transform: uppercase;
    letter-spacing: 0.18em; color: var(--text-accent); margin-bottom: 0.25rem;
  }
  .title       { font-size: 1.25rem; font-weight: 700; color: var(--text-primary); margin: 0; }
  .title-unit  { font-size: 0.875rem; font-weight: 400; color: var(--text-muted); }

  /* ── Filter Card ── */
  .filter-card {
    background: var(--bg-secondary);
    border: 1px solid var(--border-primary);
    border-radius: 8px;
    padding: 1.25rem 1.5rem;
    margin-bottom: 1.25rem;
    transition: background 0.3s ease;
  }
  .filter-row {
    display: flex;
    gap: 1.5rem;
    align-items: flex-start;
    flex-wrap: wrap;
  }
  .filter-group { display: flex; flex-direction: column; gap: 0.4rem; }


  .filter-label {
    font-size: 0.7rem; font-weight: 600; text-transform: uppercase;
    letter-spacing: 0.1em; color: var(--text-accent);
  }
  .filter-hint { font-weight: 400; text-transform: none; letter-spacing: 0; color: var(--text-muted); }

  /* Select */
  .select-wrap { position: relative; }
  .filter-select {
    appearance: none;
    background: var(--input-bg);
    border: 1px solid var(--input-border);
    border-radius: 6px;
    padding: 0.45rem 2rem 0.45rem 0.75rem;
    color: var(--input-text);
    font-size: 0.875rem;
    cursor: pointer;
    min-width: 220px;
    transition: border-color 0.2s, background 0.3s ease;
  }
  .filter-select:focus   { outline: none; border-color: var(--border-accent-hover); }
  .filter-select:disabled { opacity: 0.5; cursor: not-allowed; }
  .select-arrow {
    position: absolute; right: 0.6rem; top: 50%;
    transform: translateY(-50%);
    color: var(--text-muted); font-size: 0.75rem; pointer-events: none;
  }

  /* Area chips */
  .area-dropdown {
    width: 220px;
    max-height: 160px;
    overflow-y: auto;
    background: var(--input-bg);
    border: 1px solid var(--input-border);
    border-radius: 6px;
    scrollbar-width: thin;
    scrollbar-color: var(--border-accent) transparent;
  }
  .area-dropdown.disabled { opacity: 0.5; pointer-events: none; }
  .area-option {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.4rem 0.75rem;
    font-size: 0.8rem;
    color: var(--text-muted);
    cursor: pointer;
    border-bottom: 1px solid var(--border-primary);
    transition: background 0.15s, color 0.15s;
    user-select: none;
  }
  .area-option:last-child { border-bottom: none; }
  .area-option:hover      { background: var(--bg-tertiary); color: var(--text-secondary); }
  .area-option.selected   { color: var(--text-accent); background: rgba(43,130,203,0.08); }
  .area-option-tick {
    font-size: 0.6rem;
    color: #2b82cb;
    width: 10px;
    flex-shrink: 0;
    opacity: 0;
    transition: opacity 0.15s;
  }
  .area-option.selected .area-option-tick { opacity: 1; }
  .filter-count {
    font-size: 0.65rem;
    font-weight: 700;
    background: rgba(43,130,203,0.15);
    border: 1px solid var(--border-accent);
    color: var(--text-accent);
    padding: 0.05rem 0.35rem;
    border-radius: 3px;
    margin-left: 0.4rem;
    letter-spacing: 0;
    text-transform: none;
  }

  /* Buttons */
  .filter-actions { display: flex; align-items: flex-end; gap: 0.5rem; padding-bottom: 0.05rem; }
  .btn-query {
    display: flex; align-items: center; gap: 0.4rem;
    background: rgba(43,130,203,0.8); border: 1px solid var(--border-accent);
    border-radius: 6px; padding: 0.45rem 1.1rem;
    color: #fff; font-size: 0.875rem; font-weight: 600;
    cursor: pointer; transition: all 0.2s;
  }
  .btn-query:hover:not(:disabled) { background: var(--accent); }
  .btn-query:disabled { opacity: 0.45; cursor: not-allowed; }
  .btn-clear {
    background: none; border: 1px solid var(--border-primary);
    border-radius: 6px; padding: 0.45rem 0.9rem;
    color: var(--text-muted); font-size: 0.875rem; cursor: pointer; transition: all 0.2s;
  }
  .btn-clear:hover { border-color: rgba(229,62,62,0.4); color: var(--pct-low-text); }

  @keyframes spin { to { transform: rotate(360deg); } }
  .btn-spinner { display: inline-block; animation: spin 0.8s linear infinite; }

  /* Error */
  .error-banner {
    margin-top: 0.75rem;
    background: var(--pct-low-bg); border: 1px solid rgba(229,62,62,0.3);
    border-radius: 6px; padding: 0.5rem 0.9rem;
    color: var(--pct-low-text); font-size: 0.8rem;
  }

  /* ── Results summary bar ── */
  .results-summary-bar {
    display: flex; align-items: center; gap: 1rem; flex-wrap: wrap;
    background: var(--bg-secondary); border: 1px solid var(--border-accent);
    border-radius: 8px; padding: 0.75rem 1.25rem;
    margin-bottom: 1rem;
    transition: background 0.3s ease;
  }
  .rsb-item   { display: flex; flex-direction: column; gap: 0.1rem; }
  .rsb-label  { font-size: 0.65rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.1em; color: var(--text-muted); }
  .rsb-value  { font-size: 0.95rem; font-weight: 700; color: var(--text-primary); font-family: 'Consolas', monospace; }
  .rsb-unit   { font-size: 0.75rem; color: var(--text-muted); font-weight: 400; }
  .rsb-divider { width: 1px; height: 32px; background: var(--border-primary); flex-shrink: 0; }
  .rsb-spacer { flex: 1; }

  /* ── Group view ── */
  .group-block  { margin-bottom: 1rem; }
  .group-header {
    display: flex; align-items: center; gap: 0.75rem;
    background: var(--bg-secondary); border: 1px solid var(--border-accent);
    border-radius: 8px 8px 0 0;
    padding: 0.6rem 1rem;
    transition: background 0.3s ease;
  }
  .group-routing { font-size: 0.8rem; font-weight: 700; color: var(--text-accent); font-family: 'Consolas', monospace; flex: 1; }
  .group-count   { font-size: 0.75rem; color: var(--text-muted); }
  .group-total   { font-size: 0.9rem; font-weight: 700; color: var(--text-primary); font-family: 'Consolas', monospace; }
  .group-unit    { font-size: 0.7rem; color: var(--text-muted); }

  /* ── Table ── */
  .table-wrap {
    background: var(--bg-secondary);
    border: 1px solid var(--border-primary);
    border-radius: 8px;
    overflow: hidden;
    margin-bottom: 1rem;
    transition: background 0.3s ease;
  }
  .group-block .table-wrap {
    border-top: none;
    border-radius: 0 0 8px 8px;
  }
  .results-table {
    width: 100%; border-collapse: collapse; font-size: 0.8rem;
  }
  .results-table thead tr {
    background: var(--bg-tertiary);
    border-bottom: 1px solid var(--border-primary);
  }
  .results-table th {
    padding: 0.6rem 1rem; text-align: left;
    font-size: 0.65rem; font-weight: 700; text-transform: uppercase;
    letter-spacing: 0.1em; color: var(--text-muted);
  }
  .results-table td {
    padding: 0.55rem 1rem;
    color: var(--text-secondary);
    border-bottom: 1px solid var(--border-primary);
  }
  .results-table tbody tr:last-child td { border-bottom: none; }
  .results-table tbody tr:hover { background: var(--bg-tertiary); }
  .mono    { font-family: 'Consolas', monospace; }
  .num-col { text-align: right; }

  .area-badge {
    display: inline-block;
    background: rgba(43,130,203,0.1); border: 1px solid rgba(43,130,203,0.2);
    border-radius: 4px; padding: 0.1rem 0.45rem;
    font-size: 0.75rem; color: var(--text-accent);
  }

  /* ── Toggle (reusing existing pattern) ── */
  .toggle-label {
    display: flex; align-items: center; gap: 0.6rem;
    cursor: pointer; user-select: none;
  }
  .toggle-checkbox { display: none; }
  .toggle-track {
    position: relative; width: 36px; height: 20px;
    background: var(--bg-tertiary); border: 1px solid var(--border-primary);
    border-radius: 999px; flex-shrink: 0;
    transition: background 0.25s, border-color 0.25s, box-shadow 0.25s;
  }
  .toggle-checkbox:checked ~ .toggle-track {
    background: rgba(43,130,203,0.35); border-color: var(--border-accent);
    box-shadow: 0 0 8px rgba(43,130,203,0.4);
  }
  .toggle-thumb {
    position: absolute; top: 3px; left: 3px;
    width: 12px; height: 12px;
    background: var(--text-muted); border-radius: 50%;
    transition: transform 0.25s cubic-bezier(0.34, 1.56, 0.64, 1), background 0.25s;
  }
  .toggle-checkbox:checked ~ .toggle-track .toggle-thumb {
    transform: translateX(16px); background: #2b82cb;
    box-shadow: 0 0 6px rgba(43,130,203,0.7);
  }
  .toggle-text {
    font-size: 0.8rem; font-weight: 600; letter-spacing: 0.04em;
    color: var(--text-muted); text-transform: uppercase; transition: color 0.2s;
  }
  .toggle-label:has(.toggle-checkbox:checked) .toggle-text { color: var(--text-accent); }

  /* ── Empty state ── */
  .empty-state {
    text-align: center; padding: 3rem;
    color: var(--text-muted); font-size: 0.875rem; letter-spacing: 0.05em;
  }

  @media (max-width: 768px) {
    .page       { padding: 1rem; }
    .filter-row { flex-direction: column; }
    .filter-actions { width: 100%; }
    .btn-query  { flex: 1; justify-content: center; }
  }
  </style>
