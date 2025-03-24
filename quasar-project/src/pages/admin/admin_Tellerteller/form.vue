
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
                            v-model="selectedImage" 
                            :error="formError.hasOwnProperty('Image')"
                            :error-message="formError.Image"
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
                                        :src="imageUrl || require('assets/no-image.png')"
                                        spinner-color="primary"
                                        style="width: 100%; max-height: 500px; object-fit: contain; border-color: black; border-width: 1px; border-style: solid;"
                                    />
                                    
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
        const selectedImage = ref(null);
        const imageUrl = ref(null);
        const showPreview = ref(false);

        const initFormData = () => ({
            id: null,
            teller_firstname: '',
            teller_lastname: '',
            teller_username: '', 
            teller_password: '', 
            type_ids_selected: [],
            Image: '',
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
                console.log("Full API Response:", data.rows); // 🔍 Debugging
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
            selectedImage.value = null;
            imageUrl.value = null;
        };

        // Show the dialog and fetch data (for edit mode, populate form with row data)
        const showDialog = async (mode, row) => {
            // Set the form mode based on the action (new or edit)
            formMode.value = mode === 'new' ? 'New' : 'Edit';
            
            // Fetch the categories of personnel types
            await fetchCategories(); // This is asynchronous, so wait for it to complete

            // If in 'edit' mode, populate the form with the row data
            if (mode === 'edit') {
                // Clone the row data to formData for editing, avoiding direct mutation
                formData.value = Object.assign({}, row);

                try {
                    // Attempt to fetch the teller's image from the backend based on the teller's ID
                    const { data } = await $axios.post('teller/image', {
                        id: formData.value.id // Sending the teller's ID to the backend
                    });

                    // Set the fetched image URL to imageUrl for displaying
                    imageUrl.value = data.Image;

                    // Ensure 'type_ids_selected' is an array and contains integers
                    if (typeof formData.value.type_ids_selected === 'string') {
                        try {
                            // If 'type_ids_selected' is a string, try parsing it into an array of integers
                            formData.value.type_ids_selected = JSON.parse(formData.value.type_ids_selected).map(id => parseInt(id, 10));
                        } catch (e) {
                            // If parsing fails, fall back to an empty array
                            formData.value.type_ids_selected = [];
                        }
                    }

                    // If 'type_ids_selected' is not an array, convert it to an empty array
                    if (!Array.isArray(formData.value.type_ids_selected)) {
                        formData.value.type_ids_selected = [];
                    }

                    // Convert all values in 'type_ids_selected' to integers for consistency
                    formData.value.type_ids_selected = formData.value.type_ids_selected.map(id => parseInt(id, 10));

                    // Map each selected type ID to its corresponding category from categoriesList
                    const selectedCategories = formData.value.type_ids_selected.map(id => {
                        // Find the category matching the selected ID
                        const category = categoriesList.value.find(cat => String(cat.id) === String(id));
                        return category; // Return the category object
                    });

                    // Log the selected categories to the console for debugging
                    console.log('Selected Categories:', selectedCategories);
                } catch (error) {
                    // Log any errors encountered while fetching the teller image
                    console.error('Error fetching teller image:', error);
                }
            }

            // Show the dialog by setting isShow to true
            isShow.value = true; 
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
                if (mode === '/update') {
                    if (formDataPassword.value.teller_newPassword !== ''){
                        formData.value.teller_password = formDataPassword.value.teller_newPassword;
                    
                    }

                    // check if selectedImage has not null
                    if (selectedImage.value != null) {
                        formData.value.Image = selectedImage.value;
                        console.log("Image value",  formData.value.Image)
                    }
                }

                // Convert selected types array to a JSON string before sending it to the backend
                if (mode === '/create') {
                    //formData.value.type_ids_selected = JSON.stringify(formData.value.type_ids_selected); // Convert to JSON string
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

                    // Reset the type_ids_selected field if there's an error
                    if (formError.value.hasOwnProperty('type_ids_selected')) {
                        formData.value.type_ids_selected = []; // Clear the field value if validation fails
                    }
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
            selectedImage,
            imageUrl,
            showPreview,
            previewImage,
        };
    }
});
</script>
