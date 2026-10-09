@extends('bill.layout')

@section('bill-content')
    <div class="row">
        <div class="col-lg-6 col-md-12">
            <x-panel :title="__('Bill Properties')">
                <form method="post" action="{{ route('bill.update', $bill) }}" class="form-horizontal">
                    @csrf
                    @method('PUT')
                    @include('bill.form')
                    <div class="form-group">
                        <div class="col-sm-offset-4 col-sm-8">
                            <button type="submit" class="btn btn-primary"><i class="fa fa-check"></i> {{ __('Save Properties') }}</button>
                        </div>
                    </div>
                </form>
            </x-panel>
        </div>
        <div class="col-lg-6 col-md-12">
            @include('bill.sources', ['removable' => true])

            <x-panel :title="__('Add Source')">
                <form action="{{ route('bill.source.attach', $bill) }}" method="post" class="form-horizontal">
                    @csrf
                    @include('bill.source-picker', ['labelCols' => 2])
                    <div class="form-group">
                        <div class="col-sm-offset-2 col-sm-10">
                            <button type="submit" class="btn btn-primary"><i class="fa fa-plus"></i> {{ __('Add Source') }}</button>
                        </div>
                    </div>
                </form>
            </x-panel>
        </div>
    </div>
@endsection
