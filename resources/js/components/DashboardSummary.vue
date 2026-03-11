<template>
  <div class="dashboard-wrapper">

    <!-- Stat Cards Row -->
    <div class="stats-grid">
      <div class="stat-card" v-for="stat in stats" :key="stat.label">
        <div class="stat-icon">{{ stat.icon }}</div>
        <div class="stat-body">
          <div class="stat-value">{{ stat.value }}</div>
          <div class="stat-label">{{ stat.label }}</div>
        </div>
        <div class="stat-badge" :class="stat.trend > 0 ? 'up' : 'down'">
          {{ stat.trend > 0 ? '▲' : '▼' }} {{ Math.abs(stat.trend) }}%
        </div>
      </div>
    </div>

    <div class="dash-card full-width-card mb">
      <div class="card-header">
        <h3 class="card-title">Inventory System Quick Info</h3>
      </div>
      <div class="footer-info">
        <span class="footer-item">Latest Daily Check File: <strong>—</strong></span>
        <span class="footer-item">Total Work Order Count: <strong>—</strong>
            <span class="info-icon">ℹ️<span class="tooltip">Re-upload latest daily check file if total work order falls below 5,000.</span></span>
        </span>
      </div>
    </div>

    <div class="dash-card full-width-card mb">
      <div class="card-header">
        <h3 class="card-title">🔍 Mixing Prevention Model Scan History</h3>
        <!-- <router-link to="/summary/scanning" class="card-link">View All →</router-link> -->
      </div>
      <div class="table-placeholder">
        <table class="preview-table">
          <thead>
            <tr>
              <th>No</th>
              <th>Date/Time</th>
              <th>Area</th>
              <th>Work Order</th>
              <th>Model Name</th>
              <th>Lot No.</th>
              <th>Quantity</th>
              <th>IP Address</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="n in 5" :key="n">
              <td><div class="skel short"></div></td>
              <td><div class="skel"></div></td>
              <td><div class="skel short"></div></td>
              <td><div class="skel short"></div></td>
              <td><div class="skel"></div></td>
              <td><div class="skel"></div></td>
              <td><div class="skel short"></div></td>
              <td><div class="skel"></div></td>
            </tr>
          </tbody>
        </table>

      </div>
    </div>

    <!-- Row 2: Reject Charts side by side -->
    <div class="charts-row mb">

      <!-- Reject Per Description (pcs) -->
      <div class="dash-card">
        <div class="card-header">
          <h3 class="card-title">📊 Reject Description (pcs)</h3>
          <span class="card-date">—</span>
        </div>
        <div class="chart-area">
          <div class="chart-label-y">Reject (pcs)</div>
          <div class="chart-mock-bars">
            <div v-for="n in 10" :key="n" class="mock-bar pcs" :style="{ height: (15 + (n * 7) % 70) + '%' }"></div>
          </div>
        </div>
        <div class="reject-list">
          <div class="reject-item" v-for="item in rejectItemsPcs" :key="item.label">
            <span class="reject-dot" :style="{ background: item.color }"></span>
            <span class="reject-label">{{ item.label }}</span>
            <span class="reject-val">{{ item.value }} pcs</span>
          </div>
        </div>
      </div>

      <!-- Reject Per Description (kg) -->
      <div class="dash-card">
        <div class="card-header">
          <h3 class="card-title">⚖️ Reject Description (kg)</h3>
          <span class="card-date">—</span>
        </div>
        <div class="chart-area">
          <div class="chart-label-y">Reject (kg)</div>
          <div class="chart-mock-bars">
            <div v-for="n in 10" :key="n" class="mock-bar kg" :style="{ height: (10 + (n * 11) % 75) + '%' }"></div>
          </div>
        </div>
        <div class="reject-list">
          <div class="reject-item" v-for="item in rejectItemsKg" :key="item.label">
            <span class="reject-dot" :style="{ background: item.color }"></span>
            <span class="reject-label">{{ item.label }}</span>
            <span class="reject-val">{{ item.value }} kg</span>
          </div>
        </div>
      </div>

    </div>

    <!-- Row 3: Inventory Status + Recent Activity + Quick Actions -->
    <div class="dashboard-grid">

      <div class="dash-card">
        <div class="card-header">
          <h3 class="card-title">📦 Inventory Status</h3>
          <router-link to="/summary/inventory" class="card-link">Details →</router-link>
        </div>
        <div class="status-list">
          <div class="status-item" v-for="s in inventoryStatus" :key="s.label">
            <div class="status-top">
              <span class="status-label">{{ s.label }}</span>
              <span class="status-val">{{ s.value }}</span>
            </div>
            <div class="status-bar-bg">
              <div class="status-bar-fill" :style="{ width: s.pct + '%', background: s.color }"></div>
            </div>
          </div>
        </div>
      </div>

      <div class="dash-card">
        <div class="card-header">
          <h3 class="card-title">🕐 Recent Activity</h3>
          <router-link to="/summary/scanning" class="card-link">View All →</router-link>
        </div>
        <ul class="activity-list">
          <li class="activity-item" v-for="item in recentActivity" :key="item.id">
            <span class="activity-dot" :class="item.type"></span>
            <div class="activity-info">
              <span class="activity-text">{{ item.text }}</span>
              <span class="activity-time">{{ item.time }}</span>
            </div>
          </li>
        </ul>
      </div>

      <div class="dash-card">
        <div class="card-header">
          <h3 class="card-title">⚡ Quick Actions</h3>
        </div>
        <div class="shortcuts-grid">
          <router-link v-for="sc in shortcuts" :key="sc.label" :to="sc.to" class="shortcut-item">
            <span class="shortcut-icon">{{ sc.icon }}</span>
            <span class="shortcut-label">{{ sc.label }}</span>
          </router-link>
        </div>
      </div>

    </div>
  </div>
</template>

<script>
export default {
  name: 'DashboardSummary',
  data() {
    return {
      stats: [
        { icon: '📦', label: 'Total Inventory', value: '—', trend: 0 },
        { icon: '📱', label: 'Scans Today',      value: '—', trend: 0 },
        { icon: '⚠️', label: 'Pending Process',  value: '—', trend: 0 },
        { icon: '✅', label: 'Completed Today',  value: '—', trend: 0 },
      ],
      rejectItemsPcs: [
        { label: 'Black Chipping',    value: '—', color: '#9f7aea' },
        { label: 'Chipping',          value: '—', color: '#48bb78' },
        { label: 'Crack',             value: '—', color: '#f6ad55' },
        { label: 'Dim Out (T)',       value: '—', color: '#2b6cb0' },
        { label: 'Dimension Out (W)', value: '—', color: '#e53e3e' },
        { label: 'Kurokawa',          value: '—', color: '#718096' },
        { label: 'Loss (std)',        value: '—', color: '#38a169' },
        { label: 'Others',            value: '—', color: '#d69e2e' },
      ],
      rejectItemsKg: [
        { label: 'Black Chipping',    value: '—', color: '#9f7aea' },
        { label: 'Chipping',          value: '—', color: '#48bb78' },
        { label: 'Crack',             value: '—', color: '#f6ad55' },
        { label: 'Dim Out (T)',       value: '—', color: '#2b6cb0' },
        { label: 'Dimension Out (W)', value: '—', color: '#e53e3e' },
        { label: 'Kurokawa',          value: '—', color: '#718096' },
        { label: 'Loss (std)',        value: '—', color: '#38a169' },
        { label: 'Others',            value: '—', color: '#d69e2e' },
      ],
      recentActivity: [
        { id: 1, type: 'scan',    text: 'Single scan completed — Lot #A1023', time: 'Just now'   },
        { id: 2, type: 'process', text: 'Process updated — Line B',           time: '5 min ago'  },
        { id: 3, type: 'alert',   text: 'Wrong process flagged — Lot #B0891', time: '12 min ago' },
        { id: 4, type: 'scan',    text: 'Multiple scan — 14 items',           time: '20 min ago' },
        { id: 5, type: 'process', text: 'Endorsed to inspection — Lot #C003', time: '1 hr ago'   },
      ],
      inventoryStatus: [
        { label: 'In Process', value: '—', pct: 0, color: '#2b82cb' },
        { label: 'Received',   value: '—', pct: 0, color: '#38a169' },
        { label: 'Pending',    value: '—', pct: 0, color: '#d69e2e' },
        { label: 'Rejected',   value: '—', pct: 0, color: '#e53e3e' },
      ],
      shortcuts: [
        { icon: '📱', label: 'Single Scan',    to: '/scan/single'         },
        { icon: '📊', label: 'Multiple Scan',  to: '/scan/multiple'       },
        { icon: '🗺️', label: 'Route Scan',     to: '/scan/route'          },
        { icon: '📋', label: 'Inventory',      to: '/summary/inventory'   },
        { icon: '📤', label: 'Upload Files',   to: '/options/upload'      },
        { icon: '🖨️', label: 'Print Endorse',  to: '/summary/endorsement' },
        { icon: '🔓', label: 'Unlock Process', to: '/options/unlock'      },
        { icon: '⏱️', label: 'Process Time',   to: '/process-time'        },
      ]
    }
  }
}
</script>

<style scoped>
/* @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap'); */

.dashboard-wrapper {
  background: #0d1b2a;
  min-height: calc(100vh - 220px);
  padding: 1.5rem 2rem;
  max-width: 1400px;
  margin: 0 auto;
  font-family: 'Inter', sans-serif;
}

.mb { margin-bottom: 1rem; }

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
  margin-bottom: 1rem;
  animation: fadeUp 0.5s ease both;
}

.stat-card {
  background: #112240;
  border: 1px solid rgba(255,255,255,0.08);
  border-radius: 8px;
  padding: 1.1rem 1.25rem;
  display: flex;
  align-items: center;
  gap: 1rem;
  transition: transform 0.2s, box-shadow 0.2s;
}

.stat-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 8px 24px rgba(0,0,0,0.3);
}

.stat-icon {
    font-size: 1.75rem; flex-shrink: 0;
}
.stat-body {
    flex: 1;
}
.stat-value {
    font-size: 1.5rem; font-weight: 600; color: #e8f0f7; line-height: 1;
}
.stat-label {
    font-size: 0.75rem; color: #8899aa; margin-top: 0.2rem; text-transform: uppercase; letter-spacing: 0.08em;
}
.stat-badge {
    font-size: 0.7rem; font-weight: 600; padding: 0.2rem 0.5rem; border-radius: 4px; white-space: nowrap;
}
.stat-badge.up   {
    background: rgba(56,161,105,0.15); color: #68d391;
}
.stat-badge.down {
    background: rgba(229,62,62,0.15);  color: #fc8181;
}
.dash-card {
  background: #112240;
  border: 1px solid rgba(255,255,255,0.08);
  border-radius: 8px;
  padding: 1.25rem;
  animation: fadeUp 0.5s ease 0.1s both;
}
.full-width-card {
    width: 100%;
}

.card-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 1rem;
  padding-bottom: 0.75rem;
  border-bottom: 1px solid rgba(255,255,255,0.06);
}

.card-title {
    font-size: 0.875rem; font-weight: 600; color: #e8f0f7; text-transform: uppercase; letter-spacing: 0.08em; margin: 0;
}
.card-link {
    font-size: 0.75rem; color: #2b82cb; text-decoration: none; transition: color 0.2s;
}
.card-link:hover {
    color: #5ba3e0;
}
.card-date {
    font-size: 0.75rem; color: #8899aa;
}

.table-placeholder {
    overflow-x: auto;
}

.preview-table {
    width: 100%; border-collapse: collapse; font-size: 0.8rem;
}

.preview-table th {
  text-align: left;
  padding: 0.6rem 0.75rem;
  color: #5ba3e0;
  font-weight: 600;
  font-size: 0.75rem;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  border-bottom: 1px solid rgba(43,130,203,0.2);
  white-space: nowrap;
}

.preview-table td {
    padding: 0.65rem 0.75rem; border-bottom: 1px solid rgba(255,255,255,0.04);
}
.preview-table tr:hover td {
    background: rgba(43,130,203,0.05);
}

.skel {
  height: 10px;
  border-radius: 4px;
  background: linear-gradient(90deg, rgba(255,255,255,0.06) 25%, rgba(255,255,255,0.1) 50%, rgba(255,255,255,0.06) 75%);
  background-size: 200% 100%;
  animation: shimmer 1.5s infinite;
  min-width: 80px;
}
.skel.short {
    min-width: 40px; max-width: 60px;
}

@keyframes shimmer {
  0%   { background-position: 200% 0; }
  100% { background-position: -200% 0; }
}

.table-footer {
    margin-top: 1rem; padding-top: 0.75rem; border-top: 1px solid rgba(255,255,255,0.06);
}
.footer-info {
    display: flex; flex-wrap: wrap; gap: 1.5rem;
}
.footer-item {
    font-size: 0.8rem; color: #8899aa;
}
.footer-item strong {
    color: #e8f0f7;
}

/* Charts */
.charts-row {
    display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;
}

.chart-area {
  position: relative;
  height: 140px;
  background: rgba(0,0,0,0.2);
  border-radius: 6px;
  border: 1px solid rgba(255,255,255,0.05);
  display: flex;
  align-items: flex-end;
  padding: 0.75rem 0.75rem 0.5rem 2.25rem;
  margin-bottom: 1rem;
  overflow: hidden;
}

.chart-label-y {
  position: absolute;
  left: 0.2rem;
  top: 50%;
  transform: translateY(-50%) rotate(-90deg);
  font-size: 0.62rem;
  color: #8899aa;
  white-space: nowrap;
}

.chart-mock-bars {
    display: flex; align-items: flex-end; gap: 3px; width: 100%; height: 100%;
}

.mock-bar {
  flex: 1;
  border-radius: 2px 2px 0 0;
  min-height: 4px;
}
.mock-bar.pcs {
    background: linear-gradient(to top, #2b82cb, #5ba3e0); opacity: 0.7;
}
.mock-bar.kg  {
    background: linear-gradient(to top, #e53e3e, #fc8181); opacity: 0.7;
}

.reject-list {
    display: flex; flex-direction: column; gap: 0.4rem; max-height: 160px; overflow-y: auto;
}
.reject-list::-webkit-scrollbar {
    width: 3px;
}
.reject-list::-webkit-scrollbar-thumb {
    background: #2b82cb; border-radius: 2px;
}

.reject-item {
    display: flex; align-items: center; gap: 0.5rem; font-size: 0.78rem;
}
.reject-dot {
    width: 8px; height: 8px; border-radius: 2px; flex-shrink: 0;
}
.reject-label {
    flex: 1; color: #8899aa;
}
.reject-val {
    color: #e8f0f7; font-weight: 500; white-space: nowrap;
}

.dashboard-grid {
    display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem;
}

.status-list {
    display: flex; flex-direction: column; gap: 1rem;
}
.status-top {
    display: flex; justify-content: space-between; margin-bottom: 0.35rem;
}
.status-label {
    font-size: 0.8rem; color: #8899aa;
}
.status-val {
    font-size: 0.8rem; font-weight: 600; color: #e8f0f7;
}
.status-bar-bg {
    height: 6px; background: rgba(255,255,255,0.06); border-radius: 3px; overflow: hidden;
}
.status-bar-fill {
    height: 100%; border-radius: 3px; transition: width 1s ease;
}

.activity-list {
    list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: 0.75rem;
}
.activity-item {
    display: flex; align-items: flex-start; gap: 0.75rem;
}
.activity-dot {
    width: 8px; height: 8px; border-radius: 50%; margin-top: 4px; flex-shrink: 0;
}
.activity-dot.scan    {
    background: #2b82cb;
}
.activity-dot.process {
    background: #38a169;
}
.activity-dot.alert   {
    background: #e53e3e;
}
.activity-info {
    display: flex; flex-direction: column; gap: 0.1rem;
}
.activity-text {
    font-size: 0.825rem; color: #c8d8e8;
}
.activity-time {
    font-size: 0.7rem; color: #8899aa;
}

.shortcuts-grid {
    display: grid; grid-template-columns: repeat(2, 1fr); gap: 0.5rem;
}

.shortcut-item {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.6rem 0.75rem;
  background: rgba(43,130,203,0.06);
  border: 1px solid rgba(43,130,203,0.15);
  border-radius: 6px;
  color: #c8d8e8;
  text-decoration: none;
  font-size: 0.8rem;
  font-weight: 500;
  transition: all 0.2s ease;
}

.shortcut-item:hover {
    background: rgba(43,130,203,0.18); border-color: #2b82cb; color: white; transform: translateY(-2px);
}
.shortcut-icon {
    font-size: 1rem;
}

.info-icon {
  position: relative;
  display: inline-flex;
  align-items: center;
  cursor: pointer;
  margin-left: 0.35rem;
  font-size: 0.8rem;
}

.tooltip {
  visibility: hidden;
  opacity: 0;
  position: absolute;
  bottom: 130%;
  left: 50%;
  transform: translateX(-50%);
  background: #1e3a5f;
  color: #e8f0f7;
  font-size: 0.75rem;
  padding: 0.5rem 0.75rem;
  border-radius: 6px;
  border: 1px solid rgba(43, 130, 203, 0.3);
  white-space: nowrap;
  box-shadow: 0 4px 12px rgba(0,0,0,0.4);
  transition: opacity 0.2s ease, visibility 0.2s ease;
  z-index: 10;
  pointer-events: none;
}

.tooltip::after {
  content: '';
  position: absolute;
  top: 100%;
  left: 50%;
  transform: translateX(-50%);
  border: 5px solid transparent;
  border-top-color: #1e3a5f;
}

.info-icon:hover .tooltip {
  visibility: visible;
  opacity: 1;
}

@keyframes fadeUp {
  from { opacity: 0; transform: translateY(16px); }
  to   { opacity: 1; transform: translateY(0); }
}

@media (max-width: 1024px) {
  .charts-row     { grid-template-columns: 1fr; }
  .dashboard-grid { grid-template-columns: 1fr 1fr; }
}

@media (max-width: 640px) {
  .dashboard-wrapper { padding: 1rem; }
  .dashboard-grid    { grid-template-columns: 1fr; }
  .shortcuts-grid    { grid-template-columns: repeat(2, 1fr); }
}
</style>
