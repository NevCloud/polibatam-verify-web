<!DOCTYPE html>
<html lang="id">
<head>@include('layouts.partials.head')</head>
<body class="flex min-h-screen flex-col">
    <x-pv.header-publik :aktif="$navAktif ?? null" />
    <main class="flex-1">@yield('konten')</main>
    <x-pv.footer varian="publik" />
</body>
</html>
