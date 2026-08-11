import { nextTick, onBeforeUnmount, onMounted, watch } from 'vue'
import { useRoute } from 'vue-router'
import 'aos/dist/aos.css'
import 'animate.css'

let aosInited = false
let aosApi = null
let wowInstance = null

function prefersReducedMotion() {
  return window.matchMedia('(prefers-reduced-motion: reduce)').matches
}

async function initScrollLibs() {
  const [{ default: AOS }, { default: WOW }] = await Promise.all([
    import('aos'),
    import('wow.js'),
  ])

  aosApi = AOS

  if (!aosInited) {
    AOS.init({
      duration: 780,
      easing: 'ease-out-cubic',
      once: true,
      offset: 72,
      disable: prefersReducedMotion,
    })
    aosInited = true
  } else {
    AOS.refreshHard()
  }

  wowInstance?.stop?.()
  wowInstance = null

  if (!prefersReducedMotion()) {
    wowInstance = new WOW({
      boxClass: 'wow',
      animateClass: 'animate__animated',
      offset: 72,
      mobile: true,
      live: true,
    })
    wowInstance.init()
  }
}

function refreshScrollReveal() {
  nextTick(() => {
    requestAnimationFrame(() => {
      aosApi?.refreshHard?.()
      wowInstance?.sync?.()
    })
  })
}

/** Khởi tạo AOS + WOW.js trong layout user; refresh khi đổi route. */
export function useScrollReveal() {
  const route = useRoute()

  onMounted(() => {
    initScrollLibs().then(() => refreshScrollReveal())
  })

  watch(
    () => route.fullPath,
    () => refreshScrollReveal(),
  )

  onBeforeUnmount(() => {
    wowInstance?.stop?.()
    wowInstance = null
  })

  return { refreshScrollReveal }
}
