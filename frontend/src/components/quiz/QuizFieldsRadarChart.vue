<template>
  <div class="quiz-radar">
    <div class="quiz-radar__head">
      <h2 class="quiz-radar__title">Biểu đồ điểm phù hợp</h2>
      <p class="quiz-radar__lead muted">
        So sánh top ngành học và chuyên ngành theo tổng điểm khảo sát.
      </p>
    </div>

    <p v-if="!hasData" class="muted">Chưa đủ dữ liệu để vẽ biểu đồ radar.</p>
    <div v-else class="quiz-radar__body">
      <div class="quiz-radar__chart-wrap">
        <VueApexCharts
          type="radar"
          width="100%"
          :height="chartHeight"
          :options="chartOptions"
          :series="chartSeries"
        />
      </div>

      <div class="quiz-radar__keys" aria-label="Chú thích biểu đồ">
        <section class="quiz-radar__key-block">
          <h3 class="quiz-radar__key-title quiz-radar__key-title--nganh">
            <span class="quiz-radar__key-dot" aria-hidden="true" />
            Ngành học
          </h3>
          <ol class="quiz-radar__key-list">
            <li
              v-for="(item, index) in nganhKeyItems"
              :key="`nganh-key-${item.id}`"
              class="quiz-radar__key-item"
            >
              <span class="quiz-radar__key-rank">Top {{ index + 1 }}</span>
              <span class="quiz-radar__key-name">{{ item.name }}</span>
              <span class="quiz-radar__key-score">{{ formatScore(item.score) }} điểm</span>
            </li>
            <li v-if="!nganhKeyItems.length" class="quiz-radar__key-empty muted">
              Chưa có ngành học
            </li>
          </ol>
        </section>

        <section class="quiz-radar__key-block">
          <h3 class="quiz-radar__key-title quiz-radar__key-title--chuyen">
            <span class="quiz-radar__key-dot" aria-hidden="true" />
            Chuyên ngành
          </h3>
          <ol class="quiz-radar__key-list">
            <li
              v-for="(item, index) in chuyenKeyItems"
              :key="`chuyen-key-${item.id}`"
              class="quiz-radar__key-item"
            >
              <span class="quiz-radar__key-rank">Top {{ index + 1 }}</span>
              <span class="quiz-radar__key-name">{{ item.name }}</span>
              <span class="quiz-radar__key-score">{{ formatScore(item.score) }} điểm</span>
            </li>
            <li v-if="!chuyenKeyItems.length" class="quiz-radar__key-empty muted">
              Chưa có chuyên ngành
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

const props = defineProps({
  nganhList: {
    type: Array,
    default: () => [],
  },
  chuyenList: {
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

const axisCount = computed(() =>
  Math.max(props.nganhList.length, props.chuyenList.length, 0),
)

const hasData = computed(() => axisCount.value > 0)

const nganhKeyItems = computed(() =>
  props.nganhList.map((item) => ({
    id: item.nganh_hoc_id,
    name: item.ten_nganh || `Ngành #${item.nganh_hoc_id}`,
    score: Number(item.tong_diem) || 0,
  })),
)

const chuyenKeyItems = computed(() =>
  props.chuyenList.map((item) => ({
    id: item.chuyen_nganh_id,
    name: item.ten_chuyen_nganh || `Chuyên ngành #${item.chuyen_nganh_id}`,
    score: Number(item.tong_diem) || 0,
  })),
)

function formatScore(value) {
  const n = Number(value)
  if (!Number.isFinite(n)) return '0'
  return Number.isInteger(n) ? String(n) : n.toFixed(1)
}

/** Desktop (chart + keys 1 hàng) vs mobile (chart riêng, keys riêng) */
const isDesktop = computed(() => viewportWidth.value >= 900)
const isCompact = computed(() => viewportWidth.value < 640)

const chartHeight = computed(() => {
  if (viewportWidth.value < 480) return 260
  if (viewportWidth.value < 640) return 280
  if (viewportWidth.value < 900) return 320
  if (viewportWidth.value < 1100) return 360
  return 400
})

const categories = computed(() =>
  Array.from({ length: axisCount.value }, (_, index) => `Top ${index + 1}`),
)

function padScores(list, length) {
  return Array.from({ length }, (_, index) => {
    const item = list[index]
    return item ? Number(item.tong_diem) || 0 : 0
  })
}

const chartSeries = computed(() => [
  {
    name: 'Ngành học',
    data: padScores(props.nganhList, axisCount.value),
  },
  {
    name: 'Chuyên ngành',
    data: padScores(props.chuyenList, axisCount.value),
  },
])

const maxScore = computed(() => {
  const values = [
    ...padScores(props.nganhList, axisCount.value),
    ...padScores(props.chuyenList, axisCount.value),
  ]
  const peak = Math.max(0, ...values)
  if (peak <= 0) return 10
  return Math.ceil(peak / 5) * 5
})

const chartOptions = computed(() => {
  const nganh = props.nganhList
  const chuyen = props.chuyenList
  const count = axisCount.value
  const compact = isCompact.value
  const desktop = isDesktop.value

  const nganhLabel = (index) => {
    const item = nganh[index]
    if (!item) return '—'
    return item.ten_nganh || `Ngành #${item.nganh_hoc_id}`
  }

  const chuyenLabel = (index) => {
    const item = chuyen[index]
    if (!item) return '—'
    return item.ten_chuyen_nganh || `Chuyên ngành #${item.chuyen_nganh_id}`
  }

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
    colors: ['#1f7a4c', '#e67e22'],
    stroke: {
      width: compact ? 1.5 : 2,
      colors: ['#1f7a4c', '#e67e22'],
    },
    fill: {
      opacity: 0.14,
    },
    markers: {
      size: compact ? 2 : 4,
      hover: {
        size: compact ? 4 : 6,
      },
    },
    legend: {
      show: true,
      position: desktop ? 'top' : 'bottom',
      horizontalAlign: 'center',
      fontSize: compact ? '10px' : '13px',
      markers: {
        width: compact ? 8 : 10,
        height: compact ? 8 : 10,
        radius: 10,
      },
      itemMargin: {
        horizontal: compact ? 6 : 10,
        vertical: 4,
      },
    },
    xaxis: {
      categories: categories.value,
      labels: {
        show: true,
        style: {
          colors: Array.from({ length: count }, () => '#5c7268'),
          fontSize: compact ? '9px' : desktop ? '12px' : '11px',
          fontWeight: 500,
        },
      },
    },
    yaxis: {
      show: false,
      min: 0,
      max: maxScore.value,
      tickAmount: 5,
    },
    plotOptions: {
      radar: {
        size: undefined,
        polygons: {
          strokeColors: '#d5ddd8',
          connectorColors: '#d5ddd8',
          fill: {
            colors: ['#ffffff', '#f7faf8'],
          },
        },
      },
    },
    tooltip: {
      shared: true,
      intersect: false,
      custom({ series, dataPointIndex, w }) {
        const rank = dataPointIndex + 1
        const nganhName = escapeHtml(nganhLabel(dataPointIndex))
        const chuyenName = escapeHtml(chuyenLabel(dataPointIndex))
        const nganhScore = series?.[0]?.[dataPointIndex] ?? 0
        const chuyenScore = series?.[1]?.[dataPointIndex] ?? 0
        const seriesNames = w?.globals?.seriesNames || ['Ngành học', 'Chuyên ngành']

        return `
          <div class="quiz-radar__tooltip">
            <div class="quiz-radar__tooltip-title">Top ${rank}</div>
            <div class="quiz-radar__tooltip-row">
              <span class="quiz-radar__tooltip-dot" style="background:#1f7a4c"></span>
              <div>
                <strong>${escapeHtml(seriesNames[0])}</strong>
                <div>${nganhName}</div>
                <div>Điểm: ${nganhScore}</div>
              </div>
            </div>
            <div class="quiz-radar__tooltip-row">
              <span class="quiz-radar__tooltip-dot" style="background:#e67e22"></span>
              <div>
                <strong>${escapeHtml(seriesNames[1])}</strong>
                <div>${chuyenName}</div>
                <div>Điểm: ${chuyenScore}</div>
              </div>
            </div>
          </div>
        `
      },
    },
    responsive: [
      {
        breakpoint: 900,
        options: {
          legend: { position: 'bottom', fontSize: '11px' },
          markers: { size: 3 },
          xaxis: {
            labels: { style: { fontSize: '10px' } },
          },
        },
      },
      {
        breakpoint: 640,
        options: {
          legend: { position: 'bottom', fontSize: '10px' },
          markers: { size: 2 },
          stroke: { width: 1.5 },
          xaxis: {
            labels: { style: { fontSize: '9px' } },
          },
        },
      },
    ],
  }
})
</script>

<style scoped>
.quiz-radar {
  margin: 0 0 1.25rem;
  padding: 0.95rem 0.85rem 0.75rem;
  border: 1px solid #d9e2dc;
  border-radius: 12px;
  background: #fbfcfb;
}

.quiz-radar__head {
  margin-bottom: 0.55rem;
  padding: 0 0.25rem;
}

.quiz-radar__title {
  margin: 0 0 0.3rem;
  font-size: 1.05rem;
  font-weight: 600;
}

.quiz-radar__lead {
  margin: 0;
  font-size: 0.9rem;
  line-height: 1.45;
}

.quiz-radar__body {
  display: grid;
  gap: 0.85rem;
  align-items: start;
}

.quiz-radar__chart-wrap {
  width: 100%;
  max-width: 100%;
  min-width: 0;
  min-height: 240px;
  overflow: hidden;
}

.quiz-radar__chart-wrap :deep(.apexcharts-canvas) {
  margin: 0 auto;
}

.quiz-radar__chart-wrap :deep(.apexcharts-tooltip) {
  border: 0 !important;
  box-shadow: 0 8px 24px rgba(26, 46, 36, 0.14) !important;
  border-radius: 10px !important;
  overflow: hidden;
}

.quiz-radar__chart-wrap :deep(.quiz-radar__tooltip) {
  min-width: 12rem;
  max-width: min(18rem, 78vw);
  padding: 0.7rem 0.8rem;
  background: #fff;
  color: #24362c;
  font-size: 0.84rem;
  line-height: 1.4;
}

.quiz-radar__chart-wrap :deep(.quiz-radar__tooltip-title) {
  margin-bottom: 0.45rem;
  color: #1f7a4c;
  font-weight: 650;
}

.quiz-radar__chart-wrap :deep(.quiz-radar__tooltip-row) {
  display: grid;
  grid-template-columns: 0.7rem 1fr;
  gap: 0.45rem;
  align-items: start;
}

.quiz-radar__chart-wrap :deep(.quiz-radar__tooltip-row + .quiz-radar__tooltip-row) {
  margin-top: 0.55rem;
  padding-top: 0.55rem;
  border-top: 1px solid #e5ece7;
}

.quiz-radar__chart-wrap :deep(.quiz-radar__tooltip-dot) {
  width: 0.55rem;
  height: 0.55rem;
  margin-top: 0.28rem;
  border-radius: 999px;
}

.quiz-radar__keys {
  display: grid;
  gap: 0.75rem;
  min-width: 0;
}

.quiz-radar__key-block {
  min-width: 0;
}

.quiz-radar__key-title {
  display: inline-flex;
  align-items: center;
  gap: 0.45rem;
  margin: 0 0 0.45rem;
  font-size: 0.92rem;
  font-weight: 650;
  color: var(--text, #24362c);
}

.quiz-radar__key-dot {
  width: 0.55rem;
  height: 0.55rem;
  border-radius: 999px;
  flex-shrink: 0;
}

.quiz-radar__key-title--nganh .quiz-radar__key-dot {
  background: #1f7a4c;
}

.quiz-radar__key-title--chuyen .quiz-radar__key-dot {
  background: #e67e22;
}

.quiz-radar__key-list {
  margin: 0;
  padding: 0;
  list-style: none;
  display: grid;
  gap: 0.35rem;
}

.quiz-radar__key-item {
  display: grid;
  grid-template-columns: 3.1rem minmax(0, 1fr) auto;
  gap: 0.4rem 0.5rem;
  align-items: baseline;
  padding: 0.42rem 0.5rem;
  border-radius: 8px;
  background: #fff;
  border: 1px solid #e5ece7;
}

.quiz-radar__key-rank {
  color: #5c7268;
  font-size: 0.78rem;
  font-weight: 600;
  font-variant-numeric: tabular-nums;
}

.quiz-radar__key-name {
  min-width: 0;
  color: var(--text, #24362c);
  font-size: 0.88rem;
  font-weight: 500;
  line-height: 1.35;
  overflow-wrap: anywhere;
}

.quiz-radar__key-score {
  color: #1f7a4c;
  font-size: 0.84rem;
  font-weight: 650;
  font-variant-numeric: tabular-nums;
  white-space: nowrap;
}

.quiz-radar__key-title--chuyen ~ .quiz-radar__key-list .quiz-radar__key-score {
  color: #e67e22;
}

.quiz-radar__key-empty {
  margin: 0;
  padding: 0.35rem 0.15rem;
  font-size: 0.86rem;
}

/* Desktop: biểu đồ + chú thích cùng 1 hàng */
@media (min-width: 900px) {
  .quiz-radar__body {
    grid-template-columns: minmax(0, 1.15fr) minmax(16rem, 0.85fr);
    gap: 1rem;
    align-items: center;
  }

  .quiz-radar__keys {
    gap: 0.9rem;
    padding-left: 0.15rem;
  }

  .quiz-radar__chart-wrap {
    min-height: 340px;
  }
}

/* Mobile / tablet: biểu đồ 1 hàng, chú thích hàng riêng + font nhỏ hơn */
@media (max-width: 899px) {
  .quiz-radar {
    padding: 0.8rem 0.55rem 0.65rem;
  }

  .quiz-radar__title {
    font-size: 0.98rem;
  }

  .quiz-radar__lead {
    font-size: 0.82rem;
  }

  .quiz-radar__body {
    grid-template-columns: 1fr;
  }

  .quiz-radar__chart-wrap {
    min-height: 250px;
  }

  .quiz-radar__keys {
    padding-top: 0.65rem;
    border-top: 1px solid #e2ebe5;
  }

  .quiz-radar__key-title {
    font-size: 0.82rem;
  }

  .quiz-radar__key-item {
    grid-template-columns: 2.6rem minmax(0, 1fr) auto;
    padding: 0.35rem 0.45rem;
    gap: 0.3rem 0.4rem;
  }

  .quiz-radar__key-rank {
    font-size: 0.7rem;
  }

  .quiz-radar__key-name {
    font-size: 0.78rem;
  }

  .quiz-radar__key-score {
    font-size: 0.74rem;
  }

  .quiz-radar__chart-wrap :deep(.quiz-radar__tooltip) {
    font-size: 0.78rem;
    padding: 0.55rem 0.65rem;
  }
}

@media (max-width: 639px) {
  .quiz-radar__key-item {
    grid-template-columns: 2.5rem minmax(0, 1fr);
  }

  .quiz-radar__key-score {
    grid-column: 2;
  }
}
</style>
