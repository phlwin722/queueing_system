<template>
  <q-page class="q-px-lg" style="height: auto; min-height: unset;">

    <div class="q-my-md bg-white q-pa-sm shadow-1">
            <q-breadcrumbs 
                class="q-mx-sm"
                >
                <q-breadcrumbs-el icon="home" to="/admin/dashboard" />
                <q-breadcrumbs-el label="Window Logs" icon="upload_file" to="/admin/window-logs" />
            </q-breadcrumbs>
            </div>

            <q-table title="Window Logs" :rows="filteredRows" :columns="columns" row-key="id" dense>
  <!-- Custom Table Header -->
  <template v-slot:top>
    <q-toolbar class="q-gutter-md">
      <!-- 📌 Table Title -->
      <q-toolbar-title>Window Logs</q-toolbar-title>

      <q-space /> <!-- Pushes inputs to the right -->

      <!-- Search Input -->
      <q-input
        filled
        dense
        outlined
        class="bg-accent text-black col-2"
        v-model="text"
        label="Search"
      >
        <template v-slot:append>
          <q-icon name="search" />
        </template>
      </q-input>

      <!-- Date Picker Input -->
      <q-input
        filled
        dense
        outlined
        class="bg-accent text-black col-1.5"
        v-model="selectedDate"
        type="date"
        label="Select Date"
        @update:model-value="getTableData"
      />
    </q-toolbar>
  </template>

  <!-- Table Actions -->
  <template v-slot:body-cell-actions="props">
    <q-td :props="props"></q-td>
  </template>
</q-table>
    </q-page>
  </template>
  
  <script>
  import { defineComponent, ref, computed, onMounted, onUnmounted } from 'vue';
  import { $axios } from 'boot/app';
  
  export default defineComponent({
    name: 'WindowLogsPage',
  
    setup() {
      const text = ref('');
      const rows = ref([]);
      const selectedDate = ref('');
  
      const columns = ref([
    { name: 'window_name', label: 'Window Name', align: 'left', field: 'window_name', sortable: true },
    { name: 'type_name', label: 'Window Type', align: 'left', field: 'type_name', sortable: true },
    { name: 'teller_name', label: 'Assigned Personnel', align: 'left', field: 'teller_name', sortable: true },
    { name: 'archived_at', label: 'Archived Date', align: 'left', field: 'archived_at', sortable: true }
]);

  
      const filteredRows = computed(() => {
        return rows.value.filter(row =>
          Object.values(row).some(value =>
            String(value).toLowerCase().includes(text.value.toLowerCase())
          )
        );
      });
  
      const getTableData = async () => {
  try {
    const payload = selectedDate.value ? { date: selectedDate.value } : {};
    const { data } = await $axios.post('/admin/window-logs', payload);

    rows.value = data.rows.map(row => ({
      id: row.id,
      window_name: row.window_name,
      type_name: row.type_name || 'Unknown Type',
      teller_name: row.teller_name || 'Unassigned',
      archived_at: row.archived_at
    }));
  } catch (error) {
    console.log('Error fetching window logs:', error);
  }
};

  
  let dataTimeout

  const optimizedFecthData = async () => {
      await getTableData()
      dataTimeout = setTimeout(optimizedFecthData, 3000); // Recursive Timeout
  };
  onMounted(() => {
      optimizedFecthData()
  })
  onUnmounted(() => {
      clearTimeout(dataTimeout);
  });
  
      return {
        rows,
        columns,
        filteredRows,
        text,
        selectedDate,
        getTableData
      };
    }
  });
  </script>
  