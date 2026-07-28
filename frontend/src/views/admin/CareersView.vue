<template>
  <div v-loading="loading" class="admin-crud">
    <div class="toolbar">
      <div>
        <h2>Quản lý nghề nghiệp</h2>
        <p class="desc">Danh sách từ API `/admin/careers`</p>
      </div>
      <CustomButton type="primary" :icon="Plus" @click="ElMessage.info('CRUD sẽ bổ sung sau.')">
        Thêm nghề nghiệp
      </CustomButton>
    </div>

    <CustomAlert v-if="error" :title="error" type="error" show-icon class="mb" />

    <CustomCard shadow="never">
      <CustomTable :data="items" stripe empty-text="Chưa có dữ liệu">
        <CustomTableColumn prop="id" label="ID" width="80" />
        <CustomTableColumn prop="name" label="Tên" min-width="180" />
        <CustomTableColumn prop="category" label="Danh mục" min-width="140" />
        <CustomTableColumn prop="slug" label="Slug" min-width="160" />
        <CustomTableColumn label="Thao tác" width="160" fixed="right">
          <template #default>
            <CustomButton link type="primary" @click="ElMessage.info('Sửa — placeholder')">
              Sửa
            </CustomButton>
            <CustomButton link type="danger" @click="ElMessage.info('Xóa — placeholder')">
              Xóa
            </CustomButton>
          </template>
        </CustomTableColumn>
      </CustomTable>
    </CustomCard>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import { ElMessage } from 'element-plus'
import { Plus } from '@element-plus/icons-vue'
import { adminApi } from '@/api'
import {
  CustomAlert,
  CustomButton,
  CustomCard,
  CustomTable,
  CustomTableColumn,
} from '@/components/element'

const items = ref([])
const loading = ref(true)
const error = ref(null)

onMounted(async () => {
  try {
    const { data } = await adminApi.careers.list()
    items.value = data.data
  } catch (err) {
    error.value = err.response?.data?.message || 'Không tải được danh sách.'
  } finally {
    loading.value = false
  }
})
</script>
