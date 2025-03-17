<template>
    <q-dialog @hide="closeDialog" v-model="isShow">
        <q-card>
            <!-- Dialog Header -->
            <q-card-section class="row items-center q-pb-none">
                <div class="text-h6 text-warning">{{ formMode }} Personnel</div>
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
                    <div class="col-12">
                        <q-input
                            outlined 
                            v-model="formData.teller_firstname" 
                            label="First Name:" 
                            dense
                            hide-bottom-space
                            :error="formError.hasOwnProperty('teller_firstname')"
                            :error-message="formError.teller_firstname"
                        />
                    </div>
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
                    <div class="col-12">
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
                    <div class="col-12">
                        <q-select
                            outlined
                            v-model="formData.type_id" 
                            label="Personel Type"
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
                </div>
            </q-card-section>

            <!-- Actions -->
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
import { defineComponent, ref,  toRefs } from "vue";
import { $axios, $notify } from 'boot/app';

export default defineComponent({
    name: "TellerFormPage",
    props: {
        url: String,
        rows: Array
    },
    setup(props) {
        const isShow = ref(false);
        const isLoading = ref(false);
        const formMode = ref('New');
        const categoriesList = ref([]);

        const initFormData = () => ({
            id: null,
            teller_firstname: '',
            teller_lastname: '',
            teller_username: '', 
            teller_password: '', 
            type_id: ''
        });

        const formData = ref(initFormData());  
        const formError = ref({});

        const fetchCategories = async () => {
            try {
                const { data } = await $axios.post('/types/index');
                console.log("Full API Response:", data); // 🔍 Debugging
                categoriesList.value = data.rows; // Ensure this matches the API response structure
            } catch (error) {
                console.error('Error fetching categories:', error);
            }
        };

        const closeDialog = () => {
            isShow.value = false;
            formData.value = initFormData();
            formError.value = {};
        };

        const showDialog = async (mode, row) => {
            formMode.value = mode === 'new' ? 'New' : 'Edit';
         

            if(mode === 'edit'){
            formData.value = Object.assign({},row)
          }
            
            isShow.value = true;
            fetchCategories();
        };

        // const handleSubmitForm = async () => {
            
        //     isLoading.value = true;
        //     try {
        //         const mode = formMode.value === 'New' ? '/create' : '/update';
        //         const payload = { ...formData.value };

        //         // Remove password field if empty (for editing mode)
        //         if (!payload.teller_password) delete payload.teller_password;

        //         console.log("Submitting Data:", payload); // 🔍 Debugging

        //         const { data } = await $axios.post(props.url + mode, payload);
        //         console.log("Response Data:", data); // 🔍 Check response

        //         if (formMode.value === 'New') {
        //             props.rows.unshift(data.tellers);
        //         } else {
        //             const index = props.rows.findIndex(x => x.id === formData.value.id);
        //             if (index > -1) {
        //                 props.rows[index] = data.tellers;
        //             }
        //         }
                
        //         $notify('positive', 'done', data.message);
        //         console.log("done")
        //         closeDialog();
        //     } catch (error) {
        //         if (error.response?.status === 422) {
        //             formError.value = error.response.data;
        //             if (!formData.value.type_id) {
        //     // $notify('negative', 'error', 'Please select a Personel Type before saving.');
        //         return; // Stop submission
        //     }
        //         } else {
        //             console.error('Error:', error);
        //         }
        //     } finally {
        //         isLoading.value = false;
        //     }
        // };


    const handleSubmitForm = async () =>{
        const mode = formMode.value === 'New' ? '/create' : '/update'
        isLoading.value = true
        try{
        const {data} = await $axios.post(props.url +mode, formData.value)
        const rows = toRefs(props).rows
        if(mode === '/create'){
            rows.value.unshift(data.row)
        }else{
            const index = rows.value.findIndex(x => x.id === data.row.id)
            if(index > -1){
            rows.value[index] = Object.assign({}, data.row)
            }
        }
        $notify('positive', 'check', data.message)
        closeDialog()
        }catch(error){
        
            console.log('error',error)
        

        }finally{
        isLoading.value = false
        }
    }

        return {
            isShow,
            isLoading,
            showDialog,
            closeDialog,
            formData,
            formError,
            formMode,
            handleSubmitForm,
            categoriesList
        };
    }
});
</script>