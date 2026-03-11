export const AREA_COLORS = [
  '#2b82cb', '#38a169', '#d69e2e', '#e53e3e', '#9f7aea',
  '#38b2ac', '#ed8936', '#667eea', '#fc8181', '#68d391',
]

const MONTH_NAMES = [
  'January','February','March','April','May','June',
  'July','August','September','October','November','December'
]

export function buildChartData(rawData, today, dayLabels, targets, currentMonthName) {
    if (!rawData || Object.keys(rawData).length === 0) {
        return { labels: dayLabels, datasets: [] }
    }

    const datasets = []
    let colorIndex = 0

    Object.entries(rawData).forEach(([plant, areas]) => {
        if (!areas || typeof areas !== 'object') return

        Object.entries(areas).forEach(([area, dailyValues]) => {
            if (!Array.isArray(dailyValues)) return

            const color = AREA_COLORS[colorIndex++ % AREA_COLORS.length]
            datasets.push({
                type:            'bar',
                label:           plant === area ? plant : `${plant} - ${area}`,
                data:            dailyValues.slice(0, today),
                backgroundColor: color,
                borderColor:     'transparent',
                borderRadius:    3,
                stack:           plant,
            })
        })
    })

    const currentTarget = targets?.[currentMonthName]
    if (currentTarget?.target && currentTarget?.working_days) {
        const dailyTarget = currentTarget.target / currentTarget.working_days

        datasets.push({
            type:            'line',
            label:           'Daily Target',
            data:            Array(today).fill(dailyTarget),
            borderColor:     '#f6c90e',
            backgroundColor: 'transparent',
            borderWidth:     2,
            borderDash:      [6, 4],
            pointRadius:     0,
            tension:         0,
            stack:           undefined,
            order:           0,
        })
    }

    return { labels: dayLabels, datasets }
}

export function buildChartOptions() {
  return {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
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
            const total = items.reduce((sum, i) => sum + i.parsed.y, 0)
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
      }
    },
    animation: {
      duration: 600,
      easing: 'easeInOutQuart',
    },
  }
}

export function buildMonthlySummaries(rawData, targets = {}, allMonths = {}) {
    const now          = new Date()
    const currentMonth = now.getMonth()
    const currentYear  = now.getFullYear()

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
        }
    })
}
