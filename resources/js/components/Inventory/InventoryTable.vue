<template>
    <div>
      <!-- Summary bar -->
      <div class="results-summary-bar">
        <div class="rsb-item">
          <span class="rsb-label">Processes</span>
          <span class="rsb-value">{{ uniqueProcessCount }}</span>
        </div>
        <div class="rsb-divider"></div>
        <div class="rsb-item">
          <span class="rsb-label">Total Weight</span>
          <span class="rsb-value">{{ grandTotal }}<span class="rsb-unit">t</span></span>
        </div>
        <div class="rsb-divider"></div>
        <div class="rsb-item">
          <span class="rsb-label">Total Work Orders</span>
          <span class="rsb-value">{{ totalWorkOrders }}</span>
        </div>
      </div>

      <!-- Table -->
      <div class="table-wrap">
        <table class="results-table">
          <thead>
            <tr>
              <th>Process</th>
              <th>Area</th>
              <th class="num-col">Work Orders</th>
              <th class="num-col">Weight (t)</th>
            </tr>
          </thead>
          <tbody>
            <template v-for="(rows, process) in groupedByProcess" :key="process">
              <tr v-for="(row, rowIndex) in rows" :key="row.area" class="data-row">
                <td>
                  <span v-if="rowIndex === 0" class="process-badge">{{ process }}</span>
                </td>
                <td><span class="area-badge">{{ row.area }}</span></td>
                <td class="mono num-col">{{ row.work_order_count }}</td>
                <td class="mono num-col">{{ Number(row.tons).toFixed(6) }}</td>
              </tr>
              <tr class="subtotal-row">
                <td class="subtotal-label">↳ {{ process }}</td>
                <td class="subtotal-areas">{{ rows.length }} area{{ rows.length > 1 ? 's' : '' }}</td>
                <td class="mono num-col subtotal-val">{{ rows.reduce((s, r) => s + Number(r.work_order_count), 0) }}</td>
                <td class="mono num-col subtotal-val">{{ rows.reduce((s, r) => s + Number(r.tons), 0).toFixed(6) }}t</td>
              </tr>
            </template>
          </tbody>
        </table>
      </div>
    </div>
  </template>

  <script>
  export default {
    name: 'InventoryTable',

    props: {
      results: { type: Array, default: () => [] },
    },

    computed: {
      grandTotal() {
        return this.results.reduce((s, r) => s + Number(r.tons), 0).toFixed(6)
      },
      totalWorkOrders() {
        return this.results.reduce((s, r) => s + Number(r.work_order_count), 0)
      },
      uniqueProcessCount() {
        return new Set(this.results.map(r => r.process)).size
      },
      groupedByProcess() {
        return this.results.reduce((groups, row) => {
          const key = row.process || '—'
          if (!groups[key]) groups[key] = []
          groups[key].push(row)
          return groups
        }, {})
      },
    },
  }
  </script>

  <style scoped>
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
  .table-wrap {
    background: var(--bg-secondary);
    border: 1px solid var(--border-primary);
    border-radius: 8px;
    overflow: hidden;
    margin-bottom: 1rem;
    transition: background 0.3s ease;
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
  .data-row:hover { background: var(--bg-tertiary); }
  .mono    { font-family: 'Consolas', monospace; }
  .num-col { text-align: right; }
  .process-badge {
    display: inline-block;
    background: rgba(43,130,203,0.15);
    border: 1px solid var(--border-accent);
    border-radius: 4px; padding: 0.2rem 0.6rem;
    font-size: 0.75rem; font-weight: 700;
    color: var(--text-accent); font-family: 'Consolas', monospace;
    white-space: nowrap;
  }
  .area-badge {
    display: inline-block;
    background: rgba(43,130,203,0.1); border: 1px solid rgba(43,130,203,0.2);
    border-radius: 4px; padding: 0.1rem 0.45rem;
    font-size: 0.75rem; color: var(--text-accent);
  }
  .subtotal-row td {
    background: var(--bg-tertiary);
    border-top: 1px solid var(--border-accent);
    border-bottom: 2px solid rgba(43,130,203,0.3);
    padding: 0.4rem 1rem;
  }
  .subtotal-label {
    font-size: 0.7rem; font-weight: 700;
    text-transform: uppercase; letter-spacing: 0.08em;
    color: var(--text-accent); font-family: 'Consolas', monospace;
  }
  .subtotal-areas {
    font-size: 0.7rem; color: var(--text-muted);
  }
  .subtotal-val {
    color: var(--text-primary); font-weight: 700;
  }
  </style>
