<li class="tw:mt-4 tw:pt-2 tw:border-t tw:border-gray-200 tw:dark:border-dark-gray-400"
    x-show="addableRemaining.length > 0"
    x-cloak>
    <div class="input-group">
        <select id="add-method-select" class="form-control tw:rounded-l-lg tw:rounded-r-none tw:border-gray-200 tw:bg-white tw:dark:border-dark-gray-400 tw:dark:bg-dark-gray-500 tw:dark:text-white">
            <option value="">{{ __('Add polling type...') }}</option>
            <template x-for="m in addableRemaining" :key="m.type">
                <option :value="m.type" x-text="m.label"></option>
            </template>
        </select>
        <span class="input-group-btn">
            <button type="button" class="btn btn-success tw:bg-emerald-600 tw:hover:bg-emerald-700 tw:border-emerald-600"
                    @click="
                        const sel = $el.closest('.input-group').querySelector('select');
                        if (sel.value) { addMethod(sel.value); sel.value = ''; }
                    ">
                <i class="fa fa-plus"></i>
            </button>
        </span>
    </div>
</li>
