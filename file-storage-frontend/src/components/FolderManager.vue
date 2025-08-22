<template>
  <div class="p-4 max-w-xl mx-auto">
    <h2 class="text-xl font-bold mb-4">Carpetas</h2>

    <!-- Crear nueva carpeta -->
    <form @submit.prevent="createFolder" class="flex gap-2 mb-4">
      <input
        v-model="newFolder"
        type="text"
        placeholder="Nueva carpeta"
        class="border px-2 py-1 rounded w-full"
      />
      <button class="bg-blue-500 text-white px-3 py-1 rounded">Crear</button>
    </form>

    <!-- Lista de carpetas -->
    <ul class="space-y-2">
      <li v-for="folder in folders" :key="folder.id" class="border p-2 rounded flex justify-between items-center">
        <div v-if="editId === folder.id">
          <input v-model="editName" class="border px-2 py-1 rounded" />
        </div>
        <span v-else>{{ folder.name }}</span>

        <div class="flex gap-2">
          <button v-if="editId !== folder.id" @click="startEdit(folder)" class="text-blue-600">Editar</button>
          <button v-if="editId === folder.id" @click="saveEdit(folder)" class="text-green-600">Guardar</button>
          <button @click="deleteFolder(folder.id)" class="text-red-600">Eliminar</button>
        </div>
      </li>
    </ul>

    <!-- Toasts -->
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

    <!-- Modal de confirmación -->
    <div v-if="showConfirm" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg shadow-lg p-6 w-96">
        <h3 class="text-lg font-semibold mb-4">Confirmar eliminación</h3>
        <p class="mb-6">{{ confirmMessage }}</p>
        <div class="flex justify-end gap-3">
          <button @click="cancelDelete"
            class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">
            Cancelar
          </button>
          <button
            @click="confirmDelete"
            class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700"
          >
            Eliminar
          </button>
        </div>
      </div>
    </div>

  </div>
</template>

<script>
import folderService from '../services/folderService';

export default {
  data() {
    return {
      folders: [],
      newFolder: '',
      editId: null,
      editName: '',

      // Mensaje de confirmación y notificaciones
      showConfirm: false,
      confirmMessage: '',
      folderToDelete: null,
      toasts: []
    };
  },
  methods: {
    async loadFolders() {
      try {
        const res = await folderService.getAll();
        this.folders = res.data;
      } catch (err) {
        this.showToast('Error al cargar carpetas', 'error');
      }
    },
    async createFolder() {
      if (!this.newFolder.trim()) return;
      
      try {
        await folderService.create({ name: this.newFolder });
        this.newFolder = '';
        await this.loadFolders();
        this.showToast('Carpeta creada exitosamente', 'success');
      } catch (err) {
        this.showToast('Error al crear carpeta', 'error');
      }
    },
    startEdit(folder) {
      this.editId = folder.id;
      this.editName = folder.name;
    },
    async saveEdit(folder) {
      if (!this.editName.trim()) return;
      
      try {
        await folderService.update(folder.id, { name: this.editName });
        this.editId = null;
        this.editName = '';
        await this.loadFolders();
        this.showToast('Carpeta actualizada exitosamente', 'success');
      } catch (err) {
        this.showToast('Error al actualizar carpeta', 'error');
      }
    },
    async deleteFolder(folderId) {
      try {
        // Obtener información de la carpeta antes de eliminar
        const info = await folderService.getInfo(folderId);
        const fileCount = info.data.file_count;
        
        // Preparar mensaje según tenga archivos o no
        this.confirmMessage = fileCount > 0 
          ? `¡Cuidado! Esta carpeta contiene ${fileCount} archivo(s). ¿Estás seguro de eliminarla? Los archivos también serán eliminados.`
          : '¿Estás seguro de eliminar esta carpeta?';
        
        // Mostrar modal de confirmación
        this.folderToDelete = folderId;
        this.showConfirm = true;
        
      } catch (err) {
        this.showToast('Error al obtener información de la carpeta', 'error');
      }
    },
    async confirmDelete() {
      if (!this.folderToDelete) return;
      
      try {
        // Eliminar la carpeta (el backend se encarga de eliminar los archivos)
        const response = await folderService.remove(this.folderToDelete);
        
        // Mostrar mensaje del backend
        this.showToast(response.data.message, 'success');
        
        // Recargar la lista de carpetas
        await this.loadFolders();
        
        // Cerrar modal
        this.showConfirm = false;
        this.folderToDelete = null;
        
      } catch (err) {
        this.showToast('Error al eliminar carpeta', 'error');
        this.showConfirm = false;
      }
    },
    cancelDelete() {
      this.showConfirm = false;
      this.folderToDelete = null;
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
    }
  },
  mounted() {
    this.loadFolders();
  },
};
</script>

<style scoped>
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
