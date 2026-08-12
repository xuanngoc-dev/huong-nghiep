<template>
  <div class="quiz-fields-khao-sat">
    <p class="quiz-fields-khao-sat__lead">
      Thu thập thêm thông tin cá nhân để tư vấn hướng nghiệp chính xác hơn.
    </p>

    <form class="quiz-fields-khao-sat__form" novalidate @submit.prevent="onSubmit">
      <CustomRow :gutter="12" class="quiz-fields-khao-sat__grid">
        <CustomCol
          v-for="field in visibleFields"
          :key="field.key"
          :xs="12"
          :sm="12"
          :md="6"
          :lg="6"
          :xl="6"
        >
          <div class="quiz-fields-khao-sat__field">
            <span class="quiz-fields-khao-sat__label">
              <span
                class="quiz-fields-khao-sat__stt"
                :class="{ 'is-required': field.required }"
              >[{{ field.stt }}]</span>
              {{ field.label }}
            </span>

            <CustomSelect
              v-if="field.type === 'select'"
              v-model="form[field.key]"
              :placeholder="field.placeholder"
              clearable
              style="width: 100%"
            >
              <CustomOption
                v-for="opt in field.options"
                :key="opt.value ?? opt"
                :label="opt.label ?? opt"
                :value="opt.value ?? opt"
              />
            </CustomSelect>

            <CustomDatePicker
              v-else-if="field.type === 'date'"
              v-model="form[field.key]"
              type="date"
              value-format="YYYY-MM-DD"
              :placeholder="field.placeholder || 'Chọn ngày'"
              :disabled-date="disabledFutureDate"
              clearable
              style="width: 100%"
            />

            <CustomInput
              v-else-if="field.type === 'password'"
              v-model="form[field.key]"
              type="password"
              show-password
              :minlength="field.minlength"
              :autocomplete="field.autocomplete"
              :placeholder="field.placeholder"
            />

            <CustomSelect
              v-else-if="field.suggestions"
              v-model="form[field.key]"
              filterable
              allow-create
              default-first-option
              clearable
              :reserve-keyword="false"
              :placeholder="field.placeholder"
              style="width: 100%"
            >
              <CustomOption
                v-for="opt in field.suggestions"
                :key="`${field.key}-${opt}`"
                :label="opt"
                :value="opt"
              />
            </CustomSelect>

            <CustomInput
              v-else
              v-model="form[field.key]"
              :type="field.type === 'tel' ? 'text' : field.type"
              :maxlength="field.maxlength"
              :autocomplete="field.autocomplete"
              :placeholder="field.placeholder"
              :readonly="field.key === 'email' && hasUserAccount"
            />
          </div>
        </CustomCol>
      </CustomRow>

      <!-- Card: Trình độ học vấn -->
      <section class="quiz-fields-khao-sat__card" aria-labelledby="khao-sat-card-hoc-van">
        <header class="quiz-fields-khao-sat__card-head">
          <h2 id="khao-sat-card-hoc-van" class="quiz-fields-khao-sat__card-title">
            Trình độ học vấn
          </h2>
          <p class="quiz-fields-khao-sat__card-desc muted">
            Thông tin học tập và chứng chỉ phục vụ tư vấn xét tuyển.
          </p>
        </header>

        <CustomRow :gutter="12">
          <CustomCol :xs="12" :sm="12" :md="6" :lg="6" :xl="6">
            <div class="quiz-fields-khao-sat__field">
              <span class="quiz-fields-khao-sat__label">
                <span class="quiz-fields-khao-sat__stt">[10]</span>
                Trình độ học vấn
              </span>
              <CustomSelect
                v-model="form.trinh_do_hoc_van.trinh_do_hoc_van"
                placeholder="Chọn trình độ"
                clearable
                style="width: 100%"
              >
                <CustomOption
                  v-for="opt in trinhDoHocVanOptions"
                  :key="opt.value"
                  :label="opt.label"
                  :value="opt.value"
                />
              </CustomSelect>
            </div>
          </CustomCol>

          <CustomCol
            v-if="form.trinh_do_hoc_van.trinh_do_hoc_van === 'khac'"
            :xs="12"
            :sm="12"
            :md="6"
            :lg="6"
            :xl="6"
          >
            <div class="quiz-fields-khao-sat__field">
              <span class="quiz-fields-khao-sat__label">
                <span class="quiz-fields-khao-sat__stt">[10a]</span>
                Trình độ khác
              </span>
              <CustomInput
                v-model="form.trinh_do_hoc_van.trinh_do_khac"
                maxlength="255"
                placeholder="Nhập trình độ của bạn"
              />
            </div>
          </CustomCol>

          <CustomCol :xs="12" :sm="12" :md="6" :lg="6" :xl="6">
            <div class="quiz-fields-khao-sat__field">
              <span class="quiz-fields-khao-sat__label">
                <span class="quiz-fields-khao-sat__stt">[11]</span>
                Điểm trung bình học bạ
              </span>
              <CustomInput
                v-model="form.trinh_do_hoc_van.diem_trung_binh_to_hop_mon.diemHocBa"
                type="number"
                min="0"
                max="10"
                step="0.01"
                placeholder="Ví dụ: 8.5"
              />
            </div>
          </CustomCol>

          <CustomCol :xs="12" :sm="12" :md="6" :lg="6" :xl="6">
            <div class="quiz-fields-khao-sat__field">
              <span class="quiz-fields-khao-sat__label">
                <span class="quiz-fields-khao-sat__stt">[12]</span>
                Điểm thi THPT
              </span>
              <CustomInput
                v-model="form.trinh_do_hoc_van.diem_trung_binh_to_hop_mon.diemThiTHPT"
                type="number"
                min="0"
                max="30"
                step="0.01"
                placeholder="Tổng điểm tổ hợp"
              />
            </div>
          </CustomCol>
        </CustomRow>

        <div class="quiz-fields-khao-sat__cert-block">
          <div class="quiz-fields-khao-sat__cert-head">
            <span class="quiz-fields-khao-sat__label">
              <span class="quiz-fields-khao-sat__stt">[13]</span>
              Chứng chỉ tiếng Anh
            </span>
            <CustomTooltip content="Thêm chứng chỉ" placement="top">
              <CustomButton
                class="quiz-fields-khao-sat__cert-add"
                type="primary"
                circle
                :icon="Plus"
                aria-label="Thêm chứng chỉ"
                @click="addChungChi"
              />
            </CustomTooltip>
          </div>

          <div
            v-for="(item, index) in form.trinh_do_hoc_van.chung_chi_tieng_anh"
            :key="`chung-chi-${index}`"
            class="quiz-fields-khao-sat__cert-row"
          >
            <CustomRow :gutter="12">
              <CustomCol :xs="12" :sm="12" :md="10" :lg="10" :xl="10">
                <div class="quiz-fields-khao-sat__field">
                  <span class="quiz-fields-khao-sat__label muted">Tên chứng chỉ</span>
                  <CustomSelect
                    v-model="item.ten_chung_chi"
                    filterable
                    allow-create
                    default-first-option
                    clearable
                    placeholder="IELTS, TOEIC, TOEFL..."
                    style="width: 100%"
                  >
                    <CustomOption
                      v-for="opt in chungChiSuggestions"
                      :key="opt"
                      :label="opt"
                      :value="opt"
                    />
                  </CustomSelect>
                </div>
              </CustomCol>
              <CustomCol :xs="12" :sm="12" :md="10" :lg="10" :xl="10">
                <div class="quiz-fields-khao-sat__field">
                  <span class="quiz-fields-khao-sat__label muted">Điểm chứng chỉ</span>
                  <CustomInput
                    v-model="item.diem_chung_chi"
                    maxlength="50"
                    placeholder="Ví dụ: 6.5 hoặc 750"
                  />
                </div>
              </CustomCol>
              <CustomCol :xs="12" :sm="12" :md="4" :lg="4" :xl="4">
                <div class="quiz-fields-khao-sat__cert-remove-wrap">
                  <CustomTooltip
                    content="Xóa chứng chỉ"
                    placement="top"
                    :disabled="form.trinh_do_hoc_van.chung_chi_tieng_anh.length <= 1"
                  >
                    <CustomButton
                      class="quiz-fields-khao-sat__cert-remove"
                      type="danger"
                      circle
                      :icon="Delete"
                      aria-label="Xóa chứng chỉ"
                      :disabled="form.trinh_do_hoc_van.chung_chi_tieng_anh.length <= 1"
                      @click="removeChungChi(index)"
                    />
                  </CustomTooltip>
                </div>
              </CustomCol>
            </CustomRow>
          </div>
        </div>
      </section>

      <!-- Card: Sức khoẻ thể chất -->
      <section class="quiz-fields-khao-sat__card" aria-labelledby="khao-sat-card-suc-khoe">
        <header class="quiz-fields-khao-sat__card-head">
          <h2 id="khao-sat-card-suc-khoe" class="quiz-fields-khao-sat__card-title">
            Sức khoẻ thể chất
          </h2>
          <p class="quiz-fields-khao-sat__card-desc muted">
            Một số ngành có yêu cầu về thể trạng hoặc sức khoẻ đặc thù.
          </p>
        </header>

        <CustomRow :gutter="12">
          <CustomCol :xs="12" :sm="12" :md="6" :lg="6" :xl="6">
            <div class="quiz-fields-khao-sat__field">
              <span class="quiz-fields-khao-sat__label">
                <span class="quiz-fields-khao-sat__stt">[14]</span>
                Chiều cao (cm)
              </span>
              <CustomInput
                v-model="form.suc_khoe_the_chat.chieu_cao"
                type="number"
                min="0"
                max="250"
                step="0.1"
                placeholder="Ví dụ: 170"
              />
            </div>
          </CustomCol>
          <CustomCol :xs="12" :sm="12" :md="6" :lg="6" :xl="6">
            <div class="quiz-fields-khao-sat__field">
              <span class="quiz-fields-khao-sat__label">
                <span class="quiz-fields-khao-sat__stt">[15]</span>
                Cân nặng (kg)
              </span>
              <CustomInput
                v-model="form.suc_khoe_the_chat.can_nang"
                type="number"
                min="0"
                max="300"
                step="0.1"
                placeholder="Ví dụ: 60"
              />
            </div>
          </CustomCol>
          <CustomCol :xs="12" :sm="12" :md="12" :lg="12" :xl="12">
            <div class="quiz-fields-khao-sat__field">
              <span class="quiz-fields-khao-sat__label">
                <span class="quiz-fields-khao-sat__stt">[16]</span>
                Bệnh lý / ghi chú sức khoẻ
              </span>
              <CustomInput
                v-model="form.suc_khoe_the_chat.benh_ly"
                maxlength="500"
                placeholder="Để trống nếu không có"
              />
            </div>
          </CustomCol>
        </CustomRow>
      </section>

      <!-- Card: Khả năng tài chính -->
      <section class="quiz-fields-khao-sat__card" aria-labelledby="khao-sat-card-tai-chinh">
        <header class="quiz-fields-khao-sat__card-head">
          <h2 id="khao-sat-card-tai-chinh" class="quiz-fields-khao-sat__card-title">
            Khả năng tài chính
          </h2>
          <p class="quiz-fields-khao-sat__card-desc muted">
            Mức chi trả dự kiến giúp gợi ý trường / ngành phù hợp ngân sách.
          </p>
        </header>

        <CustomRow :gutter="12">
          <CustomCol :xs="12" :sm="12" :md="6" :lg="6" :xl="6">
            <div class="quiz-fields-khao-sat__field">
              <span class="quiz-fields-khao-sat__label">
                <span class="quiz-fields-khao-sat__stt">[17]</span>
                Khả năng chi trả cho 1 năm học (triệu/năm)
              </span>
              <CustomInput
                v-model="form.kha_nang_tai_chinh.chi_tra_mot_nam_hoc"
                type="number"
                min="0"
                step="0.1"
                placeholder="Ví dụ: 30"
              />
            </div>
          </CustomCol>
        </CustomRow>
      </section>

      <!-- Card: Vị trí địa lý -->
      <section class="quiz-fields-khao-sat__card" aria-labelledby="khao-sat-card-dia-ly">
        <header class="quiz-fields-khao-sat__card-head">
          <h2 id="khao-sat-card-dia-ly" class="quiz-fields-khao-sat__card-title">
            Vị trí địa lý
          </h2>
          <p class="quiz-fields-khao-sat__card-desc muted">
            Khu vực và tỉnh thành bạn muốn theo học hoặc đang sinh sống.
          </p>
        </header>

        <CustomRow :gutter="12">
          <CustomCol :xs="12" :sm="12" :md="6" :lg="6" :xl="6">
            <div class="quiz-fields-khao-sat__field">
              <span class="quiz-fields-khao-sat__label">
                <span class="quiz-fields-khao-sat__stt">[18]</span>
                Khu vực muốn theo học
              </span>
              <CustomSelect
                v-model="form.vi_tri_dia_ly.khu_vuc_muon_theo_hoc"
                placeholder="Chọn khu vực"
                clearable
                style="width: 100%"
              >
                <CustomOption
                  v-for="opt in khuVucHocOptions"
                  :key="opt.value"
                  :label="opt.label"
                  :value="opt.value"
                />
              </CustomSelect>
            </div>
          </CustomCol>
          <CustomCol :xs="12" :sm="12" :md="6" :lg="6" :xl="6">
            <div class="quiz-fields-khao-sat__field">
              <span class="quiz-fields-khao-sat__label">
                <span class="quiz-fields-khao-sat__stt">[19]</span>
                Tỉnh thành muốn theo học
              </span>
              <CustomSelect
                v-model="form.vi_tri_dia_ly.tinh_thanh_muon_theo_hoc"
                filterable
                placeholder="Chọn tỉnh thành"
                clearable
                style="width: 100%"
              >
                <CustomOption
                  v-for="opt in tinhThanhOptions"
                  :key="`hoc-${opt.value}`"
                  :label="opt.label"
                  :value="opt.value"
                />
              </CustomSelect>
            </div>
          </CustomCol>
          <CustomCol :xs="12" :sm="12" :md="6" :lg="6" :xl="6">
            <div class="quiz-fields-khao-sat__field">
              <span class="quiz-fields-khao-sat__label">
                <span class="quiz-fields-khao-sat__stt">[20]</span>
                Tỉnh thành đang sống
              </span>
              <CustomSelect
                v-model="form.vi_tri_dia_ly.tinh_thanh_dang_song"
                filterable
                placeholder="Chọn tỉnh thành"
                clearable
                style="width: 100%"
              >
                <CustomOption
                  v-for="opt in tinhThanhOptions"
                  :key="`song-${opt.value}`"
                  :label="opt.label"
                  :value="opt.value"
                />
              </CustomSelect>
            </div>
          </CustomCol>
        </CustomRow>
      </section>

      <p v-if="error" class="error-text" role="alert">{{ error }}</p>
      <p v-else-if="successMessage" class="quiz-fields-khao-sat__success" role="status">
        {{ successMessage }}
      </p>

      <div class="quiz-fields-khao-sat__actions">
        <button class="btn" type="submit" :disabled="saving">
          {{ saving ? 'Đang lưu...' : 'Lưu thông tin' }}
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import { Delete, Plus } from '@element-plus/icons-vue'
import { request } from '@/api'
import {
  CustomButton,
  CustomCol,
  CustomDatePicker,
  CustomInput,
  CustomOption,
  CustomRow,
  CustomSelect,
  CustomTooltip,
} from '@/components/element'
import { API_PUBLIC } from '@/constants/constant_api'
import { useAuthStore } from '@/stores/auth'

const emit = defineEmits(['submit', 'saved'])
const auth = useAuthStore()

/** Đã có hồ sơ thong_tin_nguoi_dung → ẩn mật khẩu, lưu = cập nhật. */
const isExistingProfile = ref(false)

const gioiTinhOptions = ['Nam', 'Nữ', 'Khác']

const danTocSuggestions = [
  'Kinh',
  'Tày',
  'Thái',
  'Mường',
  'Khmer',
  'Hoa',
  'Nùng',
  'HMông',
  'Dao',
  'Gia Rai',
]

const tonGiaoSuggestions = [
  'Không',
  'Phật giáo',
  'Công giáo',
  'Tin Lành',
  'Cao Đài',
  'Hòa Hảo',
  'Hồi giáo',
]

const trinhDoHocVanOptions = [
  { value: 'tot_nghiep_thpt', label: 'Tốt nghiệp THPT' },
  { value: 'tot_nghiep_thcs', label: 'Tốt nghiệp THCS' },
  { value: 'dang_hoc_thpt', label: 'Đang học THPT' },
  { value: 'trung_cap', label: 'Trung cấp' },
  { value: 'cao_dang', label: 'Cao đẳng' },
  { value: 'dai_hoc', label: 'Đại học' },
  { value: 'khac', label: 'Khác' },
]

const chungChiSuggestions = ['IELTS', 'TOEIC', 'TOEFL iBT', 'Cambridge', 'VSTEP']

const khuVucHocOptions = [
  { value: 'bac', label: 'Miền Bắc' },
  { value: 'trung', label: 'Miền Trung' },
  { value: 'nam', label: 'Miền Nam' },
]

/** Danh sách tỉnh thành (đồng bộ seeder hiện có). */
const tinhThanhOptions = [
  { value: '1', label: 'Thành phố Hà Nội' },
  { value: '4', label: 'Tỉnh Cao Bằng' },
  { value: '8', label: 'Tỉnh Tuyên Quang' },
  { value: '11', label: 'Tỉnh Điện Biên' },
  { value: '12', label: 'Tỉnh Lai Châu' },
  { value: '14', label: 'Tỉnh Sơn La' },
  { value: '15', label: 'Tỉnh Lào Cai' },
  { value: '19', label: 'Tỉnh Thái Nguyên' },
  { value: '20', label: 'Tỉnh Lạng Sơn' },
  { value: '22', label: 'Tỉnh Quảng Ninh' },
  { value: '24', label: 'Tỉnh Bắc Ninh' },
  { value: '25', label: 'Tỉnh Phú Thọ' },
  { value: '31', label: 'Tp Hải Phòng' },
  { value: '33', label: 'Tỉnh Hưng Yên' },
  { value: '37', label: 'Tỉnh Ninh Bình' },
  { value: '38', label: 'Tỉnh Thanh Hoá' },
  { value: '40', label: 'Tỉnh Nghệ An' },
  { value: '42', label: 'Tỉnh Hà Tĩnh' },
  { value: '44', label: 'Tỉnh Quảng Trị' },
  { value: '46', label: 'Thành phố Huế' },
  { value: '48', label: 'Tp Đà Nẵng' },
  { value: '51', label: 'Tỉnh Quảng Ngãi' },
  { value: '52', label: 'Tỉnh Gia Lai' },
  { value: '56', label: 'Tỉnh Khánh Hoà' },
  { value: '66', label: 'Tỉnh Đắk Lắk' },
  { value: '68', label: 'Tỉnh Lâm Đồng' },
  { value: '75', label: 'Thành phố Đồng Nai' },
  { value: '79', label: 'Tp Hồ Chí Minh' },
  { value: '80', label: 'Tỉnh Tây Ninh' },
  { value: '82', label: 'Tỉnh Đồng Tháp' },
  { value: '86', label: 'Tỉnh Vĩnh Long' },
  { value: '91', label: 'Tỉnh An Giang' },
  { value: '92', label: 'Tp Cần Thơ' },
  { value: '96', label: 'Tỉnh Cà Mau' },
]

const maxNgaySinh = new Date().toISOString().slice(0, 10)

const fields = [
  {
    stt: 1,
    key: 'ho_ten',
    label: 'Họ tên',
    type: 'text',
    required: true,
    maxlength: 255,
    autocomplete: 'name',
    placeholder: 'Nhập họ và tên',
  },
  {
    stt: 2,
    key: 'gioi_tinh',
    label: 'Giới tính',
    type: 'select',
    required: false,
    options: gioiTinhOptions,
    placeholder: 'Chọn giới tính',
  },
  {
    stt: 3,
    key: 'ngay_sinh',
    label: 'Ngày sinh',
    type: 'date',
    required: false,
    placeholder: 'Chọn ngày sinh',
  },
  {
    stt: 4,
    key: 'email',
    label: 'Email',
    type: 'email',
    required: true,
    maxlength: 255,
    autocomplete: 'email',
    placeholder: 'vidu@email.com',
  },
  {
    stt: 5,
    key: 'so_dien_thoai',
    label: 'Số điện thoại',
    type: 'tel',
    required: false,
    maxlength: 30,
    autocomplete: 'tel',
    inputmode: 'tel',
    placeholder: '09xxxxxxxx',
  },
  {
    stt: 6,
    key: 'mat_khau',
    label: 'Mật khẩu',
    type: 'password',
    required: true,
    minlength: 8,
    autocomplete: 'new-password',
    placeholder: 'Tối thiểu 8 ký tự',
  },
  {
    stt: 7,
    key: 'xac_nhan_mat_khau',
    label: 'Xác nhận mật khẩu',
    type: 'password',
    required: true,
    minlength: 8,
    autocomplete: 'new-password',
    placeholder: 'Nhập lại mật khẩu',
  },
  {
    stt: 8,
    key: 'dan_toc',
    label: 'Dân tộc',
    type: 'text',
    required: false,
    maxlength: 100,
    suggestions: danTocSuggestions,
    placeholder: 'Ví dụ: Kinh',
  },
  {
    stt: 9,
    key: 'ton_giao',
    label: 'Tôn giáo',
    type: 'text',
    required: false,
    maxlength: 100,
    suggestions: tonGiaoSuggestions,
    placeholder: 'Ví dụ: Không',
  },
]

const passwordFieldKeys = new Set(['mat_khau', 'xac_nhan_mat_khau'])

/** Đã có tài khoản users (đăng nhập hoặc đã lưu hồ sơ) → không bắt nhập mật khẩu. */
const hasUserAccount = computed(
  () => isExistingProfile.value || Boolean(auth.isAuthenticated),
)

const visibleFields = computed(() =>
  hasUserAccount.value
    ? fields.filter((field) => !passwordFieldKeys.has(field.key))
    : fields,
)

function emptyChungChi() {
  return { ten_chung_chi: undefined, diem_chung_chi: '' }
}

const form = reactive({
  ho_ten: '',
  gioi_tinh: undefined,
  ngay_sinh: undefined,
  email: '',
  so_dien_thoai: '',
  mat_khau: '',
  xac_nhan_mat_khau: '',
  dan_toc: undefined,
  ton_giao: undefined,
  trinh_do_hoc_van: {
    trinh_do_hoc_van: undefined,
    trinh_do_khac: '',
    chung_chi_tieng_anh: [emptyChungChi()],
    diem_trung_binh_to_hop_mon: {
      diemHocBa: '',
      diemThiTHPT: '',
    },
  },
  suc_khoe_the_chat: {
    chieu_cao: '',
    can_nang: '',
    benh_ly: '',
  },
  kha_nang_tai_chinh: {
    chi_tra_mot_nam_hoc: '',
  },
  vi_tri_dia_ly: {
    khu_vuc_muon_theo_hoc: undefined,
    tinh_thanh_muon_theo_hoc: undefined,
    tinh_thanh_dang_song: undefined,
  },
})

const error = ref('')
const successMessage = ref('')
const saving = ref(false)

function disabledFutureDate(date) {
  const today = new Date()
  today.setHours(23, 59, 59, 999)
  return date.getTime() > today.getTime()
}

function toFormText(value) {
  return value == null ? '' : String(value)
}

/** Giá trị select/date: trống → undefined (Element Plus hiển thị đúng). */
function toFormSelectValue(value) {
  if (value == null || value === '') return undefined
  return String(value)
}

function toFormNumber(value) {
  return value == null || value === '' ? '' : value
}

function applyProfileToForm(data) {
  if (!data || typeof data !== 'object') return

  form.ho_ten = toFormText(data.ho_ten)
  form.gioi_tinh = toFormSelectValue(data.gioi_tinh)
  form.ngay_sinh = toFormSelectValue(data.ngay_sinh)
  form.email = toFormText(data.email)
  form.so_dien_thoai = toFormText(data.so_dien_thoai)
  form.dan_toc = toFormSelectValue(data.dan_toc)
  form.ton_giao = toFormSelectValue(data.ton_giao)
  form.mat_khau = ''
  form.xac_nhan_mat_khau = ''

  const td = data.trinh_do_hoc_van || {}
  form.trinh_do_hoc_van.trinh_do_hoc_van = toFormSelectValue(td.trinh_do_hoc_van)
  form.trinh_do_hoc_van.trinh_do_khac = toFormText(td.trinh_do_khac)

  const diem = td.diem_trung_binh_to_hop_mon || {}
  form.trinh_do_hoc_van.diem_trung_binh_to_hop_mon.diemHocBa = toFormNumber(diem.diemHocBa)
  form.trinh_do_hoc_van.diem_trung_binh_to_hop_mon.diemThiTHPT = toFormNumber(diem.diemThiTHPT)

  const chungChi = Array.isArray(td.chung_chi_tieng_anh) ? td.chung_chi_tieng_anh : []
  form.trinh_do_hoc_van.chung_chi_tieng_anh = chungChi.length
    ? chungChi.map((item) => ({
        ten_chung_chi: toFormSelectValue(item?.ten_chung_chi),
        diem_chung_chi: toFormText(item?.diem_chung_chi),
      }))
    : [emptyChungChi()]

  const sk = data.suc_khoe_the_chat || {}
  form.suc_khoe_the_chat.chieu_cao = toFormNumber(sk.chieu_cao)
  form.suc_khoe_the_chat.can_nang = toFormNumber(sk.can_nang)
  form.suc_khoe_the_chat.benh_ly = toFormText(sk.benh_ly)

  const tc = data.kha_nang_tai_chinh || {}
  form.kha_nang_tai_chinh.chi_tra_mot_nam_hoc = toFormNumber(tc.chi_tra_mot_nam_hoc)

  const vt = data.vi_tri_dia_ly || {}
  form.vi_tri_dia_ly.khu_vuc_muon_theo_hoc = toFormSelectValue(vt.khu_vuc_muon_theo_hoc)
  form.vi_tri_dia_ly.tinh_thanh_muon_theo_hoc = toFormSelectValue(vt.tinh_thanh_muon_theo_hoc)
  form.vi_tri_dia_ly.tinh_thanh_dang_song = toFormSelectValue(vt.tinh_thanh_dang_song)
}

async function loadExistingProfile() {
  if (!auth.isAuthenticated || !auth.user?.email) return

  // Prefill tối thiểu từ tài khoản đăng nhập nếu chưa có hồ sơ khảo sát.
  if (!form.ho_ten && auth.user.name) form.ho_ten = auth.user.name
  if (!form.email) form.email = auth.user.email
  if (!form.so_dien_thoai && auth.user.so_dien_thoai) {
    form.so_dien_thoai = auth.user.so_dien_thoai
  }

  const res = await request({
    url: API_PUBLIC.NGUOI_DUNG.ME,
    loading: false,
    silent: true,
    errorFallback: 'Không tải được thông tin cá nhân.',
  })

  if (!res.ok || !res.data) return

  applyProfileToForm(res.data)
  isExistingProfile.value = true
}

onMounted(() => {
  loadExistingProfile()
})

function addChungChi() {
  form.trinh_do_hoc_van.chung_chi_tieng_anh.push(emptyChungChi())
}

function removeChungChi(index) {
  if (form.trinh_do_hoc_van.chung_chi_tieng_anh.length <= 1) return
  form.trinh_do_hoc_van.chung_chi_tieng_anh.splice(index, 1)
}

function toNullableNumber(value) {
  if (value === '' || value == null) return null
  const n = Number(value)
  return Number.isFinite(n) ? n : null
}

function buildTrinhDoHocVan() {
  const td = form.trinh_do_hoc_van
  const chungChi = td.chung_chi_tieng_anh
    .map((item) => ({
      ten_chung_chi: String(item.ten_chung_chi || '').trim(),
      diem_chung_chi: String(item.diem_chung_chi || '').trim(),
    }))
    .filter((item) => item.ten_chung_chi || item.diem_chung_chi)

  const diemHocBa = toNullableNumber(td.diem_trung_binh_to_hop_mon.diemHocBa)
  const diemThiTHPT = toNullableNumber(td.diem_trung_binh_to_hop_mon.diemThiTHPT)
  const trinhDo = td.trinh_do_hoc_van || null
  const trinhDoKhac =
    trinhDo === 'khac' ? String(td.trinh_do_khac || '').trim() || null : null

  if (!trinhDo && !chungChi.length && diemHocBa == null && diemThiTHPT == null) {
    return null
  }

  return {
    trinh_do_hoc_van: trinhDo,
    ...(trinhDoKhac ? { trinh_do_khac: trinhDoKhac } : {}),
    chung_chi_tieng_anh: chungChi,
    diem_trung_binh_to_hop_mon: {
      diemHocBa,
      diemThiTHPT,
    },
  }
}

function buildSucKhoeTheChat() {
  const sk = form.suc_khoe_the_chat
  const payload = {
    chieu_cao: toNullableNumber(sk.chieu_cao),
    can_nang: toNullableNumber(sk.can_nang),
    benh_ly: String(sk.benh_ly || '').trim() || null,
  }
  if (payload.chieu_cao == null && payload.can_nang == null && !payload.benh_ly) {
    return null
  }
  return payload
}

function buildKhaNangTaiChinh() {
  const chiTra = toNullableNumber(form.kha_nang_tai_chinh.chi_tra_mot_nam_hoc)
  if (chiTra == null) return null
  return { chi_tra_mot_nam_hoc: chiTra }
}

function buildViTriDiaLy() {
  const vt = form.vi_tri_dia_ly
  const payload = {
    khu_vuc_muon_theo_hoc: vt.khu_vuc_muon_theo_hoc || null,
    tinh_thanh_muon_theo_hoc: vt.tinh_thanh_muon_theo_hoc || null,
    tinh_thanh_dang_song: vt.tinh_thanh_dang_song || null,
  }
  if (
    !payload.khu_vuc_muon_theo_hoc &&
    !payload.tinh_thanh_muon_theo_hoc &&
    !payload.tinh_thanh_dang_song
  ) {
    return null
  }
  return payload
}

function validate() {
  const hoTen = String(form.ho_ten || '').trim()
  const email = String(form.email || '').trim()
  const soDienThoai = String(form.so_dien_thoai || '').trim()

  if (!hoTen) return 'Vui lòng nhập họ tên.'
  if (!email) return 'Vui lòng nhập email.'
  if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
    return 'Email không hợp lệ.'
  }
  if (soDienThoai && !/^[0-9+\s()-]{8,30}$/.test(soDienThoai)) {
    return 'Số điện thoại không hợp lệ.'
  }
  if (!hasUserAccount.value) {
    if (!form.mat_khau) return 'Vui lòng nhập mật khẩu.'
    if (form.mat_khau.length < 8) return 'Mật khẩu phải có tối thiểu 8 ký tự.'
    if (!form.xac_nhan_mat_khau) return 'Vui lòng xác nhận mật khẩu.'
    if (form.mat_khau !== form.xac_nhan_mat_khau) {
      return 'Mật khẩu xác nhận không khớp.'
    }
  }
  if (form.ngay_sinh && form.ngay_sinh > maxNgaySinh) {
    return 'Ngày sinh không hợp lệ.'
  }
  if (
    form.trinh_do_hoc_van.trinh_do_hoc_van === 'khac' &&
    !String(form.trinh_do_hoc_van.trinh_do_khac || '').trim()
  ) {
    return 'Vui lòng nhập trình độ học vấn khác.'
  }
  return ''
}

/**
 * Payload cho API khảo sát cá nhân.
 * Backend lưu email / SĐT / mật khẩu vào users trước, rồi tạo/cập nhật thong_tin_nguoi_dung.
 */
function toPayload() {
  const payload = {
    ho_ten: String(form.ho_ten || '').trim(),
    gioi_tinh: form.gioi_tinh || null,
    ngay_sinh: form.ngay_sinh || null,
    email: String(form.email || '').trim(),
    so_dien_thoai: String(form.so_dien_thoai || '').trim() || null,
    dan_toc: String(form.dan_toc || '').trim() || null,
    ton_giao: String(form.ton_giao || '').trim() || null,
    trinh_do_hoc_van: buildTrinhDoHocVan(),
    suc_khoe_the_chat: buildSucKhoeTheChat(),
    kha_nang_tai_chinh: buildKhaNangTaiChinh(),
    vi_tri_dia_ly: buildViTriDiaLy(),
  }

  if (!hasUserAccount.value) {
    payload.mat_khau = form.mat_khau
    payload.mat_khau_confirmation = form.xac_nhan_mat_khau
  }

  return payload
}

async function onSubmit() {
  if (saving.value) return

  error.value = ''
  successMessage.value = ''

  const message = validate()
  if (message) {
    error.value = message
    return
  }

  const payload = toPayload()
  emit('submit', payload)

  saving.value = true
  try {
    const res = await request({
      url: API_PUBLIC.NGUOI_DUNG.STORE,
      body: payload,
      successFallback: 'Lưu thông tin cá nhân thành công.',
      errorFallback: 'Không lưu được thông tin cá nhân.',
    })

    if (!res.ok) {
      error.value = res.message || 'Không lưu được thông tin cá nhân.'
      return
    }

    successMessage.value = res.message || 'Lưu thông tin cá nhân thành công.'
    isExistingProfile.value = true
    form.mat_khau = ''
    form.xac_nhan_mat_khau = ''
    emit('saved', res.data)
  } finally {
    saving.value = false
  }
}

defineExpose({
  form,
  validate,
  toPayload,
  isExistingProfile,
})
</script>

<style scoped>
.quiz-fields-khao-sat__lead {
  margin: 0 0 1.15rem;
  color: var(--muted);
  font-size: 0.98rem;
  line-height: 1.55;
}

.quiz-fields-khao-sat__form {
  display: grid;
  gap: 1.1rem;
}

.quiz-fields-khao-sat__grid {
  width: 100%;
}

.quiz-fields-khao-sat__card {
  margin-top: 0.35rem;
  padding: 1rem 1rem 0.35rem;
  border: 1px solid var(--border);
  border-radius: calc(var(--radius) + 2px);
  background:
    linear-gradient(180deg, rgba(231, 244, 236, 0.45) 0%, #fff 42%);
}

.quiz-fields-khao-sat__card-head {
  margin-bottom: 0.85rem;
}

.quiz-fields-khao-sat__card-title {
  margin: 0 0 0.3rem;
  font-size: 1.05rem;
  font-weight: 600;
  letter-spacing: -0.02em;
}

.quiz-fields-khao-sat__card-desc {
  margin: 0;
  font-size: 0.9rem;
  line-height: 1.45;
}

.quiz-fields-khao-sat__field {
  display: grid;
  gap: 0.35rem;
  font-size: 0.95rem;
  min-width: 0;
  margin-bottom: 0.75rem;
}

.quiz-fields-khao-sat__label {
  display: inline-flex;
  align-items: baseline;
  gap: 0.35rem;
  line-height: 1.35;
}

.quiz-fields-khao-sat__stt {
  flex-shrink: 0;
  font-weight: 600;
  color: var(--muted);
}

.quiz-fields-khao-sat__stt.is-required {
  color: var(--danger);
}

.quiz-fields-khao-sat__field :deep(.el-input),
.quiz-fields-khao-sat__field :deep(.el-select),
.quiz-fields-khao-sat__field :deep(.el-date-editor) {
  width: 100%;
}

/* Không để style global (.form input) đè lên input nội bộ Element Plus. */
.quiz-fields-khao-sat__form :deep(.el-input__wrapper input),
.quiz-fields-khao-sat__form :deep(.el-select__input),
.quiz-fields-khao-sat__form :deep(.el-date-editor input) {
  border: none !important;
  padding: 0 !important;
  background: transparent !important;
  border-radius: 0 !important;
  box-shadow: none !important;
}

.quiz-fields-khao-sat__actions .btn:disabled {
  opacity: 0.65;
  cursor: not-allowed;
  transform: none;
}

.quiz-fields-khao-sat__cert-block {
  margin: 0.15rem 0 0.85rem;
  padding-top: 0.35rem;
  border-top: 1px dashed var(--border);
}

.quiz-fields-khao-sat__cert-head {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  justify-content: space-between;
  gap: 0.65rem;
  margin-bottom: 0.65rem;
}

.quiz-fields-khao-sat__cert-add,
.quiz-fields-khao-sat__cert-remove {
  flex-shrink: 0;
  font-weight: 700;
}

.quiz-fields-khao-sat__cert-add :deep(.el-icon),
.quiz-fields-khao-sat__cert-remove :deep(.el-icon) {
  font-size: 1.15rem;
  font-weight: 700;
}

.quiz-fields-khao-sat__cert-add :deep(svg),
.quiz-fields-khao-sat__cert-remove :deep(svg) {
  stroke-width: 1.75;
}

.quiz-fields-khao-sat__cert-row + .quiz-fields-khao-sat__cert-row {
  margin-top: 0.15rem;
}

.quiz-fields-khao-sat__cert-remove-wrap {
  display: flex;
  align-items: flex-end;
  justify-content: flex-start;
  height: 100%;
  min-height: 4.4rem;
  padding-bottom: 0.75rem;
}

.quiz-fields-khao-sat__success {
  margin: 0;
  color: var(--accent);
  font-size: 0.95rem;
}

.quiz-fields-khao-sat__actions {
  display: flex;
  flex-wrap: wrap;
  gap: var(--space-btn);
  margin-top: 0.15rem;
}
</style>
