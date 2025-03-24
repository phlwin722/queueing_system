<template>
  <q-page class="q-px-lg q-py-md">
    <div class="text-secondary q-mb-md">Dashboard</div>
    <!-- Queue Summary Cards -->
    <div class="row q-gutter-md">
      <!-- Total Customers -->
      <q-card class="stat-card bg-white text-secondary">
        <q-card-section>
          <div>
          <q-icon name="people" size="24px" class="text-primary q-mr-xs"/>
          Number of Customers</div>
          <div class="text-h4">{{ total }}</div>
        </q-card-section>
      </q-card>

      <!-- Finished Customers -->
      <q-card class="stat-card bg-white text-secondary">
        <q-card-section>
          <div>
          <q-icon name="check_circle" size="24px" class="text-positive q-mr-xs"/>
          Finished
        </div>
          <div class="text-h4">{{ finishedCount }}</div>
        </q-card-section>
      </q-card>

      <!-- Cancelled Customers -->
      <q-card class="stat-card bg-white text-secondary">
        <q-card-section>
          <div>
          <q-icon name="cancel" size="24px" class="text-negative q-mr-xs"/>
          Cancelled</div>
          <div class="text-h4">{{ cancelledCount }}</div>
        </q-card-section>
      </q-card>

      
    </div>
    
  <div class="q-px-lg q-mt-xs">
    <BarChart
      :cancelledPercent="cancelledPercent"
      :finishedPercent="finishedPercent"
    />
  </div>

    Active Queue Table & Teller Workstations
    <div class="row q-mt-xs q-col-gutter-md">
      <!-- Active Queue Table -->
      <div class="col-8">
        <q-card>
          <q-card-section class="bg-primary">
            <div class="text-accent">Active Queue</div>
          </q-card-section>
          <q-separator />
          <q-table
            flat
            bordered
            :rows="queueData"
            :columns="queueColumns"
            row-key="ticket"
          />
        </q-card>
      </div>
      
      

      <!-- Teller Workstations -->
      <!-- <div class="row q-mt-xs q-col-gutter-md"> -->
    <div class="col-4">
    <q-card>
      <q-card-section class="bg-primary">
        <div class="text-accent">Teller Workstations</div>
      </q-card-section>
      <q-separator />
      <q-table
        flat
        bordered
        :rows="tellers"
        :columns="columns"
        row-key="id"
      >
        <template v-slot:body-cell-status="props">
          <q-td :props="props">
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
  onMounted
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
  data() {
    const rows = ref([]);
    const cancelledPercent = ref(0);
    const finishedPercent = ref(0);
    const cancelledCount = ref (0)
    const finishedCount = ref(0)
    const total = ref(0)
    const dateToday = new Date().toISOString().slice(0, 10);

    const getTableData = async () => {
          try{
          
            console.log(dateToday)
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

        onMounted(() => {
          getTableData()
        })
      

    return {
      queueColumns: [
        { name: "ticket", label: "Ticket Number", align: "left", field: "ticket" },
        { name: "customer", label: "Customer", align: "left", field: "customer" },
        { name: "status", label: "Status", align: "left", field: "status" },
        { name: "teller", label: "Assigned Teller", align: "left", field: "teller" },
      ],
      queueData: [
        { ticket: "A001", customer: "John Doe", status: "Waiting", teller: "Teller 1" },
        { ticket: "A002", customer: "Jane Smith", status: "Serving", teller: "Teller 2" },
        { ticket: "A003", customer: "Mike Johnson", status: "Completed", teller: "Teller 3" },
        { ticket: "A004", customer: "Rafael Johnson", status: "Completed", teller: "Teller 4" },
        { ticket: "A005", customer: "rica Johnson", status: "Completed", teller: "Teller 5" },
      ],
      tellers: [
        { name: "Teller 1", status: "Available" },
        { name: "Teller 2", status: "Busy" },
        { name: "Teller 3", status: "Available" },
        { name: "Teller 4", status: "Busy" },
        { name: "Teller 5", status: "Available" },
      ],
      cancelledPercent,
      finishedPercent,
      cancelledCount,
      finishedCount,
      total
    };
  },
};
</script>

<style scoped>
.stat-card {
  width: 260px; /* Adjust width to match your image */
  height: 125px; /* Adjust height to match your image */
  border-radius: 10px; /* Rounded corners */
  display: flex;
}

</style>
