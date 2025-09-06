<!DOCTYPE html>
<html>

<head>
    <title>@yield('title', 'Laravel Menu')</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <style>
        /* body {
            padding: 20px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
            color: #333;
        } */
        .sidebar {
            width: 220px;
            float: left;
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 0;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            min-height: 400px;
        }

        .sidebar ul.nav-pills {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .sidebar ul.nav-pills>li>a {
            display: block;
            padding: 12px 20px;
            color: #337ab7;
            font-weight: 600;
            border-bottom: 1px solid #eee;
            text-decoration: none;
            cursor: pointer;
        }

        .sidebar ul.nav-pills>li>a:hover {
            background-color: #337ab7;
            color: white;
        }

        .submenu {
            display: none;
            background: #f9f9f9;
            padding: 0;
            border-top: 1px solid #ddd;
        }

        .submenu a {
            display: block;
            padding: 10px 20px;
            color: #555;
            text-decoration: none;
            border-bottom: 1px solid #eee;
            background-color: #fdfdfd;
        }

        .submenu a:hover {
            background-color: #eee;
            color: #000;
        }

        .submenu a:last-child {
            border-bottom: none;
        }

        .submenu a.active,
        .menu-toggle.active {
            background-color: #337ab7;
            color: white;
        }

        .has-submenu.open>.submenu {
            display: block;
        }
    </style>
</head>

<body>
@extends('device.index')
    @include('device.layout.header')
@section('tab')
    {{-- <div class="container"> --}}
    @yield('content')
    {{-- </div> --}}

    @include('device.layout.footer')
    @endsection

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.menu-toggle').click(function() {
                var $submenu = $(this).next('.submenu');
                $('.submenu').not($submenu).slideUp(); // Close others
                $submenu.slideToggle(); // Toggle current
            });
        });
    </script>

</body>

</html>
