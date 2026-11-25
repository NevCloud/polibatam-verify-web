<?php

namespace App\Http\Controllers\Web\SuperAdmin;

use App\Actions\Akun\KelolaAkunAdmin;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Queries\AkunAdminQuery;
use App\Support\Ui\PortalContext;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/** HW-44 (FR-06, FR-07, US-27): akun & role admin. Aturan bisnis ada di KelolaAkunAdmin. */
class AkunController extends Controller
{
    public function index(Request $request, AkunAdminQuery $query): View
    {
        return view('admin.superadmin.akun.index', [
            'portal' => PortalContext::dariPengguna($request->user(), 'Kelola User'),
            ...$query->halaman($request->only('q', 'role', 'status'), $request->user()),
        ]);
    }

    public function store(Request $request, KelolaAkunAdmin $kelola): RedirectResponse
    {
        $user = $kelola->buat($request->only('name', 'email', 'role'));

        return back()->with('status', "Akun {$user->name} dibuat. Undangan aktivasi dikirim ke {$user->email}.");
    }

    public function ubahRole(Request $request, User $user, KelolaAkunAdmin $kelola): RedirectResponse
    {
        $this->pastikanAdmin($user);
        $kelola->ubahRole($user, (string) $request->input('role'));

        return back()->with('status', "Peran {$user->name} diperbarui.");
    }

    public function ubahStatus(Request $request, User $user, KelolaAkunAdmin $kelola): RedirectResponse
    {
        $this->pastikanAdmin($user);
        $aktif = $request->boolean('aktif');
        $kelola->ubahStatus($user, $aktif, (string) $request->input('alasan'));

        return back()->with('status', $aktif
            ? "Akun {$user->name} diaktifkan kembali."
            : "Akun {$user->name} dinonaktifkan. Sesi dan token mobile dicabut.");
    }

    public function kirimUlangUndangan(User $user, KelolaAkunAdmin $kelola): RedirectResponse
    {
        $this->pastikanAdmin($user);
        $kelola->kirimUlangUndangan($user);

        return back()->with('status', "Undangan aktivasi dikirim ulang ke {$user->email}.");
    }

    /** Halaman ini hanya mengelola akun admin; akun peserta tidak tersedia di sini (404). */
    private function pastikanAdmin(User $user): void
    {
        abort_unless($user->adalahAdmin(), 404);
    }
}
