<template>
  <nav class="navbar-alt1">
    <div class="nav-container">
      <div class="nav-left">
        <a href="/" class="brand">
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
            placeholder="Search for Lot Number or Work Order ID"
            class="search-input-alt"
            @keyup.enter="handleSearch"
          />
          <kbd class="search-kbd">Enter</kbd>
        </div>
      </div>

      <div class="nav-right">
        <Link href="/scan/single" class="quick-scan-btn" title="Single Scan">
            <span>📱</span>
            <span>Single Scan</span>
        </Link>
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
          <Link href="/" class="menu-item-alt">
            <span class="item-icon">🏠</span>
            <span>Home</span>
          </Link>
          <Link href="/ProcessTime" class="menu-item-alt">
            <span class="item-icon">⏱️</span>
            <span>Process Time</span>
          </Link>
        </div>

        <div class="menu-section">
          <h3 class="section-title">Scanning</h3>
          <Link href="/scan/single" class="menu-item-alt">
            <span class="item-icon">📱</span>
            <span>Single Scan</span>
          </Link>
          <Link href="/scan/multiple" class="menu-item-alt">
            <span class="item-icon">📱</span>
            <span>Multiple Scan</span>
          </Link>
          <Link href="/scan/route" class="menu-item-alt">
            <span class="item-icon">🗺️</span>
            <span>Route Scan</span>
          </Link>
        </div>

        <div class="menu-section">
          <h3 class="section-title">Summary Reports</h3>
          <Link href="/summary/inventory" class="menu-item-alt">Inventory Summary</Link>
          <Link href="/summary/nextprocess" class="menu-item-alt">Next Process Inventory</Link>
          <Link href="/summary/received" class="menu-item-alt">Received Summary</Link>
          <Link href="/summary/process" class="menu-item-alt">Process Summary</Link>
          <Link href="/summary/split" class="menu-item-alt">Split Summary</Link>
          <a href="#" class="show-more" @click.prevent="toggleSummaryExpand">
            {{ summaryExpanded ? 'Show Less' : `+${8} More Reports` }}
          </a>
          <template v-if="summaryExpanded">
            <Link href="/summary/temporary" class="menu-item-alt">Temporary Item Search</Link>
            <Link href="/summary/endorse" class="menu-item-alt">Endorsed</Link>
            <Link href="/summary/manualcheck" class="menu-item-alt">Manual Check</Link>
            <Link href="/summary/reject" class="menu-item-alt">Reject Lot Summary</Link>
            <Link href="/summary/machinetarget" class="menu-item-alt">Machine Target</Link>
            <Link href="/summary/firstprocess" class="menu-item-alt">First Process Output</Link>
            <Link href="/summary/scanning" class="menu-item-alt">Scanning Summary</Link>
            <Link href="/summary/rawmaterials" class="menu-item-alt">RM Blocks Traceability</Link>
          </template>
        </div>

        <div class="menu-section">
          <h3 class="section-title">Options & Settings</h3>
          <Link href="/options/register" class="menu-item-alt">Register Temporary Item</Link>
          <Link href="/options/createbarcode" class="menu-item-alt">Create QR/Bar Code</Link>
          <Link href="/options/upload" class="menu-item-alt">Upload Files</Link>
          <Link href="/options/remarks" class="menu-item-alt">Hourly Remarks</Link>
          <a href="#" class="show-more" @click.prevent="toggleOptionsExpand">
            {{ optionsExpanded ? 'Show Less' : `+${9} More Options` }}
          </a>
          <template v-if="optionsExpanded">
            <Link href="/options/endorsement" class="menu-item-alt">Print Endorsement Sheet</Link>
            <Link href="/options/processtemplate" class="menu-item-alt">Edit Inventory Process Template</Link>
            <Link href="/options/processlist" class="menu-item-alt">Edit Process List</Link>
            <Link href="/options/qareceived" class="menu-item-alt">Check QA Received</Link>
            <Link href="/options/unlock" class="menu-item-alt">Unlock Wrong Process</Link>
            <Link href="/options/wptrend" class="menu-item-alt">Wrong Process Trend</Link>
            <Link href="/options/weightfirstprocess" class="menu-item-alt">Weight & First Process</Link>
            <Link href="/options/proto" class="menu-item-alt">Generate Prototype Traveler</Link>
            <Link href="/options/keepingtime" class="menu-item-alt">Update Magnet Keeping Time</Link>
          </template>
        </div>
      </div>
    </div>
  </nav>
</template>

<script>
import { Link } from '@inertiajs/vue3'

export default {
  components: { Link },
  name: 'Navbar',
  data() {
    return {
      isMenuOpen: false,
      ipData: null,
      loading: true,
      searchQuery: '',
      summaryExpanded: false,
      optionsExpanded: false
    }
  },
  mounted() {
    this.fetchIpLocation()
  },
  methods: {
    handleSearch() {
      if (!this.searchQuery.trim()) return
      this.$router.push({ path: '/search', query: { q: this.searchQuery } })
      this.searchQuery = ''
    },
    toggleMenu() {
      this.isMenuOpen = !this.isMenuOpen
    },
    toggleSummaryExpand() {
      this.summaryExpanded = !this.summaryExpanded
    },
    toggleOptionsExpand() {
      this.optionsExpanded = !this.optionsExpanded
    },
    async fetchIpLocation() {
      try {
        const response = await fetch('/api/ip-details')
        if (!response.ok) throw new Error('Failed to fetch IP data')
        const data = await response.json()
        this.ipData = data
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
  background: #0d1b2a;
  border-bottom: 1px solid rgba(255, 255, 255, 0.08);
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
  position: sticky;
  top: 0;
  z-index: 1000;
}

.nav-container {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1rem 2rem;
  max-width: 1400px;
  margin: 0 auto;
}

.nav-left {
  flex-shrink: 0;
}

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
}

@media (min-width: 1024px) {
  .brand-text {
    display: block;
  }
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
  background: #112240;
  border: 1px solid rgba(43, 130, 203, 0.25);
  border-radius: 4px;
  padding: 0.5rem 1.25rem;
  box-shadow: none;
  transition: all 0.3s ease;
}

.search-wrapper:focus-within {
  box-shadow: 0 0 0 2px #2b82cb;
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
  color: #e8f0f7;
}

.search-input-alt::placeholder {
  color: #8899aa;
}

.search-kbd {
  background: #0d1b2a;
  border: 1px solid rgba(255, 255, 255, 0.08);
  color: #8899aa;
  border-radius: 4px;
  padding: 0.15rem 0.5rem;
  font-size: 0.75rem;
  font-family: monospace;
}

.nav-right {
  display: flex;
  align-items: center;
  gap: 1.5rem;
}

.ip-card {
  background: #112240;
  border: 1px solid rgba(255, 255, 255, 0.08);
  backdrop-filter: none;
  border-radius: 12px;
  padding: 0.75rem 1.25rem;
  color: white;
  min-width: 220px;
}

.spinner {
  width: 20px;
  height: 20px;
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-top-color: white;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.ip-row {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  margin-bottom: 0.5rem;
}

.ip-row:last-child {
  margin-bottom: 0;
}

.badge {
  padding: 0.25rem 0.5rem;
  border-radius: 6px;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
}

.badge-blue {
  background: rgba(43, 130, 203, 0.8);
}

.badge-green {
  background: rgba(43, 130, 203, 0.5);
}

.ip-text {
  font-size: 0.875rem;
  font-weight: 500;
}

.menu-toggle {
  background: #112240;
  border: 1px solid rgba(43, 130, 203, 0.3);
  border-radius: 8px;
  padding: 0.5rem 0.75rem;
  cursor: pointer;
  display: flex;
  flex-direction: column;
  gap: 4px;
  transition: all 0.3s ease;
}

.menu-toggle:hover {
  background: rgba(43, 130, 203, 0.2);
}

.hamburger-line {
  width: 24px;
  height: 2px;
  background: white;
  transition: all 0.3s ease;
  border-radius: 2px;
}

.hamburger-line.active:nth-child(1) {
  transform: rotate(45deg) translateY(8px);
}

.hamburger-line.active:nth-child(2) {
  opacity: 0;
}

.hamburger-line.active:nth-child(3) {
  transform: rotate(-45deg) translateY(-8px);
}

.mega-menu {
  background: #112240;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.4);
  max-height: 0;
  overflow: hidden;
  transition: max-height 0.4s ease;
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
  color: #5ba3e0;
  border-bottom: 2px solid rgba(43, 130, 203, 0.2);
  margin-bottom: 0.75rem;
  padding-bottom: 0.5rem;
}

.menu-item-alt {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem;
  color: #8899aa;
  text-decoration: none;
  border-radius: 8px;
  transition: all 0.2s ease;
  font-size: 0.925rem;
}

.menu-item-alt:hover {
  background: rgba(43, 130, 203, 0.15);
  color: #5ba3e0;
  transform: translateX(5px);
}

.item-icon {
  font-size: 1.25rem;
  min-width: 24px;
}

.show-more {
  color: #2b82cb;
  font-size: 0.875rem;
  font-weight: 600;
  padding: 0.5rem 0.75rem;
  cursor: pointer;
  text-decoration: none;
  display: inline-block;
  transition: color 0.2s;
}

.show-more:hover {
  color: #5ba3e0;
}

@media (max-width: 1024px) {
  .nav-center {
    display: none;
  }

  .ip-card {
    display: none;
  }

  .menu-grid {
    grid-template-columns: 1fr;
  }

  .quick-scan-btn {
    display: none;
  }
}

.quick-scan-btn {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  background: rgba(43, 130, 203, 0.15);
  border: 1px solid rgba(43, 130, 203, 0.4);
  border-radius: 8px;
  padding: 0.5rem 1rem;
  color: white;
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
</style>
