@extends('layouts.librenmsv1')

@section('title', __('Bill') . ': ' . $bill->bill_name)

@section('content')
<div class="container-fluid" x-data="{ resetBill: false, deleteBill: false }">
    <div class="tw:flex tw:flex-wrap tw:items-center tw:justify-between tw:gap-2">
        <h2>{{ __('Bill') }}: {{ $bill->bill_name }}</h2>
        <div class="tw:flex tw:items-center tw:gap-2">
            <a href="{{ route('bills.index') }}" class="btn btn-default btn-sm"><i class="fa fa-arrow-left"></i> {{ __('Back to Bills') }}</a>
            @can('update', $bill)
                <button type="button" class="btn btn-warning btn-sm" x-on:click="resetBill = true"><i class="fa fa-refresh"></i> {{ __('Reset') }}</button>
            @endcan
            @can('delete', $bill)
                <button type="button" class="btn btn-danger btn-sm" x-on:click="deleteBill = true"><i class="fa fa-trash"></i> {{ __('Delete') }}</button>
            @endcan
        </div>
    </div>

    <x-option-bar :name="__('Bill')" :selected="$view" :options="array_filter([
        'quick' => ['text' => __('Quick Graphs'), 'link' => route('bill.show', $bill)],
        'accurate' => ['text' => __('Accurate Graphs'), 'link' => route('bill.accurate', $bill)],
        'transfer' => ['text' => __('Transfer Graphs'), 'link' => route('bill.transfer', $bill)],
        'history' => ['text' => __('Historical Graphs'), 'link' => route('bill.history', $bill)],
        'edit' => auth()->user()->can('update', $bill) ? ['text' => __('Edit'), 'link' => route('bill.edit', $bill)] : null,
    ])" />

    @yield('bill-content')

    @can('update', $bill)
        <x-modal show="resetBill" :title="__('Reset Bill')">
            <form action="{{ route('bill.reset', $bill) }}" method="post" x-data="{ confirmed: false }" class="tw:space-y-4">
                @csrf
                <div class="alert alert-danger tw:mb-0">
                    <i class="fa fa-exclamation-triangle"></i>
                    {{ __('This will remove all collected data and history for this bill. This cannot be undone.') }}
                </div>
                <div class="checkbox">
                    <label><input type="checkbox" name="confirm" value="1" x-model="confirmed"> {{ __('Yes, please reset all data for this bill') }}</label>
                </div>
                <div class="tw:flex tw:justify-end tw:gap-2">
                    <button type="button" class="btn btn-default" x-on:click="resetBill = false">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-danger" x-bind:disabled="!confirmed"><i class="fa fa-refresh"></i> {{ __('Reset Bill') }}</button>
                </div>
            </form>
        </x-modal>
    @endcan

    @can('delete', $bill)
        <x-modal show="deleteBill" :title="__('Delete Bill')">
            <form action="{{ route('bill.destroy', $bill) }}" method="post" x-data="{ confirmed: false }" class="tw:space-y-4">
                @csrf
                @method('DELETE')
                <div class="alert alert-danger tw:mb-0">
                    <i class="fa fa-exclamation-triangle"></i>
                    {{ __('You are about to delete this bill and all of its data. This cannot be undone.') }}
                </div>
                <div class="checkbox">
                    <label><input type="checkbox" x-model="confirmed"> {{ __('Yes, please delete this bill') }}</label>
                </div>
                <div class="tw:flex tw:justify-end tw:gap-2">
                    <button type="button" class="btn btn-default" x-on:click="deleteBill = false">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-danger" x-bind:disabled="!confirmed"><i class="fa fa-trash"></i> {{ __('Delete Bill') }}</button>
                </div>
            </form>
        </x-modal>
    @endcan
</div>
@endsection
