<template>
  <q-page class="q-px-lg" style="height: auto; min-height: unset;">

    <div class="q-my-md bg-white q-pa-sm shadow-1">
    <q-breadcrumbs 
                class="q-mx-sm"
                >
                <q-breadcrumbs-el icon="home" />
                <q-breadcrumbs-el label="Dashboard" icon="dashboard" to="/admin/dashboard" />
            </q-breadcrumbs>
      </div>

    <!-- Queue Summary Cards -->
    <div class="row q-gutter-md">
      <!-- Total Customers -->
      <q-card class="stat-card bg-white text-secondary">
        <q-card-section>
          <div>
          <q-icon name="people" size="24px" class="text-warning q-mr-xs"/>
          Number of Customers</div>
          <div class="text-h4 q-mt-lg">{{ total }}</div>
        </q-card-section>
      </q-card>

      <!-- Finished Customers -->
      <q-card class="stat-card bg-white text-secondary">
        <q-card-section>
          <div>
          <q-icon name="check_circle" size="24px" class="text-positive q-mr-xs"/>
          Finished
        </div>
          <div class="text-h4 q-mt-lg">{{ finishedCount }}</div>
        </q-card-section>
      </q-card>

      <!-- Cancelled Customers -->
      <q-card class="stat-card bg-white text-secondary">
        <q-card-section>
          <div>
          <q-icon name="cancel" size="24px" class="text-negative q-mr-xs"/>
          Cancelled</div>
          <div class="text-h4 q-mt-lg">{{ cancelledCount }}</div>
        </q-card-section>
      </q-card>

  <div class="q-pa-md q-mt-xs shadow-1 q-mt-md" style="width: 63%;">
    <BarChart
      :cancelledPercent="cancelledPercent"
      :finishedPercent="finishedPercent"
    />
  </div>

  <div class="col-4">
    <q-card>
      <q-card-section class="bg-primary">
        <div class="text-accent">Teller Workstations</div>
      </q-card-section>
      <q-separator />
      <q-table
          flat
          bordered
          virtual-scroll
          :rows="rowsWorkStation"
          :columns="columnsWorkStation"
          row-key="id"
          
        >
          <template v-slot:body-cell-status="props">
            <q-td :props="props">
              <!-- Apply color based on the status -->
              <q-badge :color="props.row.status === 'Available' ? 'green' : 'red'">
                {{ props.row.status }}
              </q-badge>
            </q-td>
          </template>
      </q-table>
    </q-card>
  </div>
  </div>

  </q-page>
</template>

<script>

import { 
  defineComponent ,
  ref,
  computed,
  onMounted,
  onUnmounted
} from 'vue'


import {
  $axios,
  $notify,
  Dialog
} from 'boot/app'

import BarChart from 'components/BarChart.vue';
export default {
  name: "QueueDashboard",
  components: { BarChart },
  setup() {
    const rows = ref([]);
    const cancelledPercent = ref(0);
    const finishedPercent = ref(0);
    const cancelledCount = ref (0)
    const finishedCount = ref(0)
    const total = ref(0)
    const dateToday = new Date().toISOString().slice(0, 10);

    const getTableData = async () => {
          try{           
            const { data } = await $axios.post('/admin/queue-logs',{
              date: dateToday
            })
            
            rows.value.splice(0, rows.value.length, ...data.rows);
            computePercentages();
          }catch(error){
            console.log(error);
          }
        }

        const computePercentages = () => {
          total.value = rows.value.length;
          if (total === 0) {
            cancelledPercent.value = 0;
            finishedPercent.value = 0;
            return;
          }

          cancelledCount.value = rows.value.filter(row => row.status === 'cancelled').length;
          finishedCount.value = rows.value.filter(row => row.status === 'finished').length;

          cancelledPercent.value = ((cancelledCount.value / total.value) * 100).toFixed(2);
          finishedPercent.value = ((finishedCount.value / total.value) * 100).toFixed(2);

        };

       const rowsWorkStation = ref([]);
          const columnsWorkStation = ([
            {
              name:'name',
              label:'Name',
              align:'left',
              field: 'teller_name',
              sortable: true
            },
            {
              name: 'status',
              label: 'Status',
              align: 'left',
              field: 'status',
              sortable: true
            }
          ])

         // Fetch the information of teller if assigned
         const fetchWorkStation = async () => {
            try {
              const { data } = await $axios.post('/tellers/index');
              // Process rows data to assign the correct status and full name
              rowsWorkStation.value = data.rows.map(row => {
                return {
                  id: row.id,
                  // If type_id is null, status is 'Available', otherwise 'Assigned'
                  status: row.type_id === null ? 'Available' : 'Assigned',
                  // Combine teller_firstname and teller_lastname for the full name
                  teller_name: `${row.teller_firstname} ${row.teller_lastname}`
                };
              });
            } catch (error) {
              if (error.response.status === 422) {
                console.log(error.message);
              }
            }
          };

        let dataTimeout;
        let workTimeout;

        const optimizedFetchData = async () => {
          await getTableData()
          dataTimeout = setTimeout(optimizedFetchData, 5000); // Recursive Timeout
        };

          const optimizedFetchWork = async () => {
            await fetchWorkStation()
            workTimeout = setTimeout(optimizedFetchWork, 5000); // Recursive Timeout
        };

        onMounted(() => {
          optimizedFetchData()
          optimizedFetchWork()
        })

        onUnmounted(() => {
          clearTimeout(dataTimeout);
          clearTimeout(workTimeout);
        });

    return {
      columnsWorkStation,
      rowsWorkStation,
      fetchWorkStation,
      cancelledPercent,
      finishedPercent,
      cancelledCount,
      finishedCount,
      total,
    };
  },
};

</script>

<style scoped>
.stat-card {
  width: 250px; /* Adjust width to match your image */
  height: 125px; /* Adjust height to match your image */
  border-radius: 10px; /* Rounded corners */
  display: flex;
}

.q-table {
  overflow: hidden !important;
}

</style>
