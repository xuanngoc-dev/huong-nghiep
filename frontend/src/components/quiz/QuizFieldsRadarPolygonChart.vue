<template>
  <div class="quiz-radar-polygon">
    <div class="quiz-radar-polygon__head">
      <h2 class="quiz-radar-polygon__title">Biểu đồ điểm phù hợp</h2>
      <p class="quiz-radar-polygon__lead muted">
        So sánh top nhóm ngành theo tổng điểm khảo sát.
      </p>
    </div>

    <p v-if="!hasData" class="muted">Chưa đủ dữ liệu để vẽ biểu đồ radar.</p>
    <div v-else class="quiz-radar-polygon__body">
      <div class="quiz-radar-polygon__chart-wrap">
        <VueApexCharts
          :key="chartKey"
          type="radar"
          width="100%"
          :height="chartHeight"
          :options="chartOptions"
          :series="chartSeries"
        />
      </div>

      <div class="quiz-radar-polygon__keys" aria-label="Chú thích biểu đồ">
        <section class="quiz-radar-polygon__key-block">
          <h3 class="quiz-radar-polygon__key-title">
            <span class="quiz-radar-polygon__key-dot" aria-hidden="true" />
            Nhóm ngành
          </h3>
          <ol class="quiz-radar-polygon__key-list">
            <li
              v-for="item in nhomKeyItems"
              :key="`nhom-key-${item.id}`"
              class="quiz-radar-polygon__key-item"
            >
              <span class="quiz-radar-polygon__key-name">{{ item.name }}</span>
              <span class="quiz-radar-polygon__key-score">{{ formatScore(item.score) }} điểm</span>
            </li>
          </ol>
        </section>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue'
import VueApexCharts from 'vue3-apexcharts'

/** ApexCharts demo: Radar with Polygon Fill — adapted for nhóm ngành. */
const ACCENT = '#1f7a4c'
const RADAR_MIN_AXES = 3

const props = defineProps({
  nhomList: {
    type: Array,
    default: () => [],
  },
})

const viewportWidth = ref(typeof window !== 'undefined' ? window.innerWidth : 1024)

function onResize() {
  viewportWidth.value = window.innerWidth
}

onMounted(() => {
  window.addEventListener('resize', onResize, { passive: true })
  onResize()
})

onUnmounted(() => {
  window.removeEventListener('resize', onResize)
})

function nhomName(item) {
  if (!item) return '—'
  return String(item.ten_nhom_nganh || `Nhóm ngành #${item.nhom_nganh_id}`).trim()
}

function formatScore(value) {
  const n = Number(value)
  if (!Number.isFinite(n)) return '0'
  return Number.isInteger(n) ? String(n) : n.toFixed(1)
}

function wrapLabel(text, maxLen = 14) {
  const raw = String(text || '').trim()
  if (!raw) return ['—']
  if (raw.length <= maxLen) return [raw]

  const words = raw.split(/\s+/).filter(Boolean)
  if (words.length === 1) {
    return [`${raw.slice(0, Math.max(1, maxLen - 1))}…`]
  }

  const lines = []
  let current = ''

  for (let i = 0; i < words.length; i += 1) {
    const word = words[i]
    const next = current ? `${current} ${word}` : word
    if (next.length > maxLen && current) {
      lines.push(current)
      current = word
      if (lines.length >= 2) {
        current = ''
        break
      }
    } else {
      current = next
    }
  }

  if (current && lines.length < 2) lines.push(current)

  const usedWords = lines.join(' ').split(/\s+/).filter(Boolean).length
  if (usedWords < words.length && lines.length) {
    const last = lines[lines.length - 1]
    lines[lines.length - 1] = `${last.replace(/…$/, '')}…`
  }

  return lines.length ? lines.slice(0, 2) : ['—']
}

const hasData = computed(() => props.nhomList.length > 0)

const axisItems = computed(() => {
  const items = props.nhomList.map((item) => ({
    id: item.nhom_nganh_id,
    name: nhomName(item),
    score: Number(item.tong_diem) || 0,
  }))
  while (items.length > 0 && items.length < RADAR_MIN_AXES) {
    items.push({
      id: `pad-${items.length}`,
      name: '—',
      score: 0,
      padded: true,
    })
  }
  return items
})

const nhomKeyItems = computed(() => axisItems.value.filter((item) => !item.padded))

const isDesktop = computed(() => viewportWidth.value >= 900)
const isCompact = computed(() => viewportWidth.value < 640)

const chartHeight = computed(() => {
  if (viewportWidth.value < 480) return 320
  if (viewportWidth.value < 640) return 340
  if (viewportWidth.value < 900) return 380
  if (viewportWidth.value < 1100) return 400
  return 420
})

const radarSize = computed(() => {
  const h = chartHeight.value
  if (isCompact.value) return Math.round(h * 0.36)
  if (isDesktop.value) return Math.round(h * 0.4)
  return Math.round(h * 0.38)
})

const chartKey = computed(
  () =>
    `radar-poly-${axisItems.value.map((i) => `${i.id}:${i.score}`).join('|')}-${chartHeight.value}`,
)

const categories = computed(() => {
  const maxLen = isCompact.value ? 10 : isDesktop.value ? 16 : 12
  return axisItems.value.map((item) => wrapLabel(item.name, maxLen))
})

const chartSeries = computed(() => [
  {
    name: 'Điểm phù hợp',
    data: axisItems.value.map((item) => item.score),
  },
])

const maxScore = computed(() => {
  const peak = Math.max(0, ...axisItems.value.map((item) => item.score))
  if (peak <= 0) return 10
  return Math.ceil(peak / 5) * 5
})

const chartOptions = computed(() => {
  const items = axisItems.value
  const count = items.length
  const compact = isCompact.value
  const desktop = isDesktop.value
  const yMax = maxScore.value

  const escapeHtml = (value) =>
    String(value ?? '')
      .replaceAll('&', '&amp;')
      .replaceAll('<', '&lt;')
      .replaceAll('>', '&gt;')
      .replaceAll('"', '&quot;')

  return {
    chart: {
      type: 'radar',
      toolbar: { show: false },
      fontFamily: 'inherit',
      animations: {
        enabled: true,
        speed: 700,
      },
      parentHeightOffset: 0,
      redrawOnParentResize: true,
      redrawOnWindowResize: true,
    },
    dataLabels: {
      enabled: true,
      background: {
        enabled: true,
        borderRadius: 2,
        padding: 3,
        borderWidth: 0,
        foreColor: '#fff',
      },
      style: {
        fontSize: compact ? '10px' : '11px',
        fontWeight: 600,
        colors: [ACCENT],
      },
      formatter(val, opts) {
        const item = items[opts?.dataPointIndex]
        if (!item || item.padded) return ''
        return formatScore(val)
      },
    },
    plotOptions: {
      radar: {
        size: radarSize.value,
        polygons: {
          strokeColors: '#e2ebe5',
          connectorColors: '#e2ebe5',
          fill: {
            colors: ['#f3faf6', '#ffffff'],
          },
        },
      },
    },
    colors: [ACCENT],
    markers: {
      size: 4,
      colors: ['#fff'],
      strokeColors: ACCENT,
      strokeWidth: 2,
      hover: {
        size: 6,
      },
    },
    stroke: {
      width: 2,
      colors: [ACCENT],
    },
    fill: {
      opacity: 0.22,
    },
    legend: {
      show: false,
    },
    xaxis: {
      categories: categories.value,
      labels: {
        show: true,
        style: {
          colors: Array.from({ length: count }, () => '#5c7268'),
          fontSize: compact ? '9px' : desktop ? '11px' : '10px',
          fontWeight: 500,
        },
      },
    },
    yaxis: {
      min: 0,
      max: yMax,
      tickAmount: 5,
      labels: {
        formatter(val, i) {
          if (i % 2 === 0) return String(val)
          return ''
        },
      },
    },
    tooltip: {
      enabled: true,
      custom({ series, dataPointIndex }) {
        const item = items[dataPointIndex]
        if (!item || item.padded) {
          return `<div class="quiz-radar-polygon__tooltip"><div class="quiz-radar-polygon__tooltip-title">—</div></div>`
        }
        const score = series?.[0]?.[dataPointIndex] ?? item.score
        return `
          <div class="quiz-radar-polygon__tooltip">
            <div class="quiz-radar-polygon__tooltip-title">${escapeHtml(item.name)}</div>
            <div class="quiz-radar-polygon__tooltip-row">
              <span class="quiz-radar-polygon__tooltip-dot" aria-hidden="true"></span>
              <span>Điểm: ${formatScore(score)}</span>
            </div>
          </div>
        `
      },
    },
  }
})
</script>

<style scoped>
.quiz-radar-polygon {
  margin: 0 0 1.25rem;
  padding: 0.95rem 0.85rem 0.75rem;
  border: 1px solid #d9e2dc;
  border-radius: 12px;
  background: #fbfcfb;
}

.quiz-radar-polygon__head {
  margin-bottom: 0.55rem;
  padding: 0 0.25rem;
}

.quiz-radar-polygon__title {
  margin: 0 0 0.3rem;
  font-size: 1.05rem;
  font-weight: 600;
}

.quiz-radar-polygon__lead {
  margin: 0;
  font-size: 0.9rem;
  line-height: 1.45;
}

.quiz-radar-polygon__body {
  display: grid;
  gap: 0.85rem;
  align-items: start;
}

.quiz-radar-polygon__chart-wrap {
  width: 100%;
  max-width: 100%;
  min-width: 0;
  min-height: 300px;
  overflow: visible;
}

.quiz-radar-polygon__chart-wrap :deep(.apexcharts-canvas) {
  margin: 0 auto;
}

.quiz-radar-polygon__chart-wrap :deep(.apexcharts-tooltip) {
  border: 0 !important;
  box-shadow: 0 8px 24px rgba(26, 46, 36, 0.14) !important;
  border-radius: 10px !important;
  overflow: hidden;
}

.quiz-radar-polygon__chart-wrap :deep(.quiz-radar-polygon__tooltip) {
  min-width: 10rem;
  max-width: min(18rem, 78vw);
  padding: 0.7rem 0.8rem;
  background: #fff;
  color: #24362c;
  font-size: 0.84rem;
  line-height: 1.4;
}

.quiz-radar-polygon__chart-wrap :deep(.quiz-radar-polygon__tooltip-title) {
  margin-bottom: 0.4rem;
  color: #1f7a4c;
  font-weight: 650;
}

.quiz-radar-polygon__chart-wrap :deep(.quiz-radar-polygon__tooltip-row) {
  display: flex;
  align-items: center;
  gap: 0.45rem;
}

.quiz-radar-polygon__chart-wrap :deep(.quiz-radar-polygon__tooltip-dot) {
  width: 0.55rem;
  height: 0.55rem;
  border-radius: 999px;
  background: #1f7a4c;
  flex-shrink: 0;
}

.quiz-radar-polygon__keys {
  min-width: 0;
}

.quiz-radar-polygon__key-title {
  display: inline-flex;
  align-items: center;
  gap: 0.45rem;
  margin: 0 0 0.45rem;
  font-size: 0.92rem;
  font-weight: 650;
  color: var(--text, #24362c);
}

.quiz-radar-polygon__key-dot {
  width: 0.55rem;
  height: 0.55rem;
  border-radius: 999px;
  background: #1f7a4c;
  flex-shrink: 0;
}

.quiz-radar-polygon__key-list {
  margin: 0;
  padding: 0;
  list-style: none;
  display: grid;
  gap: 0.35rem;
}

.quiz-radar-polygon__key-item {
  display: grid;
  grid-template-columns: minmax(0, 1fr) auto;
  gap: 0.4rem 0.5rem;
  align-items: baseline;
  padding: 0.42rem 0.5rem;
  border-radius: 8px;
  background: #fff;
  border: 1px solid #e5ece7;
}

.quiz-radar-polygon__key-name {
  min-width: 0;
  color: var(--text, #24362c);
  font-size: 0.88rem;
  font-weight: 500;
  line-height: 1.35;
  overflow-wrap: anywhere;
}

.quiz-radar-polygon__key-score {
  color: #1f7a4c;
  font-size: 0.84rem;
  font-weight: 650;
  font-variant-numeric: tabular-nums;
  white-space: nowrap;
}

@media (min-width: 900px) {
  .quiz-radar-polygon__body {
    grid-template-columns: minmax(0, 1.15fr) minmax(16rem, 0.85fr);
    gap: 1rem;
    align-items: center;
  }

  .quiz-radar-polygon__chart-wrap {
    min-height: 380px;
  }
}

@media (max-width: 899px) {
  .quiz-radar-polygon {
    padding: 0.8rem 0.55rem 0.65rem;
  }

  .quiz-radar-polygon__title {
    font-size: 0.98rem;
  }

  .quiz-radar-polygon__lead {
    font-size: 0.82rem;
  }

  .quiz-radar-polygon__body {
    grid-template-columns: 1fr;
  }

  .quiz-radar-polygon__keys {
    padding-top: 0.65rem;
    border-top: 1px solid #e2ebe5;
  }

  .quiz-radar-polygon__key-title {
    font-size: 0.82rem;
  }

  .quiz-radar-polygon__key-item {
    padding: 0.35rem 0.45rem;
  }

  .quiz-radar-polygon__key-name {
    font-size: 0.78rem;
  }

  .quiz-radar-polygon__key-score {
    font-size: 0.74rem;
  }
}

@media (max-width: 639px) {
  .quiz-radar-polygon__key-item {
    grid-template-columns: minmax(0, 1fr);
  }

  .quiz-radar-polygon__key-score {
    justify-self: start;
  }
}
</style>
