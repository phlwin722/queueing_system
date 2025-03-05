<template>
    <div class="q-pa-md example-column-equal-width">
      <div class="column">
        <div class="col">
          <q-toolbar>
            <q-breadcrumbs active-color="black" style="font-size: 14px">
              <q-breadcrumbs-el label="QR Code" icon="qr_code" />
            </q-breadcrumbs>
          </q-toolbar>
        </div>
        
        <div class="col">
          <!-- Input to enter bank name -->
          <q-input
            color="black"
            clearable
            outlined
            v-model="QrValue"
            label="Bank name">
            <template v-slot:append>
              <q-avatar>
                <q-icon name="qr_code_2" />
              </q-avatar>
            </template>
          </q-input>
        </div>
  
        <div class="column items-end">
          <div class="col q-pa-md q-gutter-sm">
            <!-- Generate button -->
            <q-btn
              align="between"
              class="btn-fixed-width"
              color="primary"
              label="Generate QR Code"
              @click="generateQrCode"
              icon="qr_code_2" />
          </div>
        </div>
  
        <q-separator />
          
        <div class="column items-center">
          <div
              class="text-h5 "
              v-if="generatedQrValue">
              QR CODE GENERATE
            </div>
          <div class="col">
            <div style="margin-top: 10px;" ref="qrCodeContainer">
              <!-- QR Code component (Set `render-as="canvas"`) -->
              <qrcode-vue
                v-if="generatedQrValue"
                :value="generatedQrValue" 
                :size="300"
                level="H"
                render-as="canvas" /> 
            </div>
          </div>
        </div>
  
        <div class="column items-end">
          <div class="col q-pa-md q-gutter-sm">
            <!-- Generate button -->
            <q-btn
              align="between"
              class="btn-fixed-width"
              color="primary"
              label="Save"
              @click="saveBranch"
              v-if="generatedQrValue"
              icon="save" />
  
            <!-- Download PDF button -->
            <q-btn
              align="around"
              class="btn-fixed-width"
              color="green"
              label="Download PDF"
              v-if="generatedQrValue"
              :disable="!generatedQrValue"
              @click="generatePdfWithQRCode"
              icon="download" />
          </div>
        </div>
      </div>
    </div>
  </template>
  
  
  <script>
  import { defineComponent, ref, onBeforeUnmount, nextTick } from 'vue';
  import html2canvas from "html2canvas";
  import { useQuasar } from 'quasar'; // Quasar instance for loading
  import QrcodeVue from 'qrcode.vue'; // Import the QR code component
  import jsPDF from 'jspdf'; // Import jsPDF for PDF generation
  
  export default defineComponent({
    name: 'IndexPage',
  
    setup() {
      const $q = useQuasar();
      const QrValue = ref(''); // User input (for the bank name)
      const generatedQrValue = ref(''); // The value to be used for QR code generation
      const qrCodeContainer = ref(null); // Ref to get the QR code wrapper
      let timer;
  
      // Clean up before component is unmounted
      onBeforeUnmount(() => {
        if (timer !== void 0) {
          clearTimeout(timer);
          $q.loading.hide();
        }
      });
  
      // Function to generate PDF with QR Code
      const generatePdfWithQRCode = async () => {
        try {
            if (QrValue.value) {
            // Generate QR code image and update the generated value
            generatedQrValue.value = QrValue.value;
              
            await nextTick(); // Ensure QR code is rendered
  
        // Select the `<canvas>` inside the QR code container
          const qrCanvas = qrCodeContainer.value?.querySelector("canvas");
       
          if (!qrCanvas) {
            console.error("QR Code canvas not found.");
            return;
          }
  
            html2canvas(qrCanvas, { useCORS: true}).then((canvas) => {
              const imgData = canvas.toDataURL('image/png')
  
              // Create a new jsPDF instance
              const doc = new jsPDF();
  
            // Set font size for "header" text (e.g., equivalent to h1)
            doc.setFontSize(20); // You can increase this value for larger text
            doc.setFont("helvetica", "bold"); // Set font and style (bold in this case)
  
            // calculate the page width and text width
            const pageWidth = doc.internal.pageSize.width;
            const textWidth = doc.getTextWidth(QrValue.value)
  
            // calculate the x-coordinate for center alignment
            const x = (pageWidth - textWidth) / 2;
  
            // Add the QRvalue text to the top-left corner of the PDF
            doc.text(QrValue.value, x, 10) // (x, 10) positions the text horizontally centered and vertically at 10
            /* doc.text(`${QrValue.value}`, 10, 10); // 10, 10 is the top-left position */
  
             // Add QR Code Image (BEFORE saving)
            doc.addImage(imgData, "PNG", 20, 50, 170, 170);
  
            // Save the generated PDF
            doc.save(`QR_Code_${QrValue.value}_branch.pdf`);
            });
          } else {
            // If no QR Value is entered, show an error notification
            $q.notify({
              color: 'negative',
              position: 'top',
              message: 'Please enter a value to generate the QR code.',
              icon: 'warning'
            });
          }
        } catch(error) {
          console.log(error)
        }
      };
  
      // Function to generate QR code when the button is clicked
      const generateQrCode = async () => {
        try {
            if (QrValue.value) {
            // Show loading spinner while generating the QR code
            $q.loading.show({
              message: 'Generating QR Code in process. Hang on...'
            });
  
            // Simulate some processing time before generating the QR code (e.g., 2s delay)
            timer = setTimeout(() => {
              // Update the generated QR code value
              generatedQrValue.value = QrValue.value;
              
              // Hide the loading spinner
              $q.loading.hide();
              
              // Reset the timer
              timer = void 0;
            }, 2000);
          } else {
            // If no value is entered, show a warning message
            $q.notify({
              color: 'negative',
              position:'top',
              message: 'Please enter a bank name before generating the QR code.',
              icon: 'warning'
            });
          }
        } catch (error) {
          console.log(error)
        }
      };
  
      return {
        generateQrCode,
        generatePdfWithQRCode,
        QrValue,
        generatedQrValue, // Return the generated QR value to be used in the template
        qrCodeContainer, // Return the container ref
      };
    },
  
    components: {
      QrcodeVue, // Register QrcodeVue component for use
    },
  });
  </script>