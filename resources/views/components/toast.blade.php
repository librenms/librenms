@if($toasts)
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            @foreach ($toasts as $toast)
                toastr["{{ $toast['level'] }}"]({{ Js::from(\LibreNMS\Util\Clean::html($toast['message'], $purifier_config)) }}, "{{ $toast['title'] }}", {{ JS::from($toast['options']) }});
            @endforeach
        });
    </script>
@endif
