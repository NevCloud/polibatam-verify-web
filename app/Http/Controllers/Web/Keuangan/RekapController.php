<?php

namespace App\Http\Controllers\Web\Keuangan;

use App\Exports\RekapKeuanganExport;
use App\Http\Controllers\Controller;
use App\Queries\RekapKeuanganQuery;
use App\Services\AuditLogger;
use App\Support\Ui\Format;
use App\Support\Ui\PortalContext;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/** HW-32 (FR-38): rekap & ekspor keuangan (Excel detail per tagihan, PDF ringkasan). Admin Keuangan & Super Admin. */
class RekapController extends Controller
{
    public function index(Request $request, RekapKeuanganQuery $rekap): View
    {
        return view('admin.keuangan.rekap.index', $rekap->halaman($request->query()) + [
            'portal' => PortalContext::dariPengguna($request->user(), 'Rekap & Ekspor'),
        ]);
    }

    public function excel(Request $request, RekapKeuanganQuery $rekap, AuditLogger $audit): BinaryFileResponse
    {
        $filter = $rekap->bersihkan($request->query());
        $audit->catat('ekspor_rekap_keuangan', 'tagihan', null, sesudah: ['format' => 'xlsx', 'filter' => $filter]);

        return Excel::download(new RekapKeuanganExport($rekap, $filter), 'rekap-keuangan-'.Format::wib(now())->format('Ymd-His').'.xlsx');
    }

    public function pdf(Request $request, RekapKeuanganQuery $rekap, AuditLogger $audit): Response
    {
        $data = $rekap->halaman($request->query());
        $audit->catat('ekspor_rekap_keuangan', 'tagihan', null, sesudah: ['format' => 'pdf', 'filter' => $data['filter']]);

        return Pdf::loadView('pdf.rekap-keuangan', $data + ['dicetak' => Format::tanggalJam(now())])
            ->download('rekap-keuangan-'.Format::wib(now())->format('Ymd-His').'.pdf');
    }
}
