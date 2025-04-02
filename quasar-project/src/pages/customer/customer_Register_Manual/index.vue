<template>
  <q-layout class="flex flex-center">
    <div
      class="flex flex-center full-page"
      style="height: 100vh;"
    >
      <q-img
        src="../../../assets/vrtlogowhite1.png"
        alt="Logo"
        fit="full"
        :style="{ maxWidth: $q.screen.lt.sm ? '170px' : '180px', marginTop: '-450px' }"
        class="q-mb-xl"
      />
    </div>
    <q-card
        class="q-pa-md shadow-2 bg-accent"
        style="width: 350px; max-width: 90vw; margin-top: -550px;"
      >
        <q-card-section>
          <div class="text-h6 text-center">Join the Queue</div>
        </q-card-section>

        <q-card-section>
          <q-input
            v-model="name"
            label="Full Name"
            :error="formError.hasOwnProperty('name')"
            :error-message="formError.name"
            outlined
            dense
          />
          <q-input
            v-model="email"
            label="Email address"
            :error="formError.hasOwnProperty('email')"
            :error-message="formError.email"
            outlined
            dense
            class="q-mt-md"
          />
          <q-select
            v-model="type_id"
            label="Service Available"
            transition-show="flip-up"
            transition-hide="flip-down"
            outlined
            emit-value
            map-options
            @update:modelValue="fecthCurrencty"  
            :error="formError.hasOwnProperty('type_id')"
            :error-message="formError.type_id"
            dense
            class="q-mt-md"
            :options="categoriesList"
            option-label="name"
            option-value="id"
          />
          <q-select
          v-if="currentCiesList && currentCiesList.length > 0"
          v-model="currencySelected"
          label="Currency Available"
          transition-show="flip-up"
          transition-hide="flip-down"
          outlined
          emit-value
          map-options
          dense
          class="q-mt-md"
          :options="currentCiesList"
          option-label="name"
          :error="formError.hasOwnProperty('currency')"
          :error-message="formError.currency"
          option-value="id"
        >
          <template v-slot:option="scope">
            <q-item v-bind="scope.itemProps">
              <q-item-section avatar>
                <span :class="['fi', scope.opt.flag]"></span>
              </q-item-section>
              <q-item-section>
                <q-item-label>{{ scope.opt.symbol }} - {{ scope.opt.name }}</q-item-label>
                <q-item-label caption>Buy: {{ scope.opt.buy_value }} | Sell: {{ scope.opt.sell_value }}</q-item-label>
              </q-item-section>
            </q-item>
          </template>
          
          <template v-slot:selected-item="scope">
            <div class="flex items-center">
              <span :class="['fi', scope.opt.flag]" style="margin-right: 8px;"></span>
              {{ scope.opt.symbol }} - {{ scope.opt.name }}
            </div>
          </template>
        </q-select>

        <q-checkbox
            v-model="customModel"
            color="green"
            label="Priority services"
            true-value="yes"
            false-value="no"
          />

          <q-select
            v-if="customModel === 'yes'"
            v-model="prioritySelected"
            label="Priority Service"
            transition-show="flip-up"
            transition-hide="flip-down"
            outlined
            emit-value
            map-options
            dense
            class="q-mt-md"
            :options="categoriesPriority"
            :error="formError.hasOwnProperty('priority')"
            :error-message="formError.priority"
            option-label="name"
            option-value="id"
          />
        </q-card-section>

        <q-card-actions align="center">
          <q-btn label="Print" color="primary" @click="joinQueue" />
        </q-card-actions>
        <q-inner-loading
          :showing="isLoading"
          label="Please wait..."
          label-class="text-teal"
          label-style="font-size: 1.1em"
        />
      </q-card>
  </q-layout>
</template>

<script>
import { ref, onMounted } from "vue";
import { $axios, $notify } from "boot/app";
import QrCode from 'qrcode'; // import the qrcode package

export default {
  setup() {
    const name = ref("");
    const email = ref("");
    const email_status = ref("sending_customer");
    const type_id = ref(null);
    const currencySelected = ref ();
    const categoryForeignExchange = ref();
    const categoriesList = ref([]);
    const currentCiesList = ref ([]);
    const isLoading = ref(false);
    const formError = ref({});
    const indicator = ref('')
    const generatedQrValue = ref ('');
    const ServiceAvail = ref('');
    const customModel = ref('no')
    const prioritySelected = ref();

    const token = ref(); // Get token from URL

    const generateRandomString = async (length = 10) => {
      const characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
      let result = '';
      const charactersLength = characters.length;
      // Loop to create a random string
      for (let i = 0; i < length; i++) {
        result += characters.charAt(Math.floor(Math.random() * charactersLength));
      }
        // Assign the generated string to the token ref
        token.value = result;
    }

    const joinQueue = async () => {
      isLoading.value = true;
      formError.value = [];
      await generateRandomString();  // Generate random token before the submission

      try {
          // Check if the category is 'Foreign Exchange' and validate the currency selection
          if (categoryForeignExchange.value === 1) {
            if (currencySelected.value == null) {
              formError.value.currency = "Currency field is required";
              return;
            }
          }
          if (customModel.value == 'yes'){
            if (prioritySelected.value == null) {
              formError.value.priority = "Priority service field is required"
              return
            }
          }

            // Capitalize the name properly
            name.value = name.value
            .split(' ')
            .map(word => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase())
            .join(' ');
      
            const { data } = await $axios.post("/customer-join", {
              token: token.value,
              name: name.value,
              email: email.value,
              email_status: email_status.value,
              type_id: type_id.value,
              currency: currencySelected.value,
              priority_service: prioritySelected.value
            });
          
            const response = await $axios.post('sent-email-dashboard',{
              id : data.id,
              token: token.value,
              queue_number: `${indicator.value}#-${String(data.queue_number).padStart(3, '0')}`,
              email:  email.value,
              name: name.value,
              subject: "Queue Alert", // Email subject
              message: `Welcome to our bank! To provide you with a seamless and efficient service experience, 
                        we’ve implemented a queue system that helps manage customer flow. 
                        Our system is designed to prioritize your needs and minimize waiting times. 
                        You are free to go about your activities, and once your turn is approaching, 
                        you’ll receive an email notification with further details. Thank you for choosing us!`, // Email message body
            });  

            if (response.data.message) {
                 // set the qr code value
                generatedQrValue.value = `http://192.168.0.164:8080/customer-dashboard/${token.value}`

                // generate the qr code image
                const qrCodeDataUrl = await QrCode.toDataURL(generatedQrValue.value, {errorCorrectionLevel: 'H', type: 'image/png'});

                // Notify the user that the email was successfully sent
                //$notify('positive', 'check', response.data.message);

                const queuenumber = `${indicator.value}#-${String(data.queue_number).padStart(3, '0')}`

                // Trigger the print function with the queue details and QR code
                printQueueDetails(queuenumber, name.value,  ServiceAvail.value, data.window_name, qrCodeDataUrl);

                // Reset form values after successful submission
                name.value = "";
                email.value = "";
                type_id.value = "";
                currencySelected.value = "";
                token.value = ""; 
            }
      } catch (error) { 
        if (error.response) {
           // If the error response exists, check for the status
          if (error.response.status === 422) {
            formError.value = error.response.data;
          }else if (error.response.status === 400) {
            $notify('negative','error','No tellers assigned to this service type')
          }else if (error.response.status === 500) {
            $notify('negative','error','No tellers assigned to this service type')
          }
          else if (error.response.status === 500) {
            $notify('negative','error','No tellers assigned to this service type')
          }
        }else {
          console.log(error)
        }
      }
      finally{
        isLoading.value = false;
      }
    };

    const printQueueDetails = async (queueNumber, customerName, serviceType, window_name, qrCodeDataUrl) => {
      try {
        const printWindow = window.open('', '', 'height=400,width=450');

        // Write the content of the print window
        printWindow.document.write('<html>');
        printWindow.document.write('<head>');
        printWindow.document.write('<meta charset="UTF-8">');
        printWindow.document.write('<meta name="viewport" content="width=device-width, initial-scale=1.0">');
        printWindow.document.write('<title>Customer Queue Details</title>');
        printWindow.document.write('<style>');
        
        // General Body styles
        printWindow.document.write('body {');
        printWindow.document.write('font-family: Arial, sans-serif;');
        printWindow.document.write('margin: 0;');
        printWindow.document.write('padding: 0;');
        printWindow.document.write('display: flex;');
        printWindow.document.write('flex-direction: column;');
        printWindow.document.write('justify-content: center;');
        printWindow.document.write('align-items: center;');
        printWindow.document.write('text-align: center;');
        printWindow.document.write('margin-top: 20px;');
        printWindow.document.write('}');

        // Outer container styles
        printWindow.document.write('.container1 {');
        printWindow.document.write('width: 100%;');
        printWindow.document.write('max-width: 400px; /* Set to 400px to fit in the 450px window width */');
        printWindow.document.write('padding: 5px;');
        printWindow.document.write('box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);');
        printWindow.document.write('text-align: center;');
        printWindow.document.write('}');

        // Grid container for content
        printWindow.document.write('.container {');
        printWindow.document.write('margin-left: 15px;');
        printWindow.document.write('display: grid;');
        printWindow.document.write('grid-template-columns: auto auto;');
        printWindow.document.write('width: 100%;');
        printWindow.document.write('}');

        printWindow.document.write('.container > div {');
        printWindow.document.write('background-color: #ffffff;');
        printWindow.document.write('font-size: 12px;');
        printWindow.document.write('text-align: left;');
        printWindow.document.write('}');

        // Optional: Styling for the QR Code image
        printWindow.document.write('img {');
        printWindow.document.write('display: block;');
        printWindow.document.write('margin-left: auto;');
        printWindow.document.write('margin-right: auto;');
        printWindow.document.write('}');

        // Styling for headers (h1, h2, h3)
        printWindow.document.write('h1, h2, h3 {');
        printWindow.document.write('margin: 10px 0;');
        printWindow.document.write('}');

        printWindow.document.write('</style>');
        printWindow.document.write('</head>');
        
        // Body content
        printWindow.document.write('<body>');
        printWindow.document.write('<div class="container1">');
        printWindow.document.write('<strong><h5">VRTSYSTEMS TECHNOLOGIES</h5></strong> <br>');
        printWindow.document.write('<strong> <h4">Customer Queue Details</h4> </strong>');
        printWindow.document.write('<h2 style="margin-top:25px;">' + queueNumber + '</h2>');
        printWindow.document.write('<hr> <!-- Divider between sections -->');
        printWindow.document.write('<div class="container">');
        printWindow.document.write('<div><p><strong>Name:</strong></p></div>');
        printWindow.document.write('<div><p>' + customerName + '</p></div>');
        printWindow.document.write('<div><p><strong>Service Type:</strong></p></div>');
        printWindow.document.write('<div><p>' + serviceType + '</p></div>');
        printWindow.document.write('<div><p><strong>Window name:</strong></p></div>');
        printWindow.document.write('<div><p>' + window_name + '</p></div>');
        printWindow.document.write('</div>');
        printWindow.document.write('<hr> <!-- Divider between sections -->');
        printWindow.document.write('<h3>QR Code for Customer Dashboard</h3>');
        printWindow.document.write('<img src="' + qrCodeDataUrl + '" width="150" height="150" alt="QR Code">');
        printWindow.document.write('</div>');
        printWindow.document.write('</body>');
        printWindow.document.write('</html>');
        
        // Close the document to render the content
        printWindow.document.close();
        
        // Open the print dialog
        printWindow.print();
        $notify('positive', 'check', 'Successfully registered');
      } catch (error) {
        console.error('Error during print process:', error);
      }
    };

    const fetchCategories = async () => {
    try {
      const { data } = await $axios.post("/types/filteredTypes");

      // Filter rows where type_id is NOT null
      const filteredRows = data.rows.filter(row => row.type_id !== null);

      // Log filtered type_id values
      filteredRows.forEach(row => console.log(row.type_id));

      // Assign only valid rows to categoriesList.value
      categoriesList.value = filteredRows;
      
    } catch (error) {
      console.error("Error fetching categories:", error);
    }
  };

    const fecthCurrencty = async (selectedValue) => {
    try {
     // Find the category from the list that matches the selected type_id
      const selectedCategory = categoriesList.value.find(category => category.id === type_id.value);
      // Once the category is found, update the indicator with the corresponding indicator value
      indicator.value = selectedCategory.indicator
      ServiceAvail.value = selectedCategory.name

      categoryForeignExchange.value = selectedValue;
      if (selectedValue === 1) {
        const { data } = await $axios.post('/currency/showData');

        currentCiesList.value = data.rows
          .map(row => ({
            id: row.id,
            name: row.currency_name,
            symbol: row.currency_symbol,
            flag: row.flag,
            buy_value: row.buy_value,
            sell_value: row.sell_value
          }))
          .sort((a, b) => a.name.localeCompare(b.name)); // Sort alphabetically by name
      } else {
        currentCiesList.value = [];
        currencySelected.value = '';
      }
    } catch (error) {
      if (error.response.status === 422) {
        console.log(error.message);
      }
    }
  };

    onMounted(() => {
      fetchCategories();
    });

    return { 
      prioritySelected,
      customModel,
      ServiceAvail,
      generatedQrValue,
      name,
      email,
      indicator,
      email_status,
      joinQueue,
      generateRandomString,
      formError,
      isLoading,
      token,
      currencySelected,
      type_id,
      categoriesList,
      fecthCurrencty,
      currentCiesList,

      categoriesPriority: [
        'Elderly Customers', 
        'Pregnant Women', 
        'People with Disabilities', 
        'Premium Customers', 
        'Parents with Young Children', 
        'Queue-Free Services'
      ]
    };
  },
};
</script>

<style lang="scss" scoped>
.full-page {
  height: 100vh;
  width: 100vw;
  background: linear-gradient(to bottom, $primary 50%, white 50%);
}
</style>

<style>
@import 'flag-icons/css/flag-icons.min.css';
</style>
<style>
@import 'flag-icons/css/flag-icons.min.css';
</style>