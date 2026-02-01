<template>
    <div class="row text-center">
        <div class="col">
            Scan Qr Code
        </div>
    </div>
    <div class="row text-center">
        <div class="col">
        <!-- QR Code component from 'qrcode.vue' library -->
        <qrcode-vue
            :value="QrValue"  
            :size="300"   
            level="H"        
            render-as="svg" 
        /> 
        </div>
    </div>
  </template>
  
  <script>
  // Importing the 'qrcode.vue' component
  import QrcodeVue from 'qrcode.vue'
  import { ref } from 'vue' // Make sure to import 'ref' from Vue
  
  export default {
    setup() {
      // Defining 'QrValue' as a reactive reference using 'ref'
      const QrValue = ref('potwjkmldasndlk');
      
      return {
        QrValue
      };
    },    components: {
      QrcodeVue // Registering the 'QrcodeVue' component so it can be used in the template
    },
  }
  </script>
  