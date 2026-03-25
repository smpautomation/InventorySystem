<template>
    <PlantOutputChart
      title="Output by Plant 8 2nd"
      eyebrow="Plant 8 2nd Monthly Output"
      plant="plant8-2nd"
      :raw-data="rawData"
      :targets="targets"
      :all-months="allMonths"
      :daily-targets="dailyTargets"
      @targets-updated="reloadTargets"
      @daily-targets-updated="reloadDailyTargets"
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
      const { fetchYear, fetchTargets, fetchDailyTargets, invalidate, invalidateTargets, invalidateDailyTargets, loading, error } = usePlantOutput()

      const rawData      = ref({})
      const targets      = ref({})
      const allMonths    = ref({})
      const dailyTargets = ref({})

      let refreshInterval = null

      const fetchData = async () => {
        const now   = new Date()
        const year  = now.getFullYear()
        const month = now.getMonth() + 1

        const [results, fetchedTargets, fetchedDailyTargets] = await Promise.all([
          fetchYear('plant8-2nd', year),
          fetchTargets('plant8-2nd', year),
          fetchDailyTargets('plant8-2nd', year, month),
        ])

        targets.value      = fetchedTargets
        dailyTargets.value = fetchedDailyTargets

        const monthsData = {}
        results.forEach((data, i) => {
          if (data?.data) monthsData[i + 1] = { ...data.data }
        })
        allMonths.value = monthsData

        if (monthsData[month]) {
          rawData.value = { MAIN: monthsData[month] }
        }
      }

      const reloadTargets = async () => {
        const year = new Date().getFullYear()
        invalidateTargets('plant8-2nd', year)
        targets.value = await fetchTargets('plant8-2nd', year)
      }

      const reloadDailyTargets = async () => {
        const now   = new Date()
        const year  = now.getFullYear()
        const month = now.getMonth() + 1
        invalidateDailyTargets('plant8-2nd', year, month)
        dailyTargets.value = await fetchDailyTargets('plant8-2nd', year, month)
      }

      onMounted(async () => {
        await fetchData()
        refreshInterval = setInterval(fetchData, 600000)
      })

      onUnmounted(() => {
        if (refreshInterval) clearInterval(refreshInterval)
      })

      return { rawData, targets, allMonths, dailyTargets, loading, error, reloadTargets, reloadDailyTargets }
    }
  }
  </script>
