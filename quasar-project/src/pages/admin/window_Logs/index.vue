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

            <q-table title="Window Logs" :rows="filteredRows" :columns="columns" row-key="id" dense class="q-pt-sm">
        <!-- Custom Table Header -->
        <template v-slot:top>
          <q-toolbar class="q-gutter-sm">
            <!-- 📌 Table Title -->
            <q-toolbar-title>Window Logs</q-toolbar-title>

            <q-space /> <!-- Pushes inputs to the right -->
          
            <q-select
                v-if="!adminManagerInformation"
                outlined
                style="width: 250px;"
                v-model="branch_value"
                label="Branch name"
                hide-bottom-space
                dense
                emit-value
                map-options
                :options="branch_list"
                option-label="branch_name"
                option-value="id"
            />
            <!-- Search Input -->
            <q-input
              dense
              outlined
              class="text-black col-3"
              v-model="text"
              label="Search"
            >
              <template v-slot:append>
                <q-icon name="search" />
              </template>
            </q-input>

            <!-- Date Picker Input -->
            <q-input
              dense
              outlined
              class="text-black col-1.5"
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
  import { defineComponent, ref, computed, onMounted, onUnmounted, watch } from 'vue';
  import { $axios } from 'boot/app';
  import { debounce } from 'lodash';
  
  export default defineComponent({
    name: 'WindowLogsPage',
  
    setup() {
      const text = ref('');
      const rows = ref([]);
      const selectedDate = ref('');
      const branch_list = ref([])
      const branch_value = ref()
      const adminManagerInformation = ref(null);
  
      const columns = ref([]);
  
      const filteredRows = computed(() => {
        return rows.value.filter(row =>
          Object.values(row).some(value =>
            String(value).toLowerCase().includes(text.value.toLowerCase())
          )
        );
      });
  
      const getTableData = async () => {
      try {
    //    const payload = selectedDate.value ? date: selectedDate.value : '';
        const { data } = await $axios.post('/admin/window-logs', {
          date: selectedDate.value || '',
          branch_id: branch_value.value
        });

      rows.value = data.rows.map(row => ({
        id: row.id,
        window_name: row.window_name,
        type_name: row.type_name || 'Unknown Type',
        teller_name: row.teller_name || 'Unassigned',
        archived_at: row.archived_at,
        branch_name: row.branch_name,
      }));
    } catch (error) {
      console.log('Error fetching window logs:', error);
    }
  };

  
  const debouncedGetTableData = debounce(() => {
    getTableData()
  }, 300);

  const fetchBranch = async () => {
    try {
      const { data } = await $axios.post('type/Branch')
      branch_list.value = [{id: 0 , branch_name: 'All branches'}, ...data.branch]
    } catch(error) {
      if (error.response.status === 422) {
        console.log(error)
      }
    }
  }

  const columnsList =  async () => {
    if (adminManagerInformation.value != null) {
      columns.value = [
        { name: 'window_name', label: 'Window Name', align: 'left', field: 'window_name', sortable: true },
        { name: 'type_name', label: 'Window Type', align: 'left', field: 'type_name', sortable: true },
        { name: 'teller_name', label: 'Assigned Personnel', align: 'left', field: 'teller_name', sortable: true },
        { name: 'archived_at', label: 'Archived Date', align: 'left', field: 'archived_at', sortable: true }
      ]
    }else {
      columns.value = [
        { name: 'branch_name', label: 'Branch Name', align: 'left', field: 'branch_name', sortable: true },
        { name: 'window_name', label: 'Window Name', align: 'left', field: 'window_name', sortable: true },
        { name: 'type_name', label: 'Window Type', align: 'left', field: 'type_name', sortable: true },
        { name: 'teller_name', label: 'Assigned Personnel', align: 'left', field: 'teller_name', sortable: true },
        { name: 'archived_at', label: 'Archived Date', align: 'left', field: 'archived_at', sortable: true }
      ]
    }
  }

  watch([selectedDate,branch_value], (newSelectedDate, newBranch_value) => {
      debouncedGetTableData(); // Only call getTableData once when either value changes
      if (newBranch_value) {
        debouncedGetTableData
      }
  });


  onMounted(() => {
    const managerInformation = localStorage.getItem('managerInformation')
    if (managerInformation) {
      console.log(managerInformation)
      adminManagerInformation.value = JSON.parse(managerInformation)
    }

    if (!managerInformation) {
        branch_value.value = 0;
    }else {
      branch_value.value = adminManagerInformation.value.branch_id
    }
    getTableData()
    fetchBranch()
    columnsList();
  })
  
      return {
        rows,
        columns,
        filteredRows,
        text,
        selectedDate,
        getTableData,
        adminManagerInformation,
        branch_list,
        branch_value,
        fetchBranch,
      };
    }
  });
  </script>
  <style >
  span.q-table__bottom-item{
    width: 200px;
  }
</style>
  