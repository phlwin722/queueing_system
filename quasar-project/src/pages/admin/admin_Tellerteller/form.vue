<template>
    <!-- Dialog Box -->
    <q-dialog @hide="closeDialog" v-model="isShow">
        <q-card>
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
                        <div class="col-12">
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
                        </div>
                        <div class="col-12">
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
                            v-model="formData.type_ids_selected" 
                            label="Personel Type"
                            emit-value
                            multiple
                            use-chips
                            map-options
                            dense
                            hide-bottom-space
                            :error="formError.hasOwnProperty('type_ids_selected')"
                            :error-message="formError.type_ids_selected"
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
                            </template>
                        </q-file>

                        <!-- Image Preview Dialog -->
                        <q-dialog v-model="showPreview">
                            <q-card class="q-pa-md" style="width: 175px; max-width: 35vw;">
                                <q-card-section class="row justify-center">
                                    <q-img
                                        v-if="imageUrl"
                                        :src="imageUrl"
                                        spinner-color="primary"
                                        style="width: 100%; max-height: 500px; object-fit: contain; border-color: black; border-width: 1px; border-style: solid;"
                                    />
                                    <p v-else class="text-grey">No image selected</p>
                                </q-card-section>
                                <q-card-actions align="center">
                                    <q-btn flat label="Close" v-close-popup />
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
            type_ids_selected: [],
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
                //console.log("Full API Response:", data); // 🔍 Debugging
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

            if (mode === 'edit') {
                formData.value = Object.assign({}, row);
                console.log('Form Data:', formData.value);
                
                // Check if type_ids_selected is a string and parse it to an array
                if (typeof formData.value.type_ids_selected === 'string') {
                    formData.value.type_ids_selected = JSON.parse(formData.value.type_ids_selected);
                    console.log('Parsed type_ids_selected:', formData.value.type_ids_selected);
                }

                // Check if categoriesList is an array
                if (Array.isArray(categoriesList.value)) {
                    // Now you can safely map over the array and find categories
                    const selectedCategories = formData.value.type_ids_selected.map(id => 
                        categoriesList.value.find(category => category.id === id)
                    );
                    console.log('Selected Categories:', selectedCategories);
                } else {
                    console.error('categoriesList is not an array:', categoriesList);
                }
            }
            
            isShow.value = true;
            fetchCategories();
        };

        // Image preview logic
        const previewImage = (file) => {
            if (file) {
                imageUrl.value = URL.createObjectURL(file);
            } else {
                imageUrl.value = null;
            }
        };

        const handleSubmitForm = async () => {
            const mode = formMode.value === 'New' ? '/create' : '/update';
            isLoading.value = true;
            formError.value = {}; // Reset form errors

            // Check if passwords match
            if (formDataPassword.value.teller_newPassword !== formDataPassword.value.teller_retypepassword) {
                $notify('negative', 'error', 'Passwords do not match. Please check your input data');
                isLoading.value = false;
                return;
            }

            try {
                // If updating and a new password is provided, update the password in formData
                if (mode === '/update' && formDataPassword.value.teller_newPassword !== '') {
                    formData.value.teller_password = formDataPassword.value.teller_newPassword;
                }

                // Convert selected types array to a JSON string before sending it to the backend
                if (mode === '/create') {
                    formData.value.type_ids_selected = JSON.stringify(formData.value.type_ids_selected); // Convert to JSON string
                }

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
            previewImage,
        };
    }
});
</script>
