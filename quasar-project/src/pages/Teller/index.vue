<template>
  <q-layout view="hHh lpR fFf">
    <q-page-container>
      <q-page class="flex flex-center">
        <div class="q-pa-md full-width">
          <!-- Main Row Container -->
          <div class="row q-col-gutter-md justify-center">
            <!-- First Item -->
            <div class="col-12 col-md-6">
              <q-card class="q-pa-md">
                <q-card-section>
                  <q-item>
                    <q-item-section>
                      <q-item-label class="text-h6 text-center"
                        >Waiting Queue</q-item-label
                      >
                    </q-item-section>
                  </q-item>
                  <q-item>
                    <q-item-section>
                      <q-markup-table>
                        <tbody>
                          <tr>
                            <td class="text-left">
                              <h5 class="q-mb-sm">A001</h5>
                              <p>John Cruz</p>
                            </td>
                            <td class="text-right">
                              <div class="q-gutter-y-xs">
                                <q-btn class="full-width" label="Cater" />
                                <q-btn class="full-width" label="Cancel" />
                                <q-btn class="full-width" label="Wait" />
                              </div>
                            </td>
                          </tr>
                          <tr>
                            <td class="text-left">
                              <h5 class="q-mb-sm">A002</h5>
                              <p>Power Cruz</p>
                            </td>
                            <td class="text-right">
                              <div class="q-gutter-y-xs">
                                <q-btn class="full-width" label="Cater" />
                                <q-btn class="full-width" label="Cancel" />
                                <q-btn class="full-width" label="Wait" />
                              </div>
                            </td>
                          </tr>
                          <tr>
                            <td class="text-left">
                              <h5 class="q-mb-sm">A003</h5>
                              <p>Juan Dela Cruz</p>
                            </td>
                            <td class="text-right">
                              <div class="q-gutter-y-xs">
                                <q-btn
                                  class="full-width"
                                  label="Cater"
                                  color="positive"
                                />
                                <q-btn
                                  class="full-width"
                                  label="Cancel"
                                  color="negative"
                                />
                                <q-btn
                                  class="full-width"
                                  label="Wait"
                                  color="warning"
                                />
                              </div>
                            </td>
                          </tr>
                          <tr>
                            <td class="text-left">
                              <h5 class="q-mb-sm">A004</h5>
                              <p>Johnloyd Cruz</p>
                            </td>
                            <td class="text-right">
                              <div class="q-gutter-y-xs">
                                <q-btn
                                  class="full-width"
                                  label="Cater"
                                  color="positive"
                                />
                                <q-btn
                                  class="full-width"
                                  label="Cancel"
                                  color="negative"
                                />
                                <q-btn
                                  class="full-width"
                                  label="Wait"
                                  color="warning"
                                />
                              </div>
                            </td>
                          </tr>
                          <tr>
                            <td class="text-left">
                              <h5 class="q-mb-sm">A005</h5>
                              <p>Johnny Cruz</p>
                            </td>
                            <td class="text-right">
                              <div class="q-gutter-y-xs">
                                <q-btn
                                  class="full-width"
                                  label="Cater"
                                  color="positive"
                                />
                                <q-btn
                                  class="full-width"
                                  label="Cancel"
                                  color="negative"
                                />
                                <q-btn
                                  class="full-width"
                                  label="Wait"
                                  color="warning"
                                />
                              </div>
                            </td>
                          </tr>
                        </tbody>
                      </q-markup-table>
                    </q-item-section>
                  </q-item>
                </q-card-section>
              </q-card>
            </div>

            <!-- Second Item -->
            <div class="col-12 col-md-6">
              <q-card class="q-pa-md">
                <q-card-section>
                  <q-item>
                    <q-item-section>
                      <q-item-label class="text-h6">Item 2</q-item-label>
                      <q-item-label caption
                        >Stacks on small screens</q-item-label
                      >
                    </q-item-section>
                  </q-item>
                </q-card-section>
              </q-card>
            </div>
          </div>
        </div>
      </q-page>
    </q-page-container>
  </q-layout>
</template>

<script setup>
import { defineComponent } from "vue";

defineComponent({
  name: "ResponsiveLayout",
});
</script>

<style>
@media (max-width: 320px) {
  .text-left,
  .text-right {
    width: 100%;
  }
  .q-gutter-y-xs {
    display: flex;
    flex-direction: column;
    gap: 8px; /* Optional: controls the spacing between buttons */
  }
}
.q-gutter-y-xs {
  display: flex;
  flex-direction: column;
  gap: 8px; /* Optional: control the spacing between buttons */
}

/* Ensure buttons are full-width */
.q-btn.full-width {
  width: 100%;
}

</style>
