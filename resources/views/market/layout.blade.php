<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title','Beranda') · TemuLapak</title>
    <link rel="stylesheet" href="/market.css">
    <link rel="stylesheet" href="/prototype.css">
    <link rel="stylesheet" href="/toasts.css">
    <script defer src="/money-input.js"></script>
    @stack('styles')
</head>
<body>
@include('market.header')
@include('market.toasts')
<main>@yield('content')</main>
@include('market.footer')
</body>
</html>
