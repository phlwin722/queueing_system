<template>
    <div class="row q-gutter-x-md q-mx-lg q-mt-lg">
        <span class="text-bold">Filter by date:</span>

        <!-- From Date -->
        <q-input 
        filled
        class="bg-accent text-black q-pl-sm" 
        v-model="fromDate" 
        type="date"
        label="From"
        @update:model-value="getTableData"
        />

        <!-- To Date -->
        <q-input 
        filled
        class="bg-accent text-black q-pl-sm" 
        v-model="toDate" 
        type="date"
        label="To"
        @update:model-value="getTableData"
        />
    </div>

    <q-page>
        <div class="q-pa-lg">
        <q-table
            title="Data Reports"
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
        } catch (error) {
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
        fromDate,
        toDate
        
    }
    }


});
</script>
