<template>
  <PlantOutputChart
    title="Output by Plant 8 2nd"
    eyebrow="Plant 8 2nd Monthly Output"
    plant="plant8-2nd"
    :raw-data="rawData"
    :targets="targets"
    :all-months="allMonths"
    @targets-updated="reloadTargets"
  />
</template>

<script>
import { ref, onMounted, onUnmounted } from 'vue'
import PlantOutputChart from '@/components/PlantOutputChart/PlantOutputChart.vue'
import { usePlantOutput } from '@/composables/usePlantOutput'

export default {
  name: 'Plant 8 2nd',
  components: { PlantOutputChart },
  setup() {
    const { fetchYear, fetchTargets, invalidate, invalidateTargets, loading, error } = usePlantOutput()
    const rawData   = ref({})
    const targets   = ref({})
    const allMonths = ref({})
    let refreshInterval = null

    const fetchData = async () => {
        const now    = new Date()
        const year   = now.getFullYear()
        const [results, fetchedTargets] = await Promise.all([
            fetchYear('plant8-2nd', year),
            fetchTargets('plant8-2nd', year),
        ])
        targets.value = fetchedTargets
        const monthsData = {}
        results.forEach((data, i) => {
            if (data?.data) monthsData[i + 1] = { ...data.data }
        })
        allMonths.value = monthsData
        const currentMonth = now.getMonth() + 1
        if (monthsData[currentMonth]) {
            rawData.value = { 'Plant8-2nd': monthsData[currentMonth] }
        }
    }

    const reloadTargets = async () => {
        const year    = new Date().getFullYear()
        invalidateTargets('plant8-2nd', year)
        targets.value = await fetchTargets('plant8-2nd', year)
    }

    onMounted(async () => {
        await fetchData()
        refreshInterval = setInterval(fetchData, 600000)
    })

    onUnmounted(() => {
        if (refreshInterval) clearInterval(refreshInterval)
    })

    return { rawData, targets, allMonths, loading, error, reloadTargets }
  }
}
</script>
