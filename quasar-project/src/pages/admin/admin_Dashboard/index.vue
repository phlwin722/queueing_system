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

    <!-- Active Queue Table & Teller Workstations -->
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
          :rows="rowsWorkStation"
          :columns="columnsWorkStation"
          row-key="id"
        >
          <template v-slot:body-cell-status="props">
            <q-td :props="props">
              <!-- Apply color based on the status -->
              <q-badge :color="props.row.status === 'Available' ? 'red' : 'green'">
                {{ props.row.status }}
              </q-badge>
            </q-td>
          </template>
      </q-table>
    </q-card>
  </div>
</div>


<!-- Bar Chart Component -->

  </q-page>
</template>

<script>
import { $axios } from 'src/boot/app';
import { ref, onMounted } from 'vue';

export default {
  name: "QueueDashboard",
  setup() {
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

    onMounted(() => {
      fetchWorkStation();
    })

    return {
      columnsWorkStation,
      rowsWorkStation,
      fetchWorkStation,
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
