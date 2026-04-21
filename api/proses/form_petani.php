<?php
// form_petani.php — Form CRUD Petani dengan BPS Wilayah
// Dipanggil dari ajax_handler.php saat action=getForm&type=petani
require_once __DIR__ . '/../pages/bps_widget.php';

$id   = intval($_POST['id'] ?? 0);
$data = [];
if ($id > 0) {
    $r    = mysqli_query($koneksi, "SELECT * FROM petani WHERE id=$id");
    $data = mysqli_fetch_assoc($r) ?: [];
}
$isEdit = !empty($data);

$sel = [
    'prov'    => $data['provinsi']  ?? '',
    'kota'    => $data['kota']      ?? '',
    'kec'     => $data['kecamatan'] ?? '',
];
?>
<form id="crudForm">
    <input type="hidden" name="id" value="<?= $id ?>">

    <h3 style="margin-bottom:18px;color:#1e4a3b;"><?= $isEdit ? '✏️ Edit Petani' : '➕ Tambah Petani' ?></h3>

    <!-- Data Pribadi -->
    <div style="background:#f9fafb;border-radius:14px;padding:14px 16px;margin-bottom:14px;border:1px solid #e5e7eb;">
        <div style="font-size:12px;font-weight:700;color:#374151;margin-bottom:12px;">
            <i class="fas fa-user" style="margin-right:6px;color:#2d6a4f;"></i> Data Pribadi
        </div>

        <label style="font-size:12px;font-weight:600;color:#374151;display:block;margin-bottom:4px;">Nama Lengkap <span style="color:#dc2626;">*</span></label>
        <input type="text" name="nama" value="<?= htmlspecialchars($data['nama']??'') ?>"
            placeholder="Nama petani..." required
            style="width:100%;padding:9px 12px;border-radius:12px;border:1px solid #d1d5db;font-size:13px;margin-bottom:10px;outline:none;">

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
            <div>
                <label style="font-size:12px;font-weight:600;color:#374151;display:block;margin-bottom:4px;">NIK</label>
                <input type="text" name="nik" value="<?= htmlspecialchars($data['nik']??'') ?>"
                    placeholder="16 digit NIK..."
                    style="width:100%;padding:9px 12px;border-radius:12px;border:1px solid #d1d5db;font-size:13px;outline:none;">
            </div>
            <div>
                <label style="font-size:12px;font-weight:600;color:#374151;display:block;margin-bottom:4px;">No. HP</label>
                <input type="text" name="no_hp" value="<?= htmlspecialchars($data['no_hp']??'') ?>"
                    placeholder="08xxxxxxxxxx..."
                    style="width:100%;padding:9px 12px;border-radius:12px;border:1px solid #d1d5db;font-size:13px;outline:none;">
            </div>
        </div>
    </div>

    <!-- Wilayah BPS -->
    <?= bps_wilayah_fields('petani', $sel) ?>

    <!-- Desa / Kelurahan -->
    <div style="background:#f9fafb;border-radius:14px;padding:14px 16px;margin-bottom:14px;border:1px solid #e5e7eb;">
        <div style="font-size:12px;font-weight:700;color:#374151;margin-bottom:12px;">
            <i class="fas fa-home" style="margin-right:6px;color:#2d6a4f;"></i> Alamat Lengkap
        </div>

        <label style="font-size:12px;font-weight:600;color:#374151;display:block;margin-bottom:4px;">Desa / Kelurahan <span style="color:#dc2626;">*</span></label>
        <input type="text" name="desa" value="<?= htmlspecialchars($data['desa']??'') ?>"
            placeholder="Nama desa/kelurahan..." required
            style="width:100%;padding:9px 12px;border-radius:12px;border:1px solid #d1d5db;font-size:13px;margin-bottom:10px;outline:none;">

        <label style="font-size:12px;font-weight:600;color:#374151;display:block;margin-bottom:4px;">Alamat Detail</label>
        <input type="text" name="alamat" value="<?= htmlspecialchars($data['alamat']??'') ?>"
            placeholder="Jalan, RT/RW, nomor rumah..."
            style="width:100%;padding:9px 12px;border-radius:12px;border:1px solid #d1d5db;font-size:13px;outline:none;">
    </div>

    <!-- Data Lahan & Subsidi -->
    <div style="background:#f9fafb;border-radius:14px;padding:14px 16px;margin-bottom:14px;border:1px solid #e5e7eb;">
        <div style="font-size:12px;font-weight:700;color:#374151;margin-bottom:12px;">
            <i class="fas fa-seedling" style="margin-right:6px;color:#2d6a4f;"></i> Data Lahan &amp; Alokasi Pupuk
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:10px;">
            <div>
                <label style="font-size:12px;font-weight:600;color:#374151;display:block;margin-bottom:4px;">Luas Lahan (Ha) <span style="color:#dc2626;">*</span></label>
                <input type="number" step="0.01" name="luas_lahan" value="<?= htmlspecialchars($data['luas_lahan']??'') ?>"
                    placeholder="0.00" required
                    style="width:100%;padding:9px 12px;border-radius:12px;border:1px solid #d1d5db;font-size:13px;outline:none;">
            </div>
            <div>
                <label style="font-size:12px;font-weight:600;color:#374151;display:block;margin-bottom:4px;">Alokasi Pupuk (kg)</label>
                <input type="text" name="alokasi" value="<?= htmlspecialchars($data['alokasi']??'') ?>"
                    placeholder="misal: 200 kg"
                    style="width:100%;padding:9px 12px;border-radius:12px;border:1px solid #d1d5db;font-size:13px;outline:none;">
            </div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
            <div>
                <label style="font-size:12px;font-weight:600;color:#374151;display:block;margin-bottom:4px;">Jenis Komoditas</label>
                <input type="text" name="komoditas" value="<?= htmlspecialchars($data['komoditas']??'') ?>"
                    placeholder="Padi, Jagung, dll..."
                    style="width:100%;padding:9px 12px;border-radius:12px;border:1px solid #d1d5db;font-size:13px;outline:none;">
            </div>
            <div>
                <label style="font-size:12px;font-weight:600;color:#374151;display:block;margin-bottom:4px;">Kelompok Tani</label>
                <input type="text" name="kelompok_tani" value="<?= htmlspecialchars($data['kelompok_tani']??'') ?>"
                    placeholder="Nama kelompok tani..."
                    style="width:100%;padding:9px 12px;border-radius:12px;border:1px solid #d1d5db;font-size:13px;outline:none;">
            </div>
        </div>
    </div>

    <!-- Status -->
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:18px;">
        <div>
            <label style="font-size:12px;font-weight:600;color:#374151;display:block;margin-bottom:4px;">Status Penerimaan</label>
            <select name="status" style="width:100%;padding:9px 12px;border-radius:12px;border:1px solid #d1d5db;font-size:13px;outline:none;">
                <option value="Belum" <?= ($data['status']??'')=='Belum'?'selected':'' ?>>Belum Terima</option>
                <option value="Sudah" <?= ($data['status']??'')=='Sudah'?'selected':'' ?>>Sudah Terima</option>
                <option value="Proses" <?= ($data['status']??'')=='Proses'?'selected':'' ?>>Dalam Proses</option>
            </select>
        </div>
        <div>
            <label style="font-size:12px;font-weight:600;color:#374151;display:block;margin-bottom:4px;">Tanggal Realisasi</label>
            <input type="date" name="tgl_terima" value="<?= htmlspecialchars($data['tgl_terima']??'') ?>"
                style="width:100%;padding:9px 12px;border-radius:12px;border:1px solid #d1d5db;font-size:13px;outline:none;">
        </div>
    </div>

    <div class="modal-buttons">
        <button type="button" onclick="document.getElementById('crudModal').style.display='none'"
            style="padding:10px 22px;border-radius:24px;border:1px solid #d1d5db;background:white;cursor:pointer;font-size:13px;">
            Batal
        </button>
        <button type="button" id="saveCrudBtn"
            style="padding:10px 22px;border-radius:24px;border:none;background:#1e4a3b;color:white;cursor:pointer;font-size:13px;font-weight:700;">
            <i class="fas fa-save"></i> <?= $isEdit ? 'Simpan Perubahan' : 'Tambah Petani' ?>
        </button>
    </div>
</form>
