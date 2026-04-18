<template>
    <NavBarOld />
    <div class="page">

      <div class="page-header">
        <div class="eyebrow">WIP Inventory</div>
        <h2 class="title">Inventory Query <span class="title-unit">(tons)</span></h2>
      </div>

      <InventoryFilters
        :daily-check-files="dailyCheckFiles"
        :available-areas="availableAreas"
        :selected-daily-check="selectedDailyCheck"
        :selected-areas="selectedAreas"
        :loading="initLoading"
        :query-loading="queryLoading"
        :has-results="false"
        :init-error="initError"
        :query-error="queryError"
        @update:selectedDailyCheck="selectedDailyCheck = $event"
        @update:selectedAreas="onAreasChanged"
        @query="runQuery"
        @clear="clearResults"
      />

      <InventoryGraph
        v-if="selectedAreas.length"
        :selected-areas="selectedAreas"
        ref="graph"
      />

      <InventoryTable
        v-if="results.length"
        :results="results"
      />

      <div v-else-if="queried && !queryLoading" class="empty-state">
        No results found for the selected filters.
      </div>

    </div>
  </template>

  <script>
  import NavBarOld        from '@/components/NavBarOld.vue'
  import InventoryFilters from '@/components/Inventory/InventoryFilters.vue'
  import InventoryTable   from '@/components/Inventory/InventoryTable.vue'
  import InventoryGraph from '@/components/Inventory/InventoryGraph.vue'

  const API_INIT  = '/api/inventory-wip/init'
  const API_QUERY = '/api/inventory-wip/getWIP'

  const CACHE_VERSION = 'v2'

  function readCache(key) {
    try {
      const raw = localStorage.getItem(key)
      if (!raw) return null
      const parsed = JSON.parse(raw)
      if (parsed.version !== CACHE_VERSION) {
        localStorage.removeItem(key)
        return null
      }
      return parsed.payload
    } catch {
      return null
    }
  }

  function writeCache(key, data) {
    try {
      localStorage.setItem(key, JSON.stringify({ version: CACHE_VERSION, payload: data }))
    } catch {}
  }

  function makeQueryCacheKey(dailyCheckFile, areas) {
    const sorted = [...areas].sort().join(',')
    return `inventory_wip:${dailyCheckFile}:${sorted}`
  }

  export default {
    name: 'Inventory',
    components: { NavBarOld, InventoryFilters, InventoryTable, InventoryGraph },

    data() {
      return {
        dailyCheckFiles:    [],
        availableAreas:     [],
        selectedDailyCheck: '',
        selectedAreas:      [],
        results:            [],
        queried:            false,
        initLoading:        false,
        initError:          null,
        queryLoading:       false,
        queryError:         null,
      }
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
          writeCache(cacheKey, data)
        } catch (e) {
          this.initError = `Failed to load filters: ${e.message}`
        } finally {
          this.initLoading = false
        }
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
          writeCache(cacheKey, data)
        } catch (e) {
          this.queryError = `Query failed: ${e.message}`
        } finally {
          this.queryLoading = false
        }
      },

      clearResults() {
        this.results = []
        this.queried = false
      },

      csrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? ''
      },

      onAreasChanged(areas) {
        this.selectedAreas = areas
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
  .page-header { margin-bottom: 1.5rem; }
  .eyebrow {
    font-size: 0.7rem; font-weight: 600; text-transform: uppercase;
    letter-spacing: 0.18em; color: var(--text-accent); margin-bottom: 0.25rem;
  }
  .title      { font-size: 1.25rem; font-weight: 700; color: var(--text-primary); margin: 0; }
  .title-unit { font-size: 0.875rem; font-weight: 400; color: var(--text-muted); }
  .empty-state {
    text-align: center; padding: 3rem;
    color: var(--text-muted); font-size: 0.875rem; letter-spacing: 0.05em;
  }
  @media (max-width: 768px) {
    .page { padding: 1rem; }
  }
  </style>
