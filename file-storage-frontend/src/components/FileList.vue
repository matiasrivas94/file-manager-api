<template>
  <div>
    <!-- Contador -->
    <div class="mb-2 text-sm text-gray-600">
      Mostrando {{ files.length }} archivos de {{ total }}
    </div>

    <!-- Lista de archivos -->
    <ul v-if="files.length > 0" class="space-y-2">
      <FileItem
        v-for="file in files"
        :key="file.id"
        :file="file"
        @select="$emit('select', $event)"
        @delete="$emit('delete', $event)"
        @restore="$emit('restore', $event)"
        @force-delete="$emit('force-delete', $event)"
      />
    </ul>

    <p v-else class="text-gray-500 text-center mt-4">No hay archivos</p>
  </div>
</template>

<script>
import FileItem from "./FileItem.vue";

export default {
  name: "FileList",
  props: {
    files: { type: Array, required: true },
    total: { type: Number, required: true }
  },
  components: { FileItem }
};
</script>
