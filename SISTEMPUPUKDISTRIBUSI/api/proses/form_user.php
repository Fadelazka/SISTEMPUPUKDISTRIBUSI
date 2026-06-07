<?php
// form_user.php — Form CRUD User dengan BPS Wilayah (untuk Kelola User)
require_once __DIR__ . '/../pages/bps_widget.php';

$id   = intval($_POST['id'] ?? 0);
$data = [];
if ($id > 0) {
    $r    = mysqli_query($koneksi, "SELECT * FROM users WHERE id=$id");
    $data = mysqli_fetch_assoc($r) ?: [];
}
$isEdit = !empty($data);
$sel = ['prov'=>$data['provinsi']??'','kota'=>$data['kota']??'','kec'=>$data['kecamatan']??''];
?>
<form id="crudForm">
    <input type="hidden" name="id" value="<?= $id ?>">
    <h3 style="margin-bottom:18px;color:#1e4a3b;"><?= $isEdit ? '✏️ Edit User' : '➕ Tambah User' ?></h3>

    <div style="background:#f9fafb;border-radius:14px;padding:14px 16px;margin-bottom:14px;border:1px solid #e5e7eb;">
        <div style="font-size:12px;font-weight:700;color:#374151;margin-bottom:12px;">
            <i class="fas fa-user-cog" style="margin-right:6px;color:#2d6a4f;"></i> Informasi Akun
        </div>

        <label style="font-size:12px;font-weight:600;color:#374151;display:block;margin-bottom:4px;">Nama Lengkap <span style="color:#dc2626;">*</span></label>
        <input type="text" name="nama" value="<?= htmlspecialchars($data['nama']??'') ?>" placeholder="Nama lengkap..." required
            style="width:100%;padding:9px 12px;border-radius:12px;border:1px solid #d1d5db;font-size:13px;margin-bottom:10px;outline:none;">

        <label style="font-size:12px;font-weight:600;color:#374151;display:block;margin-bottom:4px;">Email <span style="color:#dc2626;">*</span></label>
        <input type="email" name="email" value="<?= htmlspecialchars($data['email']??'') ?>" placeholder="email@domain.com" required
            style="width:100%;padding:9px 12px;border-radius:12px;border:1px solid #d1d5db;font-size:13px;margin-bottom:10px;outline:none;">

        <label style="font-size:12px;font-weight:600;color:#374151;display:block;margin-bottom:4px;">
            Password <?= $isEdit ? '<span style="font-weight:400;color:#64748b;">(kosongkan jika tidak diubah)</span>' : '<span style="color:#dc2626;">*</span>' ?>
        </label>
        <input type="password" name="password" placeholder="<?= $isEdit?'Kosongkan jika tidak diubah':'Password baru...' ?>" <?= $isEdit?'':'required' ?>
            style="width:100%;padding:9px 12px;border-radius:12px;border:1px solid #d1d5db;font-size:13px;margin-bottom:10px;outline:none;">

        <label style="font-size:12px;font-weight:600;color:#374151;display:block;margin-bottom:4px;">Role <span style="color:#dc2626;">*</span></label>
        <select name="role" required style="width:100%;padding:9px 12px;border-radius:12px;border:1px solid #d1d5db;font-size:13px;margin-bottom:4px;outline:none;">
            <option value="petugas" <?= ($data['role']??'')=='petugas'?'selected':'' ?>>Petugas Dinas</option>
            <option value="admin"   <?= ($data['role']??'')=='admin'?'selected':'' ?>>Administrator</option>
        </select>
    </div>

    <!-- Wilayah Tugas BPS -->
    <div style="font-size:12px;font-weight:700;color:#374151;margin-bottom:6px;margin-top:4px;">
        <i class="fas fa-map-marked-alt" style="margin-right:4px;color:#2d6a4f;"></i> Wilayah Tugas (BPS)
    </div>
    <?= bps_wilayah_fields('user', $sel) ?>

    <div class="modal-buttons">
        <button type="button" onclick="document.getElementById('crudModal').style.display='none'"
            style="padding:10px 22px;border-radius:24px;border:1px solid #d1d5db;background:white;cursor:pointer;font-size:13px;">Batal</button>
        <button type="button" id="saveCrudBtn"
            style="padding:10px 22px;border-radius:24px;border:none;background:#1e4a3b;color:white;cursor:pointer;font-size:13px;font-weight:700;">
            <i class="fas fa-save"></i> <?= $isEdit ? 'Simpan Perubahan' : 'Tambah User' ?>
        </button>
    </div>
</form>
