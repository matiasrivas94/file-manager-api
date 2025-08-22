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
        class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 disabled:opacity-50"
      >
        Subir archivo
      </button>
    </div>

    <!-- Filtros -->
    <div class="my-6 p-4 bg-gray-100 rounded border space-y-2">
      <h3 class="font-semibold">Filtros:</h3>

      <div class="flex flex-wrap gap-4 items-center">
        <label class="flex items-center gap-2">
          <input type="checkbox" v-model="showDeleted" @change="fetchFiles"/>
          Ver eliminados
        </label>

        <label class="flex items-center gap-2">
          <input type="checkbox" v-model="onlyImages" />
            Solo imágenes
        </label>

        <FolderSelector 
          v-model="filterFolderId" 
          :folders="folders" 
        />

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
      <FileList
        :files="filteredFiles"
        :total="files.length"
        @select="handleSelectFile"
        @delete="handleDelete"
        @restore="handleRestore"  
        @force-delete="handleForceDelete"
      />
    </div>

    <!-- Previsualización del archivo seleccionado -->
    <div v-if="selectedFile" class="mt-6 p-4 bg-white rounded shadow border max-w-2xl">

      <button
        @click="selectedFile = null"
        class="absolute top-2 right-2 text-gray-400 hover:text-gray-700 text-lg"
        title="Cerrar"
      >
        Cerrar
      </button>

      <h3 class="text-lg font-semibold mb-4">Previsualización</h3>

      <div class="mb-4">
        <img
          v-if="selectedFile.mime_type.startsWith('image/')"
          :src="fileUrl(selectedFile.path)"
          alt="Vista previa"
          class="max-w-full max-h-64 object-contain border rounded"
        />
        <div v-else class="flex items-center gap-2 text-gray-600">
          <span class="text-4xl">📄</span>
          <span>{{ selectedFile.original_name }}</span>
        </div>
      </div>

      <ul class="mb-4 text-sm text-gray-700 space-y-1">
        <li><strong>Nombre:</strong> {{ selectedFile.original_name }}</li>
        <li><strong>Tipo:</strong> {{ selectedFile.mime_type }}</li>
        <li><strong>Tamaño:</strong> {{ formatSize(selectedFile.size) }}</li>
        <li><strong>Carpeta:</strong> {{ selectedFile.folder?.name || 'Sin carpeta' }}</li>
      </ul>

      <a 
        v-if="selectedFile && selectedFile.path"
        :href="getPublicUrl(selectedFile.path)"
        :download="selectedFile.original_name"
        class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
        Descargar archivo
      </a>
    </div>

    <!-- Mensaje Toasts -->
    <div class="fixed top-4 right-4 space-y-3 z-50">
      <transition-group name="toast" tag="div">
        <div
          v-for="toast in toasts" 
          :key="toast.id"
          :class="[
            'flex items-center gap-3 px-4 py-3 rounded-lg shadow-lg text-white cursor-pointer w-72',
            toast.type === 'success' ? 'bg-green-600' : 'bg-red-600'
          ]"
          @click="removeToast(toast.id)"
        >
          <span v-if="toast.type === 'success'" class="text-xl">✅</span> 
          <span v-else class="text-xl">❌</span>
          <div class="flex-1">
            <p class="font-semibold">{{ toast.message }}</p>
          </div>
        </div>
      </transition-group>
    </div>

    <!-- Modal de confirmaciónes -->
    <div v-if="showConfirm" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg shadow-lg p-6 w-96">
        <h3 class="text-lg font-semibold mb-4">Confirmar acción</h3>
        <p class="mb-6">{{ confirmMessage }}</p>
        <div class="flex justify-end gap-3">
           <button @click="confirmNo"
              class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">
              Cancelar
            </button>
            <button
              @click="confirmYes"
              :class="[
                'px-4 py-2 text-white rounded',
                confirmAction === 'delete' ? 'bg-red-600 hover:bg-red-700' :
                confirmAction === 'restore' ? 'bg-blue-600 hover:bg-blue-700' :
                'bg-black hover:bg-gray-800'
              ]"
            >
            Confirmar
          </button>
      </div>
    </div>
   </div>

  </div>
</template>

<script>
import fileService from '../services/fileService';
import folderService from '../services/folderService';
import FolderSelector from './FolderSelector.vue';
import FileList from './FileList.vue';

export default {
  name: 'FileManager',
  components: { FolderSelector, FileList },
  data() {
    return {
      file: null,
      folderId: '',
      folders: [],
      files: [],
      uploadProgress: 0,
      selectedFile: null,

      // Filtros
      showDeleted: false,
      onlyImages: false,
      filterFolderId: '',
      filterType: '',

      // Mensajes
      toasts: [],
      confirmAction: null, // Acción a confirmar
      confirmFileId: null, // ID del archivo
      confirmMessage: '',  // Mensaje a mostrar
      showConfirm: false   // Abre/cierra el modal
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
      try {
        const res = await fileService.getAll({
          trashed: this.showDeleted ? 'true' : 'false' // Para mostrar archivos eliminados.
        });
        this.files = res.data;
      } catch (err) {
        console.error('Error al obtener archivos:', err);
      }
    },
    handleFileChange(event) {
      this.file = event.target.files[0]; // Asignar el primer archivo seleccionado
    },
    fileUrl(path) {
      return `http://localhost:8001/storage/${path}`; 
    },
    getPublicUrl(path) {
      return `/storage/${path.replace(/^public\//, '')}`;
    },
    isImage(mime) {
      return mime.startsWith('image/');
    },
    formatSize(bytes) {
      const sizes = ['Bytes', 'KB', 'MB', 'GB'];
      if (bytes === 0) return '0 Byte';
      const i = parseInt(Math.floor(Math.log(bytes) / Math.log(1024)));
      return Math.round(bytes / Math.pow(1024, i), 2) + ' ' + sizes[i];
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
        this.showToast('Archivo subido con éxito', 'success');
      } catch (err) {
          this.showToast('Error al subir archivo', 'error');
          console.error('Error al subir archivo:', err);
      }
    },
    // Manejadores para eventos de los componentes
    handleSelectFile(file) {
      this.selectedFile = file;
    },
    handleDelete(fileId) {
      this.openConfirm('delete', fileId, '¿Seguro que deseas enviar este archivo a la papelera?');
    },
    handleRestore(fileId) {
      this.openConfirm('restore', fileId, '¿Deseas restaurar este archivo?');
    },
    handleForceDelete(fileId) {
      this.openConfirm('forceDelete', fileId, '¿Seguro que quieres eliminar este archivo permanentemente?');
    },
    showToast(message, type = 'success') {
      const id = Date.now();
      this.toasts.push({ id, message, type });

      setTimeout(() => {
        this.removeToast(id);
      }, 3000);
    },
    removeToast(id) {
      this.toasts = this.toasts.filter(t => t.id !== id);
    },
    openConfirm(action, fileId, message) {
      this.confirmAction = action;
      this.confirmFileId = fileId;
      this.confirmMessage = message;
      this.showConfirm = true;
    },
    async confirmYes() {
      if (!this.confirmAction || !this.confirmFileId) return;

      try {
        if (this.confirmAction === 'delete') {
          await fileService.remove(this.confirmFileId);
          this.showToast('Archivo enviado a papelera', 'success');
        } else if (this.confirmAction === 'restore') {
          await fileService.restore(this.confirmFileId);
          this.showToast('Archivo restaurado', 'success');
        } else if (this.confirmAction === 'forceDelete') {
          await fileService.forceDelete(this.confirmFileId);
          this.showToast('Archivo eliminado definitivamente', 'success');
        }

        await this.fetchFiles();
      } catch (err) {
        this.showToast('Error en la acción', 'error');
      }
      this.showConfirm = false;
    },
    confirmNo() {
      this.showConfirm = false;
    }
  },
  mounted() {
    this.fetchFolders();
    this.fetchFiles();
  },
};
</script>

<style >
.toast-enter-active, .toast-leave-active {
  transition: all 0.3s ease;
}
.toast-enter-from {
  opacity: 0;
  transform: translateY(20px);
}
.toast-leave-to {
  opacity: 0;
  transform: translateY(20px);
}
</style>
