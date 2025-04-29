<template>
  <q-page class="q-px-sm" style="height: auto; min-height: unset">
    <div class="q-ma-md bg-white q-pa-sm shadow-1">
      <q-breadcrumbs class="q-mx-sm">
        <q-breadcrumbs-el icon="home" to="/admin/dashboard" />
        <q-breadcrumbs-el label="Teller" icon="person" />
        <q-breadcrumbs-el
          label="Teller Window"
          icon="computer"
          to="/admin/teller/window"
        />
      </q-breadcrumbs>
    </div>
    <q-table
      title="Window"
      :rows="filteredRows"
      :columns="columns"
      row-key="id"
      virtual-scroll
      dense
      v-model:pagination="pagination"
      selection="multiple"
      v-model:selected="selected"
      :rows-per-page-options="[0]"
      class="q-ma-md q-mt-sm q-pt-sm"
    >
      <template v-slot:top>
        <div class="row q-col-gutter-sm q-pb-xs">
          <div class="col-auto">
            <q-btn
              color="primary"
              icon="add"
              @click="handleShowForm('new')"
              class="custom-btn"
              glossy
              size="sm"
            >
              <q-tooltip
                anchor="center left"
                self="center right"
                :offset="[10, 10]"
              >
                <strong>Add </strong> Window
              </q-tooltip>
            </q-btn>
          </div>
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
                anchor="top middle"
                self="bottom middle"
                :offset="[10, 10]"
              >
                <strong>Delete </strong> Window
              </q-tooltip>
            </q-btn>
          </div>
          <!-- v-if="!adminInformation" -->
          <div  class="col-auto">
            <q-btn
              color="warning"
              icon="reset_tv"
              @click="beforeReset(true)"
              class="custom-btn"
              glossy
              size="sm"
              :disable="rows.length === 0"
            >
              <q-tooltip
                anchor="center right"
                self="center left"
                :offset="[10, 10]"
              >
                <strong>Reset </strong> Window
              </q-tooltip>
            </q-btn>
          </div>
          <div v-if="!adminInformation" class="col-auto">
              <q-select
                style="width: 250px; position: absolute;  right: 10px;"
                outlined
                v-model="branch_name"
                label="Branch name"
                hide-bottom-space
                dense
                emit-value
                map-options
                :options="branchList"
                option-label="branch_name"
                option-value="id"
            />
          </div>
        </div>
      </template>

      <template v-slot:body-cell-teller_name="props">
        <q-td :props="props">
          {{ props.row.teller_name != null ? props.row.teller_name : 'No teller assigned' }}
        </q-td>
      </template>

      <template v-slot:body-cell-actions="props">
        <q-td :props="props">
          <div class="q-gutter-x-sm">
            <q-btn
              square
              color="positive"
              icon="edit"
              dense
              size="sm"
              class="custom-btn2"
              @click="handleShowForm('edit', props.row)"
            >
              <q-tooltip
                anchor="center left"
                self="center right"
                :offset="[10, 10]"
                class="bg-secondary"
              >
                Edit Window
              </q-tooltip>
            </q-btn>

            <q-btn
              square
              color="negative"
              icon="delete"
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
                Delete Window
              </q-tooltip>
            </q-btn>
          </div>
        </q-td>
      </template>
    </q-table>
  </q-page>
  <my-form ref="dialogForm" :url="URL" :rows="rows" />
</template>

<script>
import { defineComponent, ref, computed, onMounted, onUnmounted, watch } from "vue";
import { $axios, $notify, Dialog } from "boot/app";
import MyForm from "pages/admin/admin_Tellerwindow/form.vue";
import { useQuasar } from "quasar";

const URL = "/windows";

export default defineComponent({
  name: "IndexPage",
  components: { MyForm },
  setup() {
    const text = ref("");
    const rows = ref([]);
    const $dialog = useQuasar();
    const autoReset = ref(false);
    const resetMinutes = ref(5); // Default to 5 minutes if not set
    let intervalId = null;
    const adminInformation = ref (null);
    const branchList = ref ([]);
    const branch_name = ref (null);

    const columns = ref([]);

    const filteredRows = computed(() => {
      return rows.value.filter((row) =>
        Object.values(row).some((value) =>
          String(value).toLowerCase().includes(text.value.toLowerCase())
        )
      );
    });

    const selected = ref([]);
    const dialogForm = ref(null);

    const getTableData = async () => {
      try {
        // if managaer login 
        if (adminInformation.value && adminInformation.value.branch_id != null) {
          const { data } = await $axios.post(URL + "/getWindows",{
            branch_id: adminInformation.value.branch_id
          });
          rows.value.splice(0, rows.value.length, ...data.rows);
          console.log(rows.value)
        }
        // if super login 
        else {
          if (branch_name.value != null) {
            const { data } = await $axios.post(URL + "/getWindows",{
              branch_id: branch_name.value
            });
            rows.value.splice(0, rows.value.length, ...data.rows);
          }else {
            const { data } = await $axios.post(URL + "/getWindows");
            rows.value.splice(0, rows.value.length, ...data.rows);
          }
        }
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
        const { data } = await $axios.post("/windows/fetch-reset-settings");
        let refreshRate = 10000; // Default: 10 seconds

        if (data.auto_reset && data.reset_time) {
          const now = new Date();
          const [hours, minutes] = data.reset_time.split(":").map(Number);

          const resetTime = new Date(
            now.getFullYear(),
            now.getMonth(),
            now.getDate(),
            hours,
            minutes - 1
          );

          let timeDiff = resetTime - now;

          refreshRate = timeDiff > 10000 ? timeDiff : 10000;
        }

        // Immediately refresh the table before setting the interval
        await getTableData();

        intervalId = setInterval(() => {
          getTableData();
        }, refreshRate);
      } catch (error) {
        console.log("Failed to fetch reset settings:", error);
      }
    };

    const beforeReset = () => {
      $dialog
        .dialog({
          title: "Confirm Reset",
          message: `Are you sure you want to reset all assigned tellers?`, // Adjusted message
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
          resetTeller();
        });
    };

    const resetTeller = async () => {
      // Check if all tellers are already reset
      const hasAssignedTellers = rows.value.some(
        (row) => row.teller_id !== null
      );
      if(adminInformation.value && adminInformation.value.branch_id != null){
        console.log("manager")
        console.log(adminInformation.value.branch_id)

        if (!hasAssignedTellers) {
        $notify("warning", "info", "All windows are already reset.");
        return;
        }

        try {
            const { data } = await $axios.post("/windows/reset-windows",{
              branch_id: adminInformation.value.branch_id
            });
            $notify("positive", "check", data.message);
          await getTableData();
        } catch (error) {
          console.log("Error:", error.response?.data || error);
          $notify(
            "negative",
            "error",
            error.response?.data?.message || "Failed to reset windows"
          );
        }
      }else{
        console.log("super")
        if (!hasAssignedTellers) {
        $notify("warning", "info", "All windows are already reset.");
        return;
        }

        try {
            const { data } = await $axios.post("/windows/reset-windows",{
              branch_id: branch_name.value
            });
            $notify("positive", "check", data.message);
          await getTableData();
        } catch (error) {
          console.log("Error:", error.response?.data || error);
          $notify(
            "negative",
            "error",
            error.response?.data?.message || "Failed to reset windows"
          );
        }
      }
    };

    const handleShowForm = (mode, row) => {
      console.log('haha', row)
      dialogForm.value.showDialog(mode, row);
    };

    const beforeDelete = (isMany, row) => {
      const ids = isMany 
            ? selected.value.map((x) => ({
              id: x.id, 
              type_id: x.type_id,
              status: x.status,
              teller_name: x.teller_name,
            })) 
            : [{
              id: row.id, 
              type_id: row.type_id,
              status: row.status,
              teller_name: row.teller_name,
            }];
      const itemNames = isMany ? "Windows" : row.window_name;

      $dialog
        .dialog({
          title: "Confirm",
          message: `Are you sure you want to delete ${itemNames}?`, // Adjusted message
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
          handleDelete(ids);
        });
    };

    const handleDelete = async (deleteWindow) => {
      try {
        // check if status of teller is online
        const onlineTellers = deleteWindow.filter(win => win.status === 'Online');

        if (onlineTellers.length > 0) {
          const tellerNames = onlineTellers.map(win => win.teller_name).join(', ');
          $notify('negative','error',`Sorry, we cannot delete the window(s) while the following teller(s) are still online: ${tellerNames}`);
          return;
        }

        const { data } = await $axios.post(URL + "/delete", { deleteWindow });
        getTableData()
        $notify("positive", "check", data.message);
        selected.value = [];
      } catch (error) {
        console.log("error", error);
        $notify("negative", "error", error.response.data.message);
      }
    };

    const fetchColumn = async () => {
      if (adminInformation.value && adminInformation.value.branch_id) {
        columns.value = [
           {
            name: "window_name",
            label: "Window Name",
            align: "left",
            field: "window_name",
            sortable: true,
          },
          {
            name: "type_id",
            label: "Window Type",
            align: "left",
            field: "type_name",
            sortable: true,
          },
          {
            name: "teller_name",
            label: "Assigned Personnel",
            align: "left",
            field: "teller_name",
            sortable: true,
            sortable: true,
          },
          /* { name: 'pId', align: 'left', field: 'pId', sortable: true, classes: 'hidden' }, */
          { name: "actions", label: "Actions", align: "left" },
        ]
      } else {
        columns.value = [
         {
            name: "branch_name",
            label: "Branch name",
            align: "left",
            field: "branch_name",
            sortable: true,
          },
           {
            name: "window_name",
            label: "Window Name",
            align: "left",
            field: "window_name",
            sortable: true,
          },
          {
            name: "type_id",
            label: "Window Type",
            align: "left",
            field: "type_name",
            sortable: true,
          },
          {
            name: "teller_name",
            label: "Assigned Personnel",
            align: "left",
            field: "teller_name",
            sortable: true,
          },
          /* { name: 'pId', align: 'left', field: 'pId', sortable: true, classes: 'hidden' }, */
          { name: "actions", label: "Actions", align: "left" },
        ]
      }
    }

    const fetchBranch = async () => {
      try {
       const { data } = await $axios.post('/type/Branch');
       branchList.value = [{id: 0, branch_name: 'All Branches'}, ...data.branch]
      } catch (error) {
        if (error.response.status == 422) {
          console.log(error)
        } 
      }
    }

    watch(()=> branch_name.value, async (newVal) => {
      getTableData()
    });
    
    onMounted(() => {
      const storeManagerInfo = localStorage.getItem('managerInformation');
      if (storeManagerInfo) {
            adminInformation.value = JSON.parse(storeManagerInfo);
        }
      fetchColumn();
      getTableData();
      fetchSettings();
      fetchBranch()
          // Make sure `branch_name` is set to a meaningful default value
          if (!branch_name.value) {
            branch_name.value = 0;  // Set default to 'All branches' if needed
          }
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
      pagination: ref({ page: 1, rowsPerPage: 10 }),
      handleShowForm,
      URL,
      beforeReset,
      beforeDelete,
      filteredRows,
      setTimeout,
      text,
      branch_name,
      branchList,
      adminInformation,
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
  width: 25px;
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
