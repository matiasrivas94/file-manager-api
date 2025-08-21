<template>
  <div>
    <label class="mr-2 font-medium">Carpeta:</label>
    <select
      :value="modelValue"
      @input="$emit('update:modelValue', $event.target.value)"
      class="border rounded px-2 py-1"
    >
      <option value="">Todas</option>
      <option
        v-for="folder in folders"
        :key="folder.id"
        :value="folder.id"
      >
        {{ folder.name }}
      </option>
    </select>
  </div>
</template>

<script>
export default {
  name: "FolderSelector",
  props: {
    folders: {
      type: Array,
      required: true
    },
    modelValue: {
      type: [String, Number],
      default: ""
    }
  }
};
</script>
