<template>
    <q-page class="q-px-sm" style="height: auto; min-height: unset">
      <div class="q-ma-md bg-white q-pa-sm shadow-1">
        <q-breadcrumbs class="q-mx-sm">
          <q-breadcrumbs-el icon="home" to="/admin/dashboard" />
          <q-breadcrumbs-el label="Branch" icon="person" />
          <q-breadcrumbs-el
            label="List Appointment"
            icon="groups"
            to="/admin/appointment/list"
          />
        </q-breadcrumbs>
      </div>
      <q-table
        title="Personnel"
        :rows="rows"
        :columns="columns"
        row-key="id"
        virtual-scroll
        dense
        v-model:pagination="pagination"
        selection="multiple"
        v-model:selected="selected"
        :rows-per-page-options="[0]"
        class="q-ma-md q-mt-sm q-pt-xs"
      >
        <template v-slot:top>
          <div class="row q-col-gutter-sm q-pb-xs">
            <div class="col-auto">
            <q-btn
              color="red"
              icon="delete"
              :disable="selected.length === 0"
              @click="beforeDelete(true)"
              class="custom-btn"
              glossy
              size="sm"
            >
              <q-tooltip
                anchor="center right"
                self="center left"
                :offset="[10, 10]"
              >
                <strong>Delete </strong> Appointment
              </q-tooltip>
            </q-btn>
          </div>

            <div style=" position: absolute; max-width: 400px; right: 10px;" class="class-auto">
              <q-select
                v-if="!adminInformation"
                style="width: 250px;"
                outlined
                label="Branch name"
                hide-bottom-space
                dense
                v-model="branch_name"
                emit-value
                map-options
                :options="branchList"
                option-label="branch_name"
                option-value="id"
              />
              <q-input
                  filled
                  dense
                  outlined
                  class="bg-accent text-black"
                  v-model="fromDate"
                  type="date"
                  label="Appointment Date"
                />
            </div> 
            
          </div>
        </template>
        
        <!-- Status cell template for the table -->
        <template v-slot:body-cell-status="props">
          <q-td :props="props">
            <q-badge
              :color=" props.row.status === 'Completed'  ? 'green' : 'red'" 
            >
              {{ props.row.status }}  <!-- Display the status text -->
            </q-badge>
          </q-td>
        </template>
  
        <template v-slot:body-cell-actions="props">
          <q-td :props="props">
            <div class="q-gutter-x-sm ">
              <q-btn
                v-if="props.row.status == 'Arrived' || props.row.status == 'Booked'"
                square
                color="positive"
                icon="check"
                dense
                size="sm"
                class="custom-btn2"
                @click="handleShowForm(props.row)"
              >
                <q-tooltip
                  anchor="center left"
                  self="center right"
                  :offset="[10, 10]"
                  class="bg-secondary"
                >
                  Change Status
                </q-tooltip>
              </q-btn>
              <q-btn
                v-if="props.row.status == 'Arrived' || props.row.status == 'Booked'"
                square
                color="negative "
                icon="close"
                dense
                size="sm"
                class="custom-btn2"
                @click="beforeDelete(false, props.row)"
              >
                <q-tooltip
                  anchor="center right"
                  self="center left"
                  :offset="[10, 10]"
                  class="bg-secondary"
                >
                  Delete appointment
                </q-tooltip>
              </q-btn>
            </div>
          </q-td>
        </template>
      </q-table>
  
    </q-page>
  </template>
  
  <script>
  import { defineComponent, ref, onMounted, onUnmounted, watch } from "vue";
  import { $axios, $notify, Dialog } from "boot/app";
  import { useQuasar } from "quasar";
  
  const URL = "/appointment";
  
  export default defineComponent({
    name: "TellerIndexPage",
    setup() {
      const $dialog = useQuasar();
      const rows = ref([]);
      const branchList = ref([ { id: 0, branch_name:'All Branches'} ])
      const branch_name = ref(null);
      const columns = ref([]);
      const fromDate = ref(null);
  
      const selected = ref([]);
      const adminInformation = ref (null);
  
      const getTableData = async () => {
        try {
          if (adminInformation.value && adminInformation.value.branch_id != null) {
            const { data } = await $axios.post(URL + "/index",{
              branch_id: adminInformation.value.branch_id,
              appointment_date: fromDate.value
            });
            rows.value.splice(0, rows.value.length, ...data.rows);
          } else {
            if (branch_name.value != null) {
              const { data } = await $axios.post(URL + "/index",{
                branch_id: branch_name.value,
                appointment_date: fromDate.value
              });
              rows.value.splice(0, rows.value.length, ...data.rows);
            }else {
              const { data } = await $axios.post(URL + "/index");
              rows.value.splice(0, rows.value.length, ...data.rows);
            }
          }
        } catch (error) {
          console.log(error);
        }
      };
  
      const handleCancel = async (dataHandleCancel) => {
        try {
          const { data } = await $axios.post("/cancel/Appointment", { dataHandleCancel });
          $notify("positive", "check", data.message);
  
          for (const x of ids) {
            const index = rows.value.findIndex((o) => o.id === x);
            if (index !== -1) {
              rows.value.splice(index, 1);
            }
          }
          selected.value.splice(0, selected.value.length);
        } catch (error) {
          console.error("Error deleting tellers:", error);
          $notify("negative", "error", error.response.data.message);
        }
      };
  
      const beforeDelete = (isMany, row) => {
        const data = isMany
                  ? selected.value.map(x => ({
                      id: x.id,
                      branch_id: x.branch_id,
                      appointment_date: x.appointment_date
                    }))
                  : [{
                      id: row.id,
                      branch_id: row.branch_id,
                      appointment_date: row.appointment_date
                    }];

        $dialog
          .dialog({
            title: "Confirm",
            message: "Are you sure you want to cancel apppointment?",
            cancel: true,
            persistent: true,
            ok: {
              label: "Yes",
              color: "primary",
              unelevated: true,
              style: "width: 125px;",
            },
            cancel: {
              label: "Cancel",
              color: "red-8",
              unelevated: true,
              style: "width: 125px;",
            },
            style: "border-radius: 12px; padding: 16px;",
          })
          .onOk(async () => {
            handleCancel(data);
            selected.value = [];
          })
          .onDismiss(() => {
            // console.log('I am triggered on both OK and Cancel')
          });
      };

      const handleShowForm = async (row) => {
        try {     
        $dialog
          .dialog({
            title: "Confirm",
            message: `Please confirm if you would like to update the status ${row.status}?`,
            cancel: true,
            persistent: true,
            ok: {
              label: "Yes",
              color: "primary",
              unelevated: true,
              style: "width: 125px;",
            },
            cancel: {
              label: "Cancel",
              color: "red-8",
              unelevated: true,
              style: "width: 125px;",
            },
            style: "border-radius: 12px; padding: 16px;",
          })
          .onOk(async () => { 
            if (row.status == 'Booked') {
              row.status = 'Arrived'
            } else if (row.status === 'Arrived') {
              row.status = 'Completed'
            }

            const { data } = await $axios.post('/appointment/updateStats', row)
            
            if (data.message) {
              $notify('positive','check', data.message)
            }
          })
          .onDismiss(() => {
            // console.log('I am triggered on both OK and Cancel')
          });
        } catch (error) {
            console.log(error)
        }
      }
  
      let dataTimeout;
      const optimizedFetchData = async () => {
            await getTableData()
            dataTimeout = setTimeout(optimizedFetchData, 5000); // Recursive Timeout
          };
  
          const fetchBranch = async () => {
            try {
              const { data } = await $axios.post('/type/Branch');
  
              branchList.value = [{id: 0, branch_name: 'All Branches'}, ...data.branch]
            } catch (error) {
              if (error.response.status === 422) 
                console.log(error)
              }
          }
  
          const fetchColumn = async () => {
            try {
              if (adminInformation.value && adminInformation.value.branch_id != null) {
                columns.value = [
                  {
                    name: "reference_number",
                    label: "Referece Number",
                    align: "left",
                    field: "referenceNumber",
                    sortable: true,
                  },
                  {
                    name: "name",
                    label: "Full name",
                    align: "left",
                    field: "name",
                    sortable: true,
                  },
                  {
                    name: "email",
                    label: "Email",
                    align: "left",
                    field: "email",
                    sortable: true,
                  },
                  {
                    name: "type_id",
                    label: "Service type",
                    align: "left",
                    field: "type_name",
                    sortable: true,
                  },
                  {
                    name: "appointment_date",
                    label: "Appointment Date",
                    align: "left",
                    field: "appointment_date",
                  },
                  {
                    name: "status",
                    label: "Status",
                    align: "left",
                    field: 'status',
                    sortable: false,
                  },
                  {
                    name: "actions",
                    label: "Actions",
                    align: "left",
                    sortable: false,
                  },
                ]
              }else {
                columns.value = [
                {
                    name: "branch_name",
                    label: "Branch name",
                    align: "left",
                    field: "branch_name",
                    sortable: "true",
                  },
                  {
                    name: "reference_number",
                    label: "Referece Number",
                    align: "left",
                    field: "referenceNumber",
                    sortable: true,
                  },
                  {
                    name: "name",
                    label: "Full name",
                    align: "left",
                    field: "name",
                    sortable: true,
                  },
                  {
                    name: "email",
                    label: "Email",
                    align: "left",
                    field: "email",
                    sortable: true,
                  },
                  {
                    name: "type_id",
                    label: "Service type",
                    align: "left",
                    field: "type_name",
                    sortable: true,
                  },
                  {
                    name: "appointment_date",
                    label: "Appointment Date",
                    align: "left",
                    field: "appointment_date",
                  },
                  {
                    name: "status",
                    label: "Status",
                    align: "left",
                    field: 'status',
                    sortable: false,
                  },
                  {
                    name: "actions",
                    label: "Actions",
                    align: "left",
                    sortable: false,
                  },
                ]
              }
            } catch (error) {
              if (error.response.status === 422) {
                console.log(error)
              }
            }
          }
  
          watch ([() => branch_name.value, () => fromDate.value], ([newBranch, newFromDate]) => {
            getTableData()
          });
          onMounted(() => {
            const storeManagerInfo = localStorage.getItem('managerInformation');
            if (storeManagerInfo) {
              adminInformation.value = JSON.parse(storeManagerInfo)
            }
            fetchColumn()
            fetchBranch()
            optimizedFetchData()
            getTableData()
            
            // Make sure `branch_name` is set to a meaningful default value
            if (!branch_name.value) {
              branch_name.value = 0;  // Set default to 'All branches' if needed
            }
          })
  
          onUnmounted(() => {
            clearTimeout(dataTimeout)
          });
  
      return {
        rows,
        columns,
        selected,
        pagination: ref({ page: 1, rowsPerPage: 10 }),
        URL,
        beforeDelete,
        adminInformation,
        branchList,
        branch_name,
        handleShowForm,
        fromDate
      };
    },
  });
  </script>
  
  <style scoped>
  .custom-btn {
    width: 30px;
    height: 30px;
    text-align: center;
    border-radius: 5px;
  }
  
  .custom-btn2 {
    width: 25px; /* Adjust as needed */
    height: 25px;
    margin-left: 5px;
    border-radius: 5px;
  }
  </style>
  <style >
    span.q-table__bottom-item{
      width: 200px;
      text-align: right;
      display: flex;
      justify-content: flex-end;
    }
  </style>
  