<template>
  <q-page class="q-px-lg">

    <!-- Breadcrumbs -->
    <div class="q-my-md bg-white q-pa-sm shadow-1">
      <q-breadcrumbs class="q-mx-sm">
        <q-breadcrumbs-el icon="home" to="/admin/dashboard" />
        <q-breadcrumbs-el label="Serving Time Logs" icon="timer" to="/admin/serving_time_logs" />
      </q-breadcrumbs>
    </div>

    <!-- Data Table -->
    <q-table
      class="q-mt-md"
      title="Data Reports"
      :rows="filteredRows"
      :columns="columns"
      row-key="index"
      dense
    >

      <!-- Custom Table Header -->
      <template v-slot:top>
        <q-toolbar class="q-gutter-md">
          <q-toolbar-title>Serving Time</q-toolbar-title>
          <q-space />

          <div class="row q-col-gutter-sm items-center">
            <!-- Filter by Type -->
            <div class="col-auto">
              <span class="text-bold">Filter by Type:</span>
            </div>
            <div class="col-2">
              <q-select
                filled
                outlined
                v-model="type_id"
                :options="serviceTypeList"
                label="Window type"
                class="bg-accent text-black"
                dense
                emit-value
                map-options
              />
            </div>

            <!-- Filter by Date -->
            <div class="col-auto">
              <span class="text-bold">Filter by Date:</span>
            </div>
            <div class="col-2">
              <q-input 
                filled
                dense
                outlined
                class="bg-accent text-black"
                v-model="fromDate" 
                type="date"
                label="From"
                @update:model-value="getTableData"
              />
            </div>
            <div class="col-2">
              <q-input 
                filled
                dense
                outlined
                class="bg-accent text-black"
                v-model="toDate" 
                type="date"
                label="To"
                @update:model-value="getTableData"
              />
            </div>
          </div>
        </q-toolbar>
      </template>

      <!-- Table Actions -->
      <template v-slot:body-cell-actions="props">
        <q-td :props="props" />
      </template>
    </q-table>

    <!-- Statistics Card -->
    <template v-if="stats.min !== Infinity && stats.max !== -Infinity && stats.avg !== 'NaN'">
      <q-card flat bordered class="q-pa-md q-mt-md q-mb-md bg-grey-1 text-primary">
        <q-card-section>
          <div class="text-h6">📊 Serving Time Statistics</div>

          <div class="q-mt-sm">
            <q-item dense>
              <q-item-section avatar>
                <q-icon name="timer" />
              </q-item-section>
              <q-item-section>
                <q-item-label>Min Serving Time</q-item-label>
                <q-item-label caption class="text-weight-bold">{{ stats.min }} minute/s</q-item-label>
              </q-item-section>
            </q-item>

            <q-item dense>
              <q-item-section avatar>
                <q-icon name="schedule" />
              </q-item-section>
              <q-item-section>
                <q-item-label>Max Serving Time</q-item-label>
                <q-item-label caption class="text-weight-bold">{{ stats.max }} minute/s</q-item-label>
              </q-item-section>
            </q-item>

            <q-item dense>
              <q-item-section avatar>
                <q-icon name="trending_up" />
              </q-item-section>
              <q-item-section>
                <q-item-label>Mean (Average) Time</q-item-label>
                <q-item-label caption class="text-weight-bold">{{ stats.avg }} minute/s</q-item-label>
              </q-item-section>
            </q-item>
          </div>
        </q-card-section>
      </q-card>
    </template>
    
  </q-page>
</template>



<script>
import { 
    defineComponent ,
    ref,
    computed,
    onMounted,
    onUnmounted,
    watch
} from 'vue'


import {
    $axios,
    $notify,
    Dialog
} from 'boot/app'


import { debounce } from 'lodash';



export default defineComponent({
    name: 'IndexPage',

    setup(){
    const text = ref("");
    const rows = ref([]);
    const columns = ref([
        
        {
          name: 'minutes',
          label: 'Minute/s',
          align: 'left',
          field: 'minutes',
          sortable: true
        },
        {
          name: 'start_time',
          label: 'Start Time',
          align: 'left',
          field: 'start_time',
          sortable: true
        },
        {
          name: 'end_time',
          label: 'End Time',
          align: 'left',
          field: 'end_time',
          sortable: true
        },
        {
          name: 'window_type',
          label: 'Window Type',
          align: 'left',
          field: 'window_type',
          sortable: true
        },
    ]);
    let refreshInterval = null
    const filteredRows = computed(() => {
    return rows.value.filter((row) =>
    Object.values(row).some((value) =>
        String(value).toLowerCase().includes(text.value.toLowerCase())
    )
    );
});
    const fromDate = ref(""); // Holds the "From" date
    const toDate = ref(""); // Holds the "To" date
    const type_id = ref();
    const serviceTypeList = ref([]);
    const stats = ref({
        min: null,
        max: null,
        avg: null
    });
    const getTableData = async () => {
    try {
        const payload = {
            from_date: fromDate.value,
            to_date: toDate.value,
            type_id: type_id.value
        };

        const { data } = await $axios.post('/teller/fetch-serving-time', payload);

        // Update rows
        rows.value.splice(0, rows.value.length, ...data.rows);
        console.log(data.rows.minutes); // Test display
        // Extract the minutes directly from the response
        const minutes = data.minutes;

        // Compute min, max, and avg based on the minutes
        const min = Math.min(...minutes);
        const max = Math.max(...minutes);
        const avg = minutes.reduce((sum, value) => sum + value, 0) / minutes.length;

        stats.value = {
            min: min,
            max: max,
            avg: Math.round(avg) // Round to 2 decimal places
        };

        console.log(stats.value); // Test display

    } catch (error) {
        console.log(error);
    }
};



    const fetchWindowTypes = async () => {
      try {
        const response = await $axios.post("/types/dropdown");
        if (Array.isArray(response.data.rows)) {
          // Map id and section_name correctly
          serviceTypeList.value = response.data.rows.map((sec) => ({
            label: sec.name, // This is what the user sees
            value: sec.id, // This is what will be stored
          }));
        } else {
          console.error(
            'Expected "rows" to be an array, but got:',
            response.data.rows
          );
        }
      } catch (error) {
        console.error("Error fetching sections:", error);
      }
    };
    const debouncedGetTableData = debounce(() => {
          getTableData();
        }, 300);

    watch([type_id, fromDate, toDate], () => {
        debouncedGetTableData(); // Only call getTableData once when either value changes
    });
  

    onMounted(() => {
        getTableData()
        fetchWindowTypes()
    })



    return{
        rows,
        columns,
        filteredRows,
        fromDate,
        toDate,
        type_id,
        serviceTypeList,
        stats,
    }
    }


});
</script>
<style >
  span.q-table__bottom-item{
    width: 200px;
  }
</style>
