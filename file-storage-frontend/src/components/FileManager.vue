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

    <!-- Botón de subir -->
    <button
      @click="uploadFile"
      :disabled="!file || !folderId"
      class="bg-green-600 text-white px-4 py-1 rounded hover:bg-green-700"
    >
      Subir archivo
    </button>
    <!-- Fin -->

    <!-- Lista de archivos -->
    <div class="mt-6">
      <h3 class="font-bold mb-2">Archivos subidos:</h3>
      <ul>
        <li
          v-for="file in files"
          :key="file.id"
          class="mb-2 flex items-center gap-4 p-2 border rounded"
          :class="{ 'opacity-50 italic': file.deleted_at }"
        >
          <!-- Icono o preview -->
          <div>
            <img
              v-if="isImage(file.mime_type)"
              :src="fileUrl(file.path)"
              alt="preview"
              class="w-10 h-10 object-cover rounded"
            />
            <span v-else class="text-2xl">📄</span>
          </div>

          <!-- Información del archivo -->
          <div class="flex-1">
            <div class="font-medium">{{ file.original_name }}</div>
            <div class="text-sm text-gray-600">{{ formatSize(file.size) }}</div>
          </div>

          <!-- Acciones -->
          <div class="flex gap-2">
            <button
              v-if="!file.deleted_at"
              @click="deleteFile(file.id)"
              class="bg-red-500 text-white px-2 py-1 rounded hover:bg-red-600"
            >
              Borrar
            </button>

            <button
              v-else
              @click="restoreFile(file.id)"
              class="bg-yellow-500 text-white px-2 py-1 rounded hover:bg-yellow-600"
            >
              Restaurar
            </button>

            <button
              v-if="file.deleted_at"
              @click="forceDeleteFile(file.id)"
              class="bg-black text-white px-2 py-1 rounded hover:bg-gray-800"
            >
              Eliminar definitivo
            </button>
          </div>
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
  name: 'FileManager',
  props: {
    selectedFolderId: {
      type: Number,
      required: false
    }
  },
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
    formatSize(bytes) {
      const kb = bytes / 1024;
      const mb = kb / 1024;
      return mb >= 1 ? mb.toFixed(2) + ' MB' : kb.toFixed(2) + ' KB';
    },
    async uploadFile() {
      if (!this.file || !this.folderId) return;

      const formData = new FormData();
      formData.append('file', this.file);
      formData.append('folder_id', this.folderId);

      try {
        await fileService.upload(formData, (progressEvent) => {
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
    async deleteFile(id) {
      if (confirm('¿Estás seguro de que querés borrar este archivo?')) {
        await fileService.remove(id);
        await this.fetchFiles();
      }
    },
    async restoreFile(id) {
      await fileService.restore(id);
      await this.fetchFiles();
    },
    async forceDeleteFile(id) {
      if (confirm('¿Eliminar definitivamente este archivo?')) {
        await fileService.forceDelete(id);
        await this.fetchFiles();
      }
    },
  },
  mounted() {
    this.fetchFolders();
    this.fetchFiles();
  },
};
</script>
