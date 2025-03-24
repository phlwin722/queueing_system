
<template>
    <q-page>
        <div class="q-pa-md">
            <q-breadcrumbs 
                class="q-mx-sm"
                >
                <q-breadcrumbs-el label="Dashboard" icon="dashboard" to="/admin/dashboard" />
                <q-breadcrumbs-el label="Currency Conversion" icon="category" to="/admin/currency_conversion" />
            </q-breadcrumbs>
            <q-table
                title="Currency"
                :rows="filteredRows"
                :columns="columns"
                row-key="id"
                virtual-scroll
                v-model:pagination="pagination"
                selection="multiple"
                v-model:selected="selected"
                :rows-per-page-options="[0]"
                class="q-mx-sm q-mt-md"
            >
                <template v-slot:top>
                    <div class="row q-col-gutter-sm">
                        <div class="col-auto">
                            <q-btn 
                                color="primary" 
                                label="Add Currency"  
                                @click="handleShowForm('new')"
                                class="custom-btn"
                                glossy
                            />
                        </div>
                        <div class="col-auto">
                            <q-btn 
                                color="red" 
                                label="Delete Record(s)"  
                                :disable="selected.length === 0"
                                @click="beforeDelete(true)"
                                class="custom-btn"
                                glossy
                            />
                        </div>
                        <!-- <div class="col-auto">
                            <q-btn 
                                color="warning" 
                                label="Reset Currency"  
                                @click="beforeReset(true)"
                                class="custom-btn"
                                glossy=""
                            />
                        </div> -->
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
                                glossy
                                class="custom-btn2"
                                @click="handleShowForm('edit', props.row)" 
                                >
                                <q-tooltip anchor="center left" self="center right" :offset="[10, 10]" class="bg-secondary">
                        Edit Window
                    </q-tooltip>
                            </q-btn>
                            
                            <q-btn 
                                square 
                                color="negative" 
                                icon="delete"
                                dense
                                glossy
                                class="custom-btn2"
                                @click="beforeDelete(false, props.row)"
                            >
                            <q-tooltip anchor="center right" self="center left" :offset="[10, 10]" class="bg-secondary">
                        Delete Window
                    </q-tooltip>
                    </q-btn>
                        </div>
                    </q-td>
                </template>
            </q-table>
        </div>
    </q-page>
    <my-form ref="dialogForm" :url="URL" :rows="rows" />
</template>

<script>
import { defineComponent, ref, computed, onMounted, onUnmounted } from 'vue';
import { $axios, $notify, Dialog } from 'boot/app';
import MyForm from 'pages/admin/admin_Currency_Conversion/form.vue';
import { useQuasar } from 'quasar';

const URL = "/currency";

export default defineComponent({
    name: 'IndexPage',
    components: { MyForm },
    setup() {
        const text = ref("");
        const rows = ref([]);
        const $dialog = useQuasar();
        // const autoReset = ref(false);
        // const resetMinutes = ref(5); // Default to 5 minutes if not set
        // let intervalId = null;

        const columns = ref([
            { name: 'currency_name', label: 'Currency Name', align: 'left', field: 'currency_name', sortable: true },
            { name: 'value', label: 'Value', align: 'left', field: 'value', sortable: true },
            { name: 'actions', label: 'Actions', align: 'left', sortable: false 
            }
        ]);

        const filteredRows = computed(() => {
            return rows.value.filter(row =>
                Object.values(row).some(value =>
                    String(value).toLowerCase().includes(text.value.toLowerCase())
                )
            );
        });

        const selected = ref([]);
        const dialogForm = ref(null);

        const getTableData = async () => {
            try {
                const { data } = await $axios.post(URL + '/showData');
                rows.value.splice(0, rows.value.length, ...data.rows);
            } catch (error) {
                console.log(error);
            }
        };
        getTableData();

        // const fetchSettings = async () => {
        //     try {
        //         const { data } = await $axios.post("/waiting_Time-fetch");
        //         if (data) {
        //             autoReset.value = !!data.auto_reset;
        //             resetMinutes.value = data.reset_minutes || 5;
        //             setupAutoRefresh();
        //         }
        //     } catch (error) {
        //         console.error("Error fetching settings:", error);
        //     }
        // };

    //     const setupAutoRefresh = () => {
    //     if (intervalId) {
    //         clearInterval(intervalId);
    //     }

    //     let refreshRate = resetMinutes.value * 60 * 1000;
    //     if (refreshRate < 20000) refreshRate = 20000; // Minimum 20 sec

    //     intervalId = setInterval(() => {
    //         getTableData();
    //     }, refreshRate);
    // };

        const handleShowForm = (mode, row) => {
            dialogForm.value.showDialog(mode, row);
        };

//         const beforeReset = () => {
//     Dialog.create({
//         title: 'Confirm Reset',
//         message: 'Are you sure you want to reset all assigned tellers?'
//     }).onOk(() => {
//         resetTeller();
//     });
// };

// const resetTeller = async () => {
//     console.log("Reset Teller API Called"); // Debug log
//     try {
//         const { data } = await $axios.post('/windows/reset-tellers'); 
//         console.log("API Response:", data); // Debug response
//         $notify('positive', 'check', data.message);
//         setTimeout(() => {
//             getTableData(); 
//         }, 500);
//     } catch (error) {
//         console.log('Error:', error.response?.data || error);
//         $notify('negative', 'error', error.response?.data?.message || 'Failed to reset tellers');
//     }
// };

    const beforeDelete = (isMany, row) => {
        const ids = isMany ? selected.value.map(x => x.id) : [row.id];
        const itemNames = isMany ? 'Currencies' : row.currency_name;

        $dialog.dialog({
            title: 'Confirm',
            message: `Are you sure you want to delete ${itemNames}?`,  // Adjusted message
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
            handleDelete(ids);
        });
    };


    const handleDelete = async (ids) => {
            try {
                // Ensure ids is always an array
                if (!Array.isArray(ids)) {
                    ids = [ids];
                }

                const { data } = await $axios.post(URL + '/delete', { ids }); // Use 'ids' as the key
                rows.value = rows.value.filter(row => !ids.includes(row.id)); // Use 'ids' here
                $notify('positive', 'check', data.message);
                selected.value = [];
            } catch (error) {
                console.log('error', error);
                $notify('negative', 'error', error.response.data.message);
            }
    };


        onMounted(() => {
            getTableData();
            // fetchSettings();
        });

        // onUnmounted(() => {
        //     if (intervalId) {
        //         clearInterval(intervalId);
        //     }
        // });

        return {
            rows,
            columns,
            dialogForm,
            selected,
            handleShowForm,
            URL,
            // beforeReset,
            beforeDelete,
            filteredRows,
            text
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
    width: 35px;
    margin-left: 5px;
    border-radius: 5px;
}
</style>
