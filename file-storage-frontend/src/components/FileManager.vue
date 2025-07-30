<template>
  <div>
    <h2 class="text-xl font-bold mb-4">Subir archivo</h2>

    <!-- Selección de carpeta -->
    <div class="mb-2">
      <label>Carpeta:</label>
      <select v-model="folderId" class="border px-2 py-1">
        <option disabled value="">Seleccione carpeta</option>
        <option v-for="folder in folders" :key="folder.id" :value="folder.id">
          {{ folder.name }}
        </option>
      </select>
    </div>
    <!-- Fin -->

    <!-- Selección de archivo -->
    <div class="mb-2">
      <input type="file" @change="handleFileChange" />
    </div>
    <!-- Fin -->

    <!-- Barra de progreso -->
    <div v-if="uploadProgress > 0" class="w-full bg-gray-300 h-4 rounded mb-2">
      <div
        class="bg-blue-500 h-4 rounded text-xs text-white text-center"
        :style="{ width: uploadProgress + '%' }"
      >
        {{ uploadProgress }}%
      </div>
    </div>
    <!-- Fin -->

    <button
      @click="uploadFile"
      :disabled="!file || !folderId"
      class="bg-green-600 text-white px-4 py-1 rounded hover:bg-green-700"
    >
      Subir archivo
    </button>

    <!-- Vista previa -->
    <div class="mt-6">
      <h3 class="font-bold mb-2">Archivos subidos:</h3>
      <ul>
        <li v-for="file in files" :key="file.id" class="mb-2 flex items-center gap-3">
          <span v-if="isImage(file.mime_type)">
            <img :src="fileUrl(file.path)" alt="" class="w-10 h-10 object-cover" />
          </span>
          <span v-else>📄 {{ file.original_name }}</span>
        </li>
      </ul>
    </div>
    <!-- Fin -->
  </div>
</template>

<script>
import fileService from '../services/fileService';
import folderService from '../services/folderService';

export default {
  data() {
    return {
      file: null,
      folderId: '',
      folders: [],
      files: [],
      uploadProgress: 0,
    };
  },
  methods: {
    async fetchFolders() {
      const res = await folderService.getAll();
      this.folders = res.data;
    },
    async fetchFiles() {
      const res = await fileService.getAll();
      this.files = res.data;
    },
    handleFileChange(event) {
      this.file = event.target.files[0];
    },
    fileUrl(path) {
      return `http://localhost:8001/storage/${path}`;
    },
    isImage(mime) {
      return mime.startsWith('image/');
    },
    async uploadFile() {
      if (!this.file || !this.folderId) return;

      const formData = new FormData();
      formData.append('file', this.file);
      formData.append('folder_id', this.folderId);

      try {
        const res = await fileService.upload(formData, (progressEvent) => {
          const percent = Math.round((progressEvent.loaded * 100) / progressEvent.total);
          this.uploadProgress = percent;
        });

        this.file = null;
        this.uploadProgress = 0;
        await this.fetchFiles();
      } catch (err) {
        if (err.response && err.response.status === 422) {
            alert('Error de validación: ' + JSON.stringify(err.response.data.errors));
        } else {
            console.error('Error al subir archivo:', err);
        }
      }
    },
  },
  mounted() {
    this.fetchFolders();
    this.fetchFiles();
  },
};
</script>
