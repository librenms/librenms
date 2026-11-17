<div x-data="{
    notes: @js($notes),
    saving: false,
    save() {
        this.saving = true;
        axios.put(@js(route('port.notes.update', $port)), {notes: this.notes})
            .then((response) => toastr.success(response.data.message))
            .catch((error) => toastr.error(error.response?.data?.message ?? @js(__('Failed to save notes'))))
            .finally(() => this.saving = false);
    }
}">
    <x-panel :title="__('Port Notes')">
        <form x-on:submit.prevent="save()">
            <div class="form-group">
                <textarea class="form-control" rows="6" x-model="notes" aria-label="{{ __('Port Notes') }}"></textarea>
            </div>
            <button type="submit" class="btn btn-primary" x-bind:disabled="saving">{{ __('Save') }}</button>
        </form>
    </x-panel>
</div>
