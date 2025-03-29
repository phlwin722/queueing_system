<template>
    <q-page class="q-px-lg" style="height: auto; min-height: unset;">
        <div class="q-my-md bg-white q-pa-sm shadow-1">
            <q-breadcrumbs 
                class="q-mx-sm"
                >
                <q-breadcrumbs-el icon="home" to="/admin/dashboard" />
                <q-breadcrumbs-el label="Teller" icon="person"/>
                <q-breadcrumbs-el label="Teller Types" icon="category" to="/admin/teller/types" />
            </q-breadcrumbs>
            </div>
            <q-table
                title="Types"
                :rows="rows"
                :columns="columns"
                row-key="id"
                virtual-scroll
                v-model:pagination="pagination"
                selection="multiple"
                v-model:selected="selected"
                :rows-per-page-options="[0]"
                class="q-mt-md"
            >

            <template v-slot:top>
                <div class="row q-col-gutter-sm">
                    <div class="col-auto">
                    <q-btn 
                        color="primary" 
                        label="Add Type"  
                        @click="handleShowForm('new')"
                        class="custom-btn"
                        glossy
                    />
                    </div>
                    <div class="col-auto">
                    <q-btn 
                        color="red" 
                        label="Delete Type(s)"  
                        :disable="selected.length === 0"
                        @click="beforeDelete(true)"
                        class="custom-btn"
                        glossy
                    />
                    </div>
                </div>
            </template>
                
                <template v-slot:body-cell-actions="props">
                    <q-td :props="props">
                        <div class="q-gutter-x-sm"> 
                            <q-btn
                                square 
                                color="positive" 
                                icon="edit" 
                                dense
                                glossy
                                class="custom-btn2"
                                @click="handleShowForm('edit', props.row)"
                            >
                            <q-tooltip anchor="center left" self="center right" :offset="[10, 10]" class="bg-secondary">
                                Edit Window Type
                            </q-tooltip>
                            </q-btn>

                            <q-btn
                                square 
                                color="red" 
                                icon="delete" 
                                dense
                                glossy
                                class="custom-btn2"
                                @click="beforeDelete(false, props.row)"
                            >
                            <q-tooltip anchor="center right" self="center left" :offset="[10, 10]" class="bg-secondary">
                                Delete Window Type
                            </q-tooltip>
                            </q-btn>
                        </div>
                    </q-td>
                </template>


            </q-table>
        <my-form ref="dialogForm" :url="URL" :rows="rows" />
    </q-page>
    </template>
    
    <script>
    import { defineComponent, ref } from 'vue';
    import { $axios, $notify, Dialog } from 'boot/app';
    import MyForm from 'pages/admin/admin_Tellertype/form.vue';
    import { useQuasar } from 'quasar';
    
    const URL = "/types";
    export default defineComponent({
    name: 'TypeIndexPage',
    components: { MyForm },
    setup() {
        const $dialog = useQuasar()
        const rows = ref([]);
        const columns = ref([
            { name: 'name', label: 'Type Name', align: 'left', field: 'name', sortable: true },
            { name:'code',label:'Symbol ',align: 'left', field: 'code', sortable: false},
            { name: 'actions', label: 'Actions', align: 'left', sortable: false },
        ]);
        
        const selected = ref([]);
        const dialogForm = ref(null);
        const getTableData = async () => {
            try {
                const { data } = await $axios.post(URL + '/index');
                rows.value.splice(0, rows.value.length, ...data.rows);
            } catch (error) {
                console.log('Error fetching types:', error);
            }
        };
        getTableData();
    
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
                console.log('Error deleting types:', error);
                $notify('negative', 'error', error.response?.data?.message || 'Error deleting records');
            }
        };
    
        const beforeDelete = (isMany, row) => {
            const ids = isMany ? selected.value.map(x => x.id) : [row.id];
            $dialog.dialog({
            title: 'Confirm',
            message: 'Are you sure you want to delete these service type?',
            cancel: true,
            persistent: true,
            ok: {
              label: 'Yes',
              color: 'primary',
              unelevated: true,
              style: 'width: 125px;',
            },
            cancel: {
              label: 'Cancel',
              color: 'red-8',
              unelevated: true,
              style: 'width: 125px;',
            },
            style: 'border-radius: 12px; padding: 16px;',
          }).onOk(async () => {
            for (const id of ids) {
                if (id === 1) {
                    $notify('negative', 'error', 'Cannot delete foreign exchange');
                    return;  // Stops further execution for this iteration, no handleDelete will be called
                }
            }
             handleDelete(ids);
          }).onDismiss(() => {
            // console.log('I am triggered on both OK and Cancel')
          });
        };
    
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
    height: 35px;
    text-align: center;
    }

.custom-btn2 {
    width: 35px; /* Adjust as needed */
    margin-left: 5px;
    border-radius: 5px;
}
</style>