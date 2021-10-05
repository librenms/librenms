@extends('layouts.librenmsv1')
@section('title', __('Validate'))
@section('content')

<div class="container-fluid">
    <div class="row">
        <div class="col-xs-12">

@foreach ($validator->getAllResults() as $group => $results)
    <div class="panel-group" style="margin-bottom: 5px">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h4 class='panel-title'>
                    <a data-toggle='collapse' data-target='#{{ $group }}Body'>
                        {{ ucfirst($group) }}

                        @php
                            $group_status = $validator->getGroupStatus($group)
                        @endphp

                        @if($group_status == \LibreNMS\ValidationResult::SUCCESS)
                             <span class="text-success pull-right">Ok</span>
                        @elseif ($group_status == \LibreNMS\ValidationResult::WARNING)
                             <span class="text-warning pull-right">Warning</span>
                        @elseif ($group_status == \LibreNMS\ValidationResult::FAILURE)
                             <span class="text-danger pull-right">Failure</span>
                        @endif
                    </a>
                </h4>
            </div>
    <div id='{{ $group }}Body' class='panel-collapse collapse
    @if ($group_status !== \LibreNMS\ValidationResult::SUCCESS)
         in
    @endif
    '>
    <div class='panel-body'>

    @foreach ($results as $result)

        <div class="panel
        @if ($result->getStatus() == \LibreNMS\ValidationResult::SUCCESS)
             panel-success"><div class="panel-heading bg-success"> Ok:
        @elseif ($result->getStatus() == \LibreNMS\ValidationResult::WARNING)
             panel-warning"><div class="panel-heading bg-warning"> Warning:
        @elseif ($result->getStatus() == \LibreNMS\ValidationResult::FAILURE)
             panel-danger"><div class="panel-heading bg-danger"> Fail:
        @endif

        {{ $result->getMessage() }}
        </div>

        @if ($result->hasFix() || $result->hasList())
            <div class="panel-body" x-data="{ open: false }">
            @if ($result->hasAutoFix())
                <form action="{{ route('validate.fix', $group) }}" method="post" x-data="{ buttonDisabled: false }">
                    @csrf
                    <button type="submit" class="btn btn-primary" @click="buttonDisabled = true" x-bind:disabled="buttonDisabled" x-text="buttonDisabled ? 'Fixing...': 'Auto fix'">Auto fix</button>
                </form>
            @endif

            @if ($result->hasFix())
                Fix: <code>
                @foreach ((array) $result->getFix() as $fix)
                    <br />@linkify($fix)
                @endforeach
                </code>
                @if ($result->hasList())
                    <br /><br />
                @endif
            @endif

            @if ($result->hasList())
                <ul class='list-group' style='margin-bottom: -1px'>
                <li class='list-group-item active'>{{ $result->getListDescription() }}</li>

                @foreach (array_slice($result->getList(), 0, $short_size) as $li)
                    <li class='list-group-item'>{{ $li }}</li>
                @endforeach
                </ul>

                @if (count($result->getList()) > $short_size)
                    <button style='margin-top: 3px' type='button' class='btn btn-default' @click="open = true" x-show="!open">Show all</button>

                    <ul class='list-group' x-show="open" x-cloak>

                    @foreach (array_slice($result->getList(), $short_size) as $li)
                        <li class='list-group-item'>{{ $li }}</li>
                    @endforeach
                    </ul>
                @endif
            @endif
            </div>
        @endif
        </div>
    @endforeach
    </div></div></div></div>
@endforeach

        </div>
    </div>
</div>
@endsection
