<template>
  <q-page>
      <div class="q-pa-md">
          <q-table
              title="Window"
              :rows="rows"
              :columns="columns"
              row-key="id"
              virtual-scroll
              v-model:pagination="pagination"
              selection="multiple"
              v-model:selected="selected"
              :rows-per-page-options="[0]"
          >
              <template v-slot:top>
                  <div class="q-gutter-x-sm">
                      <q-btn color="primary" label="Add Window" @click="handleShowForm('new')" />
                      <q-btn 
                          color="red" 
                          label="Delete Window(s)"  
                          :disable="selected.length === 0"
                          @click="beforeDelete(true)"
                      />
                  </div>
              </template>
              
              <template v-slot:body-cell-actions="props">
                  <q-td :props="props">
                      <div class="q-gutter-x-sm"> 
                          <q-btn square color="primary" icon="edit" dense @click="handleShowForm('edit', props.row)" />
                          <q-btn square color="red" icon="delete" dense @click="beforeDelete(false, props.row)" />
                      </div>
                  </q-td>
              </template>
          </q-table>
      </div>
      <my-form ref="dialogForm" :url="URL" :rows="rows" :tellers="tellers" :types="types" :fetchWindows="fetchWindows" />

  </q-page>
</template>

<script>
import { defineComponent, ref ,computed,nextTick } from 'vue';
import { $axios, $notify, Dialog } from 'boot/app';
import MyForm from 'pages/admin/admin_Tellerwindow/form.vue';
await nextTick();
const rowsComputed = computed(() => rows.value);
const URL = "/windows";
export default defineComponent({
  name: 'WindowIndexPage',
  components: { MyForm },
  setup() {
      const rows = ref([]);
      const columns = ref([
  { name: 'name', label: 'Window Name', align: 'left', field: 'name', sortable: true },
  { name: 'teller', label: 'Assigned Personnel', align: 'left', field: row => row.teller?.label || 'N/A' },
  { name: 'type', label: 'Window Type', align: 'left', field: row => row.type?.label || 'N/A' },
  { name: 'actions', label: 'Actions', align: 'left', sortable: false }
]);
      
      const selected = ref([]);
      const dialogForm = ref(null);
      const tellers = ref([]);
      const types = ref([]);

      const fetchFormData = async () => {
          try {
              const { data } = await $axios.get(`${URL}/form`);
              tellers.value = data.tellers.map(t => ({ value: t.id, label: t.name }));  
types.value = data.types.map(t => ({ value: t.id, label: t.name }));

          } catch (error) {
              console.error("❌ Error fetching form data:", error);
          }
      };

      const fetchWindows = async () => {
  try {
      const { data } = await $axios.get(`${URL}/index`);
      console.log("🔍 Raw API Response:", JSON.stringify(data, null, 2));
      rows.value = data.rows.map(row => ({
  id: row.id,
  name: row.name || "No Name",
  teller: row.teller ? { value: row.teller.value, label: row.teller.label } : null,
  type: row.type ? { value: row.type.value, label: row.type.label } : null
}));


      console.log("✅ Processed Windows:", rows.value); // <-- FINAL DATA SA TABLE
  } catch (error) {
      console.error("❌ Error fetching windows:", error);
  }
};


      fetchFormData();
      fetchWindows();

      const handleShowForm = (mode, row) => {
          dialogForm.value.showDialog(mode, row);
      };

      const handleDelete = async (ids) => { 
  try {
      const { data } = await $axios.post(`${URL}/delete`, { ids }); // Dapat `ids` mismo ang pinapasa
      $notify('positive', 'check', data.message);
      rows.value = rows.value.filter(row => !ids.includes(row.id));
      selected.value = [];
  } catch (error) {
      console.error('❌ Error deleting windows:', error);
      $notify('negative', 'error', error.response?.data?.message || 'Error deleting records');
  }
};


      const beforeDelete = (isMany, row) => {
          const ids = isMany ? selected.value.map(x => x.id) : [row.id];
          const message = isMany 
              ? 'Are you sure you want to delete these windows?'
              : `Are you sure you want to delete "${row.name}"?`;

          Dialog.create({
              title: 'Confirm Delete',
              message: message,
          }).onOk(() => {
              handleDelete(ids);
          });
      };

      return {
          rows,
          columns,
          selected,
          pagination: ref({ rowsPerPage: 5 }),
          dialogForm,
          handleShowForm,
          URL,
          beforeDelete,
          fetchWindows
      };
  }
});
</script>