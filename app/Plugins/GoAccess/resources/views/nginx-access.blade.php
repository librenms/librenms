@extends('page')

@section('nginx-access')

<dev>
@verbatim
    <?php
         include app_path() . '/Plugins/GoAccess/nginx-access.html' ;
    ?>

@endverbatim

</div>
@endsection

