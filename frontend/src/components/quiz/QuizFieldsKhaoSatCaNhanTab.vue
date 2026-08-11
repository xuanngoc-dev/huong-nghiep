<template>
  <div class="quiz-fields-khao-sat">
    <p class="quiz-fields-khao-sat__lead">
      Thu thập thêm thông tin cá nhân để tư vấn hướng nghiệp chính xác hơn.
    </p>

    <form class="form quiz-fields-khao-sat__form" novalidate @submit.prevent="onSubmit">
      <CustomRow :gutter="12" class="quiz-fields-khao-sat__grid">
        <CustomCol
          v-for="field in fields"
          :key="field.key"
          :xs="12"
          :sm="12"
          :md="6"
          :lg="6"
          :xl="6"
        >
          <label class="quiz-fields-khao-sat__field" :for="`khao-sat-${field.key}`">
            <span class="quiz-fields-khao-sat__label">
              <span
                class="quiz-fields-khao-sat__stt"
                :class="{ 'is-required': field.required }"
              >[{{ field.stt }}]</span>
              {{ field.label }}
            </span>

            <select
              v-if="field.type === 'select'"
              :id="`khao-sat-${field.key}`"
              v-model="form[field.key]"
              :name="field.key"
              :required="field.required"
            >
              <option value="">{{ field.placeholder }}</option>
              <option v-for="opt in field.options" :key="opt.value ?? opt" :value="opt.value ?? opt">
                {{ opt.label ?? opt }}
              </option>
            </select>

            <div
              v-else-if="field.type === 'password'"
              class="quiz-fields-khao-sat__password"
            >
              <input
                :id="`khao-sat-${field.key}`"
                v-model="form[field.key]"
                :type="passwordVisible[field.key] ? 'text' : 'password'"
                :name="field.key"
                :required="field.required"
                :minlength="field.minlength"
                :autocomplete="field.autocomplete"
                :placeholder="field.placeholder"
              />
              <button
                type="button"
                class="quiz-fields-khao-sat__password-toggle"
                :aria-label="passwordVisible[field.key] ? 'Ẩn mật khẩu' : 'Hiện mật khẩu'"
                :aria-pressed="passwordVisible[field.key]"
                @click="togglePassword(field.key)"
              >
                <el-icon :size="18">
                  <Hide v-if="passwordVisible[field.key]" />
                  <View v-else />
                </el-icon>
              </button>
            </div>

            <input
              v-else
              :id="`khao-sat-${field.key}`"
              v-model="form[field.key]"
              :type="field.type"
              :name="field.key"
              :required="field.required"
              :minlength="field.minlength"
              :maxlength="field.maxlength"
              :autocomplete="field.autocomplete"
              :inputmode="field.inputmode"
              :list="field.list"
              :max="field.max"
              :placeholder="field.placeholder"
            />

            <datalist v-if="field.list" :id="field.list">
              <option v-for="opt in field.suggestions" :key="opt" :value="opt" />
            </datalist>
          </label>
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
            <label class="quiz-fields-khao-sat__field" for="khao-sat-trinh-do">
              <span class="quiz-fields-khao-sat__label">
                <span class="quiz-fields-khao-sat__stt">[10]</span>
                Trình độ học vấn
              </span>
              <select id="khao-sat-trinh-do" v-model="form.trinh_do_hoc_van.trinh_do_hoc_van">
                <option value="">Chọn trình độ</option>
                <option
                  v-for="opt in trinhDoHocVanOptions"
                  :key="opt.value"
                  :value="opt.value"
                >
                  {{ opt.label }}
                </option>
              </select>
            </label>
          </CustomCol>

          <CustomCol
            v-if="form.trinh_do_hoc_van.trinh_do_hoc_van === 'khac'"
            :xs="12"
            :sm="12"
            :md="6"
            :lg="6"
            :xl="6"
          >
            <label class="quiz-fields-khao-sat__field" for="khao-sat-trinh-do-khac">
              <span class="quiz-fields-khao-sat__label">
                <span class="quiz-fields-khao-sat__stt">[10a]</span>
                Trình độ khác
              </span>
              <input
                id="khao-sat-trinh-do-khac"
                v-model="form.trinh_do_hoc_van.trinh_do_khac"
                type="text"
                maxlength="255"
                placeholder="Nhập trình độ của bạn"
              />
            </label>
          </CustomCol>

          <CustomCol :xs="12" :sm="12" :md="6" :lg="6" :xl="6">
            <label class="quiz-fields-khao-sat__field" for="khao-sat-diem-hoc-ba">
              <span class="quiz-fields-khao-sat__label">
                <span class="quiz-fields-khao-sat__stt">[11]</span>
                Điểm trung bình học bạ
              </span>
              <input
                id="khao-sat-diem-hoc-ba"
                v-model="form.trinh_do_hoc_van.diem_trung_binh_to_hop_mon.diemHocBa"
                type="number"
                min="0"
                max="10"
                step="0.01"
                inputmode="decimal"
                placeholder="Ví dụ: 8.5"
              />
            </label>
          </CustomCol>

          <CustomCol :xs="12" :sm="12" :md="6" :lg="6" :xl="6">
            <label class="quiz-fields-khao-sat__field" for="khao-sat-diem-thi-thpt">
              <span class="quiz-fields-khao-sat__label">
                <span class="quiz-fields-khao-sat__stt">[12]</span>
                Điểm thi THPT
              </span>
              <input
                id="khao-sat-diem-thi-thpt"
                v-model="form.trinh_do_hoc_van.diem_trung_binh_to_hop_mon.diemThiTHPT"
                type="number"
                min="0"
                max="30"
                step="0.01"
                inputmode="decimal"
                placeholder="Tổng điểm tổ hợp"
              />
            </label>
          </CustomCol>
        </CustomRow>

        <div class="quiz-fields-khao-sat__cert-block">
          <div class="quiz-fields-khao-sat__cert-head">
            <span class="quiz-fields-khao-sat__label">
              <span class="quiz-fields-khao-sat__stt">[13]</span>
              Chứng chỉ tiếng Anh
            </span>
            <button
              class="btn btn-outline quiz-fields-khao-sat__cert-add"
              type="button"
              @click="addChungChi"
            >
              Thêm chứng chỉ
            </button>
          </div>

          <div
            v-for="(item, index) in form.trinh_do_hoc_van.chung_chi_tieng_anh"
            :key="`chung-chi-${index}`"
            class="quiz-fields-khao-sat__cert-row"
          >
            <CustomRow :gutter="12">
              <CustomCol :xs="12" :sm="12" :md="10" :lg="10" :xl="10">
                <label class="quiz-fields-khao-sat__field" :for="`khao-sat-cc-ten-${index}`">
                  <span class="quiz-fields-khao-sat__label muted">Tên chứng chỉ</span>
                  <input
                    :id="`khao-sat-cc-ten-${index}`"
                    v-model="item.ten_chung_chi"
                    type="text"
                    maxlength="100"
                    list="quiz-chung-chi-list"
                    placeholder="IELTS, TOEIC, TOEFL..."
                  />
                </label>
              </CustomCol>
              <CustomCol :xs="12" :sm="12" :md="10" :lg="10" :xl="10">
                <label class="quiz-fields-khao-sat__field" :for="`khao-sat-cc-diem-${index}`">
                  <span class="quiz-fields-khao-sat__label muted">Điểm chứng chỉ</span>
                  <input
                    :id="`khao-sat-cc-diem-${index}`"
                    v-model="item.diem_chung_chi"
                    type="text"
                    maxlength="50"
                    placeholder="Ví dụ: 6.5 hoặc 750"
                  />
                </label>
              </CustomCol>
              <CustomCol :xs="12" :sm="12" :md="4" :lg="4" :xl="4">
                <div class="quiz-fields-khao-sat__cert-remove-wrap">
                  <button
                    class="btn btn-outline quiz-fields-khao-sat__cert-remove"
                    type="button"
                    :disabled="form.trinh_do_hoc_van.chung_chi_tieng_anh.length <= 1"
                    @click="removeChungChi(index)"
                  >
                    Xóa
                  </button>
                </div>
              </CustomCol>
            </CustomRow>
          </div>
          <datalist id="quiz-chung-chi-list">
            <option v-for="opt in chungChiSuggestions" :key="opt" :value="opt" />
          </datalist>
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
            <label class="quiz-fields-khao-sat__field" for="khao-sat-chieu-cao">
              <span class="quiz-fields-khao-sat__label">
                <span class="quiz-fields-khao-sat__stt">[14]</span>
                Chiều cao (cm)
              </span>
              <input
                id="khao-sat-chieu-cao"
                v-model="form.suc_khoe_the_chat.chieu_cao"
                type="number"
                min="0"
                max="250"
                step="0.1"
                inputmode="decimal"
                placeholder="Ví dụ: 170"
              />
            </label>
          </CustomCol>
          <CustomCol :xs="12" :sm="12" :md="6" :lg="6" :xl="6">
            <label class="quiz-fields-khao-sat__field" for="khao-sat-can-nang">
              <span class="quiz-fields-khao-sat__label">
                <span class="quiz-fields-khao-sat__stt">[15]</span>
                Cân nặng (kg)
              </span>
              <input
                id="khao-sat-can-nang"
                v-model="form.suc_khoe_the_chat.can_nang"
                type="number"
                min="0"
                max="300"
                step="0.1"
                inputmode="decimal"
                placeholder="Ví dụ: 60"
              />
            </label>
          </CustomCol>
          <CustomCol :xs="12" :sm="12" :md="12" :lg="12" :xl="12">
            <label class="quiz-fields-khao-sat__field" for="khao-sat-benh-ly">
              <span class="quiz-fields-khao-sat__label">
                <span class="quiz-fields-khao-sat__stt">[16]</span>
                Bệnh lý / ghi chú sức khoẻ
              </span>
              <input
                id="khao-sat-benh-ly"
                v-model="form.suc_khoe_the_chat.benh_ly"
                type="text"
                maxlength="500"
                placeholder="Để trống nếu không có"
              />
            </label>
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
            <label class="quiz-fields-khao-sat__field" for="khao-sat-chi-tra">
              <span class="quiz-fields-khao-sat__label">
                <span class="quiz-fields-khao-sat__stt">[17]</span>
                Khả năng chi trả cho 1 năm học (triệu/năm)
              </span>
              <input
                id="khao-sat-chi-tra"
                v-model="form.kha_nang_tai_chinh.chi_tra_mot_nam_hoc"
                type="number"
                min="0"
                step="0.1"
                inputmode="decimal"
                placeholder="Ví dụ: 30"
              />
            </label>
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
            <label class="quiz-fields-khao-sat__field" for="khao-sat-khu-vuc">
              <span class="quiz-fields-khao-sat__label">
                <span class="quiz-fields-khao-sat__stt">[18]</span>
                Khu vực muốn theo học
              </span>
              <select id="khao-sat-khu-vuc" v-model="form.vi_tri_dia_ly.khu_vuc_muon_theo_hoc">
                <option value="">Chọn khu vực</option>
                <option
                  v-for="opt in khuVucHocOptions"
                  :key="opt.value"
                  :value="opt.value"
                >
                  {{ opt.label }}
                </option>
              </select>
            </label>
          </CustomCol>
          <CustomCol :xs="12" :sm="12" :md="6" :lg="6" :xl="6">
            <label class="quiz-fields-khao-sat__field" for="khao-sat-tinh-hoc">
              <span class="quiz-fields-khao-sat__label">
                <span class="quiz-fields-khao-sat__stt">[19]</span>
                Tỉnh thành muốn theo học
              </span>
              <select id="khao-sat-tinh-hoc" v-model="form.vi_tri_dia_ly.tinh_thanh_muon_theo_hoc">
                <option value="">Chọn tỉnh thành</option>
                <option
                  v-for="opt in tinhThanhOptions"
                  :key="`hoc-${opt.value}`"
                  :value="opt.value"
                >
                  {{ opt.label }}
                </option>
              </select>
            </label>
          </CustomCol>
          <CustomCol :xs="12" :sm="12" :md="6" :lg="6" :xl="6">
            <label class="quiz-fields-khao-sat__field" for="khao-sat-tinh-song">
              <span class="quiz-fields-khao-sat__label">
                <span class="quiz-fields-khao-sat__stt">[20]</span>
                Tỉnh thành đang sống
              </span>
              <select id="khao-sat-tinh-song" v-model="form.vi_tri_dia_ly.tinh_thanh_dang_song">
                <option value="">Chọn tỉnh thành</option>
                <option
                  v-for="opt in tinhThanhOptions"
                  :key="`song-${opt.value}`"
                  :value="opt.value"
                >
                  {{ opt.label }}
                </option>
              </select>
            </label>
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
import { reactive, ref } from 'vue'
import { Hide, View } from '@element-plus/icons-vue'
import { request } from '@/api'
import { CustomCol, CustomRow } from '@/components/element'
import { API_PUBLIC } from '@/constants/constant_api'

const emit = defineEmits(['submit', 'saved'])

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
    autocomplete: 'bday',
    max: maxNgaySinh,
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
    list: 'quiz-dan-toc-list',
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
    list: 'quiz-ton-giao-list',
    suggestions: tonGiaoSuggestions,
    placeholder: 'Ví dụ: Không',
  },
]

function emptyChungChi() {
  return { ten_chung_chi: '', diem_chung_chi: '' }
}

const form = reactive({
  ho_ten: '',
  gioi_tinh: '',
  ngay_sinh: '',
  email: '',
  so_dien_thoai: '',
  mat_khau: '',
  xac_nhan_mat_khau: '',
  dan_toc: '',
  ton_giao: '',
  trinh_do_hoc_van: {
    trinh_do_hoc_van: '',
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
    khu_vuc_muon_theo_hoc: '',
    tinh_thanh_muon_theo_hoc: '',
    tinh_thanh_dang_song: '',
  },
})

const error = ref('')
const successMessage = ref('')
const saving = ref(false)
const passwordVisible = reactive({
  mat_khau: false,
  xac_nhan_mat_khau: false,
})

function togglePassword(key) {
  passwordVisible[key] = !passwordVisible[key]
}

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
  if (!form.ho_ten?.trim()) return 'Vui lòng nhập họ tên.'
  if (!form.email?.trim()) return 'Vui lòng nhập email.'
  if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email.trim())) {
    return 'Email không hợp lệ.'
  }
  if (form.so_dien_thoai && !/^[0-9+\s()-]{8,30}$/.test(form.so_dien_thoai.trim())) {
    return 'Số điện thoại không hợp lệ.'
  }
  if (!form.mat_khau) return 'Vui lòng nhập mật khẩu.'
  if (form.mat_khau.length < 8) return 'Mật khẩu phải có tối thiểu 8 ký tự.'
  if (!form.xac_nhan_mat_khau) return 'Vui lòng xác nhận mật khẩu.'
  if (form.mat_khau !== form.xac_nhan_mat_khau) {
    return 'Mật khẩu xác nhận không khớp.'
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

/** Payload khớp API / bảng nguoi_dung. */
function toPayload() {
  return {
    ho_ten: form.ho_ten.trim(),
    gioi_tinh: form.gioi_tinh || null,
    ngay_sinh: form.ngay_sinh || null,
    email: form.email.trim(),
    so_dien_thoai: form.so_dien_thoai.trim() || null,
    mat_khau: form.mat_khau,
    mat_khau_confirmation: form.xac_nhan_mat_khau,
    dan_toc: form.dan_toc.trim() || null,
    ton_giao: form.ton_giao.trim() || null,
    trinh_do_hoc_van: buildTrinhDoHocVan(),
    suc_khoe_the_chat: buildSucKhoeTheChat(),
    kha_nang_tai_chinh: buildKhaNangTaiChinh(),
    vi_tri_dia_ly: buildViTriDiaLy(),
  }
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
    emit('saved', res.data)
  } finally {
    saving.value = false
  }
}

defineExpose({
  form,
  validate,
  toPayload,
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

.quiz-fields-khao-sat__form :is(input, select) {
  width: 100%;
  padding: 0.75rem 0.9rem;
  border: 1px solid var(--border);
  border-radius: 10px;
  background: #fff;
  color: var(--text);
  font: inherit;
}

.quiz-fields-khao-sat__password {
  position: relative;
  display: block;
}

.quiz-fields-khao-sat__password input {
  padding-right: 2.75rem;
}

.quiz-fields-khao-sat__password-toggle {
  position: absolute;
  top: 50%;
  right: 0.45rem;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 2rem;
  height: 2rem;
  padding: 0;
  border: 0;
  border-radius: 8px;
  background: transparent;
  color: var(--muted);
  cursor: pointer;
  transform: translateY(-50%);
}

.quiz-fields-khao-sat__password-toggle:hover {
  color: var(--text);
  background: rgba(31, 122, 76, 0.08);
}

.quiz-fields-khao-sat__password-toggle:focus-visible {
  outline: 2px solid var(--accent);
  outline-offset: 1px;
}

.quiz-fields-khao-sat__actions .btn:disabled {
  opacity: 0.65;
  cursor: not-allowed;
  transform: none;
}

.quiz-fields-khao-sat__form select {
  appearance: none;
  background-image:
    linear-gradient(45deg, transparent 50%, var(--muted) 50%),
    linear-gradient(135deg, var(--muted) 50%, transparent 50%);
  background-position:
    calc(100% - 18px) calc(50% - 3px),
    calc(100% - 12px) calc(50% - 3px);
  background-size: 6px 6px;
  background-repeat: no-repeat;
  padding-right: 2.2rem;
}

.quiz-fields-khao-sat__form :is(input, select):focus {
  outline: 2px solid color-mix(in srgb, var(--accent) 35%, transparent);
  outline-offset: 1px;
  border-color: var(--accent);
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
  padding: 0.4rem 0.85rem;
  font-size: 0.88rem;
}

.quiz-fields-khao-sat__cert-row + .quiz-fields-khao-sat__cert-row {
  margin-top: 0.15rem;
}

.quiz-fields-khao-sat__cert-remove-wrap {
  display: flex;
  align-items: flex-end;
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
