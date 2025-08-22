<template>
  <div class="p-4">
    <h2 class="text-xl font-bold mb-4">Gestor de Carpetas</h2>
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
