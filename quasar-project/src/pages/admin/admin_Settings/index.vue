<template>
  <q-page class="q-px-lg" style="height: auto; min-height: unset;">
    <div class="q-my-md bg-white q-pa-sm shadow-1">
            <q-breadcrumbs 
                class="q-mx-sm"
                >
                <q-breadcrumbs-el icon="home" to="/admin/dashboard" />
                <q-breadcrumbs-el label="Settings" icon="settings"/>
                <q-breadcrumbs-el label="Personal Info" icon="computer" to="/admin/settings" />
            </q-breadcrumbs>
            </div>

  <div class="row q-col-gutter-xs q-px-xs q-mt-sm">
      <!-- Profile Section -->
      <q-card class="col-12 col-sm-4 col-md-3 q-pa-md flex flex-center">
      <div class="column items-center q-ma-lg">
              <q-avatar size="150px">
                <template v-if="!previewAdminImage.Image">
                  <q-img
                    :src="require('assets/no-image-user.png')"
                     class="full-width full-height"
                     style="border-radius: 50%; border: 2px solid #1c5d99;" 
                     fit="cover" 
                  />
                </template>
                <template v-else>
                  <q-img
                    :src="previewAdminImage.Image"
                     class="full-width full-height"
                      style="border-radius: 50%; border: 2px solid #1c5d99;" 
                      fit="cover" 
                  />
                </template>
              </q-avatar>
      <div class="text-center q-mt-md">
          <div class="text-h6">
              <template v-if="!previewAdminImage?.Firstname">
                Loading...
              </template>
              <template v-else>
                {{ previewAdminImage?.Firstname + " " + previewAdminImage?.Lastname }}
                <div v-if="previewAdminImage?.branch_name">
                  <p class="text-gray q-mt-md">{{ previewAdminImage?.branch_name }} </p>
                    <p class="text-gray" style="margin-top: -30px;font-size: 15px; color: #1c5d99;">Branch</p>
                </div>
              </template>

          </div>
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
                            placeholder="Username" 
                            hint="Username" 
                            :dense="dense" 
                            :error="formError.hasOwnProperty('Username')"
                            :error-message="formError.Username ? formError.Username[0] : ''" 
                            class="col-12 col-md-4"/>
                          
                            <q-input 
                              filled 
                              v-model="adminInfo.Firstname" 
                              placeholder="Firstname" 
                              hint="First Name" 
                              :dense="dense" 
                              :error="formError.hasOwnProperty('Firstname')"
                              :error-message="formError.Firstname ? formError.Firstname[0] : ''" 
                              class="col-12 col-md-4"/>
                        
                            <q-input 
                              filled 
                              v-model="adminInfo.Lastname"
                              placeholder="Lastname" 
                              hint="Last Name" 
                              :dense="dense" 
                              :error="formError.hasOwnProperty('Lastname')"
                              :error-message="formError.Lastname ? formError.Lastname[0] : ''" 
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
                  :src="previewImage || require('assets/no-image.png')" 
                  spinner-color="primary"
                  class="shadow-2 rounded-borders q-ml-lg"
                  style="width: 100%; max-width: 150px; height: 150px; object-fit: cover;"
              />
              </div>

              <!-- Upload Section -->
              <div class="col">
                <q-file
                    v-model="selectedImage"
                    label="Select image"
                    accept="image/*"
                    filled
                  >
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
                      @click="uploadImage"
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
  import { ref, onMounted, watch, onUnmounted  } from 'vue';
  import { $axios, $notify } from 'src/boot/app';
  import { useQuasar } from 'quasar';
  
  export default {
    setup() {
      const $dialog = useQuasar();
      const tab = ref('info');
      const idAdmin = ref ('');
      const branch_id = ref ('');
      const formError = ref({});
      const selectedImage = ref(null); // Holds the selected image file
      const previewImage = ref(null); // Holds the image preview
      const previewAdminImage = ref({})


      const adminInfo = ref({
        id: '',
        Username: '',
        Firstname: '',
        Lastname: '',
        branch_id: '',
        branch_name: '',
      });
  
      const adminPassword = ref({
        id: '',
        newPassword: '',   // Initialized with empty string
        confirmPassword: '',  // Initialized with empty string
        });
  
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
              // Capitalize each string field individually
              adminInfo.value.Username = capitalizeWords(adminInfo.value.Username);
              adminInfo.value.Firstname = capitalizeWords(adminInfo.value.Firstname);
              adminInfo.value.Lastname = capitalizeWords(adminInfo.value.Lastname);

              const { data } = await $axios.post('/admin/updateInformation', adminInfo.value);
              formError.value = {}; // Reset form errors after successful submission
              if (data) {
                $notify('positive', 'done', data.message); // Notify success
              }
            } catch (error) {
              if (error.response.status === 422) {
                formError.value = error.response.data.errors; // Map errors to the formError objec
              }else {
                console.log(error)
              }
            }
          }).onDismiss(() => {
            // console.log('I am triggered on both OK and Cancel')
          });
      };

      // Capitalize each word in the string 
      const capitalizeWords = (str) => {
        return str
          .split(' ')
          .map(word => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase())
          .join(' ');
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
                // Ensure the admin ID is set correctly in adminPassword before making the request
                adminPassword.value.id = adminInfo.value.id;
              try {
                formError.value = {}; // Reset form errors after successful submission
                // Check if both password fields are filled
                if (adminPassword.value.newPassword != adminPassword.value.confirmPassword ) {
                    $notify('negative', 'error', 'Passwords do not match. Please check your input data.');
                    return;
                }
                // Proceed to update password
                const { data } = await $axios.post('/admin/updatePassword', adminPassword.value);
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
          if (adminInfo.value.branch_id != null) {
            const { data } = await $axios.post('/admin/Information',{
              id: idAdmin.value,
              branch_id: adminInfo.value.branch_id
             });
            
            if (data.dataValue) { 
              adminInfo.value = data.dataValue
            }
          }else {
            const { data } = await $axios.post('/admin/Information',{
            id: idAdmin.value
             });
            
            if (data.dataValue) { 
              adminInfo.value = data.dataValue
            }
          }
        } catch (error) {
          console.log(error);
        }
      };

      const fetchingImage = async () => {
        try {
          if (adminInfo.value.branch_id != null) {
            const { data } = await $axios.post('/admin/Information',{
             id: idAdmin.value,
             branch_id: adminInfo.value.branch_id
            });
            if (data.dataValue) { 
              previewAdminImage.value = data.dataValue
            }
          }else {
            const { data } = await $axios.post('/admin/Information',{
              id: idAdmin.value
            });
            if (data.dataValue) { 
              previewAdminImage.value = data.dataValue
            }
          }
        } catch (error) {
          console.log(error);
        }
      }

      // Preview image before upload
      // Watch the selectedImage variable for changes
      watch(selectedImage, (newVal) => {
        console.log("selectedImage changed:", newVal);
        // Check if newVal is an array (if multiple is enabled) or a single file
        const file = Array.isArray(newVal) ? newVal[0] : newVal;
        if (file) {
          const reader = new FileReader();
          reader.onload = (e) => {
            previewImage.value = e.target.result; // Set previewImage to the generated data URL
            console.log("Preview updated:", e.target.result);
          };
          reader.readAsDataURL(file);
        }
      });

      
      // Upload image to the server
       const uploadImage = async () => {
        if (!selectedImage.value) {
          $notify('negative', 'error', 'Please select an image');
          return;
        }

        $dialog.dialog({
            title: 'Confirm',
            message: 'Are you sure you want to update your profile picture?',
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

              let formData = new FormData();
              formData.append('id', idAdmin.value); // Ensure the ID is passed
              formData.append('Image', selectedImage.value);

              try {
                const { data } = await $axios.post('/upload-image', formData, {
                  headers: { 'Content-Type': 'multipart/form-data' }
                });

                // ✅ Correct way to update preview image
                previewImage.value = data.Image;
                
                $notify('positive', 'check', data.message); // ✅ Correct way to access message

              } catch (error) {
                console.error("Upload Error:", error);
                $notify('negative', 'error', error.response?.data?.message || 'Upload failed!');
              }
            }).onDismiss(() => {
            // Optional: Handle dismiss action
            })
      }; 

  
      // Fetch the data when the component is mounted
      let imageTimeout;
      const optimizedFetchImage = async () => {
        await fetchingImage()
        imageTimeout = setTimeout(optimizedFetchImage, 2000); // Recursive Timeout
      };
      onMounted(() => {
        // check if have localStorage 
        const storedAdminfo = localStorage.getItem('adminInformation')
        const storeManagerinfo = localStorage.getItem('managerInformation');
        if (storedAdminfo) {
          const adminData = JSON.parse(storedAdminfo);
          idAdmin.value = adminData.id;  // Make sure you're only storing the ID here
          fetchAdminInfo()
          optimizedFetchImage()
        } else if (storeManagerinfo) {
          const adminData = JSON.parse(storeManagerinfo);
          idAdmin.value = adminData.id;  // Make sure you're only storing the ID here
          adminInfo.value.branch_id = adminData.branch_id
          fetchAdminInfo()
          optimizedFetchImage()
        }
        else {
          console.error("No admin information found in localStorage");
        }
      });
      onUnmounted(() => {
      clearTimeout(imageTimeout);
    });
  
      return {
        tab,
        adminInfo,
        fetchAdminInfo,
        formError,
        UpdateAdmin,
        updatePassword,
        adminPassword,
        idAdmin,
        selectedImage,
        previewImage,
        uploadImage,
        previewAdminImage,
        fetchingImage,
      };
    },
  };
  </script>
  