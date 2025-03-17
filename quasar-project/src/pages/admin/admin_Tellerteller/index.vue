<template>
    <q-page >
        <div class="q-pa-md">
            <q-table   
                title="Personnel"
                :rows="rows"
                :columns="columns"
                row-key="id"
                virtual-scroll
                v-model:pagination="pagination"
                selection="multiple"
                v-model:selected="selected"
                :rows-per-page-options="[0]"
                class="q-mx-sm"
            >

            <template v-slot:top>
                <div class="row q-col-gutter-sm">
                    <div class="col-auto">
                    <q-btn 
                        color="primary" 
                        label="Add Personnel"  
                        @click="handleShowForm('new')"
                        class="custom-btn"
                    />
                    </div>
                    <div class="col-auto">
                    <q-btn 
                        color="red" 
                        label="Delete Personnel(s)"  
                        :disable="selected.length === 0"
                        @click="beforeDelete(true)"
                        class="custom-btn"
                    />
                    </div>
                </div>
            </template>

    
            <template v-slot:body-cell-actions="props"> 
                <q-td :props="props">
                    <div class="q-gutter-x-md"> 
                    <q-btn 
                        square 
                        color="positive" 
                        icon="edit" 
                        dense 
                        class="custom-btn2"
                        @click="handleShowForm('edit', props.row)" 
                    />
                    <q-btn 
                        square 
                        color="red" 
                        icon="delete" 
                        dense 
                        class="custom-btn2"
                        @click="beforeDelete(false, props.row)" 
                    />
                    </div>
                </q-td>
            </template>



            </q-table>
        </div>
        <my-form
            ref="dialogForm"
            :url="URL"
            :rows="rows"
            :types="types"
        />
    
    </q-page>
    </template>
    
    <script>
    import { defineComponent, ref } from 'vue';
    import { $axios, $notify, Dialog } from 'boot/app';
    import MyForm from 'pages/admin/admin_Tellerteller/form.vue';
    
    const URL = "/tellers";
    
    export default defineComponent({
    name: 'TellerIndexPage',
    components: { MyForm },
    setup() {
        const rows = ref([]);
        const columns = ref([
            { 
                name: 'teller_firstname', 
                label: 'First Name', 
                align: 'left', 
                field: 'teller_firstname', 
                sortable: true 
            },
            { 
                name: 'teller_lastname', 
                label: 'Last Name', 
                align: 'left', 
                field: 'teller_lastname', 
                sortable: true 
            },
            { 
                name: 'teller_username', 
                label: 'Username', 
                align: 'left', 
                field: 'teller_username', 
                sortable: true 
            },
            {  
                name: 'name', 
                label: 'Type', 
                align: 'left', 
                field: 'name', 
                sortable: true
            },
            { 
                name: 'actions', 
                label: 'Actions', 
                align: 'left', 
                sortable: false 
            }
        ]);
    
        const selected = ref([]);
        const dialogForm = ref(null);
    
        const getTableData = async () => {
            try{
                const { data } = await $axios.post(URL+'/index')
                rows.value.splice(
                0,
                rows.value.length,
                ...data.rows
                )
            }catch(error){
                console.log(error);
            }
            }
            getTableData()
    
        const handleShowForm = (mode, row) => {
            dialogForm.value.showDialog(mode, row);
        };
    
        const handleDelete = async (ids) => { 
            try {
                const { data } = await $axios.post(URL + '/delete', { ids });
                $notify('positive', 'check', data.message);
                
                for (const x of ids) {  
                    const index = rows.value.findIndex(o => o.id === x); 
                    if (index !== -1) {
                        rows.value.splice(index, 1);
                    }
                }
                selected.value.splice(0, selected.value.length); 
            } catch (error) {
                console.error('Error deleting tellers:', error);
                $notify('negative', 'error', error.response.data.message);
            }
        };
    
        const beforeDelete = (isMany, row) => {
            const ids = isMany ? selected.value.map(x => x.id) : [row.id];
            const message = isMany 
                ? 'Are you sure you want to delete these tellers?'
                : `Are you sure you want to delete "${row.teller_firstname} ${row.teller_lastname}"?`;
    
            Dialog.create({
                title: 'Confirm Delete',
                message: message,
            }).onOk(() => {
                handleDelete(ids);
            });
        };
    
        const types = ref([]);
    
        const getTypes = async () => {
            try {
                const { data } = await $axios.post(URL + '/index');
                console.log("Fetched Types:", data.rows);
                types.value.splice(0, types.value.length, ...data.rows);
            } catch (error) {
                console.error("Error fetching teller types:", error);
            }
        };
    
    
        getTypes();
    
        return {
            rows,
            columns,
            selected,
            pagination: ref({ rowsPerPage: 0 }),
            dialogForm,
            handleShowForm,
            URL,
            beforeDelete
        };
    }
    });
    </script>

<style scoped>
.custom-btn {
    min-width: 150px;
    height: 40px;
    text-align: center;
    }

    .custom-btn2 {
  width: 60px; /* Adjust as needed */
  margin-left: 5px;
}
</style>