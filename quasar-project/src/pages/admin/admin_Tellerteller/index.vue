<template>
  <q-page class="q-px-sm" style="height: auto; min-height: unset">
    <div class="q-ma-md bg-white q-pa-sm shadow-1">
      <q-breadcrumbs class="q-mx-sm">
        <q-breadcrumbs-el icon="home" to="/admin/dashboard" />
        <q-breadcrumbs-el label="Teller" icon="person" />
        <q-breadcrumbs-el
          label="Teller Personnel"
          icon="groups"
          to="/admin/teller/tellers"
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
                <strong>Add </strong> Personnel
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
                anchor="center right"
                self="center left"
                :offset="[10, 10]"
              >
                <strong>Delete </strong> Personnel
              </q-tooltip>
            </q-btn>
          </div>

          <div v-if="!adminInformation" class="class-auto">
            <q-select
              style="width: 250px; position: absolute; right: 270px"
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
          </div>

          <div v-if="!adminInformation" class="class-auto">
            <q-select
              style="width: 250px; position: absolute; right: 10px"
              outlined
              label="Roles"
              hide-bottom-space
              dense
              v-model="personnel_role"
              emit-value
              map-options
              :options="['Teller', 'Manager']"
            />
          </div>
        </div>
      </template>

      <!-- types cell template for the table -->
      <template v-slot:body-cell-type_names="props">
        <q-td :props="props">
          {{
            props.row.type_names ? props.row.type_names : "No service assigned"
          }}
        </q-td>
      </template>

      <!-- Status cell template for the table -->
      <template v-slot:body-cell-status="props">
        <q-td :props="props">
          <q-badge :color="props.row.status === 'Online' ? 'green' : 'red'">
            {{ props.row.status === null ? "Offline" : props.row.status }}
            <!-- Display the status text -->
          </q-badge>
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
                Edit Personnel
              </q-tooltip>
            </q-btn>
            <q-btn
              square
              color="negative "
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
                Delete Personnel
              </q-tooltip>
            </q-btn>
          </div>
        </q-td>
      </template>
    </q-table>

    <my-form ref="dialogForm" :url="URL" :rows="rows" :types="types" />
  </q-page>
</template>

<script>
import {
  defineComponent,
  ref,
  onMounted,
  onUnmounted,
  watch,
  inject,
} from "vue";
import { $axios, $notify, Dialog } from "boot/app";
import MyForm from "pages/admin/admin_Tellerteller/form.vue";
import { useQuasar } from "quasar";

const URL = "/tellers";

export default defineComponent({
  name: "TellerIndexPage",
  components: { MyForm },
  setup() {
    const $dialog = useQuasar();
    const rows = ref([]);
    const branchList = ref([{ id: 0, branch_name: "All Branches" }]);
    const branch_name = ref(null);
    const columns = ref([]);
    const personnel_role = ref("");
    const selected = ref([]);
    const dialogForm = ref(null);
    const adminInformation = ref(null);
    const isTeller = ref(false);
    // Access the global pusher instance injected into the app
    const pusher = inject("$pusher"); // Injecting $pusher that was set in the boot file

    const getTableData = async () => {
      if (isTeller.value == true) {
        console.log(personnel_role.value);
        try {
          if (
            adminInformation.value &&
            adminInformation.value.branch_id != null
          ) {
            const { data } = await $axios.post(URL + "/index", {
              branch_id: adminInformation.value.branch_id,
            });
            rows.value.splice(0, rows.value.length, ...data.rows);
            data.rows.forEach((newRow, index) => {
              if (rows.value[index]) {
                // Update individual fields
                rows.value[index].role = "Teller";
              }
            });
          } else {
            if (branch_name.value != null) {
              const { data } = await $axios.post(URL + "/index", {
                branch_id: branch_name.value,
              });
              rows.value.splice(0, rows.value.length, ...data.rows);
              data.rows.forEach((newRow, index) => {
                if (rows.value[index]) {
                  // Update individual fields
                  rows.value[index].role = "Teller";
                }
              });
            } else {
              const { data } = await $axios.post(URL + "/index");
              rows.value.splice(0, rows.value.length, ...data.rows);
              data.rows.forEach((newRow, index) => {
                if (rows.value[index]) {
                  // Update individual fields
                  rows.value[index].role = "Teller";
                }
              });
            }
            fetchColumn();
          }
        } catch (error) {
          console.log(error);
        }
      } else {
        try {
          if (branch_name.value != null) {
            const { data } = await $axios.post("/manager" + "/index", {
              branch_id: branch_name.value,
            });
            rows.value.splice(0, rows.value.length, ...data.rows);
            data.rows.forEach((newRow, index) => {
              if (rows.value[index]) {
                // Update individual fields
                rows.value[index].role = "Manager";
              }
            });
          } else {
            const { data } = await $axios.post("/manager" + "/index");
            rows.value.splice(0, rows.value.length, ...data.rows);
            console.log("try", rows.value); // Add this line to check the rows
            data.rows.forEach((newRow, index) => {
              if (rows.value[index]) {
                // Update individual fields
                rows.value[index].role = "Manager";
              }
            });
          }
          fetchColumn();
        } catch (error) {
          console.log(error);
        }
      }
    };

    const handleShowForm = (mode, row) => {
      dialogForm.value.showDialog(mode, row);
    };

    const handleDelete = async (ids) => {
      try {
        if (personnel_role.value == "Teller") {
          const { data } = await $axios.post(URL + "/delete", { ids });
          $notify("positive", "check", data.message);
        } else {
          const { data } = await $axios.post("/manager" + "/delete", { ids });
          $notify("positive", "check", data.message);
        }

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
          $notify("negative", "error", error.response.data.error);
        } else {
          $notify("negative", "error", error.response.data.message);
        }
      }
    };

    const beforeDelete = (isMany, row) => {
      const ids = isMany ? selected.value.map((x) => x.id) : [row.id];

      $dialog
        .dialog({
          title: "Confirm",
          message: "Are you sure you want to delete these personel?",
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
        })
        .onDismiss(() => {
          // console.log('I am triggered on both OK and Cancel')
        });
    };

    const types = ref([]);

    const getTypes = async () => {
      try {
        const { data } = await $axios.post(URL + "/index");
        console.log("Fetched Types:", data.rows);
        types.value.splice(0, types.value.length, ...data.rows);
      } catch (error) {
        console.error("Error fetching teller types:", error);
      }
    };

    const fetchBranch = async () => {
      try {
        const { data } = await $axios.post("/type/Branch");

        branchList.value = [
          { id: 0, branch_name: "All Branches" },
          ...data.branch,
        ];
      } catch (error) {
        if (error.response.status === 422) console.log(error);
      }
    };

    const fetchColumn = async () => {
      try {
        if (
          adminInformation.value &&
          adminInformation.value.branch_id != null
        ) {
          columns.value = [
            {
              name: "teller_firstname",
              label: "First Name",
              align: "left",
              field: "teller_firstname",
              sortable: true,
            },
            {
              name: "teller_lastname",
              label: "Last Name",
              align: "left",
              field: "teller_lastname",
              sortable: true,
            },
            {
              name: "teller_username",
              label: "Username",
              align: "left",
              field: "teller_username",
              sortable: true,
            },
            {
              name: "type_names",
              label: "Type",
              align: "left",
              field: "type_names",
              sortable: true,
            },
            {
              name: "status",
              label: "Status",
              align: "left",
              field: "status",
            },
            {
              name: "actions",
              label: "Actions",
              align: "left",
              sortable: false,
            },
            /* { name: 'role', align: 'left', field: 'role', sortable: true, classes: 'hidden' }, */
          ];
        } else {
          if (personnel_role.value == "Teller") {
            columns.value = [
              {
                name: "branch_name",
                label: "Branch name",
                align: "left",
                field: "branch_name",
                sortable: "true",
                format: (val) => val ?? "No branch assigned",
              },
              {
                name: "teller_firstname",
                label: "First Name",
                align: "left",
                field: "teller_firstname",
                sortable: true,
              },
              {
                name: "teller_lastname",
                label: "Last Name",
                align: "left",
                field: "teller_lastname",
                sortable: true,
              },
              {
                name: "teller_username",
                label: "Username",
                align: "left",
                field: "teller_username",
                sortable: true,
              },
              {
                name: "type_names",
                label: "Type",
                align: "left",
                field: "type_names",
                sortable: true,
              },
              {
                name: "status",
                label: "Status",
                align: "left",
                field: "status",
              },
              {
                name: "actions",
                label: "Actions",
                align: "left",
                sortable: false,
              },
              /* { name: 'role', align: 'left', field: 'role', sortable: true, classes: 'hidden' }, */
            ];
          } else {
            columns.value = [
              {
                name: "branch_name",
                label: "Branch name",
                align: "left",
                field: "branch_name",
                sortable: "true",
                format: (val) => val ?? "No branch assigned",
              },
              {
                name: "teller_firstname",
                label: "First Name",
                align: "left",
                field: "teller_firstname",
                sortable: true,
              },
              {
                name: "teller_lastname",
                label: "Last Name",
                align: "left",
                field: "teller_lastname",
                sortable: true,
              },
              {
                name: "teller_username",
                label: "Username",
                align: "left",
                field: "teller_username",
                sortable: true,
              },
              {
                name: "status",
                label: "Status",
                align: "left",
                field: "status",
              },
              {
                name: "actions",
                label: "Actions",
                align: "left",
                sortable: false,
              },
              /* { name: 'role', align: 'left', field: 'role', sortable: true, classes: 'hidden' }, */
            ];
          }
        }
      } catch (error) {
        if (error.response.status === 422) {
          console.log(error);
        }
      }
    };

    watch(
      () => personnel_role.value,
      (newVal) => {
        if (newVal == "Teller") {
          isTeller.value = true;
          personnel_role.value = "Teller";
          getTableData();
        } else if (newVal === "") {
          isTeller.value = true;
          personnel_role.value = "Teller";
          fetchColumn();
          getTableData();
        } else {
          isTeller.value = false;
          personnel_role.value = "Manager";
          getTableData();
        }
      },
      { immediate: true } // 👈 this makes it run right away on mount
    );

    watch(
      () => branch_name.value,
      async (newVal) => {
        getTableData();
      }
    );

    onMounted(() => {
      const storeManagerInfo = localStorage.getItem("managerInformation");
      if (storeManagerInfo) {
        adminInformation.value = JSON.parse(storeManagerInfo);
      }

      // Make sure `branch_name` is set to a meaningful default value
      if (!branch_name.value) {
        branch_name.value = 0; // Set default to 'All branches' if needed
      }

      fetchColumn();
      fetchBranch();
      getTypes();
      getTableData();

      // create new pusher instance and subscribe to channel
      const pusher = new Pusher("8d3b62bc5d67d22d3605", {
        cluster: "us2",
      });

      const channel = pusher.subscribe("teller-channel");

      // listen for tellerevent on the channel
      channel.bind("TellerEvent", (data) => {
        if (data) {
          // Update the students list with the new student data
          getTableData();
        }
      });
    });

    onUnmounted(() => {
      if (pusher) {
        pusher.unsubscribe("teller-channel");
      }
    });

    return {
      rows,
      columns,
      selected,
      pagination: ref({ page: 1, rowsPerPage: 10 }),
      dialogForm,
      handleShowForm,
      URL,
      beforeDelete,
      adminInformation,
      branchList,
      branch_name,
      personnel_role,
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
<style>
span.q-table__bottom-item {
  width: 200px;
  text-align: right;
  display: flex;
  justify-content: flex-end;
}
</style>
