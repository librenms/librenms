<x-toggle x-bind:id="inputId"
          x-bind:checked="value"
          x-on:change="changeValue($event.target.checked)"
          x-bind:required="setting.required"
          x-bind:disabled="setting.overridden"
/>
