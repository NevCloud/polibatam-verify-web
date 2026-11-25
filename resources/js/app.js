import './bootstrap';
import Alpine from 'alpinejs';

/**
 * x-pv.unggah-berkas: unggah dokumen lewat XHR agar ada indikator progres (NFR-16).
 * Validasi format/ukuran tetap di server (BR-12, NFR-07); pesan 422 ditampilkan apa adanya.
 */
Alpine.data('unggah', () => ({
    persen: null,
    galat: '',
    seret: false,

    lepas(event) {
        this.seret = false;
        if (event.dataTransfer?.files?.length) {
            this.$refs.berkas.files = event.dataTransfer.files;
            this.kirim();
        }
    },

    kirim() {
        const form = this.$root;
        if (!this.$refs.berkas.files.length) return;

        this.galat = '';
        this.persen = 0;
        const xhr = new XMLHttpRequest();
        xhr.open('POST', form.action);
        xhr.setRequestHeader('Accept', 'application/json');
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.upload.onprogress = (e) => {
            if (e.lengthComputable) this.persen = Math.round((e.loaded * 100) / e.total);
        };
        xhr.onload = () => {
            if (xhr.status >= 200 && xhr.status < 300) {
                window.location.assign(form.dataset.kembali || window.location.href);
                return;
            }
            this.persen = null;
            this.$refs.berkas.value = '';
            this.galat = this.pesan(xhr);
        };
        xhr.onerror = () => {
            this.persen = null;
            this.galat = 'Koneksi terputus. Periksa jaringan lalu unggah lagi.';
        };
        xhr.send(new FormData(form));
    },

    pesan(xhr) {
        if (xhr.status === 413) return 'Ukuran berkas melebihi batas yang diizinkan.';
        if (xhr.status === 429) return 'Terlalu banyak permintaan. Tunggu 1 menit lalu coba lagi.';
        if (xhr.status === 419) return 'Sesi berakhir. Muat ulang halaman lalu coba lagi.';
        try {
            const json = JSON.parse(xhr.responseText);
            return json.errors?.berkas?.[0] ?? json.message ?? 'Unggah gagal. Coba lagi.';
        } catch {
            return 'Unggah gagal. Coba lagi.';
        }
    },
}));

window.Alpine = Alpine;
Alpine.start();
