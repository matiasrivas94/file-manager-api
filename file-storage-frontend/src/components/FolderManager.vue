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
    };
  },
  methods: {
    async loadFolders() {
      const res = await folderService.getAll();
      this.folders = res.data;
    },
    async createFolder() {
      if (!this.newFolder.trim()) return;
      await folderService.create({ name: this.newFolder });
      this.newFolder = '';
      this.loadFolders();
    },
    startEdit(folder) {
      this.editId = folder.id;
      this.editName = folder.name;
    },
    async saveEdit(folder) {
      if (!this.editName.trim()) return;
      await folderService.update(folder.id, { name: this.editName });
      this.editId = null;
      this.editName = '';
      this.loadFolders();
    },
    async deleteFolder(id) {
      if (confirm('¿Eliminar esta carpeta?')) {
        await folderService.remove(id);
        this.loadFolders();
      }
    },
  },
  mounted() {
    this.loadFolders();
  },
};
</script>

<style scoped>
/* Estilos Adicionales */
</style>
