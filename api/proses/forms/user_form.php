<?php
if($_SESSION['role']!=='admin'){ echo "<div style='color:red;padding:20px;'>Akses ditolak.</div>"; exit(); }
require_once __DIR__ . '/../pages/bps_widget.php';
$id   = intval($_POST['id']??0);
$data = ['nama'=>'','email'=>'','role'=>'petugas','provinsi'=>'','kota'=>'','kecamatan'=>''];
if($id>0){
    $q=mysqli_query($koneksi,"SELECT * FROM users WHERE id=$id");
    if($q&&mysqli_num_rows($q)) $data=array_merge($data,mysqli_fetch_assoc($q));
}
$sel=['prov'=>$data['provinsi'],'kota'=>$data['kota'],'kec'=>$data['kecamatan']];
$isEdit=$id>0;
?>
<style>
#crudForm .fl{font-size:13px;font-weight:600;color:#374151;display:block;margin-bottom:5px;}
#crudForm .fi{width:100%;padding:10px 14px;border-radius:12px;border:1.5px solid #e2e8f0;font-size:14px;outline:none;margin-bottom:12px;box-sizing:border-box;transition:border-color 0.2s;}
#crudForm .fi:focus{border-color:#2d6a4f;}
#crudForm .fg{display:grid;grid-template-columns:1fr 1fr;gap:12px;}
#crudForm .fs{background:#f8fafc;border-radius:14px;padding:16px;margin-bottom:12px;border:1px solid #e5e7eb;}
#crudForm .fst{font-size:13px;font-weight:700;color:#1e4a3b;margin-bottom:12px;display:flex;align-items:center;gap:6px;}
</style>
<form id="crudForm">
    <input type="hidden" name="id" value="<?= $id ?>">

    <div style="display:flex;align-items:center;gap:12px;margin-bottom:20px;padding-bottom:16px;border-bottom:1px solid #e5e7eb;">
        <div style="width:44px;height:44px;border-radius:12px;background:#f5f3ff;display:flex;align-items:center;justify-content:center;font-size:20px;"><?= $isEdit?'✏️':'👤' ?></div>
        <div>
            <div style="font-size:18px;font-weight:800;color:#1e4a3b;"><?= $isEdit?'Edit User':'Tambah User Baru' ?></div>
            <div style="font-size:13px;color:#64748b;margin-top:2px;"><?= $isEdit?"ID: $id — Perbarui data pengguna":'Daftarkan pengguna sistem baru' ?></div>
        </div>
    </div>

    <div class="fs">
        <div class="fst"><i class="fas fa-user-cog" style="color:#2d6a4f;"></i> Informasi Akun</div>
        <label class="fl">Nama Lengkap <span style="color:#dc2626;">*</span></label>
        <input class="fi" type="text" name="nama" value="<?= htmlspecialchars($data['nama']) ?>" required placeholder="Nama lengkap pengguna...">
        <label class="fl">Alamat Email <span style="color:#dc2626;">*</span></label>
        <input class="fi" type="email" name="email" value="<?= htmlspecialchars($data['email']) ?>" required placeholder="email@domain.com">
        <label class="fl">
            Password
            <?= $isEdit?'<span style="font-weight:400;color:#94a3b8;font-size:12px;"> — Kosongkan jika tidak ingin mengubah password</span>':'<span style="color:#dc2626;">*</span>' ?>
        </label>
        <input class="fi" type="password" name="password" placeholder="<?= $isEdit?'Kosongkan jika tidak diubah':'Buat password baru...' ?>" <?= $isEdit?'':'required' ?>>
        <label class="fl">Role / Hak Akses <span style="color:#dc2626;">*</span></label>
        <select class="fi" name="role">
            <option value="petugas" <?= $data['role']==='petugas'?'selected':'' ?>>👷 Petugas Dinas</option>
            <option value="admin"   <?= $data['role']==='admin'?'selected':'' ?>>🔑 Administrator</option>
        </select>
    </div>

    <!-- Wilayah Tugas BPS — prefix 'user' -->
    <div style="font-size:13px;font-weight:700;color:#1e4a3b;margin-bottom:8px;display:flex;align-items:center;gap:6px;">
        <i class="fas fa-map-marked-alt" style="color:#2d6a4f;"></i> Wilayah Tugas Pengguna
    </div>
    <?= bps_wilayah_fields('user',$sel) ?>

    <div style="display:flex;justify-content:space-between;align-items:center;margin-top:16px;padding-top:16px;border-top:1px solid #e5e7eb;gap:12px;flex-wrap:wrap;">
        <button type="button" onclick="$('#crudModal').hide()"
            style="padding:11px 22px;border-radius:24px;border:1.5px solid #d1d5db;background:white;cursor:pointer;font-size:14px;color:#374151;font-weight:600;">
            <i class="fas fa-times" style="margin-right:6px;"></i> Batal
        </button>
        <button type="button" id="saveCrudBtn"
            style="padding:11px 28px;border-radius:24px;border:none;background:#1e4a3b;color:white;cursor:pointer;font-size:15px;font-weight:800;display:flex;align-items:center;gap:8px;box-shadow:0 4px 12px rgba(30,74,59,0.3);">
            <i class="fas fa-save"></i>
            <?= $isEdit?'Simpan Perubahan':'Tambah User' ?>
        </button>
    </div>
</form>
