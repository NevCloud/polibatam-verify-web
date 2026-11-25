<?php

namespace App\Http\Controllers\Web\Peserta;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\Ui\Format;
use App\Support\Ui\PortalContext;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;

/**
 * HW-20 (FR-53): notifikasi in-app milik sendiri, tandai dibaca. Notifikasi ditulis Notifier (P01);
 * jenis notifikasi lain (pengingat, tagihan) ditambah paketnya masing-masing (P05, P12).
 */
class NotifikasiController extends Controller
{
    public function index(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();
        $halaman = $user->notifications()->paginate(20);

        return view('peserta.notifikasi', [
            'portal' => PortalContext::dariPengguna($user, 'Notifikasi'),
            'belumDibaca' => $user->unreadNotifications()->count(),
            'notifikasi' => array_map(self::baris(...), $halaman->items()),
            'sebelumnya' => $halaman->previousPageUrl(),
            'berikutnya' => $halaman->nextPageUrl(),
        ]);
    }

    public function bacaSemua(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        $user->unreadNotifications()->update(['read_at' => now()]);

        return back()->with('status', 'Semua notifikasi ditandai dibaca.');
    }

    /** Buka notifikasi: tandai dibaca lalu menuju halamannya (hanya URL aplikasi ini, cegah open redirect). */
    public function buka(Request $request, string $id): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        $n = $user->notifications()->whereKey($id)->firstOrFail();
        $n->markAsRead();
        $url = (string) ($n->data['url'] ?? '');

        return str_starts_with($url, rtrim((string) config('app.url'), '/').'/') ? redirect()->to($url) : to_route('notifikasi');
    }

    /** @return list<array{judul: string, isi: string, waktu: string, dibaca: bool, href: string}> */
    public static function terbaru(User $user, int $jumlah): array
    {
        return array_map(self::baris(...), $user->notifications()->limit($jumlah)->get()->all());
    }

    /** @return array{judul: string, isi: string, waktu: string, dibaca: bool, href: string} */
    private static function baris(DatabaseNotification $n): array
    {
        return [
            'judul' => (string) ($n->data['judul'] ?? ''),
            'isi' => (string) ($n->data['isi'] ?? ''),
            'waktu' => Format::tanggalJam($n->created_at ?? now()),
            'dibaca' => $n->read(),
            'href' => route('notifikasi.buka', $n->id),
        ];
    }
}
