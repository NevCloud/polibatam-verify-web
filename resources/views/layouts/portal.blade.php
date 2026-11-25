<!DOCTYPE html>
<html lang="id">
<head>@include('layouts.partials.head')</head>
<body class="flex min-h-screen flex-col">
    {{-- $portal: App\Support\Ui\PortalContext (fixture di preview, Auth::user() di mode nyata) --}}
    <x-pv.header-portal :portal="$portal" />
    <div class="flex flex-1">
        <x-pv.sidebar :portal="$portal" />
        <main class="min-w-0 flex-1 p-6 lg:p-8">@yield('konten')</main>
    </div>
    <x-pv.footer />
</body>
</html>
