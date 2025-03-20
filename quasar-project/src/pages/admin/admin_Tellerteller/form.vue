<template>
    <!-- Dialog Box -->
    <q-dialog @hide="closeDialog" v-model="isShow">
        <q-card class="relative-position">
            <!-- Dialog Header -->
            <q-card-section class="row items-center q-pb-none">
                <div class="text-h6 text-primary">{{ formMode }} Personnel</div>
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

            <!-- Form Inputs -->
            <q-card-section>
                <div class="row q-col-gutter-md">
                    <!-- First Name Input -->
                    <div class="col-12">
                        <q-input
                            outlined 
                            v-model="formData.teller_firstname" 
                            label="First Name:" 
                            dense
                            hide-bottom-space
                            :error="formError.hasOwnProperty('teller_firstname')"
                            :error-message="formError.teller_firstname"
                            autofocus
                        />
                    </div>
                    <!-- Last Name Input -->
                    <div class="col-12">
                        <q-input 
                            outlined 
                            v-model="formData.teller_lastname" 
                            label="Last Name:" 
                            dense
                            hide-bottom-space
                            :error="formError.hasOwnProperty('teller_lastname')"
                            :error-message="formError.teller_lastname"
                        />
                    </div>
                    <!-- Username Input -->
                    <div class="col-12">
                        <q-input 
                            outlined 
                            v-model="formData.teller_username" 
                            label="Username:" 
                            dense
                            hide-bottom-space
                            :error="formError.hasOwnProperty('teller_username')" 
                            :error-message="formError.teller_username"
                        />
                    </div>
                    
                    <!-- Conditional Password Input (for Edit Mode) -->
                    <div v-if="formMode === 'Edit'" class="col-12">
                            <q-input 
                                outlined 
                                v-model="formDataPassword.teller_newPassword" 
                                label="New Password:" 
                                type="password"
                                dense
                                hide-bottom-space
                                :error="formError.hasOwnProperty('teller_newpassword')" 
                                :error-message="formError.teller_newpassword"
                        />
                        
                        <div class="col-12 q-mt-md">
                            <q-input 
                                outlined 
                                v-model="formDataPassword.teller_retypepassword" 
                                label="Re-type password:" 
                                type="password"
                                dense
                                hide-bottom-space
                                :error="formError.hasOwnProperty('teller_retypepassword')" 
                                :error-message="formError.teller_retypepassword"
                        />
                        </div>
                    </div>
                    

                    <!-- Default Password Input (for Create Mode) -->
                    <div v-else class="col-12">
                        <q-input 
                            outlined 
                            v-model="formData.teller_password" 
                            label="Password:" 
                            type="password"
                            dense
                            hide-bottom-space
                            :error="formError.hasOwnProperty('teller_password')" 
                            :error-message="formError.teller_password"
                        />
                    </div>


                    <!-- Personnel Type Select -->
                    <div class="col-12">
                        <q-select
                            outlined
                            v-model="formData.type_id" 
                            label="Personnel Type"
                            emit-value
                            map-options
                            dense
                            hide-bottom-space
                            :error="formError.hasOwnProperty('type_id')"
                            :error-message="formError.type_id"
                            :options="categoriesList"
                            option-label="name"
                            option-value="id"
                        />    
                    </div>
                    <div class="col-12">
                        <q-file 
                        outlined
                        clearable
                        dense
                        v-model="selectedFile" 
                        label="Attach your image" 
                        accept="image/*"
                        @update:model-value="previewImage"
                        >
                            <template v-slot:prepend>
                            <q-icon name="attach_file" />
                            </template>
                            <template v-slot:append>
                            <q-icon name="preview" class="cursor-pointer" @click="showPreview = true"/>
                            <q-tooltip anchor="center right" self="center left" :offset="[10, 10]" class="bg-secondary">
                                        preview image
                                    </q-tooltip>
                            </template>
                        </q-file>

                        <!-- Image Preview Dialog -->
                        <q-dialog v-model="showPreview">
                            <q-card class="q-pa-md absulute-position"  style="width: 200px; max-width: 30vw; left: 420px; top: 0;">
                                <q-card-section class="row justify-center">
                                    
                                    <q-img
                                        v-if="imageUrl"
                                        :src="imageUrl"
                                        spinner-color="primary"
                                        style="width: 100%; max-height: 500px; object-fit: contain; border-color: black; border-width: 1px; border-style: solid;"
                                    />
                                    <p v-else class="text-grey" align="center">No image selected</p>
                                    
                                </q-card-section>
                                
                                <q-card-actions align="center">
                                    <q-btn 
                                    flat 
                                    label="Close" 
                                    v-close-popup
                                    />
                                </q-card-actions>
                            </q-card>
                        </q-dialog>
                        

                    </div>
                </div>
            </q-card-section>

            <!-- Actions (Save Button) -->
            <q-card-actions class="flex justify-center">
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
import { defineComponent, ref, toRefs } from "vue";
import { $axios, $notify } from 'boot/app'; // Axios for API requests, Notify for UI feedback

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
        const categoriesList = ref([]); // List of categories for the select input

        // Image preview variables
        const selectedFile = ref(null);
        const imageUrl = ref(null);
        const showPreview = ref(false);

        const initFormData = () => ({
            id: null,
            teller_firstname: '',
            teller_lastname: '',
            teller_username: '', 
            teller_password: '', 
            type_id: ''
        });

        const initDataPassword = () => ({
            teller_newPassword: '',
            teller_retypepassword:'',
        })

        const formDataPassword = ref(initDataPassword())
        const formData = ref(initFormData());  // Reactive object holding the form data
        const formError = ref({}); // Object to hold form validation errors

        // Fetch categories list from the server (for select input)
        const fetchCategories = async () => {
            try {
                const { data } = await $axios.post('/types/index');
                console.log("Full API Response:", data); // 🔍 Debugging
                categoriesList.value = data.rows; // Ensure this matches the API response structure
            } catch (error) {
                console.error('Error fetching categories:', error); // Handle error if API request fails
            }
        };

        // Close the dialog and reset form data and errors
        const closeDialog = () => {
            isShow.value = false;
            formData.value = initFormData(); // Reset form data
            formDataPassword.value = initDataPassword(); // Reset form data
            formError.value = {}; // Reset form errors
            selectedFile.value = null;
            imageUrl.value = null;
        };

        // Show the dialog and fetch data (for edit mode, populate form with row data)
        const showDialog = async (mode, row) => {

            formMode.value = mode === 'new' ? 'New' : 'Edit';

            if(mode === 'edit'){
            formData.value = Object.assign({},row)
            }
            
            isShow.value = true;
            fetchCategories();
        };

        // Image preview logic
        const previewImage = (file) => {
            if (file) {
                imageUrl.value = URL.createObjectURL(file);
                showPreview.value = true;
            } else {
                imageUrl.value = null;
                showPreview.value = false;
            }
        };

    const handleSubmitForm = async () =>{
        const mode = formMode.value === 'New' ? '/create' : '/update'
        isLoading.value = true
        formError.value = {}; // Reset form errors

        // Check if passwords match
        if (formDataPassword.value.teller_newPassword !== formDataPassword.value.teller_retypepassword) {
            $notify('negative', 'error', 'Passwords do not match. Please check your input data');
            isLoading.value = false; // Hide the loading spinner
            return;
        }
            try {
                // If updating and a new password is provided, update the password in formData
                if (mode === '/update' && formDataPassword.value.teller_newPassword !== '') {
                    formData.value.teller_password = formDataPassword.value.teller_newPassword; // Update password for the edit
                }

                // Send form data to the server
                const { data } = await $axios.post(props.url + mode, formData.value);

                const rows = toRefs(props).rows; // Access rows passed in props

                // Handle response data and update the rows accordingly
                if (mode === '/create') {
                    rows.value.unshift(data.row); // Add new row at the beginning
                } else {
                    const index = rows.value.findIndex(x => x.id === data.row.id); // Find the index of the edited row
                    if (index > -1) {
                        rows.value[index] = Object.assign({}, data.row); // Update existing row
                    }
                }

                $notify('positive', 'check', data.message); // Show success notification
                closeDialog(); // Close the dialog and reset form
            } catch (error) {
                console.error('Error:', error); // Handle errors (e.g., validation issues, API failures)
                $notify('negative', 'error', 'An error occurred while processing your request.');
            } finally {
                isLoading.value = false; // Hide the loading spinner
            }
        }

        // Return necessary variables and methods for the template
        return {
            isShow,
            isLoading,
            showDialog,
            closeDialog,
            formData,
            formError,
            formDataPassword,
            formMode,
            handleSubmitForm,
            categoriesList,
            selectedFile,
            imageUrl,
            showPreview,
            previewImage
        };
    }
});
</script>
