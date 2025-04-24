 

<template>
  <q-page class="q-px-sm" style="height: auto; min-height: unset">
    <div class="q-ma-md bg-white q-pa-sm shadow-1">
      <q-breadcrumbs class="q-mx-sm">
        <q-breadcrumbs-el icon="home" to="/admin/dashboard" />
        <q-breadcrumbs-el label="Archive" icon="archive" />
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
              color="green"
              icon="restore"
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
                <strong>Restore </strong> 
              </q-tooltip>
            </q-btn>
          </div>

          <div v-if="!adminManagerInformation" class="class-auto">
            <q-select
              style="width: 250px; position: absolute;  right: 230px;"
              outlined
              label="Branch name"
              hide-bottom-space
              dense
              v-model="branch_value"
              emit-value
              map-options
              :options="branchList"
              option-label="branch_name"
              option-value="id"
            />
            <q-select
                style="width: 200px; position: absolute;  right: 10px;"
                v-model="choosingFetch"
                outlined
                label="Choose"
                hide-bottom-space
                dense
                :options="choose"
                emit-value
                map-options
                option-label="label"
                option-value="id"
            />
          </div>
        </div>
      </template>


      <template v-slot:body-cell-actions="props">
        <q-td :props="props">
          <div class="q-gutter-x-sm ">
            <q-btn
              square
              color="positive "
              icon="restore"
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
                Restore
              </q-tooltip>
            </q-btn>
          </div>
        </q-td>
      </template>
    </q-table>
  </q-page>
</template>

<script>
import { defineComponent, ref, onMounted, watch } from "vue";
import { $axios, $notify } from "boot/app";
import { useQuasar } from "quasar";

const URL = "/archieve";

export default defineComponent({
  name: "TellerIndexPage",
  setup() {
    const $dialog = useQuasar();
    const rows = ref([]);
    const branchList = ref([ { id: 0, branch_name:'All Branches'} ])
    const branch_value = ref(null);
    const columns = ref([]);
    const choose = ref ([
        {id: 0, label:'Manager'}, 
        {id: 1, label:'Personel'}
      ])

      const choosingFetch = ref(null);

      const selected = ref([]);
      const adminManagerInformation = ref (null);

      const getTableData = async () => {
        try {
          const { data } = await $axios.post('/admin/archive', {
            branch_id: branch_value.value,
            personel: choosingFetch.value
          });
          rows.value = data.rows;
        } catch (error) {
          console.log('Error fetching window logs:', error);
        }
      };

      const handleRestore = async (ids) => {
        try {
          const { data } = await $axios.post(URL + "/restore", { ids });
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
        
          if (error.response.status === 400) {
            $notify('negative', 'error', error.response.data.error)
          }
          else {
            $notify("negative", "error", error.response.data.message);}
          }
      };

      const beforeDelete = (isMany, row) => {
      const ids = isMany ? selected.value.map((x) => x.id) : [row.id];
      let message = isMany ? "Are you sure you want to restore all personel?" : "Are you sure you want to restore these personel?"
        $dialog
          .dialog({
            title: "Confirm",
            message: message,
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
            handleRestore(ids);
          })
          .onDismiss(() => {
            // console.log('I am triggered on both OK and Cancel')
          });
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
            if (adminManagerInformation.value && adminManagerInformation.value.branch_id != null) {
              columns.value = [
                  { name: 'fullname', label: 'Full Name', align: 'left', field: 'fullname', sortable: true },
                  { name: 'username', label: 'Username', align: 'left', field: 'username', sortable: true },
                  { name: 'archived_at', label: 'Archived Date', align: 'left', field: 'archived_at', sortable: true },
                  { name: 'actions', label: 'Status', align: 'left' }
                ]
              }else {
                columns.value = [
                  { name: 'branch_name', label: 'Branch Name', align: 'left', field: 'branch_name', sortable: true },
                  { name: 'fullname', label: 'Full Name', align: 'left', field: 'fullname', sortable: true },
                  { name: 'username', label: 'Username', align: 'left', field: 'username', sortable: true },
                  { name: 'archived_at', label: 'Archived Date', align: 'left', field: 'archived_at', sortable: true },
                  { name: 'actions', label: 'Action', align: 'center' }
                ]
             }

          } catch (error) {
            if (error.response.status === 422) {
              console.log(error)
            }
          }
        }

        watch([() => branch_value.value, () => choosingFetch.value], async ([newBranch_value, newChoosingFetch]) => {
          getTableData(); // Only call getTableData once when either value changes
        });

        onMounted(() => {
        const managerInformation = localStorage.getItem('managerInformation')
        if (managerInformation) {
          console.log(managerInformation)
          adminManagerInformation.value = JSON.parse(managerInformation)
        }

        if (!managerInformation) {
            branch_value.value = 0;
        }else {
          branch_value.value = adminManagerInformation.value.branch_id
          choosingFetch.value = 1
        }
        getTableData()
        fetchBranch()
        fetchColumn();
      })

    return {
      rows,
      columns,
      selected,
      pagination: ref({ rowsPerPage: 0 }),
      URL,
      beforeDelete,
      adminManagerInformation,
      branchList,
      choosingFetch,
      branch_value,
      choose
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
  }
</style>