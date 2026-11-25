{{-- Status resmi → warna lewat App\Support\Ui\StatusBadge (satu-satunya pemetaan, PRD §3.2). --}}
@props(['status'])
<x-pv.badge :nada="\App\Support\Ui\StatusBadge::nada($status)" {{ $attributes }}>{{ $status }}</x-pv.badge>
