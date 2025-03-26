<template>
    <q-dialog v-model="icon" @hide="closeDialog">
        <q-card>
            <q-card-section class="row items-center q-pb-none">
                <div class="text-h6 text-primary">{{formMode}} Window</div>
                <q-space />
                <q-btn icon="close" flat round dense v-close-popup style="width: 24px; height: 24px;" />
            </q-card-section>

            <q-card-section>
                <div class="row q-col-gutter-md">
                    <!-- Currency Symbol Dropdown -->
                    <div class="col-12">
                        <q-select
                        outlined
                        v-model="formData.currency_symbol"
                        label="Currency Symbol"
                        dense
                        hide-bottom-space
                        :options="allCurrencies"
                        option-label="symbol"
                        option-value="symbol"
                        emit-value
                        map-options
                        @update:model-value="updateCurrencyName"
                        :error="formError.hasOwnProperty('currency_symbol')"
                        :error-message="formError.currency_symbol"
                        autofocus
                    >
                        <template v-slot:option="scope">
                            <q-item v-bind="scope.itemProps">
                                <q-item-section avatar>
                                    <span :class="['fi', scope.opt.flag]"></span>
                                </q-item-section>
                                <q-item-section>
                                    <q-item-label>{{ scope.opt.symbol }}</q-item-label>
                                    <q-item-label caption>{{ scope.opt.name }}</q-item-label>
                                </q-item-section>
                            </q-item>
                        </template>
                        
                        <template v-slot:selected-item="scope">
                            <span :class="['fi', scope.opt.flag]" style="margin-right: 8px;"></span>
                            {{ scope.opt.symbol }}
                        </template>
                    </q-select>
                    </div>

                    <!-- Currency Name Input (Auto-filled) -->
                    <div class="col-12">
                        <q-input
                            outlined
                            v-model="formData.currency_name"
                            label="Currency Name"
                            dense
                            hide-bottom-space
                            :readonly="true"
                            :error="formError.hasOwnProperty('currency_name')"
                            :error-message="formError.currency_name"
                        />
                    </div>

                    <!-- Buy Value -->
                    <div class="col-12">
                        <q-input
                            outlined
                            v-model="formData.buy_value"
                            label="Buy Value"
                            type="number"
                            dense
                            hide-bottom-space
                            :error="formError.hasOwnProperty('buy_value')"
                            :error-message="formError.buy_value"
                        />
                    </div>

                    <!-- Sell Value -->
                    <div class="col-12">
                        <q-input
                            outlined
                            v-model="formData.sell_value"
                            label="Sell Value"
                            type="number"
                            dense
                            hide-bottom-space
                            :error="formError.hasOwnProperty('sell_value')"
                            :error-message="formError.sell_value"
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
import { defineComponent, ref, toRefs } from 'vue';
import { $axios, $notify } from 'boot/app';
import 'flag-icons/css/flag-icons.min.css';


export default defineComponent({
    name: 'CurrencyFormPage',
    props: {
        url: String,
        rows: Array
    },
    setup(props) {
        const icon = ref(false);
        const isLoading = ref(false);

        const allCurrencies = ref([
            { name: 'Argentine Peso', symbol: '$', flag: 'fi-ar' },
            { name: 'Australian Dollar', symbol: 'A$', flag: 'fi-au' },
            { name: 'Bangladeshi Taka', symbol: '৳', flag: 'fi-bd' },
            { name: 'Brazilian Real', symbol: 'R$', flag: 'fi-br' },
            { name: 'British Pound', symbol: '£', flag: 'fi-gb' },
            { name: 'Bulgarian Lev', symbol: 'лв', flag: 'fi-bg' },
            { name: 'Canadian Dollar', symbol: 'C$', flag: 'fi-ca' },
            { name: 'Chilean Peso', symbol: '$', flag: 'fi-cl' },
            { name: 'Chinese Yuan', symbol: '¥', flag: 'fi-cn' },
            { name: 'Colombian Peso', symbol: '$', flag: 'fi-co' },
            { name: 'Croatian Kuna', symbol: 'kn', flag: 'fi-hr' },
            { name: 'Czech Koruna', symbol: 'Kč', flag: 'fi-cz' },
            { name: 'Danish Krone', symbol: 'kr', flag: 'fi-dk' },
            { name: 'Egyptian Pound', symbol: 'E£', flag: 'fi-eg' },
            { name: 'Emirati Dirham', symbol: 'د.إ', flag: 'fi-ae' },
            { name: 'Euro', symbol: '€', flag: 'fi-eu' },
            { name: 'Georgian Lari', symbol: '₾', flag: 'fi-ge' },
            { name: 'Ghanaian Cedi', symbol: '₵', flag: 'fi-gh' },
            { name: 'Hong Kong Dollar', symbol: 'HK$', flag: 'fi-hk' },
            { name: 'Hungarian Forint', symbol: 'Ft', flag: 'fi-hu' },
            { name: 'Icelandic Krona', symbol: 'kr', flag: 'fi-is' },
            { name: 'Indian Rupee', symbol: '₹', flag: 'fi-in' },
            { name: 'Indonesian Rupiah', symbol: 'Rp', flag: 'fi-id' },
            { name: 'Israeli Shekel', symbol: '₪', flag: 'fi-il' },
            { name: 'Japanese Yen', symbol: '¥', flag: 'fi-jp' },
            { name: 'Kazakhstani Tenge', symbol: '₸', flag: 'fi-kz' },
            { name: 'Kenyan Shilling', symbol: 'KSh', flag: 'fi-ke' },
            { name: 'Malaysian Ringgit', symbol: 'RM', flag: 'fi-my' },
            { name: 'Mexican Peso', symbol: '$', flag: 'fi-mx' },
            { name: 'Moroccan Dirham', symbol: 'DH', flag: 'fi-ma' },
            { name: 'New Zealand Dollar', symbol: 'NZ$', flag: 'fi-nz' },
            { name: 'Nigerian Naira', symbol: '₦', flag: 'fi-ng' },
            { name: 'Norwegian Krone', symbol: 'kr', flag: 'fi-no' },
            { name: 'Pakistani Rupee', symbol: '₨', flag: 'fi-pk' },
            { name: 'Peruvian Sol', symbol: 'S/', flag: 'fi-pe' },
            { name: 'Philippine Peso', symbol: '₱', flag: 'fi-ph' },
            { name: 'Polish Zloty', symbol: 'zł', flag: 'fi-pl' },
            { name: 'Qatari Riyal', symbol: '﷼', flag: 'fi-qa' },
            { name: 'Romanian Leu', symbol: 'lei', flag: 'fi-ro' },
            { name: 'Russian Ruble', symbol: '₽', flag: 'fi-ru' },
            { name: 'Saudi Riyal', symbol: '﷼', flag: 'fi-sa' },
            { name: 'Singapore Dollar', symbol: 'S$', flag: 'fi-sg' },
            { name: 'South African Rand', symbol: 'R', flag: 'fi-za' },
            { name: 'South Korean Won', symbol: '₩', flag: 'fi-kr' },
            { name: 'Sri Lankan Rupee', symbol: 'රු', flag: 'fi-lk' },
            { name: 'Swedish Krona', symbol: 'kr', flag: 'fi-se' },
            { name: 'Swiss Franc', symbol: 'CHF', flag: 'fi-ch' },
            { name: 'Thai Baht', symbol: '฿', flag: 'fi-th' },
            { name: 'Turkish Lira', symbol: '₺', flag: 'fi-tr' },
            { name: 'Ukrainian Hryvnia', symbol: '₴', flag: 'fi-ua' },
            { name: 'US Dollar', symbol: '$', flag: 'fi-us' },
            { name: 'Uzbekistani Som', symbol: 'soʻm', flag: 'fi-uz' },
            { name: 'Vietnamese Dong', symbol: '₫', flag: 'fi-vn' }
        ]);






        const initFormData = () => ({
            id: null,
            currency_name: '',
            currency_symbol: '',
            flag: '',
            buy_value: '',
            sell_value: ''
        });

        const formData = ref(initFormData());
        const formError = ref({});
        const formMode = ref('New');

        const closeDialog = () => {
            icon.value = false;
            formData.value = initFormData();
            formError.value = {};
        };

        const showDialog = (mode, row) => {
        formMode.value = mode === 'new' ? 'New' : 'Edit';
        if (mode === 'edit') {
            formData.value = Object.assign({}, row);
            // If the flag isn't already set, find it from the currency list
            if (!formData.value.flag) {
                const currency = allCurrencies.value.find(
                    c => c.symbol === row.currency_symbol
                );
                if (currency) {
                    formData.value.flag = currency.flag;
                }
            }
        }
        icon.value = true;
    };

        const updateCurrencyName = (selectedSymbol) => {
            const selectedCurrency = allCurrencies.value.find(
                currency => currency.symbol === selectedSymbol
            );
            if (selectedCurrency) {
                formData.value.currency_name = selectedCurrency.name;
                formData.value.flag = selectedCurrency.flag;  // Store the flag class
            }
        };

        const handleSubmitForm = async () => {
            isLoading.value = true;
            try {
                const mode = formMode.value === 'New' ? '/create' : '/update';
                const { data } = await $axios.post(props.url + mode, formData.value);

                const rows = toRefs(props).rows;

                if (formMode.value === 'New') {
                    rows.value.push({
                        id: data.row.id,
                        currency_name: data.row.currency_name,
                        currency_symbol: data.row.currency_symbol,
                        flag: data.row.flag,
                        buy_value: parseFloat(data.row.buy_value).toFixed(2),
                        sell_value: parseFloat(data.row.sell_value).toFixed(2)
                    });
                } else {
                    const index = rows.value.findIndex(x => x.id === data.row.id);
                    if (index > -1) {
                        rows.value[index] = {
                            id: data.row.id,
                            currency_name: data.row.currency_name,
                            currency_symbol: data.row.currency_symbol,
                            flag: data.row.flag,
                            buy_value: parseFloat(data.row.buy_value).toFixed(2),
                            sell_value: parseFloat(data.row.sell_value).toFixed(2)
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

        return {
            icon,
            formData,
            formError,
            isLoading,
            formMode,
            closeDialog,
            showDialog,
            handleSubmitForm,
            allCurrencies,
            updateCurrencyName
        };
    }
});
</script>
