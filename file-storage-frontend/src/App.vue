<template>
  <!-- <main class="min-h-screen bg-gray-100 p-6">
    <FolderManager />
  </main> -->
  <div class="p-4">
    <h1 class="text-2xl font-bold mb-4">Gestor de Archivos</h1>
    <FolderManager />
    <hr class="my-6" />
    <FileManager />
  </div>
</template>

<script>
import FolderManager from './components/FolderManager.vue';
import FileManager from './components/FileManager.vue';

export default {
  components: { FolderManager, FileManager },
  data() {
    return {
      selectedFolderId: null
    };
  },
  methods: {
    setFolder(id) {
      this.selectedFolderId = id;
    }
  }
};
</script>
