<template>
  <div class="tw:text-gray-900 tw:dark:text-white">
    <button
      type="button"
      id="maintenance"
      name="maintenance"
      :data-device-id="deviceId"
      :data-device-name="deviceName"
      :data-maintenance-id="localMaintenanceId"
      :disabled="isInMaintenance && !localMaintenanceId"
      :class="[
        'tw:px-4 tw:py-2 tw:rounded tw:flex tw:items-center tw:gap-2',
        isInMaintenance ? 'tw:bg-amber-500 tw:text-white tw:hover:bg-amber-600 tw:dark:bg-amber-600 tw:dark:hover:bg-amber-700' : 'tw:bg-green-500 tw:text-white tw:hover:bg-green-600 tw:dark:bg-green-600 tw:dark:hover:bg-green-700',
        (isInMaintenance && !localMaintenanceId) ? 'tw:opacity-50 tw:cursor-not-allowed' : ''
      ]"
      @click="showModal"
    >
      <i class="fa fa-wrench"></i>
      <span>{{ isInMaintenance ? 'Device under Maintenance' : 'Maintenance Mode' }}</span>
    </button>

    <!-- Maintenance Modal -->
    <div
      v-if="isModalVisible"
      class="tw:fixed tw:inset-0 tw:z-50 tw:flex tw:items-center tw:justify-center tw:bg-black/50 tw:dark:bg-black/70"
      @keydown.esc="hideModal"
      role="dialog"
      aria-labelledby="maintenance-modal-title"
      aria-modal="true"
    >
      <div class="tw:bg-white tw:dark:bg-gray-800 tw:rounded-lg tw:shadow-xl tw:w-full tw:max-w-xl tw:mx-auto">
        <div class="tw:flex tw:justify-between tw:items-center tw:px-5 tw:py-3 tw:border-b tw:border-gray-200 tw:dark:border-gray-700">
          <h5 id="maintenance-modal-title" class="tw:text-2xl tw:font-medium tw:dark:text-white">Device Maintenance</h5>
          <button
            type="button"
            class="tw:text-gray-400 tw:hover:text-gray-500 tw:dark:text-gray-300 tw:dark:hover:text-white"
            @click="hideModal"
            aria-label="Close"
          >
            &times;
          </button>
        </div>
        <div class="tw:p-6">
          <form id="sched-form">
            <div class="tw:mb-6">
              <div class="tw:grid tw:grid-cols-5 tw:gap-6 tw:items-start">
                <label for="notes" class="tw:col-span-1 tw:font-medium tw:text-gray-700 tw:dark:text-gray-300 tw:pt-2">Notes: </label>
                <div class="tw:col-span-4">
                  <textarea
                    id="notes"
                    name="notes"
                    placeholder="Maintenance notes"
                    v-model="notes"
                    class="tw:w-full tw:px-4 tw:py-3 tw:border tw:border-gray-300 tw:dark:border-gray-600 tw:rounded-md tw:bg-white tw:dark:bg-gray-700 tw:text-gray-900 tw:dark:text-white tw:placeholder-gray-400 tw:dark:placeholder-gray-300 tw:focus:outline-none tw:focus:ring-2 tw:focus:ring-blue-500 tw:dark:focus:ring-blue-400"
                    rows="3"
                  ></textarea>
                </div>
              </div>
            </div>
            <div class="tw:mb-6">
              <div class="tw:grid tw:grid-cols-5 tw:gap-6 tw:items-center">
                <label for="duration" class="tw:col-span-1 tw:font-medium tw:text-gray-700 tw:dark:text-gray-300">Duration: </label>
                <div class="tw:col-span-4">
                  <select
                    name="duration"
                    id="duration"
                    v-model="duration"
                    class="tw:w-full tw:px-4 tw:py-3 tw:border tw:border-gray-300 tw:dark:border-gray-600 tw:rounded-md tw:bg-white tw:dark:bg-gray-700 tw:text-gray-900 tw:dark:text-white tw:focus:outline-none tw:focus:ring-2 tw:focus:ring-blue-500 tw:dark:focus:ring-blue-400"
                  >
                    <option v-for="duration in getMaintenanceDurationList()" :key="duration" :value="duration">
                      {{ duration }}h
                    </option>
                  </select>
                </div>
              </div>
            </div>
            <div class="tw:mb-6">
              <div class="tw:grid tw:grid-cols-5 tw:gap-6 tw:items-center">
                <label for="behavior" class="tw:col-span-1 tw:font-medium tw:text-gray-700 tw:dark:text-gray-300">Behavior: </label>
                <div class="tw:col-span-4">
                  <select
                    name="behavior"
                    id="behavior"
                    v-model="behavior"
                    class="tw:w-full tw:px-4 tw:py-3 tw:border tw:border-gray-300 tw:dark:border-gray-600 tw:rounded-md tw:bg-white tw:dark:bg-gray-700 tw:text-gray-900 tw:dark:text-white tw:focus:outline-none tw:focus:ring-2 tw:focus:ring-blue-500 tw:dark:focus:ring-blue-400"
                  >
                    <option v-for="option in getMaintenanceBehaviorList()" :key="option.value" :value="option.value">
                      {{ option.text }}
                    </option>
                  </select>
                </div>
              </div>
            </div>
            <div class="tw:mt-8">
              <div class="tw:grid tw:grid-cols-5 tw:gap-6 tw:items-center">
                <div class="tw:col-span-1"></div>
                <div class="tw:col-span-4">
                  <button
                    type="button"
                    id="maintenance-submit"
                    :disabled="isButtonDisabled()"
                    :class="[
                      'tw:px-5 tw:py-3 tw:rounded-md tw:font-medium tw:text-white',
                      isInMaintenance
                        ? 'tw:bg-amber-500 tw:hover:bg-amber-600 tw:dark:bg-amber-600 tw:dark:hover:bg-amber-700'
                        : 'tw:bg-green-500 tw:hover:bg-green-600 tw:dark:bg-green-600 tw:dark:hover:bg-green-700',
                      isButtonDisabled() ? 'tw:opacity-50 tw:cursor-not-allowed' : ''
                    ]"
                    @click="toggleMaintenance()"
                    name="maintenance-submit"
                  >
                    {{ getButtonText() }}
                  </button>
                </div>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'MaintenanceMode',
  props: {
    deviceId: {
      type: Number,
      required: true
    },
    deviceName: {
      type: String,
      default: ''
    },
    maintenanceId: {
      type: Number,
      default: null
    },
    defaultMaintenanceBehavior: {
        type: Number,
        default: 1
    }
  },
  data() {
    return {
      notes: '',
      duration: '',
      behavior: this.defaultMaintenanceBehavior,
      localMaintenanceId: this.maintenanceId === '' ? null : this.maintenanceId,
      isInMaintenance: false,
      isLoading: false,
      isModalVisible: false
    };
  },
  mounted() {
    // Set initial maintenance status
    this.isInMaintenance = this.localMaintenanceId !== null && this.localMaintenanceId !== '';

    // Set default value for duration if empty
    if (!this.duration) {
      const durations = this.getMaintenanceDurationList();
      if (durations.length > 0) {
        this.duration = durations[0];
      }
    }
  },
  methods: {
    getMaintenanceDurationList() {
      const durations = [];
      const minuteSteps = [0, 30];

      for (let hour = 0; hour <= 23; hour++) {
        for (const minute of minuteSteps) {
          if (hour === 0 && minute === 0) {
            continue; // Skip 0:00
          }

          durations.push(`${hour}:${minute.toString().padStart(2, '0')}`);
        }
      }

      return durations;
    },
    getMaintenanceBehaviorList() {
      return [
        { value: 1, text: 'Skip alerts' },
        { value: 2, text: 'Mute alerts' },
        { value: 3, text: 'Run alerts' }
      ];
    },
    showModal() {
      // Show the modal using Vue's reactive state
      this.isModalVisible = true;
    },
    hideModal() {
      // Hide the modal using Vue's reactive state
      this.isModalVisible = false;
    },
    toggleMaintenance() {
      if (this.isInMaintenance) {
        this.disableMaintenance();
      } else {
        this.enableMaintenance();
      }
    },
    enableMaintenance() {
      this.isLoading = true;

      // Send AJAX request to enable maintenance
      fetch('ajax_form.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
          type: 'schedule-maintenance',
          sub_type: 'new-maintenance',
          title: this.deviceName,
          notes: this.notes,
          behavior: this.behavior,
          recurring: 0,
          start: new Date().toISOString().slice(0, 19).replace('T', ' '),
          duration: this.duration,
          'maps[]': this.deviceId
        })
      })
      .then(response => response.json())
      .then(data => {
        this.isLoading = false;
        if (data.status === 'ok') {
          // Update data
          this.isInMaintenance = true;
          // Update localMaintenanceId with the returned schedule_id
          if (data.schedule_id) {
            this.localMaintenanceId = data.schedule_id;
          }
          toastr.success(data.message);
          // Close the modal
          this.hideModal();
        } else {
          toastr.error(data.message);
        }
      })
      .catch(error => {
        this.isLoading = false;
        toastr.error('An error occurred setting this device into maintenance mode');
        console.error('Error:', error);
      });
    },
    disableMaintenance() {
      this.isLoading = true;

      // Send AJAX request to disable maintenance
      fetch('ajax_form.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
          type: 'schedule-maintenance',
          sub_type: 'del-maintenance',
          del_schedule_id: this.localMaintenanceId
        })
      })
      .then(response => response.json())
      .then(data => {
        this.isLoading = false;
        if (data.status === 'ok') {
          // Update data
          this.isInMaintenance = false;
          this.localMaintenanceId = null;
          toastr.success(data.message);
          // Close the modal
          this.hideModal();
        } else {
          toastr.error(data.message);
        }
      })
      .catch(error => {
        this.isLoading = false;
        toastr.error('An error occurred disabling maintenance mode');
        console.error('Error:', error);
      });
    },
    getButtonText() {
      return this.isInMaintenance ? 'End Maintenance' : 'Start Maintenance';
    },
    isButtonDisabled() {
      return this.isLoading;
    }
  }
};
</script>
