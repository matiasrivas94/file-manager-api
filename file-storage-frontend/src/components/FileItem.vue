<template>
  <li 
    class="flex justify-between items-center border rounded p-2"
    :class="{ 'opacity-50': file.deleted_at }"
     @click="$emit('select', file)"
  >
    <div class="flex items-center gap-3">
      <!-- Icono/imagen del archivo -->
      <span v-if="isImage(file.mime_type)">
        <img :src="fileUrl(file.path)" alt="" class="w-10 h-10 object-cover rounded" />
      </span>
      <span v-else class="text-2xl">📄</span>

      <!-- Info del archivo -->
      <div>
        <div class="font-medium">{{ file.original_name }}</div>
        <div class="text-sm text-gray-500">{{ formatSize(file.size) }}</div>
        <span v-if="file.deleted_at" class="text-red-500 text-sm">(Eliminado)</span>
      </div>
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
  },
  methods: {
    isImage(mime) {
      return mime && mime.startsWith('image/');
    },
    formatSize(bytes) {
      const sizes = ['Bytes', 'KB', 'MB', 'GB'];
      if (bytes === 0) return '0 Byte';
      const i = parseInt(Math.floor(Math.log(bytes) / Math.log(1024)));
      return Math.round(bytes / Math.pow(1024, i), 2) + ' ' + sizes[i];
    },
    fileUrl(path) {
      return `http://localhost:8001/storage/${path}`;
    }
  }
};
</script>
