<template>
  <div class="single-page">

    <!-- Scan Bar -->
    <div class="scan-bar">
      <label class="scan-label">Scan Barcode</label>
      <div class="scan-input-group">
        <input type="text" v-model="barcode" class="scan-input" placeholder="Scan or type barcode..." @keyup.enter="handleScan" autofocus />
        <button class="scan-btn" @click="handleScan">Go</button>
      </div>
    </div>

    <div class="page-body">

      <!-- LEFT COLUMN -->
      <div class="left-col">

        <!-- Process / Location Banner -->
        <template v-if="!hasQaProcess">
            <div class="banner-row">
                <div class="banner process-banner">
                <span class="banner-label">PROCESS</span>
                <span class="banner-value">{{ lotData.process || '—' }}</span>
                </div>
                <div class="banner location-banner">
                <span class="banner-label">LOCATION</span>
                <span class="banner-value">{{ lotData.location || '—' }}</span>
                </div>
            </div>

            <div class="next-process-card">
                <div class="np-label">NEXT PROCESS</div>
                <div class="np-value">{{ lotData.nextProcess || '—' }}</div>
            </div>
        </template>

        <!-- Process Steps -->
        <div class="process-steps">
          <div
            v-for="step in processSteps"
            :key="step.name"
            class="step-chip"
            :class="{
              'step-active': step.active,
              'step-current': step.current,
              'step-in': step.inProgress
            }"
          >
            <span class="step-name">{{ step.name }}</span>
            <span v-if="step.inProgress" class="step-in-badge">IN</span>
          </div>
        </div>

        <template v-if="hasQaProcess">
            <div class="process-flow-divider">
                <span class="flow-divider-label">QA Process Flow</span>
            </div>

            <!-- QA Current / Next Process banners -->
            <div class="banner-row">
                <div class="banner process-banner">
                <span class="banner-label">CURRENT PROCESS</span>
                <span class="banner-value">{{ lotData.qaCurrentProcess || '—' }}</span>
                </div>
                <div class="banner location-banner">
                <span class="banner-label">NEXT PROCESS</span>
                <span class="banner-value">{{ lotData.qaNextProcess || '—' }}</span>
                </div>
            </div>

            <div class="process-steps">
                <div
                v-for="step in processSteps2"
                :key="step.name"
                class="step-chip"
                :class="{
                    'step-active':  step.active,
                    'step-current': step.current,
                    'step-in':      step.inProgress
                }"
                >
                <span class="step-name">{{ step.name }}</span>
                <span v-if="step.inProgress" class="step-in-badge">IN</span>
                </div>
            </div>
        </template>

        <div class="lot-row">

        <!-- Lot Info Card -->
        <div class="info-card lot-info-card">
          <div class="info-grid">
            <div class="info-row">
              <span class="info-label">Work Order ID</span>
              <span class="info-val">{{ lotData.workOrderId || '—' }}</span>
            </div>
            <div class="info-row">
              <span class="info-label">FIFO No.</span>
              <span class="info-val">{{ lotData.fifoNo || '—' }}</span>
            </div>
            <div class="info-row">
              <span class="info-label">Area</span>
              <span class="info-val">{{ lotData.area || '—' }}</span>
            </div>
            <div class="info-row-vertial">
              <span class="info-label">Model Name</span>
              <div  class="model-name">{{ lotData.modelName || '—' }}</div>
            </div>
            <div class="info-divider"></div>
            <div class="info-row">
              <span class="info-label">Initial Quantity</span>
              <span class="info-val">{{ lotData.initialQty || '—' }}</span>
            </div>
            <div class="info-row">
              <span class="info-label">Received Qty</span>
              <span class="info-val">{{ lotData.receivedQty || '—' }}</span>
            </div>
            <div class="info-row">
              <span class="info-label">Date Received</span>
              <span class="info-val">{{ lotData.dateReceived || '—' }}</span>
            </div>
            <div class="info-row">
              <span class="info-label">Shift Date</span>
              <span class="info-val">{{ lotData.shiftDate || '—' }}</span>
            </div>
            <div class="info-row">
              <span class="info-label">Received By</span>
              <span class="info-val">{{ lotData.receivedBy || '—' }}</span>
            </div>
            <div class="info-row">
              <span class="info-label">Daily Check File</span>
              <span class="info-val">{{ lotData.dailyCheckFile || '—' }}</span>
            </div>
          </div>

          <div class="remarks-row">
            <label class="info-label">Remarks</label>
            <div class="remarks-input-group">
              <input type="text" v-model="remarks" class="remarks-input" placeholder="Enter remarks..." />
              <button class="update-btn">Update</button>
            </div>
          </div>

          <div class="location-row">
            <span class="info-label">Location</span>
            <span class="location-tag">{{ lotData.location || '—' }}</span>
          </div>

          <button class="print-btn">🖨️ Print Barcode Label (SATO)</button>
        </div>

        <!-- Lot No + Quantity Card -->
        <div class="info-card lot-card">
          <div class="lot-no-label">Lot No.</div>
          <div class="lot-no-value">{{ lotData.lotNo || '—' }}</div>
          <div class="lot-meta-row">
            <div class="lot-meta-item">
              <span class="info-label">Latest BCS Qty</span>
              <span class="info-val accent">{{ lotData.bcsQty || '—' }}</span>
            </div>
            <div class="lot-meta-item">
              <span class="info-label">Running Quantity</span>
              <span class="running-qty">{{ lotData.runningQty || '—' }}</span>
            </div>
          </div>
          <div class="lot-meta-row">
            <div class="lot-meta-item">
              <span class="info-label">Split Type</span>
              <span class="info-val">{{ lotData.splitType || '—' }}</span>
            </div>
            <div class="lot-meta-item">
              <span class="info-label">Split Remarks</span>
              <span class="info-val">{{ lotData.splitRemarks || '—' }}</span>
            </div>
          </div>
          <div class="select-row">
            <label class="info-label">Category</label>
            <select class="styled-select">
              <option value="">Select category...</option>
            </select>
          </div>
          <div class="select-row">
            <select class="styled-select">
              <option value="">Select option...</option>
            </select>
          </div>
        </div>

        </div>

        <!-- Process History -->
        <div class="info-card">
          <div class="section-title">Process History</div>
          <div class="table-wrap">
            <table class="data-table">
              <thead>
                <tr>
                  <th>Date</th>
                  <th>Shift Date</th>
                  <th>Area</th>
                  <th>Process</th>
                  <th>Quantity</th>
                  <th>Scanned By</th>
                </tr>
              </thead>
              <tbody>
                <tr v-if="processHistory.length === 0">
                  <td colspan="6" class="empty-row">No data available</td>
                </tr>
                <tr v-for="row in processHistory" :key="row.id">
                  <td>{{ row.date }}</td>
                  <td>{{ row.shiftDate }}</td>
                  <td>{{ row.area }}</td>
                  <td>{{ row.process }}</td>
                  <td>
                    <div class="qty-input-group">
                      <input type="number" :value="row.quantity" class="qty-input" />
                      <button class="update-btn-sm">Update</button>
                    </div>
                  </td>
                  <td>{{ row.scannedBy }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Location History -->
        <div class="info-card">
          <div class="section-title">Location History</div>
          <div class="table-wrap">
            <table class="data-table">
              <thead>
                <tr>
                  <th>Date</th>
                  <th>Location</th>
                  <th>Scanned By</th>
                </tr>
              </thead>
              <tbody>
                <tr v-if="locationHistory.length === 0">
                  <td colspan="3" class="empty-row">No data available</td>
                </tr>
                <tr v-for="row in locationHistory" :key="row.id">
                  <td>{{ row.date }}</td>
                  <td>{{ row.location }}</td>
                  <td>{{ row.scannedBy }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

      </div>

      <!-- RIGHT COLUMN -->
      <div class="right-col">

        <!-- Reject Encoding Form -->
        <div class="info-card reject-card">
          <div class="reject-title">REJECT ENCODING FORM</div>
          <div class="reject-form">
            <div class="form-row">
              <span class="form-label">Name</span>
              <span class="form-val">{{ lotData.encoderName || '—' }}</span>
            </div>
            <div class="form-row">
              <span class="form-label">Model Name</span>
              <span class="form-val">{{ lotData.modelName || '—' }}</span>
            </div>
            <div class="form-row">
              <span class="form-label">Process</span>
              <span class="form-val">{{ lotData.process || '—' }}</span>
            </div>
            <div class="form-row">
              <span class="form-label">Location</span>
              <span class="form-val">{{ lotData.location || '—' }}</span>
            </div>
            <div class="form-row form-row-vertical">
              <label class="form-label">Description</label>
              <select class="styled-select">
                <option value="">Select description...</option>
              </select>
            </div>
            <div class="form-row form-row-vertical">
              <label class="form-label">Remarks</label>
              <input type="text" class="remarks-input" placeholder="Enter remarks..." />
            </div>
            <div class="form-row form-row-vertical">
              <label class="form-label">Quantity</label>
              <input type="number" class="remarks-input" placeholder="0" min="0" />
            </div>
            <button class="submit-btn">SUBMIT</button>
          </div>

          <!-- Final Quantity -->
          <div class="final-qty-row">
            <span class="final-qty-label">Final Quantity (Initial Qty - Sum of Rejects)</span>
            <span class="final-qty-value">{{ lotData.finalQty || '—' }}</span>
          </div>

          <!-- Reject Table -->
          <div class="table-wrap mt">
            <table class="data-table">
              <thead>
                <tr>
                  <th>Process</th>
                  <th>Location</th>
                  <th>Reject Code</th>
                  <th>Description</th>
                  <th>Remarks</th>
                  <th>Qty</th>
                </tr>
              </thead>
              <tbody>
                <tr v-if="rejectList.length === 0">
                  <td colspan="6" class="empty-row">No rejects recorded</td>
                </tr>
                <tr v-for="row in rejectList" :key="row.id">
                  <td>{{ row.process }}</td>
                  <td>{{ row.location }}</td>
                  <td>{{ row.rejectCode }}</td>
                  <td>{{ row.description }}</td>
                  <td>{{ row.remarks }}</td>
                  <td class="qty-cell">{{ row.qty }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Process Output List -->
        <div class="info-card">
          <div class="section-title">Process Output List</div>
          <div class="table-wrap">
            <table class="data-table">
              <thead>
                <tr>
                  <th>Date</th>
                  <th>Shift Date</th>
                  <th>Area</th>
                  <th>Process</th>
                  <th>Quantity</th>
                  <th>Location</th>
                  <th>Encoder</th>
                </tr>
              </thead>
              <tbody>
                <tr v-if="processOutputList.length === 0">
                  <td colspan="7" class="empty-row">No data available</td>
                </tr>
                <tr v-for="row in processOutputList" :key="row.id">
                  <td>{{ row.date }}</td>
                  <td>{{ row.shiftDate }}</td>
                  <td>{{ row.area }}</td>
                  <td>{{ row.process }}</td>
                  <td>
                    <div class="qty-input-group">
                      <input type="number" :value="row.quantity" class="qty-input" />
                      <button class="update-btn-sm">Update</button>
                    </div>
                  </td>
                  <td>{{ row.location }}</td>
                  <td>{{ row.encoder }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

      </div>
    </div>
  </div>
</template>

<script>
import AppLayout from '@/Layouts/AppLayout.vue'

export default {
  name: 'Single',
  layout: AppLayout,
  data() {
    return {
      barcode: '',
      remarks: '',
      lotData: {
        process: '',
        location: '',
        nextProcess: '',
        workOrderId: '',
        fifoNo: '',
        area: '',
        modelName: 'TTM0869D N49SH-DF',
        initialQty: '',
        receivedQty: '',
        dateReceived: '',
        shiftDate: '',
        receivedBy: '',
        dailyCheckFile: '',
        lotNo: '1000-01',
        bcsQty: '',
        runningQty: '',
        splitType: '',
        splitRemarks: '',
        encoderName: '',
        finalQty: '',
      },
      processSteps: [
        { name: 'BATCH PREPARATION', active: true,  current: false, inProgress: false },
        { name: 'PRETREATMENT DRYING', active: true,  current: false, inProgress: false },
        { name: 'ARRANGEMENT SIDE A', active: true,  current: false, inProgress: false },
        { name: 'PREHEAT-SIDE A COAT-DRY', active: true,  current: false, inProgress: false },
        { name: 'SIDE A BAKING', active: true,  current: false, inProgress: false },
        { name: 'ARRANGEMENT SIDE B', active: true,  current: false,  inProgress: false },
        { name: 'PREHEAT-SIDE B COAT-DRY', active: true, current: true, inProgress: true },
        { name: 'SIDE B BAKING', active: false, current: false, inProgress: false },
        { name: 'PREPACKING', active: false, current: false, inProgress: false },
        { name: 'ENDORSE TO INSPECTION', active: false, current: false, inProgress: false },
      ],
      processHistory: [],
      locationHistory: [],
      rejectList: [],
      processOutputList: [],
      processSteps2: [
        { name: 'MS MACHINE', active: true,  current: false, inProgress: false },
        { name: 'FOR CONFIRM', active: true,  current: false, inProgress: false },
        { name: 'FINAL VISUAL', active: true,  current: false, inProgress: false },
        { name: 'MARKING', active: true,  current: false, inProgress: false },
        { name: 'SAMPLING', active: true,  current: false, inProgress: false },
        { name: 'PRE-PACKING', active: true,  current: false,  inProgress: false },
        ],
      hasQaProcess: true,
    }
  },
  methods: {
    handleScan() {
      if (!this.barcode.trim()) return
      // fetch lot data from API using this.barcode
      console.log('Scanning:', this.barcode)
      this.barcode = ''
    }
  }
}
</script>

<style scoped>
/* ── Page Base ── */
.single-page {
  background: #0d1b2a;
  min-height: 100vh;
  font-family: 'Inter', sans-serif;
  color: #e8f0f7;
  padding-bottom: 2rem;
}

/* ── Scan Bar ── */
.scan-bar {
  background: #112240;
  border-bottom: 1px solid rgba(255,255,255,0.08);
  padding: 0.875rem 2rem;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 1rem;
}

.scan-label {
  font-size: 0.85rem;
  font-weight: 600;
  color: #8899aa;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  white-space: nowrap;
}

.scan-input-group {
  display: flex;
  gap: 0.5rem;
}

.scan-input {
  width: 320px;
  padding: 0.45rem 0.85rem;
  background: #0d1b2a;
  border: 1px solid rgba(43,130,203,0.35);
  border-radius: 4px;
  color: #e8f0f7;
  font-size: 0.9rem;
  font-family: 'JetBrains Mono', monospace;
  outline: none;
  transition: border-color 0.2s;
}

.scan-input:focus { border-color: #2b82cb; }
.scan-input::placeholder { color: #4a6080; }

.scan-btn {
  padding: 0.45rem 1.25rem;
  background: #2b82cb;
  border: none;
  border-radius: 4px;
  color: white;
  font-size: 0.875rem;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.2s;
}
.scan-btn:hover { background: #3a95e0; }

/* ── Page Body ── */
.page-body {
  display: grid;
  grid-template-columns: 3fr 1fr;
  gap: 1.25rem;
  padding: 1.25rem 2rem;
  max-width: 1400px;
  margin: 0 auto;
  animation: fadeUp 0.5s ease both;
}

@keyframes fadeUp {
  from { opacity: 0; transform: translateY(12px); }
  to   { opacity: 1; transform: translateY(0); }
}

.left-col, .right-col {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

/* ── Banners ── */
.banner-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 0.75rem;
}

.banner {
  border-radius: 6px;
  padding: 0.75rem 1rem;
  display: flex;
  flex-direction: column;
  gap: 0.2rem;
}

.process-banner {
  background: rgba(229,62,62,0.12);
  border: 1px solid rgba(229,62,62,0.3);
}

.location-banner {
  background: rgba(43,130,203,0.1);
  border: 1px solid rgba(43,130,203,0.3);
}

.banner-label {
  font-size: 0.65rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.12em;
  color: #8899aa;
}

.banner-value {
  font-size: 1rem;
  font-weight: 700;
  color: #e8f0f7;
}

.process-banner .banner-value { color: #fc8181; }
.location-banner .banner-value { color: #5ba3e0; }

/* ── Next Process ── */
.next-process-card {
  background: rgba(43,130,203,0.1);
  border: 1px solid rgba(43,130,203,0.3);
  border-radius: 6px;
  padding: 1rem;
  text-align: center;
}

.np-label {
  font-size: 0.7rem;
  font-weight: 600;
  letter-spacing: 0.15em;
  text-transform: uppercase;
  color: #8899aa;
  margin-bottom: 0.35rem;
}

.np-value {
  font-size: 1.5rem;
  font-weight: 700;
  color: #5ba3e0;
  letter-spacing: 0.05em;
}

/* ── Process Steps ── */
.process-steps {
  display: flex;
  flex-wrap: wrap;
  gap: 0.4rem;
}

.step-chip {
  display: flex;
  align-items: center;
  gap: 0.3rem;
  padding: 0.4rem 0.75rem;
  border-radius: 4px;
  font-size: 0.75rem;
  font-weight: 600;
  background: rgba(255,255,255,0.05);
  border: 1px solid rgba(255,255,255,0.08);
  color: #8899aa;
}

.step-chip.step-active {
  background: rgba(56,161,105,0.15);
  border-color: rgba(56,161,105,0.4);
  color: #68d391;
}

.step-chip.step-current {
  background: rgba(43,130,203,0.2);
  border-color: #2b82cb;
  color: #5ba3e0;
}

.step-chip.step-in {
  background: rgba(214,158,46,0.12);
  border-color: rgba(214,158,46,0.35);
  color: #f6e05e;
}

.step-in-badge {
  background: #d69e2e;
  color: #0d1b2a;
  font-size: 0.6rem;
  font-weight: 800;
  padding: 0.1rem 0.3rem;
  border-radius: 2px;
}

/* ── Info Card ── */
.info-card {
  background: #112240;
  border: 1px solid rgba(255,255,255,0.08);
  border-radius: 8px;
  padding: 1.25rem;
}

.info-grid {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  margin-bottom: 1rem;
}

.info-row {
  display: flex;
  justify-content: space-between;
  align-items: baseline;
  gap: 1rem;
}

.info-label {
  font-size: 0.75rem;
  color: #8899aa;
  white-space: nowrap;
  flex-shrink: 0;
}

.info-val {
  font-size: 0.825rem;
  color: #c8d8e8;
  font-weight: 500;
  text-align: right;
}

.info-val.accent { color: #5ba3e0; font-weight: 600; }

.model-name {
  font-family: 'JetBrains Mono', monospace;
  font-size: 1.5rem;
  font-weight: 700;
  color: #5ba3e0;
  letter-spacing: 0.02em;
  margin: 0.25rem 0 0.75rem;
}

.info-divider {
  height: 1px;
  background: rgba(255,255,255,0.06);
  margin: 0.25rem 0;
}

.remarks-row {
  display: flex;
  flex-direction: column;
  gap: 0.4rem;
  margin-bottom: 0.75rem;
}

.remarks-input-group {
  display: flex;
  gap: 0.5rem;
}

.remarks-input {
  flex: 1;
  padding: 0.4rem 0.75rem;
  background: #0d1b2a;
  border: 1px solid rgba(255,255,255,0.1);
  border-radius: 4px;
  color: #e8f0f7;
  font-size: 0.825rem;
  outline: none;
  transition: border-color 0.2s;
}
.remarks-input:focus { border-color: #2b82cb; }

.update-btn {
  padding: 0.4rem 0.85rem;
  background: rgba(43,130,203,0.2);
  border: 1px solid rgba(43,130,203,0.4);
  border-radius: 4px;
  color: #5ba3e0;
  font-size: 0.775rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
  white-space: nowrap;
}
.update-btn:hover { background: rgba(43,130,203,0.35); }

.location-row {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  margin-bottom: 0.75rem;
}

.location-tag {
  background: rgba(43,130,203,0.15);
  border: 1px solid rgba(43,130,203,0.3);
  border-radius: 4px;
  padding: 0.2rem 0.6rem;
  font-size: 0.8rem;
  font-weight: 600;
  color: #5ba3e0;
}

.print-btn {
  width: 100%;
  padding: 0.6rem;
  background: rgba(255,255,255,0.04);
  border: 1px solid rgba(255,255,255,0.12);
  border-radius: 4px;
  color: #8899aa;
  font-size: 0.825rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
}
.print-btn:hover { background: rgba(255,255,255,0.08); color: #e8f0f7; }



.lot-no-label {
  font-size: 0.7rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.12em;
  color: #8899aa;
  margin-bottom: 0.25rem;
}

.lot-no-value {
  font-family: 'JetBrains Mono', monospace;
  font-size: 1.75rem;
  font-weight: 700;
  color: #5ba3e0;
  margin-bottom: 1rem;
  letter-spacing: 0.02em;
}

.running-qty {
  font-family: 'JetBrains Mono', monospace;
  font-size: 1.25rem;
  font-weight: 700;
  color: #e8f0f7;
}

.lot-meta-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
  margin-bottom: 0.75rem;
}

.lot-meta-item {
  display: flex;
  flex-direction: column;
  gap: 0.2rem;
}

.select-row {
  display: flex;
  flex-direction: column;
  gap: 0.35rem;
  margin-bottom: 0.75rem;
}

.styled-select {
  padding: 0.4rem 0.75rem;
  background: #0d1b2a;
  border: 1px solid rgba(255,255,255,0.1);
  border-radius: 4px;
  color: #8899aa;
  font-size: 0.825rem;
  outline: none;
  cursor: pointer;
  transition: border-color 0.2s;
}
.styled-select:focus { border-color: #2b82cb; }

/* ── Section Title ── */
.section-title {
  font-size: 0.8rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.1em;
  color: #5ba3e0;
  margin-bottom: 0.875rem;
  padding-bottom: 0.5rem;
  border-bottom: 1px solid rgba(43,130,203,0.2);
}

/* ── Tables ── */
.table-wrap { overflow-x: auto; }
.mt { margin-top: 1rem; }

.data-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.775rem;
}

.data-table th {
  text-align: left;
  padding: 0.5rem 0.6rem;
  color: #5ba3e0;
  font-weight: 600;
  font-size: 0.7rem;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  border-bottom: 1px solid rgba(43,130,203,0.2);
  white-space: nowrap;
}

.data-table td {
  padding: 0.55rem 0.6rem;
  border-bottom: 1px solid rgba(255,255,255,0.04);
  color: #c8d8e8;
  vertical-align: middle;
}

.data-table tr:hover td { background: rgba(43,130,203,0.05); }

.empty-row {
  text-align: center;
  color: #4a6080;
  padding: 1.5rem !important;
  font-style: italic;
}

.qty-input-group { display: flex; gap: 0.35rem; align-items: center; }

.qty-input {
  width: 60px;
  padding: 0.3rem 0.4rem;
  background: #0d1b2a;
  border: 1px solid rgba(255,255,255,0.1);
  border-radius: 3px;
  color: #e8f0f7;
  font-size: 0.775rem;
  text-align: center;
  outline: none;
}

.update-btn-sm {
  padding: 0.3rem 0.5rem;
  background: rgba(43,130,203,0.2);
  border: 1px solid rgba(43,130,203,0.35);
  border-radius: 3px;
  color: #5ba3e0;
  font-size: 0.7rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
  white-space: nowrap;
}
.update-btn-sm:hover { background: rgba(43,130,203,0.35); }

.qty-cell { font-weight: 700; color: #fc8181; }



.reject-title {
  font-size: 0.875rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.1em;
  color: #fc8181;
  margin-bottom: 1rem;
  padding-bottom: 0.5rem;
  border-bottom: 1px solid rgba(229,62,62,0.2);
}

.reject-form {
  display: flex;
  flex-direction: column;
  gap: 0.6rem;
  margin-bottom: 1rem;
}

.form-row {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.form-row-vertical {
  flex-direction: column;
  align-items: flex-start;
  gap: 0.3rem;
}

.form-label {
  font-size: 0.75rem;
  color: #8899aa;
  min-width: 90px;
  flex-shrink: 0;
}

.form-val {
  font-size: 0.825rem;
  color: #c8d8e8;
  font-weight: 500;
}

.submit-btn {
  margin-top: 0.5rem;
  padding: 0.6rem 1.5rem;
  background: #2b82cb;
  border: none;
  border-radius: 4px;
  color: white;
  font-size: 0.875rem;
  font-weight: 700;
  cursor: pointer;
  transition: background 0.2s;
  letter-spacing: 0.05em;
  align-self: flex-start;
}
.submit-btn:hover { background: #3a95e0; }

.final-qty-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  background: rgba(56,161,105,0.1);
  border: 1px solid rgba(56,161,105,0.25);
  border-radius: 6px;
  padding: 0.65rem 1rem;
  margin-top: 0.5rem;
}

.final-qty-label {
  font-size: 0.775rem;
  color: #8899aa;
}

.final-qty-value {
  font-family: 'JetBrains Mono', monospace;
  font-size: 1.1rem;
  font-weight: 700;
  color: #68d391;
}

.lot-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
}

.lot-info-card {
  min-width: 0; /* prevents overflow */
}

.info-row-vertical {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
  margin-bottom: 0.25rem;
}

.process-flow-divider {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  margin: 0.25rem 0;
}

.process-flow-divider::before,
.process-flow-divider::after {
  content: '';
  flex: 1;
  height: 1px;
  background: rgba(255,255,255,0.08);
}

.flow-divider-label {
  font-size: 0.7rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.12em;
  color: #8899aa;
  white-space: nowrap;
}

/* ── Responsive ── */
@media (max-width: 1024px) {
  .page-body { grid-template-columns: 1fr; }
}

@media (max-width: 640px) {
  .scan-input { width: 200px; }
  .page-body  { padding: 1rem; }
  .banner-row { grid-template-columns: 1fr; }
}
</style>
