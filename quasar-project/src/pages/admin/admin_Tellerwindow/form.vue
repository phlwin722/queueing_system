<template>
    <q-dialog @hide="closeDialog" v-model="isShow">
        <q-card>
            <q-card-section class="row items-center q-pb-none">
                <div class="text-h6">{{ formMode }} Window</div>
                <q-space />
                <q-btn icon="close" flat round dense v-close-popup />
            </q-card-section>

            <q-card-section>
                <div class="row q-col-gutter-sm">
                    <div class="col-12">
                        <q-input 
                            outlined 
                            v-model="formData.name" 
                            label="Window:" 
                            dense
                           
                            hide-bottom-space
                            :error="!!formError.name"
                            :error-message="formError.name"
                        />
                    </div>

                    <div class="col-12">
                        <q-select
                            outlined
                            v-model="formData.teller_id"
                            label="Assign Personnel"
                            dense
                            hide-bottom-space
                            :error="!!formError.teller_id"
                            :error-message="formError.teller_id"
                            :options="tellers"
                            option-value="value"
                            option-label="label"
                        />
                    </div>

                    <div class="col-12">
                        <q-select
                            outlined
                            v-model="formData.type_id"
                            label="Window Type"
                            dense
                            hide-bottom-space
                            :error="!!formError.type_id"
                            :error-message="formError.type_id"
                            :options="types"
                            option-value="value"
                            option-label="label"
                            
                        />
                    </div>
                </div>
            </q-card-section>

            <q-card-actions align="right">
                <q-btn flat color="orange" label="Save" @click="handleSubmitForm" />
            </q-card-actions>

            <q-inner-loading :showing="isLoading">
                <q-spinner-gears size="50px" color="orange" />
            </q-inner-loading>
        </q-card>
    </q-dialog>
</template>

<script>
import { defineComponent, ref } from "vue";
import { $axios, $notify } from 'boot/app';

export default defineComponent({
    name: "WindowFormPage",
    props: {
        url: String,
        fetchWindows: Function,
        rows: Array,
    },
    setup(props) {
        const tellers = ref([]);
        const types = ref([]);
        const isShow = ref(false);
        const isLoading = ref(false);
        const formData = ref({ id: null, name: '', teller_id: null, type_id: null });
        const formError = ref({});
        const formMode = ref('New');

        // Fetch Dropdown Data
        const fetchDropdownData = async () => {
            try {
                console.log('Fetching dropdown data...');
                const { data } = await $axios.get(`${props.url}/form`);
                console.log('Dropdown data fetched:', data);
                tellers.value = data.tellers || [];
                types.value = data.types || [];
            } catch (error) {
                console.error('❌ Error fetching dropdown data:', error);
            }
        };

        // Show Dialog for Create/Edit
        // Show Dialog for Create/Edit
        const showDialog = async (mode, row) => {
    formMode.value = mode === 'new' ? 'New' : 'Edit';
    await fetchDropdownData();

    if (mode === 'edit') {
        console.log("📝 Editing Window:", row);

        formData.value = { 
            id: row.id, 
            name: row.name, 
            teller_id: row.teller || null,
            type_id: row.type || null 
        };
    } else {
        // No need to fetch latest from backend, just use existing rows in the table
        const windowCount = props.rows?.length ?? 0; 
        const nextNumber = windowCount + 1;

        formData.value = {
            id: null,
            name: `Window ${nextNumber}`, // ✅ Default "Window 1" when empty
            teller_id: null,
            type_id: null
        };
    
    }

    console.log("📢 Showing dialog...");
    isShow.value = true; // ✅ Dialog will always show
};


        // Close Dialog
        const closeDialog = () => {
            isShow.value = false;
            formData.value = { id: null, name: '', teller_id: null, type_id: null };
            formError.value = {};
        };
        const validateForm = () => {
    let errors = {};

    if (!formData.value.teller_id) {
        errors.teller_id = "The teller field is required.";
    }

    if (!formData.value.type_id) {
        errors.type_id = "The type field is required.";
    }
    formError.value = errors;

    return Object.keys(errors).length === 0; // Return true kung walang errors
};

        // Submit Form (Create or Update)
        const handleSubmitForm = async () => {
    isLoading.value = true;
    formError.value = {}; // Reset errors

    // **Check frontend validation**
    if (!validateForm()) {
        isLoading.value = false;
        return; // Stop execution kung may errors
    }

    try {
        const mode = formMode.value === 'New' ? '/create' : '/update';

        const payload = {
            id: formData.value.id,
            name: formData.value.name,
            teller_id: formData.value.teller_id?.value ?? null,
            type_id: formData.value.type_id?.value ?? null
        };

        const { data } = await $axios.post(`${props.url}${mode}`, payload);

        $notify('positive', 'done', data.message);
        await props.fetchWindows(); // Refresh table data
        closeDialog();
    } catch (error) {
        if (error.response?.status === 422 && error.response?.data?.errors) {
            console.log("⚠️ Validation Errors:", error.response.data.errors);
            
            // Convert Laravel errors object into Vue-friendly format
            formError.value = Object.fromEntries(
                Object.entries(error.response.data.errors).map(([key, value]) => [key, value.join(', ')])
            );
        } else {
            console.error('❌ Unexpected Error:', error);
            $notify('negative', 'error', 'Something went wrong!');
        }
    } finally {
        isLoading.value = false; // Stop loading spinner
    }
};



        return {
            isShow,
            isLoading,
            showDialog,
            closeDialog,
            formData,
            formError,
            formMode,
            handleSubmitForm,
            tellers, 
            types
        };
    }
});

// Only Validations Error Validations

//:options="availableTypes"
// import { defineComponent, ref, computed } from "vue";

// export default defineComponent({
//     name: "WindowFormPage",
//     props: {
//         url: String,
//         fetchWindows: Function,
//         rows: Array, // ✅ Windows List
//     },
//     setup(props) {
//         const tellers = ref([]);
//         const types = ref([]);
//         const isShow = ref(false);
//         const isLoading = ref(false);
//         const formData = ref({ id: null, name: '', teller_id: null, type_id: null });
//         const formError = ref({});
//         const formMode = ref('New');

//         // ✅ Compute Available Types (Filter Out Already Used Type IDs)
//         const availableTypes = computed(() => {
//             const usedTypeIds = props.rows?.map(w => w.type_id).filter(id => id !== null) || [];
//             return types.value.filter(type => !usedTypeIds.includes(type.value));
//         });

//         // Fetch Dropdown Data
//         const fetchDropdownData = async () => {
//             try {
//                 console.log('Fetching dropdown data...');
//                 const { data } = await $axios.get(`${props.url}/form`);
//                 console.log('Dropdown data fetched:', data);
//                 tellers.value = data.tellers || [];
//                 types.value = data.types || [];
//             } catch (error) {
//                 console.error('❌ Error fetching dropdown data:', error);
//             }
//         };

//         // Show Dialog for Create/Edit
//         const showDialog = async (mode, row) => {
//             formMode.value = mode === 'new' ? 'New' : 'Edit';
//             await fetchDropdownData();

//             if (mode === 'edit') {
//                 console.log("📝 Editing Window:", row);
//                 formData.value = { 
//                     id: row.id, 
//                     name: row.name, 
//                     teller_id: row.teller || null,
//                     type_id: row.type || null 
//                 };
//             } else {
//                 const windowCount = props.rows?.length ?? 0; 
//                 const nextNumber = windowCount + 1;

//                 formData.value = {
//                     id: null,
//                     name: `Window ${nextNumber}`,
//                     teller_id: null,
//                     type_id: null
//                 };
//             }

//             console.log("📢 Showing dialog...");
//             isShow.value = true;
//         };

//         // Close Dialog
//         const closeDialog = () => {
//             isShow.value = false;
//             formData.value = { id: null, name: '', teller_id: null, type_id: null };
//             formError.value = {};
//         };

//         // Submit Form (Create or Update)
//         const handleSubmitForm = async () => {
//             isLoading.value = true;
//             try {
//                 const mode = formMode.value === 'New' ? '/create' : '/update';

//                 const payload = {
//                     id: formData.value.id,
//                     name: formData.value.name,
//                     teller_id: formData.value.teller_id ? formData.value.teller_id.value : null,
//                     type_id: formData.value.type_id ? formData.value.type_id.value : null
//                 };

//                 const { data } = await $axios.post(`${props.url}${mode}`, payload);

//                 $notify('positive', 'done', data.message);
//                 await props.fetchWindows(); // ✅ Refresh table data
//                 closeDialog();
//             } catch (error) {
//                 if (error.response?.status === 422) {
//                     console.log("Validation Errors:", error.response.data.errors);
//                     formError.value = error.response.data.errors;
//                 } else {
//                     console.error('Error:', error);
//                     $notify('negative', 'error', 'Something went wrong!');
//                 }
//             } finally {
//                 isLoading.value = false;
//             }
//         };

//         return {
//             isShow,
//             isLoading,
//             showDialog,
//             closeDialog,
//             formData,
//             formError,
//             formMode,
//             handleSubmitForm,
//             tellers, 
//             types,
//             availableTypes // ✅ Use this instead of `types`
//         };
//     }
// });

</script>