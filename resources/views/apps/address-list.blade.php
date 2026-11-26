@foreach($addresses as $address)
    @if(! $loop->first), @endif{{ $address }}
@endforeach
