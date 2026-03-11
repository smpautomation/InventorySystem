<template>
  <PlantOutputChart
    title="Output by QA P10 EPOXY"
    eyebrow="QA P10 EPOXY Monthly Output"
    plant="p10epoxy"
    :raw-data="rawData"
    :targets="targets"
    :all-months="allMonths"
    @targets-updated="reloadTargets"
  />
</template>

<script>
import { ref, onMounted, onUnmounted } from 'vue'
import PlantOutputChart from '@/components/PlantOutputChartPieces/PlantOutputChart.vue'
import { usePlantOutput } from '@/composables/usePlantOutput'

export default {
  name: 'QA P10 EPOXY',
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
            fetchYear('p10epoxy', year),
            fetchTargets('p10epoxy', year),
        ])
        targets.value = fetchedTargets
        const monthsData = {}
        results.forEach((data, i) => {
            if (data?.data) monthsData[i + 1] = { ...data.data }
        })
        allMonths.value = monthsData
        const currentMonth = now.getMonth() + 1
        if (monthsData[currentMonth]) {
            rawData.value = { 'QA P10 EPOXY': monthsData[currentMonth] }
        }
    }

    const reloadTargets = async () => {
        const year    = new Date().getFullYear()
        invalidateTargets('p10epoxy', year)
        targets.value = await fetchTargets('p10epoxy', year)
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
