<template>
  <div>
    <Navbar />
    <main>
      <slot />
    </main>
  </div>
</template>

<script>
import Navbar from '@/components/NavBar.vue'

export default {
  components: { Navbar }
}
</script>
