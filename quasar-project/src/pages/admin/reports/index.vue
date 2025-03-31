<template>
    <q-page class="q-px-lg">

        <div class="q-my-md bg-white q-pa-sm shadow-1">
            <q-breadcrumbs 
                class="q-mx-sm"
                >
                <q-breadcrumbs-el icon="home" to="/admin/dashboard" />
                <q-breadcrumbs-el label="Reports" icon="bar_chart" to="/admin/reports" />
            </q-breadcrumbs>
            </div>

            <q-table
  class="q-mt-md"
  title="Data Reports"
  :rows="filteredRows"
  :columns="columns"
  row-key="index"
>
  <!-- Custom Table Header -->
  <template v-slot:top>
    <q-toolbar class="q-gutter-md">
      <!-- 📌 Table Title -->
      <q-toolbar-title>Data Reports</q-toolbar-title>

      <q-space /> <!-- Pushes inputs to the right -->

      <span class="text-bold">Filter by date:</span>

      <!-- From Date -->
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

      <!-- To Date -->
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
    </q-toolbar>
  </template>

  <!-- Table Actions -->
  <template v-slot:body-cell-actions="props">
    <q-td :props="props"></q-td>
  </template>
</q-table>



    <!-- Bar Chart Component -->

    <div class="q-mt-md">
        <div class="q-pa-md row justify-around bg-grey-2 rounded-borders shadow-2">
            <!-- Finished Customers -->
            <div class="column items-center text-center">
                <q-icon name="check_circle" color="positive" size="40px" />
                <div class="text-h6 text-positive">Finished</div>
                <div class="text-h5">{{ finishedCount }}</div>
            </div>

            <!-- Cancelled Customers -->
            <div class="column items-center text-center">
                <q-icon name="cancel" color="negative" size="40px" />
                <div class="text-h6 text-negative">Cancelled</div>
                <div class="text-h5">{{ cancelledCount }}</div>
            </div>

            <!-- Total Customers -->
            <div class="column items-center text-center">
                <q-icon name="people" color="primary" size="40px" />
                <div class="text-h6 text-primary">Total</div>
                <div class="text-h5">{{ total }}</div>
            </div>
        </div>
    </div>
    <div class="q-px-lg q-my-md">
        <BarChart
        :cancelledPercent="cancelledPercent"
        :finishedPercent="finishedPercent"
        />
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






export default defineComponent({
    name: 'IndexPage',
    components: { BarChart },

    setup(){
    const text = ref("");
    const rows = ref([]);
    const columns = ref([
        
    {
            name: 'name',
            label: 'Customer Name',
            align: 'left',
            field: 'name',
            sortable: true
          },
        {
            name: 'email',
            label: 'Email address',
            align: 'left',
            field: 'email',
            sortable: true
          },
          {
            name: 'queue_number',
            label: 'Queue Number',
            align: 'left',
            field: 'queue_number',
            sortable: true
          },
          {
            name: 'type_id',
            label: 'Service Type',
            align: 'left',
            field: 'type_id',
            sortable: true
          },
          {
            name: 'teller_id',
            label: 'Assigned Personnel',
            align: 'left',
            field: 'teller_id',
            sortable: true
          },
          {
            name: 'status',
            label: 'Status',
            align: 'left',
            field: 'status',
            sortable: true
          },
          {
            name: 'updated_at',
            label: 'Date and Time',
            align: 'left',
            field: 'updated_at',
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
    const cancelledPercent = ref(0);
    const finishedPercent = ref(0);
    const cancelledCount = ref (0)
    const finishedCount = ref(0)
    const total = ref(0)
    const fromDate = ref(""); // Holds the "From" date
    const toDate = ref(""); // Holds the "To" date

    const getTableData = async () => {
        
        try {
            // Send both dates as a payload
            const payload = {
            from_date: fromDate.value,
            to_date: toDate.value
            };

            const { data } = await $axios.post('/admin/reports', payload);
            rows.value.splice(0, rows.value.length, ...data.rows);
            computePercentages();
        } catch (error) {
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
    


    return{
        rows,
        columns,
        filteredRows,
        fromDate,
        toDate,
        cancelledPercent,
        finishedPercent,
        cancelledCount,
        finishedCount,
        total,
        
    }
    }


});
</script>
