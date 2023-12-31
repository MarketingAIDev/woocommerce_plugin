<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title')</title>

    @include('layouts._favicon')

    @include('layouts._head')

    @include('layouts._css')

    @include('layouts._js')
</head>

<body class="bg-slate-800 color-scheme-white"
      style="display:flex;align-items:center;justify-content:center;height:100%;">

@yield('content')

</body>
</html>
