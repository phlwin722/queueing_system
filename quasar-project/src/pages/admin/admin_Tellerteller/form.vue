
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
                            <div v-if="!adminInformation" class="col-12">
                                <q-select
                                    outlined
                                    v-model="formData.branch_id"
                                    :options="branch_list"
                                    option-label="branch_name"
                                    option-value="id"
                                    label="Branch name"
                                    hide-bottom-space
                                    :error="formError.hasOwnProperty('branch_id')"
                                    :error-message="formError.branch_id"
                                    dense
                                    emit-value
                                    map-options
                                />
                            </div>
                            <!-- Personnel Type Select -->
                            <div class="col-12">
                                <q-select
                                    outlined
                                    :disable="!adminInformation && !formData.branch_id"
                                    v-model="formData.type_ids_selected" 
                                    label="Personnel Type"
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
                            
                            <!-- File Upload -->
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
import { defineComponent, onMounted, ref, toRefs, watch } from "vue";
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
        const adminInformation = ref (null);
        const branch_list = ref ([]);

        const initFormData = () => ({
            id: null,
            teller_firstname: '',
            teller_lastname: '',
            teller_username: '', 
            teller_password: '', 
            type_ids_selected: [],
            Image: '',
            branch_id: '',
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
               if (adminInformation.value != null) {
                    const { data } = await $axios.post('/dropdown/types',{
                    branch_id: adminInformation.value.branch_id
                  });
                    categoriesList.value = data.rows; // Ensure this matches the API response structure
                } else {
                    const { data } = await $axios.post('/dropdown/types',{
                    branch_id: formData.value.branch_id
                  });
                    categoriesList.value = data.rows; // Ensure this matches the API response structure
                }
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
            
            // If in 'edit' mode, populate the form with the row data
            if (mode === 'edit') {
                // Clone the row data to formData for editing, avoiding direct mutation
                formData.value = Object.assign({}, row);

                try {
                     // Attempt to fetch the teller's image from the backend based on the teller's ID
                     const { data } = await $axios.post('teller/image', {
                         id: formData.value.id, // Sending the teller's ID to the backend
                         image: formData.value.Image // Sending the teller's ID to the backend
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
            try {
                const mode = formMode.value === 'New' ? '/create' : '/update';
                isLoading.value = true;
                formError.value = {}; // Reset form errors

                // check if admininformation is available token from manager
                if (adminInformation.value && adminInformation.value.branch_id) {
                    formData.value.branch_id = adminInformation.value.branch_id
                }

                // Check if passwords match
                if (formDataPassword.value.teller_newPassword !== formDataPassword.value.teller_retypepassword) {
                    $notify('negative', 'error', 'Passwords do not match. Please check your input data');
                    isLoading.value = false;
                    return;
                }
                // If updating and a new password is provided, update the password in formData
                if (mode === '/update') {
                    if (formDataPassword.value.teller_newPassword == '' && formDataPassword.value.teller_retypepassword == '') {
                        formData.value.teller_password = "oldpass"
                    }else{
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

        const fetchBranch = async () => {
            try {
                const { data } = await $axios.post('/type/Branch')
                branch_list.value = data.branch
            } catch (error) {
                if (error.response.status === 422) {
                    console.log(error)
                }
            }
        }
        
        watch(()=> formData.value.branch_id, async (newVal, oldnewVal) => {
            if (newVal) {
      
                if (adminInformation.value == null && formMode.value === 'Edit' && oldnewVal !== '') {
                    formData.value.type_ids_selected = []                  
                }
               await fetchCategories()
               
            }
        }) 

        onMounted(async () => {
            const managerInformation = localStorage.getItem('managerInformation');
            if (managerInformation) {
                adminInformation.value = JSON.parse(managerInformation)
                formData.value.branch_id = adminInformation.value.branch_id;
                await fetchCategories()
            }
            fetchBranch();
        })


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
            branch_list,
            adminInformation,
        };
    }
});
</script>
