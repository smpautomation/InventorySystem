<template>
    <div class="filter-card">
      <div class="filter-row">

        <div class="filter-group">
          <label class="filter-label">Daily Check File</label>
          <div class="select-wrap">
            <select
              :value="selectedDailyCheck"
              class="filter-select"
              :disabled="loading"
              @change="$emit('update:selectedDailyCheck', $event.target.value)"
            >
              <option value="" disabled>{{ loading ? 'Loading…' : 'Select a file' }}</option>
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
          <div class="area-dropdown" :class="{ disabled: loading }">
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
          <button class="btn-query" :disabled="!canQuery || queryLoading" @click="$emit('query')">
            <span v-if="queryLoading" class="btn-spinner">⟳</span>
            <span v-else>⬡</span>
            {{ queryLoading ? 'Querying…' : 'Run Query' }}
          </button>
          <button v-if="hasResults" class="btn-clear" @click="$emit('clear')">
            ✕ Clear
          </button>
        </div>

      </div>

      <div v-if="initError"  class="error-banner">⚠ {{ initError }}</div>
      <div v-if="queryError" class="error-banner">⚠ {{ queryError }}</div>
    </div>
  </template>

  <script>
  export default {
    name: 'InventoryFilters',

    props: {
      dailyCheckFiles:    { type: Array,   default: () => [] },
      availableAreas:     { type: Array,   default: () => [] },
      selectedDailyCheck: { type: String,  default: '' },
      selectedAreas:      { type: Array,   default: () => [] },
      loading:            { type: Boolean, default: false },
      queryLoading:       { type: Boolean, default: false },
      hasResults:         { type: Boolean, default: false },
      initError:          { type: String,  default: null },
      queryError:         { type: String,  default: null },
    },

    emits: ['update:selectedDailyCheck', 'update:selectedAreas', 'query', 'clear'],

    computed: {
      canQuery() {
        return this.selectedDailyCheck && this.selectedAreas.length > 0
      },
    },

    methods: {
      toggleArea(area) {
        const updated = [...this.selectedAreas]
        const idx     = updated.indexOf(area)
        if (idx === -1) updated.push(area)
        else            updated.splice(idx, 1)
        this.$emit('update:selectedAreas', updated)
      },
    },
  }
  </script>

  <style scoped>
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
    font-size: 0.6rem; color: #2b82cb;
    width: 10px; flex-shrink: 0;
    opacity: 0; transition: opacity 0.15s;
  }
  .area-option.selected .area-option-tick { opacity: 1; }
  .filter-count {
    font-size: 0.65rem; font-weight: 700;
    background: rgba(43,130,203,0.15); border: 1px solid var(--border-accent);
    color: var(--text-accent); padding: 0.05rem 0.35rem;
    border-radius: 3px; margin-left: 0.4rem;
    letter-spacing: 0; text-transform: none;
  }
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
  .error-banner {
    margin-top: 0.75rem;
    background: var(--pct-low-bg); border: 1px solid rgba(229,62,62,0.3);
    border-radius: 6px; padding: 0.5rem 0.9rem;
    color: var(--pct-low-text); font-size: 0.8rem;
  }
  @media (max-width: 768px) {
    .filter-row     { flex-direction: column; }
    .filter-actions { width: 100%; }
    .btn-query      { flex: 1; justify-content: center; }
  }
  </style>
