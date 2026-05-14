<script setup>
/**
 * ToastNotification — Notificaciones temporales
 * Props: message, type ('success' | 'error' | 'info')
 * Auto-dismiss después de 4 segundos
 */
import { computed, onMounted, ref } from 'vue'

const props = defineProps({
  message: { type: String, default: '' },
  type: { type: String, default: 'success' },
})

const emit = defineEmits(['close'])
const visible = ref(false)

const classes = computed(() => ({
  'bg-emerald-50 text-emerald-700 border-emerald-200': props.type === 'success',
  'bg-red-50 text-red-700 border-red-200': props.type === 'error',
  'bg-purple-50 text-purple-700 border-purple-200': props.type === 'info',
}))

const icons = {
  success: '✅',
  error: '❌',
  info: '💡',
}

onMounted(() => {
  visible.value = true
  setTimeout(() => emit('close'), 4000)
})
</script>

<template>
  <Transition
    enter-active-class="transition-all duration-300 ease-out"
    enter-from-class="translate-x-full opacity-0"
    leave-active-class="transition-all duration-200 ease-in"
    leave-to-class="translate-x-full opacity-0"
  >
    <div
      v-if="visible && message"
      class="fixed right-4 top-4 z-50 flex items-center gap-3 rounded-xl border px-5 py-4 shadow-lg"
      :class="classes"
    >
      <span>{{ icons[type] }}</span>
      <span class="text-sm font-medium">{{ message }}</span>
      <button
        class="ml-2 text-current opacity-50 hover:opacity-100"
        @click="emit('close')"
      >
        ✕
      </button>
    </div>
  </Transition>
</template>
