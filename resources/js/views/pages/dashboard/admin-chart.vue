<script setup>
import axiosIns from '@/plugins/axios'
import { getLineChartSimpleConfig } from '@core/libs/apex-chart/apexCharConfig'
import { watchEffect } from 'vue'
import VueApexCharts from 'vue3-apexcharts'
import { useTheme } from 'vuetify'

const vuetifyTheme = useTheme()

const loading = ref(false)
const statistics = ref([])
const selected = ref('weekly')
const customDateRange = ref('')

const chartConfig = computed(() => {
  const config = getLineChartSimpleConfig(vuetifyTheme.current.value)

  config.xaxis.categories = Object.keys(statistics.value)

  return config
})

const series = computed(() => [
  {
    name: 'Count',
    data: Object.values(statistics.value),
  },
])

watchEffect(() => {
  let selectedQuery = '?filter=weekly'    // default weekly
  if (selected.value === 'monthly') {
    selectedQuery = '?filter=monthly'
  } else if (selected.value === 'custom') {
    const [startDate, endDate] = customDateRange.value.split(' to ')
    if (!startDate || !endDate) return
    selectedQuery = `?filter=custom&start_date=${startDate}&end_date=${endDate}`
  }
  loading.value = true
  axiosIns.get(`/api/v1/admin/dashboard/total-statistic-graph${selectedQuery}`).then(response => {
    statistics.value = response.data.data.patient
    loading.value = false
  })
})
</script>

<template>
  <VCol 
    cols="12"
    md="8"
  >
    <VCard :loading="loading">
      <VCardItem class="d-flex flex-wrap justify-space-between gap-4">
        <VCardTitle>Patient Statistics</VCardTitle>

        <template #append>
          <VBtnToggle
            v-model="selected"
            density="compact"
            color="primary"
            variant="outlined"
            divided
            mandatory
            :disabled="loading"
          >
            <VBtn value="weekly">
              Weekly
            </VBtn>
            <VBtn value="monthly">
              Monthly
            </VBtn>
            <VBtn value="custom">
              Custom
            </VBtn>
          </VBtnToggle>
        </template>
      </VCardItem>

      <VExpandTransition>
        <VCardText v-if="selected === 'custom'">
          <AppDateTimePicker
            v-model="customDateRange"
            :label="null"
            placeholder="Select date range"
            :config="{ mode: 'range' }"
          />
        </VCardText>
      </VExpandTransition>

      <VCardText>
        <VueApexCharts
          type="line"
          height="300"
          :options="chartConfig"
          :series="series"
        />
      </VCardText>
    </VCard>
  </VCol>
</template>

<style lang="scss">
@use "@core-scss/template/libs/apex-chart.scss";
</style>
