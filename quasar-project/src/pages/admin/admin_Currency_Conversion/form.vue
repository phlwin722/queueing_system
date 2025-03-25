<template>
    <q-dialog 
        v-model="icon"
        @hide="closeDialog"
    >
        <q-card>
        <q-card-section class="row items-center q-pb-none">
            <div class="text-h6 text-primary">{{formMode}} Window</div>
        <q-space />
            <q-btn icon="close" flat round dense v-close-popup style="width: 24px; height: 24px;"/>
        </q-card-section>

        <q-card-section>
            <div class="row q-col-gutter-md">
                <div class="col-12">
                    <q-input 
                    outlined 
                    v-model="formData.currency_name" 
                    label="Currency name" 
                    dense 
                    hide-bottom-space
                    :error="formError.hasOwnProperty('currency_name')"
                    :error-message="formError.currency_name"
                    autofocus
                    />
                    
                </div>
                <div class="col-12">
                    <q-input 
                    outlined 
                    v-model="formData.currency_symbol" 
                    label="Currency Symbol" 
                    dense 
                    hide-bottom-space
                    :error="formError.hasOwnProperty('currency_symbol')"
                    :error-message="formError.currency_symbol"
                    autofocus
                    />
                    
                </div>
                <div class="col-12">
                    <q-input 
                    outlined 
                    v-model="formData.value" 
                    label="Value" 
                    type = "number"
                    dense
                    hide-bottom-space
                    :error="formError.hasOwnProperty('value')"
                    :error-message="formError.value"
                    />
                </div>
            </div>
        </q-card-section>

        <q-card-actions class="flex justify-center">
                <q-btn 
                color="positive" 
                label="Save" 
                @click="handleSubmitForm" 
                class="full-width" 
                style="max-width: 150px;"
                />
            </q-card-actions>

        <q-inner-loading
        :showing="isLoading"
        label="Please wait..."
        label-class="text-teal"
        label-style="font-size: 1.1em"
        />
        </q-card>
    </q-dialog>
    </template>

    <script>
    import { 
    defineComponent ,
    ref,
    toRefs,
    } from 'vue'

    import {
    $axios,
    $notify
    } from 'boot/app'

    export default defineComponent({
    name:'CurrencyFormPage',
    props: {
        url: String,
        rows: Array
    },
    setup(props){
        const icon = ref(false)
        const isLoading= ref(false)
        const initFormData = ()=>   {
            return{
                id: null,
                currency_name: "",
                currency_symbol: "",
                value: "",
            }
        }
        const formData = ref(initFormData())
        const formError = ref({})
        const formMode = ref('New')
        
        const closeDialog = ()=>{
            icon.value=false
            formData.value = initFormData()
            formError.value = {}
        }

        
        const showDialog = (mode, row) => {
            formMode.value = mode === 'new' ? 'New' : 'Edit';
            

            if (mode === 'edit') {
    
                formData.value = Object.assign({},row)
            }

            icon.value = true;
        };




        const handleSubmitForm = async () => {
            isLoading.value = true;
            try {
                const mode = formMode.value === 'New' ? '/create' : '/update';
                const { data } = await $axios.post(props.url + mode, formData.value);
                const rows = toRefs(props).rows;

                if (formMode.value === 'New') {
                    if (data && data.row) {
                        // Add the single new row object to rows.value
                        rows.value.push({
                            id: data.row.id,
                            currency_name: data.row.currency_name,
                            currency_symbol: data.row.currency_symbol,
                            value: parseFloat(data.row.value).toFixed(2) // Ensure 2 decimal places
                        });
                    } else {
                        console.log('No valid row found in the response:', data);
                    }
                } else {
                    const index = rows.value.findIndex(x => x.id === data.row.id); // Use data.row, not formData.value
                    if (index > -1) {
                        rows.value[index] = {
                            id: data.row.id,
                            currency_name: data.row.currency_name,
                            currency_symbol: data.row.currency_symbol,
                            value: parseFloat(data.row.value).toFixed(2)  // Ensure 2 decimal places
                        };
                    }
                }


                $notify('positive', 'done', data.message);
                closeDialog();
            } catch (error) {
                if (error.response?.status === 422) {
                    formError.value = error.response.data;
                } else {
                    console.error('Error:', error);
                }
            } finally {
                isLoading.value = false;
            }
        };





        return{
            props,
            icon,
            showDialog,
            formData,
            formError,
            isLoading,
            formMode,
            closeDialog,
            handleSubmitForm,
        }
    }
    })
</script>