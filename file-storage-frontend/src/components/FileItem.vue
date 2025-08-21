<template>
  <li 
    class="flex justify-between items-center border rounded p-2"
    :class="{ 'opacity-50': file.deleted_at }"
  >
    <div>
      <span>{{ file.original_name }}</span>
      <span v-if="file.deleted_at" class="text-red-500 ml-2">(Eliminado)</span>
    </div>

    <div class="flex space-x-2">
      <!-- Restaurar -->
      <button
        v-if="file.deleted_at"
        @click="$emit('restore', file.id)"
        class="bg-blue-500 text-white px-2 py-1 rounded hover:bg-blue-600"
      >
        Restaurar
      </button>

      <!-- Eliminar -->
      <button
        v-if="!file.deleted_at"
        @click="$emit('delete', file.id)"
        class="bg-red-500 text-white px-2 py-1 rounded hover:bg-red-600"
      >
        Eliminar
      </button>

      <!-- Borrado permanente -->
      <button
        v-if="file.deleted_at"
        @click="$emit('force-delete', file.id)"
        class="bg-gray-700 text-white px-2 py-1 rounded hover:bg-gray-800"
      >
        Borrar definitivamente
      </button>
    </div>
  </li>
</template>

<script>
export default {
  name: "FileItem",
  props: {
    file: { type: Object, required: true }
  }
};
</script>
