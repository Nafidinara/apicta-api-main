<script setup>
import axiosIns from '@/plugins/axios'
import { computed, onMounted } from 'vue'
import AdminChart from './admin-chart.vue'

const totalStatistics = ref(null)

const patientsStatistics = computed(() => {
  return [
    {
      title: 'Total',
      stats: totalStatistics.value?.total_patient,
      icon: 'tabler-users',
      color: 'primary',
    },
    {
      title: 'Today',
      stats: totalStatistics.value?.total_doctor,
      icon: 'tabler-24-hours',
      color: 'info',
    },
    {
      title: 'Normal',
      stats: totalStatistics.value?.total_patient_normal,
      icon: 'tabler-checks',
      color: 'success',
    },
    {
      title: 'Myocardial Infarction',
      stats: totalStatistics.value?.total_patient_mi,
      icon: 'tabler-alert-triangle',
      color: 'error',
    },
    {
      title: 'Male',
      stats: totalStatistics.value?.total_patient_male,
      icon: 'tabler-gender-male',
      color: 'primary',
    },
    {
      title: 'Female',
      stats: totalStatistics.value?.total_patient_female,
      icon: 'tabler-gender-female',
      color: 'info',
    },
  ]
})

const doctorStatistics = computed(()=>{
  return [
    {
      title: 'Total',
      stats: totalStatistics.value?.total_doctor,
      icon: 'tabler-stethoscope',
      color: 'info',
    },
  ]
})

onMounted(async () => {
  axiosIns.get('/api/v1/admin/dashboard/total-statistic').then(response => {
    totalStatistics.value = response.data.data
  })
})
</script>

<template>
  <VCol
    cols="12"
    sm="6"
    md="8"
  >
    <VCard title="Patients">
      <VCardText>
        <VRow>
          <VCol
            v-for="item in patientsStatistics"
            :key="item.title"
            cols="6"
            md="4"
          >
            <div class="d-flex align-center gap-4">
              <VAvatar
                :color="item.color"
                variant="tonal"
                size="64"
                rounded="sm"
              >
                <VIcon
                  :icon="item.icon"
                  size="32"
                />
              </VAvatar>

              <div class="d-flex flex-column">
                <span class="text-h4 font-weight-medium">{{ item.stats }}</span>
                <span class="text-body">
                  {{ item.title }}
                </span>
              </div>
            </div>
          </VCol>
        </VRow>
      </VCardText>
    </VCard>
  </VCol>
  <VCol
    cols="12"
    md="4"
  >
    <VCard title="Doctors">
      <VCardText>
        <VRow>
          <VCol
            v-for="item in doctorStatistics"
            :key="item.title"
            cols="12"
          >
            <div class="d-flex align-center gap-4">
              <VAvatar
                :color="item.color"
                variant="tonal"
                size="64"
                rounded="sm"
              >
                <VIcon
                  :icon="item.icon"
                  size="32"
                />
              </VAvatar>

              <div class="d-flex flex-column">
                <span class="text-h4 font-weight-medium">{{ item.stats }}</span>
                <span class="text-body">
                  {{ item.title }}
                </span>
              </div>
            </div>
          </VCol>
        </VRow>
      </VCardText>
    </VCard>
  </VCol>
  <AdminChart />
</template>

