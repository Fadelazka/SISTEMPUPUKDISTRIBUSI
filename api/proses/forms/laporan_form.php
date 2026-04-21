<?php
// forms/laporan_form.php
require_once __DIR__ . '/../pages/bps_widget.php';

$id   = intval($_POST['id'] ?? 0);
$data = ['judul' => '', 'deskripsi' => '', 'provinsi' => '', 'kota' => '', 'kecamatan' => ''];
if ($id > 0) {
    $q = mysqli_query($koneksi, "SELECT * FROM laporan WHERE id = $id");
    if ($q && mysqli_num_rows($q)) {
        $data = array_merge($data, mysqli_fetch_assoc($q));
    }
}
$sel    = ['prov' => $data['provinsi'], 'kota' => $data['kota'], 'kec' => $data['kecamatan']];
$isEdit = ($id > 0);
?>

<style>
#crudForm .fl {
    font-size: 13px; font-weight: 600; color: #374151;
    display: block; margin-bottom: 5px;
}
#crudForm .fi {
    width: 100%; padding: 10px 14px; border-radius: 12px;
    border: 1.5px solid #e2e8f0; font-size: 14px; outline: none;
    margin-bottom: 12px; box-sizing: border-box;
    transition: border-color 0.2s;
}
#crudForm .fi:focus { border-color: #2d6a4f; }
</style>

<form id="crudForm">
    <input type="hidden" name="id" value="<?= $id ?>">

    <!-- Header form -->
    <div style="display:flex;align-items:center;gap:12px;margin-bottom:20px;
                padding-bottom:16px;border-bottom:1px solid #e5e7eb;">
        <div style="width:48px;height:48px;border-radius:12px;background:#f0f9ff;
                    display:flex;align-items:center;justify-content:center;font-size:22px;flex-shrink:0;">
            <?= $isEdit ? '✏️' : '📄' ?>
        </div>
        <div>
            <div style="font-size:18px;font-weight:800;color:#1e4a3b;">
                <?= $isEdit ? 'Edit Laporan' : 'Buat Laporan Baru' ?>
            </div>
            <div style="font-size:13px;color:#64748b;margin-top:2px;">
                <?= $isEdit ? "ID: $id — Ubah konten laporan" : 'Isi judul, wilayah, dan isi laporan' ?>
            </div>
        </div>
    </div>

    <!-- Judul -->
    <label class="fl">Judul Laporan <span style="color:#dc2626;">*</span></label>
    <input class="fi" type="text" name="judul"
           value="<?= htmlspecialchars($data['judul']) ?>"
           required placeholder="Masukkan judul laporan yang deskriptif...">

    <!-- Wilayah BPS dropdown (provinsi → kota → kecamatan via BPS API) -->
    <?= bps_wilayah_fields('lap', $sel) ?>

    <!-- Deskripsi -->
    <label class="fl">Deskripsi / Isi Laporan <span style="color:#dc2626;">*</span></label>
    <textarea class="fi" name="deskripsi" rows="6" required
              style="resize:vertical;min-height:120px;"
              placeholder="Tulis isi laporan secara lengkap..."><?= htmlspecialchars($data['deskripsi']) ?></textarea>

    <!-- Tombol Aksi -->
    <div style="display:flex;justify-content:space-between;align-items:center;
                margin-top:20px;padding-top:16px;border-top:1px solid #e5e7eb;
                gap:12px;flex-wrap:wrap;">
        <button type="button"
                onclick="$('#crudModal').hide()"
                style="padding:11px 24px;border-radius:24px;border:1.5px solid #d1d5db;
                       background:white;cursor:pointer;font-size:14px;color:#374151;font-weight:600;">
            <i class="fas fa-times" style="margin-right:6px;"></i> Batal
        </button>
        <button type="button" id="saveCrudBtn"
                style="padding:12px 32px;border-radius:24px;border:none;
                       background:#1e4a3b;color:white;cursor:pointer;
                       font-size:15px;font-weight:800;
                       display:flex;align-items:center;gap:8px;
                       box-shadow:0 4px 16px rgba(30,74,59,0.35);">
            <i class="fas fa-save"></i>
            <?= $isEdit ? 'Simpan Perubahan' : 'Buat Laporan' ?>
        </button>
    </div>
</form>
