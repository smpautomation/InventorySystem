<template>
  <div>
    <Navbar />
    <main>
      <slot />
    </main>
  </div>
</template>

<script>
import Navbar from '@/components/NavBarOld.vue'

export default {
  components: { Navbar }
}
</script>
