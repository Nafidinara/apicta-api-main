<script setup>
import { onBeforeUnmount, onMounted, watchEffect } from 'vue'
import WaveSurfer from 'wavesurfer.js'
// eslint-disable-next-line import/extensions
import TimelinePlugin from 'wavesurfer.js/dist/plugins/timeline.js'

const props = defineProps({
  url: {
    type: String,
    required: true,
  },
})

const zoom = ref(50)

const audioEl = ref()
const waveEl = ref()

onMounted(()=>{
  const audio = new Audio()

  audio.controls = true
  audio.src = props.url
  
  const topTimeline = TimelinePlugin.create({
    height: 20,
    insertPosition: 'beforebegin',
    timeInterval: 0.1,
    primaryLabelInterval: 5,
    secondaryLabelInterval: 1,
    style: {
      fontSize: '8px',
      color: '#2D5B88',
    },
  })

  const ws = WaveSurfer.create({
    container: waveEl.value,
    waveColor: '#4F4A85',
    progressColor: '#383351',
    url: props.url,
    media: audio,
    plugins: [topTimeline],
    minPxPerSec: 50,
    normalize: true,
  })

  ws.once('decode', () => {
    watchEffect(() => {
      ws.zoom(zoom.value)
    })
  })

  audio.style.width = '100%'
  audioEl.value.appendChild(audio)

  onBeforeUnmount(() => {
    ws.destroy()
    audioEl.value.removeChild(audio)
  })
})
</script>

<template>
  <div>
    <VSlider
      v-model="zoom"
      label="Zoom"
      min="50"
      max="200"
      step="1"
      class="mx-0"
      prepend-icon="tabler-circle-minus"
      append-icon="tabler-circle-plus"
      @click:prepend="zoom = Math.max(zoom - 10, 50)"
      @click:append="zoom = Math.min(zoom + 10, 200)"
    />
    <div ref="waveEl" />
    <div
      ref="audioEl"
      style="width: 100%;"
    />
  </div>
</template>
