<template>
  
  <q-input class="bg-accent text-black q-mx-lg q-mt-lg q-pl-sm" 
  v-model="text" 
  label="Search"
  :dense="dense"
  />
  
  <q-page>
    <div class="q-pa-lg">
      <q-table
      title="Customer Logs"
      :rows="filteredRows"
      :columns="columns"
      row-key="index"
      >

      <template v-slot:body-cell-actions="props">
        <q-td :props="props">
          <q-chip
            :class="
              props.row.status === 'cancelled'
                ? 'bg-negative text-white'
                : props.row.status === 'finished'
                ? 'bg-positive text-white'
                : 'bg-grey-3 text-black'
            "
            :label="props.row.status"
            dense
          />
        </q-td>
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
          name: 'status',
          label: 'Status',
          align: 'left',
          field: 'status',
          sortable: true
        },
        {
          name: 'crated_at',
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
      const getTableData = async () => {
        try{
          const { data } = await $axios.post('/admin/queue-logs')
          rows.value.splice(
            0,
            rows.value.length,
            ...data.rows
          )
        }catch(error){
          console.log(error);
        }
      }
      onMounted(() => {
        refreshInterval = setInterval(getTableData, 5000)
        getTableData()
      })
    
  
  
      return{
        rows,
        columns,
        filteredRows,
        text
      }
    }
  
  
  });
  </script>
  