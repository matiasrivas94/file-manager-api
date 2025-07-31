<template>
  <div>
    <h2 class="text-xl font-bold mb-4">Gestor de Archivos</h2>

    <!-- Subida de archivo -->
    <div class="mb-6 p-4 bg-white rounded shadow">
      <div class="mb-2">
        <label>Carpeta:</label>
        <select v-model="folderId" class="border px-2 py-1">
          <option disabled value="">Seleccione carpeta</option>
          <option v-for="folder in folders" :key="folder.id" :value="folder.id">
            {{ folder.name }}
          </option>
        </select>
      </div>

      <div class="mb-2">
        <input type="file" @change="handleFileChange" />
      </div>

      <div v-if="uploadProgress > 0" class="w-full bg-gray-300 h-4 rounded mb-2">
        <div
          class="bg-blue-500 h-4 rounded text-xs text-white text-center"
          :style="{ width: uploadProgress + '%' }"
        >
          {{ uploadProgress }}%
        </div>
      </div>

      <button
        @click="uploadFile"
        :disabled="!file || !folderId"
        class="bg-green-600 text-white px-4 py-1 rounded hover:bg-green-700"
      >
        Subir archivo
      </button>
    </div>

    <!-- Filtros -->
    <div class="my-6 p-4 bg-gray-100 rounded border space-y-2">
      <h3 class="font-semibold">Filtros:</h3>

      <div class="flex flex-wrap gap-4 items-center">
        <label class="flex items-center gap-2">
          <input type="checkbox" v-model="showDeleted" />
          Ver eliminados
        </label>

        <label class="flex items-center gap-2">
          <input type="checkbox" v-model="onlyImages" />
          Solo imágenes
        </label>

        <div>
          <label class="mr-2">Carpeta:</label>
          <select v-model="filterFolderId" class="border px-2 py-1">
            <option value="">Todas</option>
            <option v-for="folder in folders" :key="folder.id" :value="folder.id">
              {{ folder.name }}
            </option>
          </select>
        </div>

        <div>
          <label class="mr-2">Tipo:</label>
          <select v-model="filterType" class="border px-2 py-1">
            <option value="">Todos</option>
            <option value="image">Imagen</option>
            <option value="pdf">PDF</option>
            <option value="word">Word</option>
            <option value="excel">Excel</option>
            <option value="ppt">PowerPoint</option>
            <option value="text">Texto</option>
            <option value="other">Otros</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Lista de archivos -->
    <div>
      <h3 class="font-bold mb-2">Archivos:</h3>
      <ul>
        <li
          v-for="file in filteredFiles"
          :key="file.id"
          class="mb-2 p-2 bg-white shadow rounded flex items-center justify-between"
        >
          <div class="flex items-center gap-3">
            <span v-if="isImage(file.mime_type)">
              <img :src="fileUrl(file.path)" alt="" class="w-10 h-10 object-cover" />
            </span>
            <span v-else>📄</span>

            <div>
              <div class="font-medium">{{ file.original_name }}</div>
              <div class="text-sm text-gray-500">{{ formatSize(file.size) }}</div>
            </div>
          </div>

          <div class="flex gap-2">
            <button
              v-if="!showDeleted"
              @click="deleteFile(file.id)"
              class="bg-red-600 text-white px-2 py-1 rounded hover:bg-red-700"
            >
              Eliminar
            </button>

            <button
              v-if="showDeleted"
              @click="restoreFile(file.id)"
              class="bg-yellow-500 text-white px-2 py-1 rounded hover:bg-yellow-600"
            >
              Restaurar
            </button>

            <button
              v-if="showDeleted"
              @click="forceDeleteFile(file.id)"
              class="bg-black text-white px-2 py-1 rounded hover:bg-gray-800"
            >
              Eliminar definitivo
            </button>
          </div>
        </li>
      </ul>
    </div>
  </div>
</template>

<script>
import fileService from '../services/fileService';
import folderService from '../services/folderService';

export default {
  name: 'FileManager',
  data() {
    return {
      file: null,
      folderId: '',
      folders: [],
      files: [],
      uploadProgress: 0,

      // Filtros
      showDeleted: false,
      onlyImages: false,
      filterFolderId: '',
      filterType: '',
    };
  },
  computed: {
    filteredFiles() {
      return this.files
        .filter(file => this.showDeleted ? file.deleted_at : !file.deleted_at)
        .filter(file => {
          if (this.onlyImages) return this.isImage(file.mime_type);
          return true;
        })
        .filter(file => {
          if (!this.filterFolderId) return true;
          return file.folder_id === Number(this.filterFolderId);
        })
        .filter(file => {
          if (!this.filterType) return true;
          const mime = file.mime_type;

          const map = {
            image: mime.startsWith('image/'),
            pdf: mime === 'application/pdf',
            word: ['application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'].includes(mime),
            excel: ['application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'].includes(mime),
            ppt: ['application/vnd.ms-powerpoint', 'application/vnd.openxmlformats-officedocument.presentationml.presentation'].includes(mime),
            text: mime === 'text/plain',
            other: ![
              'image/', 'application/pdf', 'application/msword',
              'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
              'application/vnd.ms-excel',
              'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
              'application/vnd.ms-powerpoint',
              'application/vnd.openxmlformats-officedocument.presentationml.presentation',
              'text/plain'
            ].some(type => mime.startsWith(type) || mime === type)
          };

          return map[this.filterType];
        });
    }
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
      if (bytes < 1024) return `${bytes} B`;
      if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(1)} KB`;
      return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
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
        if (err.response?.status === 422) {
          alert('Error de validación: ' + JSON.stringify(err.response.data.errors));
        } else {
          console.error('Error al subir archivo:', err);
        }
      }
    },
    async deleteFile(id) {
      await fileService.remove(id);
      await this.fetchFiles();
    },
    async restoreFile(id) {
      await fileService.restore(id);
      await this.fetchFiles();
    },
    async forceDeleteFile(id) {
      if (!confirm('¿Seguro que quieres eliminar el archivo permanentemente?')) return;
      await fileService.forceDelete(id);
      await this.fetchFiles();
    },
  },
  mounted() {
    this.fetchFolders();
    this.fetchFiles();
  },
};
</script>
