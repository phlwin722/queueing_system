
<template>
    <q-page>
        <div class="q-pa-md">
            <q-breadcrumbs 
                class="q-mx-sm"
                >
                <q-breadcrumbs-el label="Dashboard" icon="dashboard" to="/admin/dashboard" />
                <q-breadcrumbs-el label="Teller Window" icon="category" to="/admin/teller/window" />
            </q-breadcrumbs>
            <q-table
                title="Window"
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
                                label="Add Window"  
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
                        <div class="col-auto">
                            <q-btn 
                                color="warning" 
                                label="Reset Window"  
                                @click="beforeReset(true)"
                                class="custom-btn"
                                glossy=""
                                :disable="rows.length === 0"
                                
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
                                glossy
                                class="action-btn"
                                @click="handleShowForm('edit', props.row)" 
                            />
                            <q-btn 
                                square 
                                color="negative" 
                                icon="delete"
                                dense
                                glossy
                                class="action-btn"
                                @click="beforeDelete(false, props.row)"
                            />
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
import MyForm from 'pages/admin/admin_Tellerwindow/form.vue';
import { useQuasar } from 'quasar';

const URL = "/windows";

export default defineComponent({
    name: 'IndexPage',
    components: { MyForm },
    setup() {
        const text = ref("");
        const rows = ref([]);
        const $dialog = useQuasar();
        const autoReset = ref(false);
        const resetMinutes = ref(5); // Default to 5 minutes if not set
        let intervalId = null;

        const columns = ref([
            { name: 'window_name', label: 'Window Name', align: 'left', field: 'window_name', sortable: true },
            { name: 'type_id', label: 'Window Type', align: 'left', field: 'type_id', sortable: true },
            { name: 'teller_id', label: 'Assigned Personnel', align: 'left', field: 'teller_id', sortable: true },
            /* { name: 'pId', align: 'left', field: 'pId', sortable: true, classes: 'hidden' }, */
            { name: 'actions', label: 'Actions', align: 'left' }
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
                const { data } = await $axios.post(URL + '/getWindows');
                rows.value = data.rows.map(row => ({
                    id: row.id,
                    window_name: row.window_name,
                    type_id: row.type_id,
                    teller_id: row.teller_id,
                    pId: row.pId
                }));
            } catch (error) {
                console.log(error);
            }
        };

        const fetchSettings = async () => {
            try {
                const { data } = await $axios.post("/waiting_Time-fetch");
                if (data) {
                    autoReset.value = !!data.auto_reset;
                    resetMinutes.value = data.reset_minutes || 5;
                    setupAutoRefresh();
                }
            } catch (error) {
                console.error("Error fetching settings:", error);
            }
        };

        const setupAutoRefresh = async () => {
    if (intervalId) {
        clearInterval(intervalId);
    }

    try {
        const { data } = await $axios.post('/windows/fetch-reset-settings'); 
        let refreshRate = 60000; // Default: 1 min

        if (data.auto_reset && data.reset_time) {
            const now = new Date();
            const [hours, minutes] = data.reset_time.split(':').map(Number);
            const resetTime = new Date(now.getFullYear(), now.getMonth(), now.getDate(), hours, minutes);
            const timeDiff = resetTime - now;

            refreshRate = timeDiff > 20000 ? timeDiff : 20000;
        }

        intervalId = setInterval(() => {
            getTableData();
        }, refreshRate);
    } catch (error) {
        console.log("Failed to fetch reset settings:", error);
    }
};


        const beforeReset = () => {
    Dialog.create({
        title: 'Confirm Reset',
        message: 'Are you sure you want to reset all assigned tellers?'
    }).onOk(() => {
        resetTeller();
    });
};
const resetTeller = async () => {
    // Check if all tellers are already reset
    const hasAssignedTellers = rows.value.some(row => row.teller_id !== null);

    if (!hasAssignedTellers) {
        $notify('warning', 'info', 'All windows are already reset.');
        return;
    }

    console.log("Reset Teller API Called"); // Debug log
    try {
        const { data } = await $axios.post('/windows/reset-windows'); 
        console.log("API Response:", data); // Debug response
        $notify('positive', 'check', data.message);
        setTimeout(() => {
            getTableData();
        }, 10000); 
    } catch (error) {
        console.log('Error:', error.response?.data || error);
        $notify('negative', 'error', error.response?.data?.message || 'Failed to reset windows');
    }
};




const handleShowForm = (mode, row) => {
            dialogForm.value.showDialog(mode, row);
        };

        const beforeDelete = (isMany, row) => {
            const ids = isMany ? selected.value.map(x => x.id) : [row.id];
            $dialog.dialog({
            title: 'Confirm',
            message: `Are you sure you want to delete ${row.window_name} ?`,
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
          }).onDismiss(() => {
            // console.log('I am triggered on both OK and Cancel')
          });
        };

        const handleDelete = async (id) => {
            try {
                const { data } = await $axios.post(URL + '/delete', { id });
                rows.value = rows.value.filter(row => !id.includes(row.id));
                $notify('positive', 'check', data.message);
                selected.value = [];
            } catch (error) {
                console.log('error', error);
                $notify('negative', 'error', error.response.data.message);
            }
        };

        onMounted(() => {
            getTableData();
            fetchSettings();
        });

        onUnmounted(() => {
            if (intervalId) {
                clearInterval(intervalId);
            }
        });

        return {
            rows,
            columns,
            dialogForm,
            selected,
            handleShowForm,
            URL,
            beforeReset,
            beforeDelete,
            filteredRows,
            setTimeout,
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

.action-btn {
    width: 35px;
    margin-left: 5px;
    border-radius: 5px;
}
</style>
