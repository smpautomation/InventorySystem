<template>
    <div class="page">
      <div class="page-header">
        <div class="eyebrow">WIP Inventory</div>
        <h2 class="title">Plant 8 2nd <span class="title-unit">Daily WIP</span></h2>
      </div>
      <InventoryGraph plant="plant8-2nd" />
    </div>
  </template>

  <script>
  import NavBarOld      from '@/components/NavBarOld.vue'
  import InventoryGraph from '@/components/Inventory/InventoryGraph.vue'
  import AppLayout      from '@/Layouts/AppLayout.vue'

  export default {
    layout:     AppLayout,
    name:       'Inventoryplant8-2nd',
    components: { NavBarOld, InventoryGraph },
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
  @media (max-width: 768px) {
    .page { padding: 1rem; }
  }
  </style>
