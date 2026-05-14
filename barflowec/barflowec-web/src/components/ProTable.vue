<script setup>
/**
 * ProTable — Tabla profesional responsive
 *
 * Características:
 * - Scroll horizontal en mobile
 * - Sticky header con sombra
 * - Hover y striped rows
 * - Estado de carga con skeleton
 * - Estado vacío personalizable
 * - Columnas adaptativas
 */
defineProps({
  columns: {
    type: Array,
    required: true,
    // { key, label, class, sortable, render }
  },
  rows: {
    type: Array,
    default: () => [],
  },
  loading: {
    type: Boolean,
    default: false,
  },
  emptyMessage: {
    type: String,
    default: 'No hay registros.',
  },
  emptyIcon: {
    type: String,
    default: '📋',
  },
})
</script>

<template>
  <div>
    <!-- Loading state -->
    <div
      v-if="loading"
      class="space-y-4 py-6"
    >
      <div
        v-for="i in 4"
        :key="i"
        class="h-12 animate-pulse rounded-xl bg-slate-100"
        :style="{ width: `${85 + i * 3}%` }"
      />
    </div>

    <!-- Empty state -->
    <div
      v-else-if="rows.length === 0"
      class="flex flex-col items-center justify-center rounded-xl border border-dashed border-slate-200 p-10 text-center"
    >
      <span class="mb-3 text-4xl">{{ emptyIcon }}</span>
      <p class="text-sm font-medium text-slate-500">{{ emptyMessage }}</p>
      <slot name="empty-actions" />
    </div>

    <!-- Table -->
    <div v-else class="overflow-x-auto rounded-xl border border-slate-100">
      <table class="w-full text-left text-sm">
        <thead>
          <tr class="border-b border-slate-100 bg-slate-50/80 text-slate-500">
            <th
              v-for="col in columns"
              :key="col.key || col.label"
              class="sticky top-0 whitespace-nowrap bg-slate-50/80 px-4 py-3 text-xs font-bold uppercase tracking-wider"
              :class="[
                col.align === 'right' ? 'text-right' : 'text-left',
                col.class || '',
              ]"
            >
              {{ col.label }}
            </th>
            <th
              v-if="$slots.actions"
              class="sticky right-0 bg-slate-50/80 px-4 py-3 text-right text-xs font-bold uppercase tracking-wider"
            >
              Acciones
            </th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-50">
          <tr
            v-for="(row, idx) in rows"
            :key="row.id || idx"
            class="transition-colors hover:bg-purple-50/50"
          >
            <td
              v-for="col in columns"
              :key="col.key || col.label"
              class="px-4 py-3.5 text-slate-700"
              :class="[
                col.align === 'right' ? 'text-right' : 'text-left',
                col.cellClass || '',
              ]"
            >
              <!-- Renderizado personalizado por columna -->
              <slot
                :name="`cell-${col.key}`"
                :row="row"
                :value="row[col.key]"
              >
                {{ row[col.key] ?? '-' }}
              </slot>
            </td>
            <td
              v-if="$slots.actions"
              class="sticky right-0 bg-white px-4 py-3.5"
            >
              <div class="flex items-center justify-end gap-2">
                <slot
                  name="actions"
                  :row="row"
                />
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
