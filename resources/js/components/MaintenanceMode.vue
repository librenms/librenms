<template>
  <div>
    <button
      type="button"
      id="maintenance"
      name="maintenance"
      :data-device-id="deviceId"
      :data-device-name="deviceName"
      :data-maintenance-id="localMaintenanceId"
      :disabled="isInMaintenance && !localMaintenanceId"
      :class="'btn ' + getButtonClass()"
      @click="showModal"
    >
      <i class="fa fa-wrench"></i>
      <span>{{ isInMaintenance ? 'Device under Maintenance' : 'Maintenance Mode' }}</span>
    </button>

    <!-- Maintenance Modal -->
    <div class="modal fade" id="device_maintenance_modal" tabindex="-1" role="dialog" aria-labelledby="device_edit">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h5 class="modal-title" id="search_alert_rule_list">Device Maintenance</h5>
          </div>
          <div class="modal-body">
            <form
              method="post"
              role="form"
              id="sched-form"
              class="form-horizontal schedule-maintenance-form"
            >
              <div class="form-group">
                <label for="notes" class="col-sm-4 control-label">Notes: </label>
                <div class="col-sm-8">
                  <textarea class="form-control" id="notes" name="notes" placeholder="Maintenance notes" v-model="notes"></textarea>
                </div>
              </div>
              <div class="form-group">
                <label for="duration" class="col-sm-4 control-label">Duration: </label>
                <div class="col-sm-8">
                  <select name="duration" id="duration" class="form-control input-sm" v-model="duration">
                    <option v-for="duration in getMaintenanceDurationList()" :key="duration" :value="duration">
                      {{ duration }}h
                    </option>
                  </select>
                </div>
              </div>
              <div class="form-group">
                <label for="behavior" class="col-sm-4 control-label">Behavior: </label>
                <div class="col-sm-8">
                  <select name="behavior" id="behavior" class="form-control input-sm" v-model="behavior">
                    <option v-for="option in getMaintenanceBehaviorList()" :key="option.value" :value="option.value">
                      {{ option.text }}
                    </option>
                  </select>
                </div>
              </div>
              <div class="form-group">
                <label for="maintenance-submit" class="col-sm-4 control-label"></label>
                <div class="col-sm-8">
                  <button
                    type="button"
                    id="maintenance-submit"
                    :disabled="isButtonDisabled()"
                    :class="'btn ' + getButtonClass()"
                    @click="toggleMaintenance()"
                    name="maintenance-submit"
                  >
                    {{ getButtonText() }}
                  </button>
                </div>
              </div>
            </form>
          </div>
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
      modalElement: null
    };
  },
  mounted() {
    // Set initial maintenance status
    this.isInMaintenance = this.localMaintenanceId !== null && this.localMaintenanceId !== '';

    // Get reference to the modal
    this.modalElement = document.querySelector('#device_maintenance_modal');

    if (!this.modalElement) {
      console.error('Maintenance modal not found');
    }

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
      // Show the modal using Bootstrap's modal method
      if (!this.modalElement) {
        console.error('Cannot show modal: Modal element not found');
        toastr.error('An error occurred: Modal element not found');
        return;
      }
      $(this.modalElement).modal('show');
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
          // Close the modal if it exists
          if (this.modalElement) {
            $(this.modalElement).modal('hide');
          }
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
          // Close the modal if it exists
          if (this.modalElement) {
            $(this.modalElement).modal('hide');
          }
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
    getButtonClass() {
      return this.isInMaintenance ? 'btn-warning' : 'btn-success';
    },
    isButtonDisabled() {
      return this.isLoading;
    }
  }
};
</script>
