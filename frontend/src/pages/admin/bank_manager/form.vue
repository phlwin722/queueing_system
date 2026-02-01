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

            <!-- Form Inputs with Image Preview -->
            <q-card-section>
                <div class="row q-gutter-md">
                    
                    <!-- Image Preview (Left Side) -->
                    <div class="column items-center col-shrink justify-center q-gutter-md">
                        <div v-if="imageUrl" class="flex flex-center column">
                            <div class="text-subtitle2 q-mb-sm">Selected Image Preview</div>
                            <q-img
                                :src="imageUrl"
                                spinner-color="primary"
                                style="width: 100%; min-height: 150px; object-fit: contain; border: 1px solid black; border-radius: 5px;"
                            />
                        </div>
                        <div v-else class="text-grey q-mt-md">No image selected</div>
                    </div>

                    <div class="col-auto flex flex-center">
                        <q-separator vertical class="q-mx-md" />
                    </div>
                    
                    <!-- Form Content (Right Side) -->
                    <div class="col">
                        <div class="row q-col-gutter-md">
                            <!-- First Name Input -->
                            <div class="col-12">
                                <q-input
                                    outlined 
                                    v-model="formData.manager_firstname" 
                                    label="First Name:" 
                                    dense
                                    hide-bottom-space
                                    :error="formError.hasOwnProperty('manager_firstname')"
                                    :error-message="formError.manager_firstname"
                                    autofocus
                                />
                            </div>
                            <!-- Last Name Input -->
                            <div class="col-12">
                                <q-input 
                                    outlined 
                                    v-model="formData.manager_lastname" 
                                    label="Last Name:" 
                                    dense
                                    hide-bottom-space
                                    :error="formError.hasOwnProperty('manager_lastname')"
                                    :error-message="formError.manager_lastname"
                                />
                            </div>
                            <!-- Username Input -->
                            <div class="col-12">
                                <q-input 
                                    outlined 
                                    v-model="formData.manager_username" 
                                    label="Username:" 
                                    dense
                                    hide-bottom-space
                                    :error="formError.hasOwnProperty('manager_username')" 
                                    :error-message="formError.manager_username"
                                />
                            </div>
                            
                            <!-- Conditional Password Input (for Edit Mode) -->
                            <div v-if="formMode === 'Edit'" class="col-12">
                                <q-input 
                                    outlined 
                                    v-model="formDataPassword.manager_newPassword" 
                                    label="New Password:" 
                                    type="password"
                                    dense
                                    hide-bottom-space
                                    :error="formError.hasOwnProperty('manager_newpassword')" 
                                    :error-message="formError.manager_newpassword"
                                />
                                
                                <div class="col-12 q-mt-md">
                                    <q-input 
                                        outlined 
                                        v-model="formDataPassword.manager_retypepassword" 
                                        label="Re-type password:" 
                                        type="password"
                                        dense
                                        hide-bottom-space
                                        :error="formError.hasOwnProperty('manager_retypepassword')" 
                                        :error-message="formError.manager_retypepassword"
                                    />
                                </div>
                            </div>

                            <!-- Default Password Input (for Create Mode) -->
                            <div v-else class="col-12">
                                <q-input 
                                    outlined 
                                    v-model="formData.manager_password" 
                                    label="Password:" 
                                    type="password"
                                    dense
                                    hide-bottom-space
                                    :error="formError.hasOwnProperty('manager_password')" 
                                    :error-message="formError.manager_password"
                                />
                            </div>
<!-- 
                            Status
                            <div class="col-12">
                                <q-select
                                    outlined 
                                    v-model="formData.manager_status" 
                                    :options="status" 
                                    label="Status" 
                                    hide-bottom-space
                                    :error="formError.hasOwnProperty('manager_status')"
                                    :error-message="formError.manager_status"
                                    dense
                                    emit-value
                                    map-options
                                />
                                </div> -->
                            
                            <!-- File Upload -->
                            <div class="col-12">
                                <q-file 
                                    outlined
                                    clearable
                                    dense
                                    v-model="selectedImage" 
                                    hide-bottom-space
                                    :error="formError.hasOwnProperty('Image')"
                                    :error-message="formError.Image"
                                    label="Attach your image" 
                                    accept="image/*"
                                    @update:model-value="previewImage"
                                >
                                    <template v-slot:prepend>
                                        <q-icon name="attach_file" />
                                    </template>
                                </q-file>
                            </div>
                        </div>
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

        // Image preview variables
        const selectedImage = ref(null);
        const imageUrl = ref(null);
        const showPreview = ref(false);

        const initFormData = () => ({
            id: null,
            manager_firstname: '',
            manager_lastname: '',
            manager_username: '', 
            manager_password: '', 
            manager_status: '',
            Image: '',
        });

        const initDataPassword = () => ({
            manager_newPassword: '',
            manager_retypepassword:'',
        })

        const formDataPassword = ref(initDataPassword())
        const formData = ref(initFormData());  // Reactive object holding the form data
        const formError = ref({}); // Object to hold form validation errors

        // Close the dialog and reset form data and errors
        const closeDialog = () => {
            isShow.value = false;
            formData.value = initFormData(); // Reset form data
            formDataPassword.value = initDataPassword(); // Reset form data
            formError.value = {}; // Reset form errors
            selectedImage.value = null;
            imageUrl.value = null;
        };

        // Show the dialog and fetch data (for edit mode, populate form with row data)
        const showDialog = async (mode, row) => {
            // Set the form mode based on the action (new or edit)
            formMode.value = mode === 'new' ? 'New' : 'Edit';

            // If in 'edit' mode, populate the form with the row data
            if (mode === 'edit') {
                // Clone the row data to formData for editing, avoiding direct mutation
                formData.value = Object.assign({}, row);

                try {
                    // Attempt to fetch the manager's image from the backend based on the manager's ID
                    const { data } = await $axios.post('manager/image', {
                        id: formData.value.id // Sending the manager's ID to the backend
                    });

                    // Set the fetched image URL to imageUrl for displaying
                    imageUrl.value = data.Image;

                } catch (error) {
                    // Log any errors encountered while fetching the manager image
                    console.error('Error fetching manager image:', error);
                }
            }

            // Show the dialog by setting isShow to true
            isShow.value = true; 
        };


        // Image preview logic
        const previewImage = (file) => {
            if (file) {
        const reader = new FileReader();
        reader.onload = (e) => {
            imageUrl.value = e.target.result; // Convert file to base64 URL
        };
        reader.readAsDataURL(file);
    } else {
        imageUrl.value = null;
    }
        };

        const handleSubmitForm = async () => {
            const mode = formMode.value === 'New' ? '/create' : '/update';
            isLoading.value = true;
            formError.value = {}; // Reset form errors

            // Check if passwords match
            if (formDataPassword.value.manager_newPassword !== formDataPassword.value.manager_retypepassword) {
                $notify('negative', 'error', 'Passwords do not match. Please check your input data');
                isLoading.value = false;
                return;
            }

            try {
                // If updating and a new password is provided, update the password in formData
                if (mode === '/update') {
                    if (formDataPassword.value.manager_newPassword == '' && formDataPassword.value.manager_retypepassword == '') {
                        formData.value.manager_password = "oldpass" 
                    }else{
                        formData.value.manager_password = formDataPassword.value.manager_newPassword;
                    }

                    // check if selectedImage has not null
                    if (selectedImage.value != null) {
                        formData.value.Image = selectedImage.value;
                        console.log("Image value",  formData.value.Image)
                    }
                }

                // Convert selected types array to a JSON string before sending it to the backend
                if (mode === '/create') {
                    formData.value.Image = selectedImage.value
                }

                // Send form data to the server
                const { data } = await $axios.post(props.url + mode, formData.value,{
                    headers: {'Content-Type': 'multipart/form-data' }
                });

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

        const status =  [
            { label: 'Active', value: 'Active' },
            { label: 'Deactive', value: 'Deactive' }
        ]

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
            selectedImage,
            imageUrl,
            showPreview,
            previewImage,
            status,
        };
    }
});
</script>
