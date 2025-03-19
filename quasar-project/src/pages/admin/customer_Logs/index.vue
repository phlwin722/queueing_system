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
    
  
    
  
    
    
    export default defineComponent({
      name: 'IndexPage',
  
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
        const selectedDate = ref(""); // Holds the selected date
  
        const getTableData = async () => {
          try{
          
            const payload = selectedDate.value ? { date: selectedDate.value } : {}
            const { data } = await $axios.post('/admin/queue-logs',payload)
            
            rows.value = data.rows.map(row => ({
                    name: row.name,
                    email: row.email,
                    queue_number: row.queue_number,
                    type_id: row.type_id,
                    teller_id: row.teller_id,
                    status: row.status,
                    created_at: row.created_at,
                }));
          }catch(error){
            console.log(error);
          }
        }
        onMounted(() => {
          // refreshInterval = setInterval(getTableData, 5000)
          getTableData()
        })
      
    
    
        return{
          rows,
          columns,
          filteredRows,
          text,
          selectedDate,
          
        }
      }
    
    
    });
    </script>