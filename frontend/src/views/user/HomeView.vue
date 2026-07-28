<template>
  <div class="home">
    <div class="home-block home-block--hero">
      <HomeHero />
      <div class="section-wave" aria-hidden="true">
        <svg viewBox="0 0 1440 72" preserveAspectRatio="none" focusable="false">
          <path
            fill="var(--home-bg-audiences)"
            d="M0,36 C180,68 360,4 540,28 C720,52 900,76 1080,44 C1260,12 1380,8 1440,20 L1440,72 L0,72 Z"
          />
        </svg>
      </div>
    </div>

    <div class="home-block home-block--audiences">
      <HomeAudiences />
      <div class="section-wave" aria-hidden="true">
        <svg viewBox="0 0 1440 72" preserveAspectRatio="none" focusable="false">
          <path
            fill="var(--home-bg-services)"
            d="M0,28 C220,8 420,70 640,42 C860,14 1100,-4 1440,36 L1440,72 L0,72 Z"
          />
        </svg>
      </div>
    </div>

    <div class="home-block home-block--services">
      <HomeServices />
      <div class="section-wave" aria-hidden="true">
        <svg viewBox="0 0 1440 72" preserveAspectRatio="none" focusable="false">
          <path
            fill="var(--home-bg-admission)"
            d="M0,44 C160,12 380,64 600,40 C820,16 1040,0 1220,24 C1340,40 1400,48 1440,40 L1440,72 L0,72 Z"
          />
        </svg>
      </div>
    </div>

    <div class="home-block home-block--admission">
      <HomeAdmission />
    </div>
  </div>
</template>

<script setup>
import {
  HomeHero,
  HomeAudiences,
  HomeServices,
  HomeAdmission,
} from '@/components/home'
</script>

<style scoped>
.home {
  --home-bg-hero: #eef6f1;
  --home-bg-audiences: #f8fcfa;
  --home-bg-services: #eaf3ee;
  --home-bg-admission: #f3f8f5;
  display: flex;
  flex-direction: column;
}

.home-block {
  position: relative;
}

.home-block--hero {
  background: var(--home-bg-hero);
}

.home-block--audiences {
  background: var(--home-bg-audiences);
}

.home-block--services {
  background: var(--home-bg-services);
}

.home-block--admission {
  background: var(--home-bg-admission);
}

.section-wave {
  display: block;
  line-height: 0;
  margin-bottom: -1px;
}

.section-wave svg {
  display: block;
  width: 100%;
  height: clamp(2.25rem, 5vw, 3.5rem);
}

/* Section con dùng nền trong suốt để lộ màu khối cha */
.home-block :deep(.hero-section),
.home-block :deep(.audiences),
.home-block :deep(.services),
.home-block :deep(.admission) {
  background: transparent;
}
</style>
