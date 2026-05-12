<script setup>
/**
 * ImportExcel — Componente de importación drag & drop
 *
 * Prepara la estructura para futura importación masiva.
 * Soporta:
 * - Drag & drop de archivos .xlsx/.csv
 * - Vista previa de datos cargados
 * - Mapeo de columnas
 */
import { ref } from 'vue'

const emit = defineEmits(['imported'])

const props = defineProps({
  accept: {
    type: String,
    default: '.xlsx,.csv',
  },
  maxSize: {
    type: Number,
    default: 5, // MB
  },
})

const dragging = ref(false)
const file = ref(null)
const preview = ref(null)

function onDragOver(e) {
  e.preventDefault()
  dragging.value = true
}

function onDragLeave() {
  dragging.value = false
}

function onDrop(e) {
  e.preventDefault()
  dragging.value = false
  const droppedFile = e.dataTransfer.files[0]
  if (droppedFile) handleFile(droppedFile)
}

function onFileSelect(e) {
  const selectedFile = e.target.files[0]
  if (selectedFile) handleFile(selectedFile)
}

function handleFile(f) {
  // Validar tamaño
  if (f.size > props.maxSize * 1024 * 1024) {
    alert(`El archivo excede el tamaño máximo de ${props.maxSize}MB`)
    return
  }

  file.value = f
  preview.value = {
    name: f.name,
    size: `${(f.size / 1024).toFixed(1)} KB`,
    type: f.name.endsWith('.csv') ? 'CSV' : 'Excel',
  }
}

function removeFile() {
  file.value = null
  preview.value = null
}

function submitImport() {
  emit('imported', file.value)
  // Aquí se integrará la lógica de importación real
  alert('Funcionalidad de importación próximamente.')
  removeFile()
}
</script>

<template>
  <div class="space-y-4">
    <!-- Drop zone -->
    <div
      class="relative cursor-pointer rounded-xl border-2 border-dashed p-8 text-center transition-colors"
      :class="
        dragging
          ? 'border-purple-400 bg-purple-50'
          : 'border-slate-200 hover:border-purple-300 hover:bg-slate-50'
      "
      @dragover="onDragOver"
      @dragleave="onDragLeave"
      @drop="onDrop"
      @click="$refs.fileInput.click()"
    >
      <input
        ref="fileInput"
        type="file"
        :accept="accept"
        class="hidden"
        @change="onFileSelect"
      />

      <span class="mb-2 block text-4xl">
        {{ dragging ? '📂' : '📤' }}
      </span>
      <p class="text-sm font-medium text-slate-600">
        {{ dragging ? 'Suelta el archivo aquí' : 'Arrastra o selecciona un archivo Excel/CSV' }}
      </p>
      <p class="mt-1 text-xs text-slate-400">
        Máximo {{ maxSize }}MB · Formatos: {{ accept }}
      </p>
    </div>

    <!-- File preview -->
    <div
      v-if="preview"
      class="flex items-center justify-between rounded-xl border border-slate-200 bg-slate-50 p-4"
    >
      <div class="flex items-center gap-3">
        <span class="text-2xl">📄</span>
        <div>
          <p class="text-sm font-medium text-slate-700">{{ preview.name }}</p>
          <p class="text-xs text-slate-400">{{ preview.size }} · {{ preview.type }}</p>
        </div>
      </div>
      <div class="flex gap-2">
        <button
          class="rounded-lg bg-purple-600 px-4 py-2 text-xs font-bold text-white hover:bg-purple-700"
          @click.stop="submitImport"
        >
          Importar
        </button>
        <button
          class="rounded-lg border border-slate-200 px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100"
          @click.stop="removeFile"
        >
          Quitar
        </button>
      </div>
    </div>
  </div>
</template>
