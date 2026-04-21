<?php
require_once __DIR__ . '/../pages/bps_widget.php';
$id   = intval($_POST['id']??0);
$data = ['nama'=>'','desa'=>'','luas_lahan'=>'','alokasi'=>'','status'=>'Pending','tgl_terima'=>'','provinsi'=>'','kota'=>'','kecamatan'=>''];
if($id>0){
    $q=mysqli_query($koneksi,"SELECT * FROM petani WHERE id=$id");
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

    <!-- Header -->
    <div style="display:flex;align-items:center;gap:12px;margin-bottom:20px;padding-bottom:16px;border-bottom:1px solid #e5e7eb;">
        <div style="width:44px;height:44px;border-radius:12px;background:#f0f9f3;display:flex;align-items:center;justify-content:center;font-size:20px;">
            <?= $isEdit?'✏️':'➕' ?>
        </div>
        <div>
            <div style="font-size:18px;font-weight:800;color:#1e4a3b;"><?= $isEdit?'Edit Data Petani':'Tambah Petani Baru' ?></div>
            <div style="font-size:13px;color:#64748b;margin-top:2px;"><?= $isEdit?"ID: $id — Lengkapi data yang perlu diubah":'Isi semua field yang wajib diisi' ?></div>
        </div>
    </div>

    <!-- Data Pribadi -->
    <div class="fs">
        <div class="fst"><i class="fas fa-user" style="color:#2d6a4f;"></i> Data Petani</div>
        <label class="fl">Nama Lengkap Petani <span style="color:#dc2626;">*</span></label>
        <input class="fi" type="text" name="nama" value="<?= htmlspecialchars($data['nama']) ?>" required placeholder="Masukkan nama lengkap petani...">
        <div class="fg">
            <div>
                <label class="fl">Luas Lahan (Ha) <span style="color:#dc2626;">*</span></label>
                <input class="fi" type="text" name="luas_lahan" value="<?= htmlspecialchars($data['luas_lahan']) ?>" required placeholder="Contoh: 1.5">
            </div>
            <div>
                <label class="fl">Alokasi Pupuk <span style="color:#dc2626;">*</span></label>
                <input class="fi" type="text" name="alokasi" value="<?= htmlspecialchars($data['alokasi']) ?>" required placeholder="Contoh: 200 kg">
            </div>
        </div>
        <div class="fg">
            <div>
                <label class="fl">Status Penerimaan</label>
                <select class="fi" name="status">
                    <?php foreach(['Terealisasi'=>'✅ Terealisasi','Pending'=>'⏳ Pending','Proses'=>'🔄 Proses'] as $v=>$lbl): ?>
                    <option value="<?=$v?>" <?=$data['status']===$v?'selected':''?>><?=$lbl?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="fl">Tanggal Terima</label>
                <input class="fi" type="date" name="tgl_terima" value="<?= htmlspecialchars($data['tgl_terima']) ?>">
            </div>
        </div>
    </div>

    <!-- Wilayah BPS -->
    <?= bps_wilayah_fields('petani',$sel) ?>

    <!-- Desa -->
    <div class="fs">
        <div class="fst"><i class="fas fa-home" style="color:#2d6a4f;"></i> Alamat</div>
        <label class="fl">Desa / Kelurahan <span style="color:#dc2626;">*</span></label>
        <input class="fi" type="text" name="desa" value="<?= htmlspecialchars($data['desa']) ?>" required placeholder="Nama desa atau kelurahan...">
    </div>

    <!-- Tombol Aksi -->
    <div style="display:flex;justify-content:space-between;align-items:center;margin-top:20px;padding-top:16px;border-top:1px solid #e5e7eb;gap:12px;flex-wrap:wrap;">
        <button type="button" onclick="$('#crudModal').hide()"
            style="padding:11px 22px;border-radius:24px;border:1.5px solid #d1d5db;background:white;cursor:pointer;font-size:14px;color:#374151;font-weight:600;">
            <i class="fas fa-times" style="margin-right:6px;"></i> Batal
        </button>
        <button type="button" id="saveCrudBtn"
            style="padding:11px 28px;border-radius:24px;border:none;background:#1e4a3b;color:white;cursor:pointer;font-size:15px;font-weight:800;display:flex;align-items:center;gap:8px;box-shadow:0 4px 12px rgba(30,74,59,0.3);">
            <i class="fas fa-save"></i>
            <?= $isEdit?'Simpan Perubahan':'Tambah Petani' ?>
        </button>
    </div>
</form>
