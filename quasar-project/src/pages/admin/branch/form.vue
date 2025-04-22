<template>
    <!-- Dialog Box -->
    <q-dialog @hide="closeDialog" v-model="isShow">
        <q-card class="relative-position">
            <!-- Dialog Header -->
            <q-card-section class="row items-center q-pb-none">
                <div class="text-h6 text-primary">{{ formMode }} Branch</div>
                <q-space />
                <q-btn
                    icon="close" 
                    flat 
                    round
                    dense 
                    v-close-popup
                    style="width: 24px; height: 24px;"
                />
            </q-card-section>

            <!-- Form Inputs with Image Preview -->
            <q-card-section>
                <div class="row q-col-gutter-md">
                    <!-- Branch name -->
                    <div class="col-6">
                        <q-input
                            outlined 
                            v-model="formData.branch_name" 
                            label="Branch name" 
                            dense
                            hide-bottom-space
                            :error="formError.hasOwnProperty('branch_name')"
                            :error-message="formError.branch_name"
                            autofocus
                        />
                    </div>
                    <!-- Manager assigned -->
                    <div class="col-6">
                        <q-select
                            outlined
                            v-model="formData.manager_name"
                            label="Manager Assigned"
                            dense
                            hide-bottom-space
                            emit-value
                            map-options
                            :options="managerList"
                            option-label="fullname"
                            option-value="id"
                            :error="formError.hasOwnProperty('manager_assigned')"
                            :error-message="formError.manager_assigned"
                        />
                    </div>
                    <!-- ✅ REGION (always enabled) -->
                    <div class="col-4">
                        <q-select
                            outlined
                            v-model="formData.region"
                            label="Region"
                            dense
                            hide-bottom-space
                            option-label="label"
                            option-value="label"
                            :options="regionList"
                            emit-value
                            map-options
                            @update:model-value="fetchProvinceList"
                            :disable="false"
                            :error="formError.hasOwnProperty('region')"
                            :error-message="formError.region"
                            autofocus
                        />
                    </div>

                    <!-- ✅ PROVINCE (disabled until region selected) -->
                    <div class="col-4">
                        <q-select
                            outlined
                            v-model="formData.province"
                            label="Province"
                            dense
                            hide-bottom-space
                            option-label="label"
                            option-value="label"
                            :options="provinceList"
                            emit-value
                            map-options
                            @update:model-value="fetchCityList"
                            :disable="!formData.region"
                            :error="formError.hasOwnProperty('province')"
                            :error-message="formError.province"
                            autofocus
                        />
                    </div>

                    <!-- ✅ CITY (disabled until province selected) -->
                    <div class="col-4">
                        <q-select
                            outlined
                            v-model="formData.city"
                            label="City"
                            dense
                            hide-bottom-space
                            option-label="label"
                            option-value="label"
                            :options="cityList"
                            emit-value
                            map-options
                            @update:model-value="fetchBarangayList"
                            :disable="!formData.province"
                            :error="formError.hasOwnProperty('city')"
                            :error-message="formError.city"
                            autofocus
                        />
                    </div>

                    <!-- ✅ BARANGAY (disabled until city selected) -->
                    <div class="col-4">
                        <q-select
                            outlined
                            v-model="formData.Barangay"
                            label="Barangay"
                            dense
                            hide-bottom-space
                            option-label="label"
                            option-value="label"
                            :options="barangayList"
                            emit-value
                            map-options
                            :disable="!formData.city"
                            :error="formError.hasOwnProperty('barangay')"
                            :error-message="formError.barangay"
                            autofocus
                        />
                    </div>

                    <!-- Address -->
                    <div class="col-8">
                        <q-input
                            outlined 
                            v-model="formData.address" 
                            label="Address" 
                            dense
                            hide-bottom-space
                            :error="formError.hasOwnProperty('address')"
                            :error-message="formError.address"
                            autofocus
                        />
                    </div>
                    <!-- Status of branch -->
                    <div class="col-6">
                        <q-select
                             outlined
                            v-model="formData.status"
                            label="Status"
                            dense
                            hide-bottom-space
                            :options="branch_status"
                            emit-value
                            map-options
                            :error="formError.hasOwnProperty('status')"
                            :error-message="formError.status"
                            autofocus
                        />
                    </div>
                    <div class="col-6">
                        <q-input 
                            filled 
                            dense 
                            outlined 
                            class="text-black col-1.5"
                            v-model="formData.opening_date" 
                            type="date" 
                            label="Opening Date"
                            :error="formError.hasOwnProperty('opening_date')"
                            :error-message="formError.opening_date"
                        />
                    </div>
                </div>
            </q-card-section>
            <q-separator/>

            <!-- Actions (Save Button) -->
            <q-card-actions class="justify-center">
                <q-btn 
                    color="positive" 
                    label="Save" 
                    @click="handleSubmitForm" 
                    class="full-width" 
                    style="max-width: 150px;"
                />
            </q-card-actions>

            <!-- Loading Spinner -->
            <q-inner-loading :showing="isLoading">
                <q-spinner-gears size="50px" color="orange" />
            </q-inner-loading>
        </q-card>
    </q-dialog>
</template>

<script>
import { defineComponent, onMounted, ref, toRefs } from "vue";
import { $axios, $notify } from 'boot/app'; // Axios for API requests, Notify for UI feedback
import { useQuasar } from "quasar";
export default defineComponent({
    name: "TellerFormPage", // Component name
    props: {
        url: String, // API endpoint URL passed as a prop
        rows: Array // Array of rows (data) passed as a prop
    },
    setup(props) {
        // Reactive variables
        const isShow = ref(false); // Controls the visibility of the dialog
        const isLoading = ref(false); // Controls the loading spinner
        const formMode = ref('New'); // Tracks the mode (New or Edit) of the form

        const managerList = ref ([]);
        const regionList = ref([]);
        const provinceList = ref([]);
        const cityList = ref ([]);
        const barangayList = ref ([]);
        const loadingTime = useQuasar();

        const initFormData = () => ({
            id: null,
            branch_name: '',
            manager_name: '',
            region: '', 
            province: '', 
            city: '',
            Barangay: '',
            address: '', 
            status: '',
            opening_date: '',
        });

        const formData = ref(initFormData());  // Reactive object holding the form data
        const formError = ref({}); // Object to hold form validation errors

        // Close the dialog and reset form data and errors
        const closeDialog = () => {
            isShow.value = false;
            formData.value = initFormData(); // Reset form data
            formError.value = {}; // Reset form errors
        };

        // Show the dialog and fetch data (for edit mode, populate form with row data)
        const showDialog = async (mode, row) => {
            // Set the form mode based on the action (new or edit)
            loadingTime.loading.show({
                message: 'Some important process  is in progress. Hang on...'
            });
            
            formMode.value = mode === 'new' ? 'New' : 'Edit';

             // Fetch the fetchManager 
            await fetchManager();

            // If in 'edit' mode, populate the form with the row data
            if (mode === 'edit') {
                // Clone the row data to formData for editing, avoiding direct mutation
                formData.value = Object.assign({}, row);

                // Trigger dropdown fetches to populate values
                await fetchProvinceList(formData.value.region);
                await fetchCityList(formData.value.province);
                await fetchBarangayList(formData.value.city);
            }

            // Show the dialog by setting isShow to true
            loadingTime.loading.hide()
            isShow.value = true; 
        };

        const handleSubmitForm = async () => {
            const mode = formMode.value === 'New' ? '/create' : '/update';
            isLoading.value = true;
            formError.value = {}; // Reset form errors

            try {
                // Send form data to the server
                const { data } = await $axios.post(props.url + mode, formData.value);

                const rows = toRefs(props).rows; // Access rows passed in props

                // Handle response data and update the rows accordingly
                if (mode === '/create') {
                    console.log(data.row)
                    rows.value.unshift(data.row); // Add new row at the beginning
                } else {
                    const index = rows.value.findIndex(x => x.id === data.row.id); // Find the index of the edited row
                    if (index > -1) {
                        console.log(data.row)
                        rows.value[index] = Object.assign({}, data.row); // Update existing row
                    }
                }

                $notify('positive', 'check', data.message); // Show success notification
                closeDialog(); // Close the dialog and reset form
            } catch (error) {
                if (error.response.status === 422) {
                    formError.value = error.response.data;
                }else {
                    console.error('Error:', error); // Handle errors (e.g., validation issues, API failures)
                    $notify('negative', 'error', 'An error occurred while processing your request.');
                }
            } finally {
                isLoading.value = false; // Hide the loading spinner
            }
        };

        const fetchManager = async () => {
            try {
                const { data } = await $axios.post('/admin-branch/fetchManager');
                managerList.value = data.manager.filter(row=>row.branch_id === null).map((row) => ({
                    id: row.id,
                    fullname: `${row.manager_firstname} ${row.manager_lastname}`
                }));
            } catch (error) {
                if (error.response.status === 422) {
                    console.log(error)
                }
            }
        }

        // fetch region 
        const fetchRegionList = async () => {
            try {
                const { data } = await $axios.post('/api/regions')
                regionList.value = data.regions.map(item => ({
                    label : `${item.name}`,
                    id : item.code,
                }));
            } catch (error) {
                if (error.response.status === 422) {
                    console.log(error)
                }
            }
        }

        // Fetch provinces based on selected region
        const fetchProvinceList = async (regionCode) => {
            try {
                const { data } = await $axios.post('/api/provinces',{
                    regionCode: regionCode
                });

                provinceList.value = data.provinces.map(item => ({
                    label : item.name,
                    id : item.code,
                }));

            } catch (error) {
                if (error.response.status === 422) {
                    console.log(error)
                }
            }
        }

        // Fetch cities based on selected province
        const fetchCityList = async (provinceCode) => {
            try {
                const { data } = await $axios.post('/api/cities',{
                    provinceCode: provinceCode
                });

                cityList.value = data.cities.map(item => ({
                    label: item.name,
                    id: item.code
                }));
            } catch (error) {
                if (error.response.status === 422) {
                    console.log(error)
                }
            }
        }

        // Fetch barangays based on selected city
        const fetchBarangayList = async (cityCode) => {
            try {   
                const { data } = await $axios.post('/api/barangays', {
                    cityCode: cityCode
                });

                barangayList.value = data.barangays.map(item => ({
                    label: item.name,
                    id: item.code,
                }));
            } catch (error) {
                if (error.response.status === 422) {
                    console.log(error)
                }
            }
        }
        const branch_status =  [
            { label: 'Active', value: 'Active' },
            { label: 'Deactive', value: 'Deactive' }
        ]

        onMounted(() => {
            // Initialize regions on component mount
            fetchRegionList();
        });

        // Return necessary variables and methods for the template
        return {
            isShow,
            isLoading,
            showDialog,
            closeDialog,
            formData,
            formError,
            formMode,
            handleSubmitForm,
            branch_status,
            fetchManager,
            managerList,
            regionList,
            provinceList,
            cityList,
            barangayList,
            fetchProvinceList,
            fetchCityList,
            fetchBarangayList,
            loadingTime
        };
    }
});
</script>
