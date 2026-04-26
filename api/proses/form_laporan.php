<?php
// form_laporan.php — Form CRUD Laporan
require_once __DIR__ . '/../pages/bps_widget.php';

$id   = intval($_POST['id'] ?? 0);
$data = [];
if ($id > 0) {
    $r    = mysqli_query($koneksi, "SELECT * FROM laporan WHERE id=$id");
    $data = mysqli_fetch_assoc($r) ?: [];
}
$isEdit = !empty($data);
$sel = ['prov'=>$data['provinsi']??'','kota'=>$data['kota']??'','kec'=>$data['kecamatan']??''];
?>
<form id="crudForm">
    <input type="hidden" name="id" value="<?= $id ?>">
    <h3 style="margin-bottom:18px;color:#1e4a3b;"><?= $isEdit ? '✏️ Edit Laporan' : '➕ Tambah Laporan' ?></h3>

    <label style="font-size:12px;font-weight:600;color:#374151;display:block;margin-bottom:4px;">Judul Laporan <span style="color:#dc2626;">*</span></label>
    <input type="text" name="judul" value="<?= htmlspecialchars($data['judul']??'') ?>" placeholder="Judul laporan..." required
        style="width:100%;padding:9px 12px;border-radius:12px;border:1px solid #d1d5db;font-size:13px;margin-bottom:12px;outline:none;">

    <!-- Wilayah BPS -->
    <?= bps_wilayah_fields('lap', $sel) ?>

    <label style="font-size:12px;font-weight:600;color:#374151;display:block;margin-bottom:4px;">Deskripsi / Isi Laporan <span style="color:#dc2626;">*</span></label>
    <textarea name="deskripsi" placeholder="Isi laporan..." required
        style="width:100%;padding:9px 12px;border-radius:12px;border:1px solid #d1d5db;font-size:13px;outline:none;resize:vertical;min-height:120px;margin-bottom:16px;"><?= htmlspecialchars($data['deskripsi']??'') ?></textarea>

    <div class="modal-buttons">
        <button type="button" onclick="document.getElementById('crudModal').style.display='none'"
            style="padding:10px 22px;border-radius:24px;border:1px solid #d1d5db;background:white;cursor:pointer;font-size:13px;">Batal</button>
        <button type="button" id="saveCrudBtn"
            style="padding:10px 22px;border-radius:24px;border:none;background:#1e4a3b;color:white;cursor:pointer;font-size:13px;font-weight:700;">
            <i class="fas fa-save"></i> <?= $isEdit ? 'Simpan' : 'Tambah Laporan' ?>
        </button>
    </div>
</form>
