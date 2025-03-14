<template>
  <q-page class="q-pa-lg">
  <div class="row q-col-gutter-xs">
      <!-- Profile Section -->
      <q-card class="col-12 col-sm-4 col-md-3 q-pa-md flex flex-center">
      <div class="column items-center q-ma-lg">
              <q-avatar size="150px">
                <q-img 
                  src="~assets/vrtlogoblack.webp" 
                  class="full-width full-height"
        style="border-radius: 50%; border: 2px solid #1c5d99;" 
        fit="cover" />
              </q-avatar> 
      <div class="text-center q-mt-md">
          <div class="text-h6">{{ adminInfo.Firstname + " " + adminInfo.Lastname }}</div>
      </div>
      </div>
      </q-card>

      <!-- Account Settings -->
          <q-card class="col-12 col-sm-8 col-md-9 q-pa-md">
      <!-- Title and Tabs in One Row -->
          <div class="row items-center justify-between q-gutter-md">
              <div class="text-h6 text-primary text-bold">Account Settings</div>
                  <q-tabs v-model="tab" active-color="primary" class="text-primary" dense narrow-indicator style="display: flex; overflow-x: auto; white-space: nowrap; max-width: 100%;">
                      <q-tab name="info" label="User Account Info"/>
                      <q-tab name="avatar" label="Change Avatar"/>
                      <q-tab name="password" label="Change Password"/>
                  </q-tabs>
              </div>
              <q-separator class="q-my-md" />

              <q-tab-panels v-model="tab" animated>
              <!-- User Account Info Tab -->
              <q-tab-panel name="info">
                  <q-form @submit="saveChanges">
                      <div class="row q-col-gutter-md">
                          <q-input 
                            filled 
                            v-model="adminInfo.Username"
                            placeholder="fetch admin username" 
                            hint="Username" 
                            :dense="dense" 
                            :error="formError.hasOwnProperty('Username')"
                            :error-message="formError.Username"
                            class="col-12 col-md-4"/>
                          
                            <q-input 
                              filled 
                              v-model="adminInfo.Firstname" 
                              placeholder="fetch admin firstname" 
                              hint="First Name" 
                              :dense="dense" 
                              :error="formError.hasOwnProperty('Firstname')"
                              :error-message="formError.Firstname"
                              class="col-12 col-md-4"/>
                        
                            <q-input 
                              filled 
                              v-model="adminInfo.Lastname"
                              placeholder="fetch admin lastname" 
                              hint="Last Name" 
                              :dense="dense" 
                              :error="formError.hasOwnProperty('Lastname')"
                              :error-message="formError.Lastname"
                              class="col-12 col-md-4"/>
                      </div>

                      <q-separator class="q-my-md" />

                      <div class="q-mt-sm text-right">
                          <q-btn 
                            label="Save Changes" 
                            color="positive" 
                            class="q-pa-xs" 
                            @click="UpdateAdmin"
                            style="width: 125px; 
                            margin-right: 10px;"/>  
                      </div>
                  </q-form>
              </q-tab-panel>

          <!-- Change Avatar -->
          <q-tab-panel name="avatar">
          <div class="row q-col-gutter-md">
              <!-- Image Preview -->
              <div class="col-12 col-sm-4 col-md-3">
              <q-img
                  :src="previewImage || 'no-image.png'"
                  spinner-color="primary"
                  class="shadow-2 rounded-borders q-ml-lg"
                  style="width: 100%; max-width: 150px; height: 150px; object-fit: cover;"
              />
              </div>

              <!-- Upload Section -->
              <div class="col">
              <q-file v-model="selectedImage" label="Select image" accept="image/*" @change="previewFile" filled>
                  <q-icon name="attach_file" />
              </q-file>

              <q-banner class="text-warning q-mt-md">
                  <q-icon name="warning" />
                  <strong>NOTE!</strong> Attached image thumbnail must
              </q-banner>

              <q-separator class="q-my-md" />

              <div class="q-mt-sm text-right">
                    <q-btn 
                      label="Save Changes" 
                      type="submit" 
                      color="positive" 
                      class="q-pa-xs" 
                      style="width: 125px; 
                      margin-right: 10px;"/> 
              </div>
              </div>
          </div>
          </q-tab-panel>

          <!-- Change Password Panel -->
          <q-tab-panel name="password">
              <q-form @submit="changePassword">
              <div class="row q-col-gutter-md">
                  <q-input 
                      filled 
                      v-model="adminPassword.newPassword"  
                      type="password" 
                      label="New Password" 
                      :error="formError.hasOwnProperty('newPassword')"
                      :error-message="formError.newPassword"
                      class="col-12" />

                  <q-input 
                      filled 
                      v-model="adminPassword.confirmPassword" 
                      type="password" 
                      label="Re-type New Password" 
                      :error="formError.hasOwnProperty('confirmPassword')"
                      :error-message="formError.confirmPassword"
                      class="col-12" />
              </div>

              <div class="q-mt-sm text-right">
                  <q-btn 
                      label="Save Changes"  
                      color="positive"
                      class="q-pa-xs" 
                      @click="updatePassword"
                      style="width: 125px; 
                      margin-right: 10px;"/> 
              </div>
              </q-form>
          </q-tab-panel>

          </q-tab-panels>
          </q-card>
      </div>
  </q-page>
</template>
  
  <script>
  import { ref, onMounted } from 'vue';
  import { $axios, $notify } from 'src/boot/app';
  import { useQuasar } from 'quasar';
  
  export default {
    setup() {
      const $dialog = useQuasar();
      const tab = ref('info');
      const adminInfo = ref({
        id: '',
        Username: '',
        Firstname: '',
        Lastname: '',
      });
  
      const adminPassword = ref({
        id: '',
        newPassword: '',   // Initialized with empty string
        confirmPassword: '',  // Initialized with empty string
        });

  
      const formError = ref({});
  
      const UpdateAdmin = async () => {
          $dialog.dialog({
            title: 'Confirm',
            message: 'Are you sure you want to update?',
            cancel: true,
            persistent: true,
            ok: {
              label: 'Yes',
              color: 'primary',
              unelevated: true,
              style: 'width: 125px;',
            },
            cancel: {
              label: 'Cancel',
              color: 'red-8',
              unelevated: true,
              style: 'width: 125px;',
            },
            style: 'border-radius: 12px; padding: 16px;',
          }).onOk(async () => {
            try {
              const { data } = await $axios.post('/admin/updateInformation', adminInfo.value);
              formError.value = {}; // Reset form errors after successful submission
              if (data) {
                $notify('positive', 'done', data.message); // Notify success
              }
            } catch (error) {
              if (error.response.status === 422) {
                formError.value = error.response.data; // Map errors to the formError objec
              }else {
                console.log(error)
              }
            }
          }).onDismiss(() => {
            // console.log('I am triggered on both OK and Cancel')
          });
      };
  
      const updatePassword = async () => {
            $dialog.dialog({
            title: 'Confirm',
            message: 'Are you sure you want to update your password?',
            cancel: true,
            persistent: true,
            ok: {
                label: 'Yes',
                color: 'primary',
                unelevated: true,
                style: 'width: 125px;',
            },
            cancel: {
                label: 'Cancel',
                color: 'red-8',
                unelevated: true,
                style: 'width: 125px;',
            },
            style: 'border-radius: 12px; padding: 16px;',
            }).onOk(async () => {
              try {
                // Check if both password fields are filled
                if (adminPassword.value.newPassword != adminPassword.value.confirmPassword ) {
                    $notify('negative', 'error', 'Passwords do not match. Please check your input data.');
                    return;
                }

                // Proceed to update password
                const { data } = await $axios.post('/admin/updatePassword', adminPassword.value);
                formError.value = {}; // Reset form errors after successful submission
                if (data) {
                    $notify('positive', 'done', data.message); // Notify success
                }
              } catch (error) {
                if (error.response.status === 422) {
                  // Capture validation errors
                  formError.value = error.response.data;  // Map errors to the formError object
                } else {
                console.log('Error:', error);
                }
              }
            }).onDismiss(() => {
            // Optional: Handle dismiss action
            })
        };

      const fetchAdminInfo = async () => {
        try {
          const { data } = await $axios.post('/admin/Information');
          if (data && data.dataValue && data.dataValue.length > 0) {
            adminInfo.value = data.dataValue[0];
            console.log(adminInfo.value); // Log the fetched admin info
          }
        } catch (error) {
          console.log(error);
        }
      };
  
      // Fetch the data when the component is mounted
      onMounted(() => {
        fetchAdminInfo();
      });
  
      return {
        tab,
        adminInfo,
        fetchAdminInfo,
        formError,
        UpdateAdmin,
        updatePassword,
        adminPassword,
      };
    },
  };
  </script>
  