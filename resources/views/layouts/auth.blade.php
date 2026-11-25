<!DOCTYPE html>
<html lang="id">
<head>@include('layouts.partials.head')</head>
{{-- Halaman publik/autentikasi & galat (Figma HW-49): Layar/Header publik, isi di tengah, Layar/Footer --}}
<body class="flex min-h-screen flex-col">
    <x-pv.header-publik />
    <main class="flex flex-1 flex-col items-center justify-center px-5 py-16">@yield('konten')</main>
    <x-pv.footer />
</body>
</html>
