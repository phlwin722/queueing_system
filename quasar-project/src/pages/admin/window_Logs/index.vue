<template>
    <div class="row q-mx-lg q-mt-md items-center">
      <!-- Search Input -->
      <q-input
        filled
        class="bg-accent text-black col q-mr-sm"
        v-model="text"
        label="Search"
        dense
        outlined
      />
  
      <!-- Date Picker Input -->
      <q-input
        filled
        class="bg-accent text-black"
        v-model="selectedDate"
        type="date"
        label="Select Date"
        @update:model-value="getTableData"
        outlined
      />
    </div>
  
    <q-page style="min-height: auto;">
      <div class="q-px-lg q-mt-md">
        <q-table title="Window Logs" :rows="filteredRows" :columns="columns" row-key="id">
          <template v-slot:body-cell-actions="props">
            <q-td :props="props"></q-td>
          </template>
        </q-table>
      </div>
    </q-page>
  </template>
  
  <script>
  import { defineComponent, ref, computed, onMounted } from 'vue';
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

  
      let refreshInterval = null;
      onMounted(() => {
        refreshInterval = setInterval(getTableData, 5000);
        getTableData();
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
  