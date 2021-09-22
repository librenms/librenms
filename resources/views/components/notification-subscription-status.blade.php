<div x-data="{
 supported: 'Notification' in window,
 enabled: 'Notification' in window && Notification.permission === 'granted' && localStorage.getItem('notifications') !== 'disabled',
 toggle() {
    if (this.enabled) {
      localStorage.setItem('notifications', 'disabled');
      this.enabled = false;
    } else {
      Notification.requestPermission().then(function (permission) {
        this.enabled = permission === 'granted';
      });
      localStorage.setItem('notifications', 'enabled');
      this.enabled = true;
    }
 }
}">
    <div x-show="! supported">This browser does not support notifications</div>
    <div x-show="supported">
        <div>
            Notifications <span x-text="enabled ? 'enabled' : 'not enabled'"></span> for this browser
            <button x-on:click="toggle()" type="button" class="tw-float-right tw-border tw-border-gray-500 tw-text-gray-500 hover:tw-bg-gray-500 hover:tw-text-gray-100 tw-rounded tw-px-4 tw-py-2" x-text="enabled ? 'Disable' : 'Enable'"></button>
        </div>
    </div>
</div>
