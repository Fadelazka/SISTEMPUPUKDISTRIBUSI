<?php
// form_distribusi.php — Form CRUD Distribusi dengan BPS Wilayah
require_once __DIR__ . '/../pages/bps_widget.php';

$id   = intval($_POST['id'] ?? 0);
$data = [];
if ($id > 0) {
    $r    = mysqli_query($koneksi, "SELECT * FROM distribusi WHERE id=$id");
    $data = mysqli_fetch_assoc($r) ?: [];
}
$isEdit = !empty($data);

$sel = [
    'prov' => $data['provinsi']  ?? '',
    'kota' => $data['kota']      ?? '',
    'kec'  => $data['kecamatan'] ?? '',
];
?>
<form id="crudForm">
    <input type="hidden" name="id" value="<?= $id ?>">
    <h3 style="margin-bottom:18px;color:#1e4a3b;"><?= $isEdit ? '✏️ Edit Distribusi' : '➕ Tambah Distribusi' ?></h3>

    <!-- Identitas Distribusi -->
    <div style="background:#f9fafb;border-radius:14px;padding:14px 16px;margin-bottom:14px;border:1px solid #e5e7eb;">
        <div style="font-size:12px;font-weight:700;color:#374151;margin-bottom:12px;">
            <i class="fas fa-truck" style="margin-right:6px;color:#2d6a4f;"></i> Identitas Distribusi
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:10px;">
            <div>
                <label style="font-size:12px;font-weight:600;color:#374151;display:block;margin-bottom:4px;">Tanggal <span style="color:#dc2626;">*</span></label>
                <input type="date" name="tgl" value="<?= htmlspecialchars($data['tgl']??date('Y-m-d')) ?>" required
                    style="width:100%;padding:9px 12px;border-radius:12px;border:1px solid #d1d5db;font-size:13px;outline:none;">
            </div>
            <div>
                <label style="font-size:12px;font-weight:600;color:#374151;display:block;margin-bottom:4px;">No. Delivery Order (DO)</label>
                <input type="text" name="no_do" value="<?= htmlspecialchars($data['no_do']??'') ?>" placeholder="DO-XXXX-XXXX"
                    style="width:100%;padding:9px 12px;border-radius:12px;border:1px solid #d1d5db;font-size:13px;outline:none;">
            </div>
        </div>

        <label style="font-size:12px;font-weight:600;color:#374151;display:block;margin-bottom:4px;">Kelompok Tani <span style="color:#dc2626;">*</span></label>
        <input type="text" name="kelompok" value="<?= htmlspecialchars($data['kelompok']??'') ?>" placeholder="Nama kelompok tani..." required
            style="width:100%;padding:9px 12px;border-radius:12px;border:1px solid #d1d5db;font-size:13px;margin-bottom:10px;outline:none;">

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
            <div>
                <label style="font-size:12px;font-weight:600;color:#374151;display:block;margin-bottom:4px;">Jenis Pupuk <span style="color:#dc2626;">*</span></label>
                <select name="pupuk" required style="width:100%;padding:9px 12px;border-radius:12px;border:1px solid #d1d5db;font-size:13px;outline:none;">
                    <?php $pp=$data['pupuk']??'';
                    foreach(['Urea','NPK','ZA','Phonska','SP-36','Organik'] as $jenis):?>
                    <option value="<?=$jenis?>" <?=$pp==$jenis?'selected':''?>><?=$jenis?></option>
                    <?php endforeach;?>
                </select>
            </div>
            <div>
                <label style="font-size:12px;font-weight:600;color:#374151;display:block;margin-bottom:4px;">Jumlah (kg) <span style="color:#dc2626;">*</span></label>
                <input type="text" name="jumlah" value="<?= htmlspecialchars($data['jumlah']??'') ?>" placeholder="misal: 500 kg" required
                    style="width:100%;padding:9px 12px;border-radius:12px;border:1px solid #d1d5db;font-size:13px;outline:none;">
            </div>
        </div>
    </div>

    <!-- Wilayah BPS -->
    <?= bps_wilayah_fields('dist', $sel) ?>

    <!-- Tujuan -->
    <div style="background:#f9fafb;border-radius:14px;padding:14px 16px;margin-bottom:18px;border:1px solid #e5e7eb;">
        <div style="font-size:12px;font-weight:700;color:#374151;margin-bottom:10px;">
            <i class="fas fa-map-pin" style="margin-right:6px;color:#2d6a4f;"></i> Tujuan Distribusi
        </div>
        <label style="font-size:12px;font-weight:600;color:#374151;display:block;margin-bottom:4px;">Kios / Lokasi Tujuan <span style="color:#dc2626;">*</span></label>
        <input type="text" name="tujuan" value="<?= htmlspecialchars($data['tujuan']??'') ?>" placeholder="Nama kios atau lokasi..." required
            style="width:100%;padding:9px 12px;border-radius:12px;border:1px solid #d1d5db;font-size:13px;margin-bottom:10px;outline:none;">

        <label style="font-size:12px;font-weight:600;color:#374151;display:block;margin-bottom:4px;">Catatan</label>
        <textarea name="catatan" placeholder="Catatan tambahan (opsional)..."
            style="width:100%;padding:9px 12px;border-radius:12px;border:1px solid #d1d5db;font-size:13px;outline:none;resize:vertical;min-height:60px;"><?= htmlspecialchars($data['catatan']??'') ?></textarea>
    </div>

    <div class="modal-buttons">
        <button type="button" onclick="document.getElementById('crudModal').style.display='none'"
            style="padding:10px 22px;border-radius:24px;border:1px solid #d1d5db;background:white;cursor:pointer;font-size:13px;">
            Batal
        </button>
        <button type="button" id="saveCrudBtn"
            style="padding:10px 22px;border-radius:24px;border:none;background:#1e4a3b;color:white;cursor:pointer;font-size:13px;font-weight:700;">
            <i class="fas fa-save"></i> <?= $isEdit ? 'Simpan Perubahan' : 'Tambah Distribusi' ?>
        </button>
    </div>
</form>
