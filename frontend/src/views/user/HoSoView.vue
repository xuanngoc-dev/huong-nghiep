<template>
  <section class="hoso">
    <p v-if="loading" class="muted">Đang tải hồ sơ...</p>
    <p v-else-if="error" class="error-text">{{ error }}</p>

    <template v-else>
      <article class="card hoso-hero">
        <div class="hoso-hero__identity">
          <span class="hoso-avatar" aria-hidden="true">{{ avatarLetter }}</span>
          <div class="hoso-hero__copy">
            <h1>{{ displayName }}</h1>
            <p class="muted">{{ auth.user?.email || '—' }}</p>
            <p v-if="auth.user?.so_dien_thoai" class="muted">
              {{ auth.user.so_dien_thoai }}
            </p>
          </div>
        </div>

        <dl class="hoso-wallet">
          <div>
            <dt>Edu Coin</dt>
            <dd>{{ formatNumber(profile?.edu_coin) }}</dd>
          </div>
          <div>
            <dt>Xu hệ thống</dt>
            <dd>{{ formatNumber(profile?.xu_he_thong) }}</dd>
          </div>
        </dl>

        <div class="btn-row hoso-hero__actions">
          <RouterLink class="btn" :to="{ name: 'profile-thong-tin' }">
            Cập nhật thông tin
          </RouterLink>
          <RouterLink class="btn btn-outline" :to="{ name: 'profile-bao-mat' }">
            Đổi mật khẩu
          </RouterLink>
          <RouterLink v-if="auth.isAdmin" class="btn btn-outline" to="/admin">
            Vào CMS
          </RouterLink>
        </div>
      </article>

      <article class="card">
        <h2>Thông tin cá nhân</h2>
        <p v-if="!hasProfile" class="muted">
          Bạn chưa bổ sung hồ sơ khảo sát. Hãy cập nhật để nhận tư vấn chính xác hơn.
        </p>
        <dl v-else class="hoso-dl">
          <div>
            <dt>Họ tên</dt>
            <dd>{{ profile.ho_ten || displayName }}</dd>
          </div>
          <div>
            <dt>Email</dt>
            <dd>{{ profile.email || auth.user?.email || '—' }}</dd>
          </div>
          <div>
            <dt>Số điện thoại</dt>
            <dd>{{ profile.so_dien_thoai || auth.user?.so_dien_thoai || '—' }}</dd>
          </div>
          <div>
            <dt>Giới tính</dt>
            <dd>{{ profile.gioi_tinh || '—' }}</dd>
          </div>
          <div>
            <dt>Ngày sinh</dt>
            <dd>{{ formatDate(profile.ngay_sinh) }}</dd>
          </div>
          <div>
            <dt>Dân tộc</dt>
            <dd>{{ profile.dan_toc || '—' }}</dd>
          </div>
          <div>
            <dt>Tôn giáo</dt>
            <dd>{{ profile.ton_giao || '—' }}</dd>
          </div>
          <div>
            <dt>Trình độ học vấn</dt>
            <dd>{{ trinhDoLabel }}</dd>
          </div>
          <div>
            <dt>Khu vực muốn theo học</dt>
            <dd>{{ khuVucLabel }}</dd>
          </div>
          <div>
            <dt>Tỉnh thành đang sống</dt>
            <dd>{{ tinhThanhLabel(profile.vi_tri_dia_ly?.tinh_thanh_dang_song) }}</dd>
          </div>
        </dl>
      </article>
    </template>
  </section>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { RouterLink } from 'vue-router'
import { request } from '@/api'
import { API_PUBLIC } from '@/constants/constant_api'
import { useAuthStore } from '@/stores/auth'

const auth = useAuthStore()
const loading = ref(true)
const error = ref(null)
const profile = ref(null)

const trinhDoLabels = {
  tot_nghiep_thpt: 'Tốt nghiệp THPT',
  tot_nghiep_thcs: 'Tốt nghiệp THCS',
  dang_hoc_thpt: 'Đang học THPT',
  trung_cap: 'Trung cấp',
  cao_dang: 'Cao đẳng',
  dai_hoc: 'Đại học',
  khac: 'Khác',
}

const khuVucLabels = {
  bac: 'Miền Bắc',
  trung: 'Miền Trung',
  nam: 'Miền Nam',
}

const tinhThanhLabels = {
  1: 'Thành phố Hà Nội',
  4: 'Tỉnh Cao Bằng',
  8: 'Tỉnh Tuyên Quang',
  11: 'Tỉnh Điện Biên',
  12: 'Tỉnh Lai Châu',
  14: 'Tỉnh Sơn La',
  15: 'Tỉnh Lào Cai',
  19: 'Tỉnh Thái Nguyên',
  20: 'Tỉnh Lạng Sơn',
  22: 'Tỉnh Quảng Ninh',
  24: 'Tỉnh Bắc Ninh',
  25: 'Tỉnh Phú Thọ',
  31: 'Tp Hải Phòng',
  33: 'Tỉnh Hưng Yên',
  37: 'Tỉnh Ninh Bình',
  38: 'Tỉnh Thanh Hoá',
  40: 'Tỉnh Nghệ An',
  42: 'Tỉnh Hà Tĩnh',
  44: 'Tỉnh Quảng Trị',
  46: 'Thành phố Huế',
  48: 'Tp Đà Nẵng',
  51: 'Tỉnh Quảng Ngãi',
  52: 'Tỉnh Gia Lai',
  56: 'Tỉnh Khánh Hoà',
  66: 'Tỉnh Đắk Lắk',
  68: 'Tỉnh Lâm Đồng',
  75: 'Thành phố Đồng Nai',
  79: 'Tp Hồ Chí Minh',
  80: 'Tỉnh Tây Ninh',
  82: 'Tỉnh Đồng Tháp',
  86: 'Tỉnh Vĩnh Long',
  91: 'Tỉnh An Giang',
  92: 'Tp Cần Thơ',
  96: 'Tỉnh Cà Mau',
}

const hasProfile = computed(() => Boolean(profile.value?.id || profile.value?.user_id))
const displayName = computed(() => profile.value?.ho_ten || auth.user?.name || 'Tài khoản')
const avatarLetter = computed(() => {
  const name = String(displayName.value || '').trim()
  return name ? name.charAt(0).toUpperCase() : 'U'
})

const trinhDoLabel = computed(() => {
  const td = profile.value?.trinh_do_hoc_van || {}
  const key = td.trinh_do_hoc_van
  if (key === 'khac') return td.trinh_do_khac || 'Khác'
  return trinhDoLabels[key] || '—'
})

const khuVucLabel = computed(() => {
  const key = profile.value?.vi_tri_dia_ly?.khu_vuc_muon_theo_hoc
  return khuVucLabels[key] || '—'
})

function tinhThanhLabel(value) {
  if (value == null || value === '') return '—'
  return tinhThanhLabels[String(value)] || String(value)
}

function formatDate(value) {
  if (!value) return '—'
  const [year, month, day] = String(value).slice(0, 10).split('-')
  if (!year || !month || !day) return String(value)
  return `${day}/${month}/${year}`
}

function formatNumber(value) {
  return new Intl.NumberFormat('vi-VN').format(Number(value) || 0)
}

onMounted(async () => {
  try {
    await auth.fetchMe()
  } catch {
    // Giữ phiên local nếu /auth/me tạm thời lỗi.
  }

  try {
    const res = await request({
      url: API_PUBLIC.NGUOI_DUNG.ME,
      loading: false,
      silent: true,
      errorFallback: 'Không tải được thông tin người dùng.',
    })
    if (!res.ok) {
      error.value = res.message || 'Không tải được thông tin người dùng.'
      return
    }
    profile.value = res.data
  } catch {
    error.value = 'Không tải được thông tin người dùng.'
  } finally {
    loading.value = false
  }
})
</script>

<style scoped>
.hoso {
  display: grid;
  gap: 1rem;
}

.hoso h1,
.hoso h2 {
  margin: 0;
}

.hoso h2 {
  margin-bottom: 0.85rem;
  font-size: 1.08rem;
}

.hoso-hero {
  display: grid;
  gap: 1.15rem;
}

.hoso-hero__identity {
  display: flex;
  align-items: center;
  gap: 0.9rem;
  min-width: 0;
}

.hoso-hero__copy {
  min-width: 0;
}

.hoso-hero__copy p {
  margin: 0.2rem 0 0;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.hoso-avatar {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 3.4rem;
  height: 3.4rem;
  flex-shrink: 0;
  border-radius: 50%;
  background: var(--accent-soft);
  color: var(--accent);
  font-size: 1.35rem;
  font-weight: 700;
}

.hoso-wallet {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 0.75rem;
  margin: 0;
}

.hoso-wallet div {
  padding: 0.85rem 1rem;
  border: 1px solid var(--border);
  border-radius: 12px;
  background: linear-gradient(180deg, rgba(231, 244, 236, 0.55) 0%, #fff 70%);
}

.hoso-wallet dt {
  margin: 0;
  color: var(--muted);
  font-size: 0.82rem;
  font-weight: 600;
}

.hoso-wallet dd {
  margin: 0.25rem 0 0;
  font-size: 1.25rem;
  font-weight: 700;
  letter-spacing: -0.03em;
}

.hoso-dl {
  display: grid;
  gap: 0.75rem 1.25rem;
  margin: 0;
}

.hoso-dl dt {
  margin: 0 0 0.15rem;
  color: var(--muted);
  font-size: 0.82rem;
  font-weight: 600;
}

.hoso-dl dd {
  margin: 0;
  font-weight: 600;
}

@media (min-width: 720px) {
  .hoso-dl {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}
</style>
