<template>
  <div>
    <Navbar />
    <main>
      <slot />
    </main>
  </div>
</template>

<script>
import Navbar from '@/Components/Navbar.vue'

export default {
  components: { Navbar }
}
</script>
