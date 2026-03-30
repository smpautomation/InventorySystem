<template>
    <nav class="navbar-alt1">
      <div class="nav-container">
        <div class="nav-left">
          <a href="http://172.17.2.235/inventory" class="brand">
            <img src="/images/is2.png" alt="Logo" class="logo" title="Inventory System"/>
            <span class="brand-text">Inventory System</span>
          </a>
        </div>

        <div class="nav-center">
          <div class="search-wrapper">
            <span class="search-icon">🔍</span>
            <input
              v-model="searchQuery"
              type="text"
              name="keyword2"
              id="keyword2"
              placeholder="Search for Lot Number or Work Order ID"
              class="search-input-alt"
              @keyup.enter="handleSearch"
            />
            <kbd class="search-kbd">Enter</kbd>
          </div>
        </div>

        <div class="nav-right">
          <a href="http://172.17.2.235/inventory/single_scan.php" class="quick-scan-btn" title="Single Scan">
              <span>📱</span>
              <span>Single Scan</span>
          </a>
          <div class="ip-card">
            <div v-if="loading" class="spinner"></div>
            <template v-else-if="ipData">
              <div class="ip-row">
                <span class="badge badge-blue">IP</span>
                <span class="ip-text">{{ ipData.ip }}</span>
              </div>
              <div class="ip-row">
                <span class="badge badge-green">📍</span>
                <span class="ip-text">{{ ipData.location }}</span>
              </div>
            </template>
          </div>

          <!-- ── Theme Toggle ── -->
          <button
            class="theme-toggle"
            @click="toggleTheme"
            :title="theme === 'dark' ? 'Switch to Light Mode' : 'Switch to Dark Mode'"
            :aria-label="theme === 'dark' ? 'Switch to Light Mode' : 'Switch to Dark Mode'"
          >
            <span class="theme-icon">
              <span v-if="theme === 'dark'" class="icon-sun">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <circle cx="12" cy="12" r="5"/>
                  <line x1="12" y1="1" x2="12" y2="3"/>
                  <line x1="12" y1="21" x2="12" y2="23"/>
                  <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/>
                  <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/>
                  <line x1="1" y1="12" x2="3" y2="12"/>
                  <line x1="21" y1="12" x2="23" y2="12"/>
                  <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/>
                  <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>
                </svg>
              </span>
              <span v-else class="icon-moon">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
                </svg>
              </span>
            </span>
            <span class="theme-label">{{ theme === 'dark' ? 'Light' : 'Dark' }}</span>
          </button>

          <button class="menu-toggle" @click="toggleMenu">
            <span class="hamburger-line" :class="{ active: isMenuOpen }"></span>
            <span class="hamburger-line" :class="{ active: isMenuOpen }"></span>
            <span class="hamburger-line" :class="{ active: isMenuOpen }"></span>
          </button>
        </div>
      </div>

      <div class="mega-menu" :class="{ 'is-active': isMenuOpen }">
        <div class="menu-grid">
          <div class="menu-section">
            <h3 class="section-title">Quick Actions</h3>
            <a href="http://172.17.2.235/inventory/" class="menu-item-alt">
              <span class="item-icon">🏠</span>
              <span>Home</span>
            </a>
            <a href="http://172.17.2.235/inventory/process_time_summary.php" class="menu-item-alt">
              <span class="item-icon">⏱️</span>
              <span>Process Time</span>
            </a>
          </div>

          <div class="menu-section">
            <h3 class="section-title">Scanning</h3>
            <a href="http://172.17.2.235/inventory/single_scan.php" class="menu-item-alt">
              <span class="item-icon">📱</span>
              <span>Single Scan</span>
            </a>
            <a href="http://172.17.2.235/inventory/multiple_scan.php" class="menu-item-alt">
              <span class="item-icon">📱</span>
              <span>Multiple Scan</span>
            </a>
            <a href="http://172.17.2.235/inventory/single_scan_route.php" class="menu-item-alt">
              <span class="item-icon">🗺️</span>
              <span>Route Scan</span>
            </a>
          </div>

          <div class="menu-section">
            <h3 class="section-title">Summary Reports</h3>
            <div class="submenu-wrapper">
              <button class="menu-item-alt submenu-toggle" @click="toggleInventoryReports">
                <span class="item-icon">⚙️</span>
                <span>Inventory Reports</span>
                <span class="submenu-arrow" :class="{ 'is-open': inventoryReportsOpen }">▶</span>
              </button>
              <div class="submenu" :class="{ 'is-open': inventoryReportsOpen }">
                  <a href="http://172.17.2.235/inventory/summary_model.php" class="submenu-item">Inventory All Area</a>
                  <a href="http://172.17.2.235/inventory/summary_inventory.php" class="submenu-item">Inventory Per Area</a>
                  <a href="http://172.17.2.235/inventory/summary_inventory_template.php" class="submenu-item">Inventory Process Template Per Area</a>
                  <a href="http://172.17.2.235/inventory/summary_inventory_next_process.php" class="submenu-item">Next Process Inventory</a>
              </div>
            </div>

            <div class="submenu-wrapper">
              <button class="menu-item-alt submenu-toggle" @click="toggleProcessReports">
                <span class="item-icon">⚙️</span>
                <span>Process Reports</span>
                <span class="submenu-arrow" :class="{ 'is-open': processReportsOpen }">▶</span>
              </button>
              <div class="submenu" :class="{ 'is-open': processReportsOpen }">
                <a href="http://172.17.2.235/inventory/summary_process.php" class="submenu-item">Process Output</a>
                <a href="http://172.17.2.235/inventory/summary_process_weight.php" class="submenu-item">Process Output Weight</a>
                <a href="http://172.17.2.235/inventory/summary_process_trace_all.php" class="submenu-item">Process Trace All Model</a>
                <a href="http://172.17.2.235/inventory/summary_process_print.php" class="submenu-item">Print Endorsement Sheet</a>
                <a href="http://172.17.2.235/inventory/summary_first_process.php" class="submenu-item">First Process Output</a>
              </div>
            </div>

            <div class="submenu-wrapper">
              <button class="menu-item-alt submenu-toggle" @click="toggleTonsReports">
                <span class="item-icon">⚙️</span>
                <span>Output Per Tons</span>
                <span class="submenu-arrow" :class="{ 'is-open': tonsReportsOpen }">▶</span>
              </button>
              <div class="submenu" :class="{ 'is-open': tonsReportsOpen }">
                <Link href="/summary/tons/main" class="submenu-item">MAIN</Link>
                <Link href="/summary/tons/plant7" class="submenu-item">Plant 7</Link>
                <Link href="/summary/tons/plant8-1st" class="submenu-item">Plant 8 1st</Link>
                <Link href="/summary/tons/plant8-2nd" class="submenu-item">Plant 8 2nd</Link>
              </div>
            </div>

            <div class="submenu-wrapper">
              <button class="menu-item-alt submenu-toggle" @click="togglePiecesReports">
                <span class="item-icon">⚙️</span>
                <span>Output Per Pieces</span>
                <span class="submenu-arrow" :class="{ 'is-open': piecesReportsOpen }">▶</span>
              </button>
              <div class="submenu" :class="{ 'is-open': piecesReportsOpen }">
                <Link href="/summary/pieces/ncp2" class="submenu-item">QA NCP2</Link>
                <Link href="/summary/pieces/ncp3" class="submenu-item">QA NCP3</Link>
                <Link href="/summary/pieces/ncp8" class="submenu-item">QA NCP8</Link>
                <Link href="/summary/pieces/inorganic" class="submenu-item">QA Inorganic</Link>
                <Link href="/summary/pieces/epoxy" class="submenu-item">QA Epoxy</Link>
                <Link href="/summary/pieces/chiptype" class="submenu-item">QA Chiptype</Link>
                <Link href="/summary/pieces/p10epoxy" class="submenu-item">QA P10 Epoxy</Link>
                <Link href="/summary/pieces/vcm" class="submenu-item">QA VCM</Link>
              </div>
            </div>

            <a href="http://172.17.2.235/inventory/summary_received.php" class="menu-item-alt">Received</a>
            <a href="http://172.17.2.235/inventory/summary_received_total.php" class="menu-item-alt">Received Total</a>

            <a href="#" class="show-more" @click.prevent="toggleSummaryExpand">
              {{ summaryExpanded ? 'Show Less' : `+${9} More Reports` }}
            </a>
            <template v-if="summaryExpanded">
              <a href="http://172.17.2.235/inventory/summary_split" class="menu-item-alt">Split Summary</a>
              <a href="http://172.17.2.235/inventory/summary_warehouse.php" class="menu-item-alt">Temporary Item Search</a>
              <a href="http://172.17.2.235/inventory/washing_out.php" class="menu-item-alt">Washing Inventory For Endorsed To Inspection</a>
              <a href="http://172.17.2.235/inventory/manual_check.php" class="menu-item-alt">Manual Check No Received Data</a>
              <a href="http://172.17.2.235/inventory/reject_summary.php" class="menu-item-alt">Reject Lot Summary</a>
              <a href="http://172.17.2.235/inventory/machine_target.php" class="menu-item-alt">Machine Target</a>
              <a href="http://172.17.2.235/inventory/scanning_summary.php" class="menu-item-alt">Scanning Summary In->Out</a>
              <a href="http://172.17.2.235/inventory/scanning_summary_obverse.php" class="menu-item-alt">Scanning Summary Out->In</a>
              <a href="http://172.17.2.235/inventory/summary_blocks_traceability.php" class="menu-item-alt">RM Blocks Traceability</a>
            </template>
          </div>

          <div class="menu-section">
            <h3 class="section-title">Options & Settings</h3>
            <a href="http://172.17.2.235/inventory/warehouse.php" class="menu-item-alt">Register Temporary Item</a>
            <a href="http://172.17.2.235/inventory/ID_barcode.php" class="menu-item-alt">Create ID BarCode</a>
            <a href="http://172.17.2.235/inventory/process_barcode.php" class="menu-item-alt">Create Process Barcode</a>
            <a href="http://172.17.2.235/inventory/upload.php" class="menu-item-alt">Upload Daily Check File</a>
            <a href="http://172.17.2.235/inventory/upload_split.php" class="menu-item-alt">Upload Split Enquiry File</a>
            <a href="http://172.17.2.235/inventory/hourly_remarks.php" class="menu-item-alt">Hourly Remarks</a>
            <a href="#" class="show-more" @click.prevent="toggleOptionsExpand">
              {{ optionsExpanded ? 'Show Less' : `+${10} More Options` }}
            </a>
            <template v-if="optionsExpanded">
              <a href="http://172.17.2.235/inventory/edit_inventory_process.php" class="menu-item-alt">Edit Inventory Process Template</a>
              <a href="http://172.17.2.235/inventory/edit_process.php" class="menu-item-alt">Edit Process List</a>
              <a href="http://172.17.2.235/inventory/location_barcode.php" class="menu-item-alt">Create Location Barcode</a>
              <a href="http://172.17.2.235/inventory/check_qa_received_no_washing.php" class="menu-item-alt">Check QA Received No Washing</a>
              <a href="http://172.17.2.235/inventory/qr.php" class="menu-item-alt">Print QR Code</a>
              <a href="http://172.17.2.235/inventory/unlock.php" class="menu-item-alt">Unlock Wrong Process</a>
              <a href="http://172.17.2.235/inventory/wp_trend.php" class="menu-item-alt">Wrong Process Trend</a>
              <a href="http://172.17.2.235/inventory/weight_fp_encoder.php" class="menu-item-alt">Weight & First Process</a>
              <a href="http://172.17.2.235/inventory/generate_proto.php" class="menu-item-alt">Generate Prototype Traveler</a>
              <a href="http://172.17.2.235/inventory/keeping_time.php" class="menu-item-alt">Update Magnet Keeping Time</a>
            </template>
          </div>
        </div>
      </div>
    </nav>
  </template>

  <script>
  import { Link } from '@inertiajs/vue3'
  import { useTheme } from '@/composables/useTheme'

  export default {
    components: { Link },
    name: 'Navbar',
    setup() {
      const { theme, toggleTheme } = useTheme()
      return { theme, toggleTheme }
    },
    data() {
      return {
        isMenuOpen: false,
        ipData: null,
        loading: true,
        searchQuery: '',
        summaryExpanded: false,
        optionsExpanded: false,
        processReportsOpen: false,
        inventoryReportsOpen: false,
        tonsReportsOpen: false,
        piecesReportsOpen: false,
      }
    },
    mounted() {
      this.fetchIpLocation()
    },
    methods: {
      handleSearch() {
        if (!this.searchQuery.trim()) return
        const form = document.createElement('form')
        form.method = 'POST'
        form.action = 'http://172.17.2.235/inventory/search_lot.php'
        const input = document.createElement('input')
        input.type = 'hidden'
        input.name = 'search'
        input.value = this.searchQuery
        form.appendChild(input)
        const input2 = document.createElement('input')
        input2.type = 'hidden'
        input2.name = 'keyword2'
        input2.value = this.searchQuery
        form.appendChild(input2)
        document.body.appendChild(form)
        form.submit()
        this.searchQuery = ''
      },
      toggleMenu()            { this.isMenuOpen           = !this.isMenuOpen },
      toggleSummaryExpand()   { this.summaryExpanded       = !this.summaryExpanded },
      toggleOptionsExpand()   { this.optionsExpanded       = !this.optionsExpanded },
      toggleProcessReports()  { this.processReportsOpen    = !this.processReportsOpen },
      toggleInventoryReports(){ this.inventoryReportsOpen  = !this.inventoryReportsOpen },
      toggleTonsReports()     { this.tonsReportsOpen       = !this.tonsReportsOpen },
      togglePiecesReports()   { this.piecesReportsOpen     = !this.piecesReportsOpen },
      async fetchIpLocation() {
        try {
          const response = await fetch('/api/ip-details')
          if (!response.ok) throw new Error('Failed to fetch IP data')
          this.ipData  = await response.json()
          this.loading = false
        } catch (error) {
          console.error('Error fetching IP location:', error)
          this.loading = false
        }
      }
    }
  }
  </script>

<style scoped>
.navbar-alt1 {
    background: var(--navbar-bg);
    border-bottom: 1px solid var(--navbar-border);
    box-shadow: 0 4px 20px var(--shadow);
    position: sticky;
    top: 0;
    z-index: 1000;
    transition: background 0.3s ease, border-color 0.3s ease;
}

.nav-container {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1rem 2rem;
    max-width: 1400px;
    margin: 0 auto;
}

.nav-left { flex-shrink: 0; }

.brand {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    text-decoration: none;
    color: white;
}

.logo {
    height: 45px;
    width: auto;
    filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.2));
}

.brand-text {
    font-size: 1.25rem;
    font-weight: 700;
    display: none;
    color: #ffffff;
}

@media (min-width: 1024px) {
    .brand-text { display: block; }
}

.nav-center {
    flex: 1;
    max-width: 600px;
    margin: 0 2rem;
}

.search-wrapper {
    position: relative;
    display: flex;
    align-items: center;
    background: var(--input-bg);
    border: 1px solid var(--border-accent);
    border-radius: 4px;
    padding: 0.5rem 1.25rem;
    transition: all 0.3s ease;
}

.search-wrapper:focus-within {
    box-shadow: 0 0 0 2px var(--accent);
    transform: translateY(-2px);
}

.search-icon {
    font-size: 1.1rem;
    margin-right: 0.75rem;
    opacity: 0.6;
}

.search-input-alt {
    flex: 1;
    border: none;
    outline: none;
    background: transparent;
    font-size: 0.95rem;
    color: var(--input-text);
}

.search-input-alt::placeholder { color: var(--text-muted); }

.search-kbd {
    background: var(--bg-primary);
    border: 1px solid var(--border-primary);
    color: var(--text-muted);
    border-radius: 4px;
    padding: 0.15rem 0.5rem;
    font-size: 0.75rem;
    font-family: monospace;
}

.nav-right {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.ip-card {
    background: var(--bg-secondary);
    border: 1px solid var(--border-primary);
    border-radius: 12px;
    padding: 0.75rem 1.25rem;
    color: var(--text-primary);
    min-width: 220px;
    transition: background 0.3s ease;
}

.spinner {
    width: 20px;
    height: 20px;
    border: 2px solid rgba(255, 255, 255, 0.3);
    border-top-color: white;
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
}

@keyframes spin { to { transform: rotate(360deg); } }

.ip-row {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 0.5rem;
}
.ip-row:last-child { margin-bottom: 0; }

.badge {
    padding: 0.25rem 0.5rem;
    border-radius: 6px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
}
.badge-blue  { background: rgba(43, 130, 203, 0.8); color: white; }
.badge-green { background: rgba(43, 130, 203, 0.5); color: white; }
.ip-text { font-size: 0.875rem; font-weight: 500; color: var(--text-primary); }

/* ── Theme Toggle ── */
.theme-toggle {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    background: var(--bg-secondary);
    border: 1px solid var(--border-accent);
    border-radius: 8px;
    padding: 0.45rem 0.85rem;
    color: var(--text-primary);
    cursor: pointer;
    font-size: 0.8rem;
    font-weight: 600;
    transition: all 0.25s ease;
    white-space: nowrap;
}
.theme-toggle:hover {
    background: var(--accent-hover);
    border-color: var(--accent);
    color: var(--text-accent);
    transform: translateY(-1px);
}
.theme-icon {
    display: flex;
    align-items: center;
    color: var(--text-accent);
    transition: transform 0.4s ease;
}
.theme-toggle:hover .theme-icon { transform: rotate(20deg); }
.theme-label { color: var(--text-secondary); }

/* ── Menu Toggle ── */
.menu-toggle {
    background: var(--bg-secondary);
    border: 1px solid var(--border-accent);
    border-radius: 8px;
    padding: 0.5rem 0.75rem;
    cursor: pointer;
    display: flex;
    flex-direction: column;
    gap: 4px;
    transition: all 0.3s ease;
}
.menu-toggle:hover { background: var(--accent-hover); }

.hamburger-line {
    width: 24px;
    height: 2px;
    background: var(--text-primary);
    transition: all 0.3s ease;
    border-radius: 2px;
}
.hamburger-line.active:nth-child(1) { transform: rotate(45deg)  translateY(8px); }
.hamburger-line.active:nth-child(2) { opacity: 0; }
.hamburger-line.active:nth-child(3) { transform: rotate(-45deg) translateY(-8px); }

/* ── Mega Menu ── */
.mega-menu {
    background: var(--bg-secondary);
    box-shadow: 0 10px 40px var(--shadow);
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.4s ease, background 0.3s ease;
    border-top: 1px solid var(--border-primary);
}
.mega-menu.is-active {
    max-height: 800px;
    overflow-y: auto;
}

.menu-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 2rem;
    padding: 2rem;
    max-width: 1400px;
    margin: 0 auto;
}

.menu-section {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.section-title {
    font-size: 0.875rem;
    font-weight: 700;
    text-transform: uppercase;
    color: var(--text-accent);
    border-bottom: 2px solid var(--border-accent);
    margin-bottom: 0.75rem;
    padding-bottom: 0.5rem;
}

.menu-item-alt {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem;
    color: var(--text-muted);
    text-decoration: none;
    border-radius: 8px;
    transition: all 0.2s ease;
    font-size: 0.925rem;
    background: none;
    border: none;
}
.menu-item-alt:hover {
    background: var(--accent-hover);
    color: var(--text-accent);
    transform: translateX(5px);
}

.item-icon { font-size: 1.25rem; min-width: 24px; }

.show-more {
    color: var(--accent);
    font-size: 0.875rem;
    font-weight: 600;
    padding: 0.5rem 0.75rem;
    cursor: pointer;
    text-decoration: none;
    display: inline-block;
    transition: color 0.2s;
}
.show-more:hover { color: var(--text-accent); }

.submenu-wrapper { display: flex; flex-direction: column; }

.submenu-toggle {
    width: 100%;
    background: transparent;
    border: none;
    text-align: left;
    cursor: pointer;
    justify-content: space-between;
}

.submenu-arrow {
    font-size: 0.75rem;
    transition: transform 0.3s ease;
    margin-left: auto;
    color: var(--text-muted);
}
.submenu-arrow.is-open { transform: rotate(90deg); }

.submenu {
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.3s ease;
    padding-left: 2rem;
}
.submenu.is-open { max-height: 500px; }

.submenu-item {
    display: block;
    padding: 0.5rem 0.75rem;
    color: var(--text-muted);
    text-decoration: none;
    border-radius: 6px;
    transition: all 0.2s ease;
    font-size: 0.875rem;
    margin: 0.25rem 0;
}
.submenu-item:hover {
    background: var(--accent-hover);
    color: var(--text-accent);
    transform: translateX(3px);
}

.quick-scan-btn {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    background: var(--bg-primary);
    border: 1px solid var(--border-accent);
    border-radius: 8px;
    padding: 0.5rem 1rem;
    color: var(--text-primary);
    text-decoration: none;
    font-size: 0.875rem;
    font-weight: 600;
    transition: all 0.2s ease;
    white-space: nowrap;
}
.quick-scan-btn:hover {
    background: rgba(43, 130, 203, 0.3);
    box-shadow: 0 4px 12px rgba(43, 130, 203, 0.2);
    transform: translateY(-2px);
}

@media (max-width: 1024px) {
    .nav-center, .ip-card, .quick-scan-btn { display: none; }
    .menu-grid { grid-template-columns: 1fr; }
}
</style>
