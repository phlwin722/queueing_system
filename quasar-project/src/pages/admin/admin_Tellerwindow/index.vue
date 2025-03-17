<template>

    <q-input standout="bg-teal text-white" v-model="text" label="Search" :dense="dense" />


    <q-page>
        <div class="q-pa-md">
        <q-table
            title="Window"
            :rows="filteredRows"
            :columns="columns"
            row-key="id"
            selection="multiple"
            v-model:selected="selected"
        >
        <template v-slot:top>
            <div class="q-gutter-x-sm">
            <q-btn 
            color="primary" 
            label="Add Window"
            icon="add"
            @click="handleShowForm('new')" 
            />
            <q-btn 
            color="negative" 
            label="Delete Record(s)"
            icon="delete"
            :disable="selected.length === 0"
            @click="beforeDelete(true)"
            />
            </div>
        

        </template>

        <template v-slot:body-cell-actions="props">
            <q-td :props="props">
            <div class="q-gutter-x-sm">
                <q-btn 
                square 
                color="green" 
                icon="edit"
                dense
                @click="handleShowForm('edit', props.row)" 
                />
                <q-btn 
                square 
                color="negative" 
                icon="delete"
                dense
                @click="beforeDelete(false,props.row)"
                />
            </div>
            </q-td>
        </template>
        </q-table>
        </div>
    </q-page>
    <my-form
        ref="dialogForm"
        :url="URL"
        :rows="rows"
    />
    </template>

    <script>
    import { 
    defineComponent ,
    ref,
    computed 
    } from 'vue'


    import {
    $axios,
    $notify,
    Dialog
    } from 'boot/app'

    import MyForm from 'pages/admin/admin_Tellerwindow/form.vue';

    const URL = "/windows";


    export default defineComponent({
    name: 'IndexPage',
    components: {
        MyForm,
    },
    setup(){
        const text = ref("");
        const rows = ref([]);
        const columns = ref([
            
    
        {
            name: 'window_name',
            label: 'Window Name',
            align: 'left',
            field: 'window_name',
            sortable: true
        },
        {
            name: 'type_id',
            label: 'Window Type',
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
            name: 'pId',
            align: 'left',
            field: 'pId', 
            sortable: true,
            classes: 'hidden'
        },

        {
            name: 'actions',
            label: 'Actions',
            align: 'left'
        },
        ]);
        const filteredRows = computed(() => {
        return rows.value.filter((row) =>
        Object.values(row).some((value) =>
        String(value).toLowerCase().includes(text.value.toLowerCase())
        )
    );
    });
        const dialogForm = ref(null)
        const selected = ref([])
        const getTableData = async () => {
        try{
            const { data } = await $axios.post(URL+'/getWindows')
            rows.value = data.rows.map(row => ({
            id: row.id,
            window_name: row.window_name,
            type_id: row.type_id, // Window Type
            teller_id: row.teller_id, // Full Name as a single field
            pId: row.pId
            }));
        }catch(error){
            console.log(error);
        }
        }
        getTableData()
        const handleShowForm = (mode, row)=>{
        dialogForm.value.showDialog(mode, row)
        }


        const beforeDelete = (isMany, row) => {
        const ids = isMany ? selected.value.map(x => x.id) : [row.id]
        console.log("ids",ids)
        const message = isMany
            ? 'Are you sure you want to delete this Records ?'
            : 'Are you sure you want to delete this specific record?'+'  id: '+row.id

            Dialog.create({
            title: 'Confirm Delete',
            message: message
        }).onOk(() =>{
            handleDelete(ids)
            
        })
        }

        const handleDelete = async (id) =>{
        try{
            const { data } = await $axios.post(URL +'/delete', {id}) 
            for(const x in id){
            const index = rows.value.findIndex(o => o.id === id[x])
            if(index > -1){
                rows.value.splice(index,1)
            }
            }
            $notify('positive', 'check', data.message)

            selected.value.splice(
            0,
            selected.value.length,

            )      
        }catch(error){
            console.log('error',error)
            $notify('negative', 'error',error.response.data.message)
        }
        }

        return{
        rows,
        columns,
        dialogForm,
        selected,
        handleShowForm,
        URL,
        beforeDelete,
        filteredRows,
        text,
        }
    }


    });
</script>
