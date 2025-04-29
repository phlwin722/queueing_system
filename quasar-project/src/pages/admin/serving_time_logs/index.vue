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

        <!-- Use q-space to push everything to the right -->
        <q-space />

        <!-- Wrapping the filters in a row -->
        <div class="row items-center q-gutter-x-md no-wrap q-mt-lg">

          <!-- Branch Selector -->
          <div v-if="!adminManagerInformation" style="min-width: 180px;">
            <q-select
              outlined
              style="width: 250px;"
              dense
              v-model="branch_value"
              :options="branch_list"
              label="Branch name"
              hide-bottom-space
              emit-value
              map-options
              option-label="branch_name"
              option-value="id"
            />
          </div>

          <!-- Type Filter -->
          <!-- <div>
            <span class="text-bold">Filter by Type:</span>
          </div> -->
          <div style="min-width: 180px;">
            <q-select
              outlined
              dense
              v-model="type_id"
              :options="serviceTypeList"
              label="Window type"
              class="text-black"
              emit-value
              map-options
            />
          </div>

          <!-- Date Filter -->
          <!-- <div>
            <span class="text-bold">Filter by Date:</span>
          </div> -->
          <div style="min-width: 150px;">
            <q-input 
              dense
              outlined
              class="text-black"
              v-model="fromDate" 
              type="date"
              label="From"
              @update:model-value="getTableData"
            />
          </div>
          <div style="min-width: 150px;">
            <q-input 
              dense
              outlined
              class="text-black"
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
    <template v-if ="rows.length > 0">
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
    const adminManagerInformation = ref(null);
    const branch_value = ref (null);
    const branch_list = ref ([]);
    const text = ref("");
    const rows = ref([]);
    const columns = ref([]);
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
              type_id: type_id.value,
              branch_id: branch_value.value
          };

          const { data } = await $axios.post('/teller/fetch-serving-time', payload);
          // Format start_time and end_time without seconds
          data.rows.forEach(row => {
            row.start_time = row.start_time.replace(/:\d{2}(?=\s[APMapm]{2})/, ''); // removes :SS before AM/PM
            row.end_time = row.end_time.replace(/:\d{2}(?=\s[APMapm]{2})/, '');
          });
          // Update rows
          rows.value.splice(0, rows.value.length, ...data.rows);
          console.log(data.rows); // Test display
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

    const fetchBranch = async () => {
      try {
        const { data } = await $axios.post('/type/Branch');
        branch_list.value = data.branch
      } catch (error) {
        if (error.response.status === 422) {
          console.log(error)
        }
      }
    }

    const fetchWindowTypes = async () => {
      try {
        const response = await $axios.post("/types/dropdown", {
          branch_id: branch_value.value,
        });
        if (Array.isArray(response.data.rows)) {
          
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

    const fetchColumn = async () => {
      try {
        if (adminManagerInformation.value != null) {
          columns.value = [
            {
              name: 'window_type',
              label: 'Window Type',
              align: 'left',
              field: 'window_type',
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
              name: 'minutes',
              label: 'Minute/s',
              align: 'left',
              field: 'minutes',
              sortable: true
            },
            {
              name: 'actions',
              label: 'Actions',
              align: 'left',
              field: 'actions',
              sortable: false,
              style: 'width: 100px;'
            }
          ]
        }else {
          columns.value = [
            {
              name: 'branch_name',
              label: 'Branch Name',
              align: 'left',
              field: 'branch_name',
              sortable: true
            },
            {
              name: 'window_type',
              label: 'Window Type',
              align: 'left',
              field: 'window_type',
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
              name: 'minutes',
              label: 'Minute/s',
              align: 'left',
              field: 'minutes',
              sortable: true
            },
          ]
        }
      } catch (error) {
        if (error.response.status === 422) {
          console.log(error)
        }
      }
    }
    const debouncedGetTableData = debounce(() => {
          getTableData();
        }, 300);

    watch([type_id, fromDate, toDate, branch_value], ([newTypeId, newFromDate, newToDate, newBranch],[oldTypeId, oldFromDate, oldToDate, oldBranch]) => {
      if (newBranch !== oldBranch) {
      type_id.value = null
      fromDate.value = null
      toDate.value = null

      fetchWindowTypes()
      debouncedGetTableData()
      fetchColumn();
      

      }else if(newTypeId){
        debouncedGetTableData()
      }
    });

    onMounted(() => {
      const managerInformation = localStorage.getItem('managerInformation')
        if (managerInformation) {
          adminManagerInformation.value = JSON.parse(managerInformation);
        }

        if (adminManagerInformation.value == null) {
          branch_value.value = 1
        }else  {
          branch_value.value = adminManagerInformation.value.branch_id
        }
        fetchWindowTypes()
        getTableData()
      fetchBranch()
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
        adminManagerInformation,
        branch_value,
        branch_list
      }
    }

});
</script>
<style >
  span.q-table__bottom-item{
    width: 200px;
  }
</style>
