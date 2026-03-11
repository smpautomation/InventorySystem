<script>
import AppLayout from '@/Layouts/AppLayout.vue'
import WelcomeHeader from '../components/WelcomeHeader.vue'
import DashboardSummary from '../components/DashboardSummary.vue'

export default {
  layout: AppLayout,
  components: { WelcomeHeader, DashboardSummary }
}

</script>
<template>
  <div style="background: #0d1b2a; min-height: 100vh;">
    <WelcomeHeader />
    <DashboardSummary />
    <main>
      <router-view />
    </main>
  </div>
</template>
