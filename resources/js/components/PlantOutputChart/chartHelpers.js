export const AREA_COLORS = [
    '#2b82cb', '#38a169', '#d69e2e', '#e53e3e', '#9f7aea',
    '#38b2ac', '#ed8936', '#667eea', '#fc8181', '#68d391',
  ]

  const MONTH_NAMES = [
    'January','February','March','April','May','June',
    'July','August','September','October','November','December'
  ]

  export function buildChartData(rawData, today, dayLabels, targets, currentMonthName, dateRange = {}, allMonths = {}, dailyTargets = {}) {
      if (!rawData || Object.keys(rawData).length === 0) {
          return { labels: [], datasets: [] }
      }

      const now          = new Date()
      const currentYear  = now.getFullYear()
      const currentMonth = now.getMonth() + 1


      const fromDate = dateRange?.from
          ? new Date(dateRange.from)
          : new Date(currentYear, currentMonth - 1, 1)

      const toDate = dateRange?.to
          ? new Date(dateRange.to)
          : new Date(currentYear, currentMonth - 1, today)

      const labels  = []
      const areaMap = {}


      const avgDailyTargetData    = []
      const inputDailyTargetData  = []

      const cursor = new Date(fromDate)
      cursor.setHours(0, 0, 0, 0)

      const end = new Date(toDate)
      end.setHours(23, 59, 59, 999)

      while (cursor <= end) {
          const year  = cursor.getFullYear()
          const month = cursor.getMonth() + 1
          const day   = cursor.getDate()

          labels.push(`${MONTH_NAMES[month - 1].slice(0, 3)} ${day}`)


          const areas = month === currentMonth
              ? (Object.values(rawData)[0] ?? {})
              : (allMonths[month] ?? {})

          Object.entries(areas).forEach(([area, dailyValues]) => {
              if (!areaMap[area]) areaMap[area] = []
              const val = Array.isArray(dailyValues) ? Number(dailyValues[day - 1] ?? 0) : 0
              areaMap[area].push(val)
          })

          // ── Average daily target (flat per month) ─────────────
          const monthTarget = targets?.[MONTH_NAMES[month - 1]]
          const avgTarget   = monthTarget?.target && monthTarget?.working_days
              ? Number(monthTarget.target) / Number(monthTarget.working_days)
              : null
          avgDailyTargetData.push(avgTarget)

          if (month === currentMonth && Object.keys(dailyTargets).length > 0) {
            const dayKey = String(day)
            const sum = Object.entries(dailyTargets)
                .filter(([area]) => area !== 'ALL')
                .reduce((total, [, areaDays]) => {
                    return total + (areaDays[String(day)] !== undefined ? Number(areaDays[String(day)]) : 0)
                }, 0)
            inputDailyTargetData.push(sum > 0 ? sum : null)
          } else {
            inputDailyTargetData.push(null)
          }

          cursor.setDate(cursor.getDate() + 1)
      }

      const datasets = []
      let colorIndex = 0

      Object.entries(areaMap).forEach(([area, values]) => {
          if (!Array.isArray(values)) return
          const color = AREA_COLORS[colorIndex++ % AREA_COLORS.length]
          datasets.push({
              type:            'bar',
              label:           area,
              data:            values,
              backgroundColor: color,
              borderColor:     'transparent',
              borderRadius:    3,
              stack:           'main',
          })
      })

      // ── Average daily target line (white dashed) ─────────────
      const hasAvgTarget = avgDailyTargetData.some(v => v !== null)
      if (hasAvgTarget) {
          datasets.push({
              type:            'line',
              label:           'Avg Daily Target',
              data:            avgDailyTargetData,
              borderColor:     '#F5DEB3',
              backgroundColor: 'transparent',
              borderWidth:     2,
              borderDash:      [6, 4],
              pointRadius:     0,
              tension:         0,
              stack:           'targets',
              order:           -1,
              spanGaps:        true,
          })
      }

      // ── Inputted daily target line (red dashed) ─────────────
      const hasInputTarget = inputDailyTargetData.some(v => v !== null)
      if (hasInputTarget) {
          datasets.push({
            type:            'line',
            label:           'Daily Target (Set)',
            data:            inputDailyTargetData,
            borderColor:     '#DC143C',
            backgroundColor: 'rgba(56, 178, 172, 0.15)',
            //fill:            'origin',
            borderWidth:     2,
            borderDash:      [3, 3],
            pointRadius:     0,
            tension:         0.0,
            stepped:         false,

            stack:           undefined,
            order:           -1,
            spanGaps:        true,
          })
      }

      return { labels, datasets }
  }

  export function buildChartOptions() {
    return {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        customLineBase: true,
        legend: { display: false },
        tooltip: {
          backgroundColor: '#112240',
          titleColor: '#5ba3e0',
          bodyColor: '#c8d8e8',
          borderColor: 'rgba(43,130,203,0.3)',
          borderWidth: 1,
          padding: 12,
          callbacks: {
            label(ctx) {
              return ` ${ctx.dataset.label}: ${ctx.parsed.y} t`
            },
            footer(items) {
              const barItems = items.filter(i => i.dataset.type === 'bar')
              if (!barItems.length) return ''
              const total = barItems.reduce((sum, i) => sum + i.parsed.y, 0)
              return `Total: ${total.toFixed(2)} t`
            }
          }
        },
      },
      scales: {
        x: {
          stacked: true,
          grid: { color: 'rgba(255,255,255,0.04)' },
          ticks: { color: '#8899aa', font: { size: 11 } },
          border: { color: 'rgba(255,255,255,0.08)' }
        },
        y: {
          stacked: true,
          grid: { color: 'rgba(255,255,255,0.06)' },
          ticks: {
            color: '#8899aa',
            font: { size: 11 },
            callback: (val) => `${val}t`,
          },
          border: { color: 'rgba(255,255,255,0.08)' },
          title: {
            display: true,
            text: 'Output (tons)',
            color: '#8899aa',
            font: { size: 11 },
          }
        },
        y2: {
            stacked: false,
            display: false,
            grid:    { drawOnChartArea: false },
            afterDataLimits(axis) {
                const yAxis = axis.chart.scales['y']
                if (yAxis) {
                    axis.min = yAxis.min
                    axis.max = yAxis.max
                }
            },
            ticks: {
                color: '#8899aa',
                font: { size: 11 },
                callback: (val) => `${val}t`,
            },
            border: { color: 'rgba(255,255,255,0.08)' },
        }
      },
      animation: {
        duration: 600,
        easing: 'easeInOutQuart',
      },
    }
  }

  export function buildMonthlySummaries(rawData, targets = {}, allMonths = {}, dailyTargets = {}) {
    const now          = new Date()
    const currentMonth = now.getMonth()
    const currentYear  = now.getFullYear()
    const yesterday    = now.getDate() - 1

    return Array.from({ length: currentMonth + 1 }, (_, i) => {
        const monthNumber   = i + 1
        const isCurrent     = i === currentMonth
        const monthTarget   = targets?.[MONTH_NAMES[i]]

        const areas = isCurrent
            ? Object.values(rawData)[0] ?? {}
            : allMonths[monthNumber] ?? {}

        const plantTotals = Object.entries(areas).map(([area, dailyValues]) => ({
            plant: area,
            total: Array.isArray(dailyValues)
                ? dailyValues.reduce((s, v) => s + Number(v), 0)
                : 0
        }))

        const grandTotal = plantTotals.reduce((s, p) => s + p.total, 0)

        const yesterdayTotal = isCurrent && yesterday > 0
            ? Object.values(areas).reduce((sum, dailyValues) => {
                return sum + (Array.isArray(dailyValues) ? Number(dailyValues[yesterday - 1] ?? 0) : 0)
            }, 0)
            : null


        const yesterdayDailyTarget = isCurrent && yesterday > 0
            ? Object.entries(dailyTargets)
                .filter(([area]) => area !== 'ALL')
                .reduce((sum, [, areaDays]) => {
                    return sum + (areaDays[String(yesterday)] !== undefined ? Number(areaDays[String(yesterday)]) : 0)
                }, 0)
            : 0

        return {
            month:        MONTH_NAMES[i],
            isCurrent,
            year:         currentYear,
            plantTotals,
            grandTotal,
            target:       Number(monthTarget?.target       ?? 0),
            working_days: Number(monthTarget?.working_days ?? 0),
            dailyTarget:  monthTarget?.target && monthTarget?.working_days
                            ? Number(monthTarget.target) / Number(monthTarget.working_days)
                            : 0,
            yesterdayTotal,
            yesterdayDailyTarget,
        }
    })
}
