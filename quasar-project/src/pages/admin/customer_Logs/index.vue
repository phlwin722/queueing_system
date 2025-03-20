<template>
  <div class="row q-mx-lg q-mt-md items-center">
    
    <!-- Search Input -->
    <q-input
      filled
      class="bg-accent text-black col q-mr-sm"
      v-model="text"
      label="Search"
      :dense="dense"
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

  <q-page>
    <div class="q-px-lg q-mt-md">
      <q-table
        title="Customer Logs"
        :rows="filteredRows"
        :columns="columns"
        row-key="index"
      >
        <template v-slot:body-cell-actions="props">
          <q-td :props="props"></q-td>
        </template>
      </q-table>
    </div>

     <!-- Bar Chart Component -->
<div class="q-px-lg q-mt-md">
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

  <div class="q-px-lg q-mt-md">
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
      onMounted
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
            name: 'created_at',
            label: 'Date and Time',
            align: 'left',
            field: 'created_at',
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
        const selectedDate = ref(""); // Holds the selected date
  
        const getTableData = async () => {
          try{
          
            const payload = selectedDate.value ? { date: selectedDate.value } : {}
            const { data } = await $axios.post('/admin/queue-logs',payload)
            
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
          refreshInterval = setInterval(getTableData, 3000)
          getTableData()
        })
      
    
    
        return{
          rows,
          columns,
          filteredRows,
          text,
          selectedDate,
          cancelledPercent,
          finishedPercent,
          finishedCount,
          cancelledCount,
          total,
          
        }
      }
    
    });
    </script>

<style scoped>
.q-mx-lg {
  margin-left: 20px;
  margin-right: 20px;
}

.q-mt-md {
  margin-top: 16px;
}
</style>