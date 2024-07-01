<script setup>
import axiosIns from '@/plugins/axios'
import { showToast } from '@/plugins/toast'
import { emailValidator, requiredValidator } from '@validators'
import { onMounted } from 'vue'
import { VDataTable } from 'vuetify/labs/VDataTable'

const editDialog = ref(false)
const deleteDialog = ref(false)

const refForm = ref()

const defaultItem = ref({
  id: '',
  user_id: '',
  fullname: '',
  address: '',
  phone: '',
  emergency_phone: '',
  gender: '',
  age: '',
})

const email = ref('')
const password = ref('')

const editedItem = ref(defaultItem.value)
const editedIndex = ref(-1) 

const headers = [
  { title: 'Fullname', key: 'fullname' },
  { title: 'Addres', key: 'address' },
  { title: 'Phone', key: 'phone' },
  { title: 'Emergency Phone', key: 'emergency_phone' },
  { title: 'Gender', key: 'gender' },
  { title: 'Age', key: 'age' },
  { title: 'Actions', key: 'actions', sortable: false },
]

const sortBy = ref([{ key: 'fullname', order: 'asc' }])

const search = ref('')
const doctors = ref([])

const searchedDoctors = computed(() => {
  return doctors.value.filter(doctor => {
    return Object.values(doctor).some(value => {
      return String(value).toLowerCase().includes(search.value.toLowerCase())
    })
  })
})

const editLoading = ref(false)
const deleteLoading = ref(false)

const editItemShow = item => {
  editedIndex.value = doctors.value.indexOf(item)
  editedItem.value = { ...item }
  editDialog.value = true
}

const deleteItemShow = item => {
  editedIndex.value = doctors.value.indexOf(item)
  editedItem.value = { ...item }
  deleteDialog.value = true
}

const addItemShow = () => {
  editedIndex.value = -1
  editedItem.value = { ...defaultItem.value }
  editDialog.value = true
}

const close = () => {
  editDialog.value = false
  editedIndex.value = -1
  editedItem.value = { ...defaultItem.value }
  email.value = ''
  password.value = ''
  refForm.value.reset()
}

const closeDelete = () => {
  deleteDialog.value = false
  editedIndex.value = -1
  editedItem.value = { ...defaultItem.value }
  email.value = ''
  password.value = ''
}

const save = async () => {
  const validation = await refForm.value.validate()
  if (!validation.valid) return

  editLoading.value = true

  if (editedIndex.value > -1) {
    await axiosIns.put(`/api/v1/admin/doctor/${editedItem.value.id}`, editedItem.value)
      .then (response => {
        showToast(response.data.message, 'success')
      })
      .catch(error => {
        showToast(error.response.data.error, 'error')
      })
  } else {
    await axiosIns.post('/api/v1/admin/doctor', {
      ...editedItem.value,
      email: email.value,
      password: password.value,
    })
      .then (response => {
        showToast(response.data.message, 'success')
      })
      .catch(error => {
        showToast(error.response.data.error, 'error')
      })
  }
  await loadDoctors()
  editLoading.value = false
  close()
}

const deleteItemConfirm = async () => {
  deleteLoading.value = true
  await axiosIns.delete(`/api/v1/admin/doctor/${editedItem.value.id}`)
    .then (response => {
      showToast(response.data.message, 'success')
    })
    .catch(error => {
      showToast(error.response.data.error, 'error')
    })
  await loadDoctors()
  deleteLoading.value = false
  closeDelete()
}

const loading = ref(false)

const loadDoctors = async () => {
  loading.value = true
  await axiosIns.get('/api/v1/admin/doctor')
    .then(response => {
      doctors.value = response.data.data
    })
    .catch(error => {
      console.log(error)
    })
  loading.value = false
}

onMounted(loadDoctors)
</script>

<template>
  <div>
    <VCard
      title="Doctor"
      :loading="loading"
    >
      <template #append>
        <div>
          <VBtn
            color="primary"
            @click="addItemShow"
          >
            Add Doctor
            <VIcon
              end
              icon="tabler-plus"
            />
          </VBtn>
        </div>
      </template>
      <VCardText density="compact">
        <AppTextField
          v-model="search"
          prepend-inner-icon="tabler-search"
          :append-inner-icon="search.length > 0 ? 'tabler-x' : null"
          placeholder="Search"
          class="mb-2"
          @click:append-inner="search=''"
        />
        <VDataTable
          v-model:sort-by="sortBy"
          :headers="headers"
          :items="searchedDoctors"
          :items-per-page="10"
          density="compact"
        >
          <template #item.actions="{ item }">
            <div class="d-flex gap-1">
              <RouterLink :to="`/admin/doctor/${item.raw.id}`">
                <IconBtn>
                  <VIcon
                    icon="mdi-information-outline"
                    color="primary"
                  />
                </IconBtn>
              </RouterLink> 
              <IconBtn @click="editItemShow(item.raw)">
                <VIcon
                  icon="mdi-pencil-outline"
                  color="primary"
                />
              </IconBtn>
              <IconBtn @click="deleteItemShow(item.raw)">
                <VIcon
                  icon="mdi-delete-outline"
                  color="error"
                />
              </IconBtn>
            </div>
          </template>
        </VDataTable>
      </VCardText>
    </VCard>

    <!-- DIALOG EDIT -->
    <VDialog
      v-model="editDialog"
      max-width="600px"
      persistent
    >
      <VCard>
        <VForm
          ref="refForm"
          @submit.prevent="save"
        >
          <VCardTitle>
            <span class="headline">
              {{ editedIndex > -1 ? "Edit Doctor" : "Add Doctor" }}
            </span>
          </VCardTitle>
          <VCardText>
            <VContainer>
              <VRow dense>
                <VCol cols="12">
                  <AppTextField
                    v-model="editedItem.fullname"
                    label="Fullname"
                    :rules="[requiredValidator]"
                  />
                </VCol>
                <VCol cols="12">
                  <AppTextarea
                    v-model="editedItem.address"
                    label="Address"
                    rows="2"
                    auto-grow
                    :rules="[requiredValidator]"
                  />
                </VCol>
                <VCol cols="6">
                  <AppTextField
                    v-model="editedItem.phone"
                    label="Phone"
                    :rules="[requiredValidator]"
                  />
                </VCol>
                <VCol cols="6">
                  <AppTextField
                    v-model="editedItem.emergency_phone"
                    label="Emergency Phone"
                    :rules="[requiredValidator]"
                  />
                </VCol>
                <VCol cols="6">
                  <AppSelect
                    v-model="editedItem.gender"
                    :items="['male', 'female']"
                    label="Gender"
                    density="compact"
                    :rules="[requiredValidator]"
                  />
                </VCol>
                <VCol cols="6">
                  <AppTextField
                    v-model="editedItem.age"
                    type="number"
                    label="Age"
                    :rules="[requiredValidator]"
                  />
                </VCol>
                <!-- FIELD FOR NEW ITEM -->
                <VCol
                  v-if="editedIndex == -1"
                  cols="12"
                >
                  <AppTextField
                    v-model="email"
                    type="email"
                    label="Email"
                    :rules="[requiredValidator, emailValidator]"
                  />
                </VCol>
                <VCol
                  v-if="editedIndex == -1"
                  cols="12"
                >
                  <AppTextField
                    v-model="password"
                    type="password"
                    label="Password"
                    :rules="[requiredValidator]"
                  />
                </VCol>
              </VRow>
            </VContainer>
          </VCardText>

          <VCardActions>
            <VSpacer />

            <VBtn
              color="primary"
              variant="outlined"
              :disabled="editLoading"
              type="button"
              @click="close"
            >
              Cancel
            </VBtn>

            <VBtn
              color="success"
              variant="elevated"
              :loading="editLoading"
              :disabled="editLoading"
              type="submit"
            >
              Save
            </VBtn>
          </VCardActions>
        </VForm>
      </VCard>
    </VDialog>

    <!-- DIALOG DELETE -->
    <VDialog
      v-model="deleteDialog"
      max-width="500px"
    >
      <VCard>
        <VCardText>
          Are you sure you want to delete {{ editedItem.fullname }}?
        </VCardText>

        <VCardActions>
          <VSpacer />

          <VBtn
            color="primary"
            variant="outlined"
            :disabled="deleteLoading"
            type="button"
            @click="closeDelete"
          >
            Cancel
          </VBtn>

          <VBtn
            color="error"
            variant="elevated"
            :loading="deleteLoading"
            :disabled="deleteLoading"
            @click="deleteItemConfirm"
          >
            Delete
          </VBtn>

          <VSpacer />
        </VCardActions>
      </VCard>
    </VDialog>
  </div>
</template>

<route lang="yaml">
meta:
  requiresAuth: true
  allowedRoles: ['admin']
</route>

