@extends('layouts.librenmsv1')

@section('title', __('SSL Certificate') . ' – ' . $certificate->host)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <x-panel title="{{ __('SSL Certificate') }}: {{ $certificate->host }}:{{ $certificate->port }}" id="ssl-certificate-detail">
                <div class="row">
                    <div class="col-md-12">
                        <table class="table table-condensed">
                            <tr>
                                <th>{{ __('Host') }}</th>
                                <td>{{ $certificate->host }}</td>
                            </tr>
                            <tr>
                                <th>{{ __('Port') }}</th>
                                <td>{{ $certificate->port }}</td>
                            </tr>
                            <tr>
                                <th>{{ __('Subject') }}</th>
                                <td>{{ $certificate->subject ?? __('N/A') }}</td>
                            </tr>
                            <tr>
                                <th>{{ __('Issuer') }}</th>
                                <td>{{ $certificate->issuer ?? __('N/A') }}</td>
                            </tr>
                            @if ($certificate->issuer_organization)
                            <tr>
                                <th>{{ __('Issuer Organization') }}</th>
                                <td>{{ $certificate->issuer_organization }}</td>
                            </tr>
                            @endif
                            @if ($certificate->issuer_country)
                            <tr>
                                <th>{{ __('Issuer Country') }}</th>
                                <td>{{ $certificate->issuer_country }}</td>
                            </tr>
                            @endif
                            @if ($certificate->serial_number)
                            <tr>
                                <th>{{ __('Serial Number') }}</th>
                                <td><code>{{ $certificate->serial_number }}</code></td>
                            </tr>
                            @endif
                            @if ($certificate->serial_number_hex)
                            <tr>
                                <th>{{ __('Serial Number (Hex)') }}</th>
                                <td><code>{{ $certificate->serial_number_hex }}</code></td>
                            </tr>
                            @endif
                            <tr>
                                <th>{{ __('Self Signed') }}</th>
                                <td>{{ $certificate->self_signed ? __('Yes') : __('No') }}</td>
                            </tr>
                            @if ($certificate->signature_algorithm)
                            <tr>
                                <th>{{ __('Signature Algorithm') }}</th>
                                <td>{{ $certificate->signature_algorithm }}</td>
                            </tr>
                            @endif
                            @if ($certificate->certificate_version)
                            <tr>
                                <th>{{ __('Certificate Version') }}</th>
                                <td>X.509 v{{ $certificate->certificate_version }}</td>
                            </tr>
                            @endif
                            @if ($certificate->key_usage)
                            <tr>
                                <th>{{ __('Key Usage') }}</th>
                                <td>{{ $certificate->key_usage }}</td>
                            </tr>
                            @endif
                            @if ($certificate->extended_key_usage)
                            <tr>
                                <th>{{ __('Extended Key Usage') }}</th>
                                <td>{{ $certificate->extended_key_usage }}</td>
                            </tr>
                            @endif
                            @if ($certificate->basic_constraints)
                            <tr>
                                <th>{{ __('Basic Constraints') }}</th>
                                <td>{{ $certificate->basic_constraints }}</td>
                            </tr>
                            @endif
                            @if ($certificate->subject_key_identifier)
                            <tr>
                                <th>{{ __('Subject Key Identifier') }}</th>
                                <td><code>{{ $certificate->subject_key_identifier }}</code></td>
                            </tr>
                            @endif
                            @if ($certificate->authority_key_identifier)
                            <tr>
                                <th>{{ __('Authority Key Identifier') }}</th>
                                <td><code>{{ $certificate->authority_key_identifier }}</code></td>
                            </tr>
                            @endif
                            <tr>
                                <th>{{ __('Valid From') }}</th>
                                <td>{{ $certificate->valid_from ? $certificate->valid_from->format('Y-m-d H:i:s') : __('N/A') }}</td>
                            </tr>
                            <tr>
                                <th>{{ __('Valid Until') }}</th>
                                <td>{{ $certificate->valid_to ? $certificate->valid_to->format('Y-m-d H:i:s') : __('N/A') }}</td>
                            </tr>
                            @if ($certificate->days_until_expiry !== null)
                            <tr>
                                <th>{{ __('Days Until Expiry') }}</th>
                                <td>
                                    @if ($certificate->days_until_expiry < 0)
                                        <span class="text-danger">{{ $certificate->days_until_expiry }} {{ __('days') }}</span>
                                    @elseif ($certificate->days_until_expiry <= 30)
                                        <span class="text-warning">{{ $certificate->days_until_expiry }} {{ __('days') }}</span>
                                    @else
                                        {{ $certificate->days_until_expiry }} {{ __('days') }}
                                    @endif
                                </td>
                            </tr>
                            @endif
                            <tr>
                                <th>{{ __('Last Checked') }}</th>
                                <td>{{ $certificate->last_checked_at ? $certificate->last_checked_at->format('Y-m-d H:i:s') : __('N/A') }}</td>
                            </tr>
                            <tr>
                                <th>{{ __('Fingerprint') }}</th>
                                <td><code>{{ $certificate->fingerprint ?? __('N/A') }}</code></td>
                            </tr>
                            <tr>
                                <th>{{ __('Device') }}</th>
                                <td>
                                    @if ($certificate->device)
                                        <a href="{{ url('device/' . $certificate->device_id) }}">{{ $certificate->device->hostname }}</a>
                                    @else
                                        {{ __('N/A') }}
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>{{ __('Status') }}</th>
                                <td>
                                    @if ($certificate->disabled)
                                        <span class="label label-default">{{ __('Paused') }}</span>
                                    @elseif ($certificate->isExpired())
                                        <span class="label label-danger">{{ __('Expired') }}</span>
                                    @elseif ($certificate->expiresWithinDays(30))
                                        <span class="label label-warning">{{ __('Expires soon') }}</span>
                                    @else
                                        <span class="label label-success">{{ __('Valid') }}</span>
                                    @endif
                                </td>
                            </tr>
                            @if ($certificate->subject_alternative_names && count($certificate->subject_alternative_names) > 0)
                            <tr>
                                <th>{{ __('Subject Alternative Names') }}</th>
                                <td>{{ implode(', ', $certificate->subject_alternative_names) }}</td>
                            </tr>
                            @endif
                        </table>
                    </div>
                </div>

                <div class="form-group">
                    @can('update', $certificate)
                    @if ($certificate->disabled)
                        <form action="{{ route('ssl-certificates.update', $certificate) }}" method="POST" style="display:inline">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="disabled" value="0">
                            <button type="submit" class="btn btn-success">{{ __('Enable') }}</button>
                        </form>
                    @else
                        <form action="{{ route('ssl-certificates.update', $certificate) }}" method="POST" style="display:inline">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="disabled" value="1">
                            <button type="submit" class="btn btn-warning">{{ __('Pause') }}</button>
                        </form>
                    @endif
                    @endcan
                    @can('delete', $certificate)
                    <form action="{{ route('ssl-certificates.destroy', $certificate) }}" method="POST" style="display:inline" onsubmit="return confirm('{{ __('Delete this certificate?') }}');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">{{ __('Delete') }}</button>
                    </form>
                    @endcan
                    <a href="{{ route('ssl-certificates.index') }}" class="btn btn-default">{{ __('Back to list') }}</a>
                </div>
            </x-panel>
        </div>
    </div>
</div>
@endsection
