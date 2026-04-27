<?php
$userId = isset($_COOKIE['id']) ? intval($_COOKIE['id']) : 0;
$query = mysqli_query($koneksi, "SELECT * FROM users WHERE id = $userId");
$user = $query ? mysqli_fetch_assoc($query) : ['nama'=>'Gagal Memuat', 'email'=>'', 'bio'=>''];

$totalDistribusi = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM distribusi"))['total'];
$totalPetani = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM petani"))['total'];

// Aktivitas terbaru
$aktivitas = [];
$dist = mysqli_query($koneksi, "SELECT tgl, CONCAT('Realisasi pupuk ', pupuk, ' — ', jumlah, ' disalurkan ke ', kelompok) as text FROM distribusi ORDER BY tgl DESC LIMIT 5");
while($row = mysqli_fetch_assoc($dist)) $aktivitas[] = ['tgl' => $row['tgl'], 'text' => $row['text'], 'icon' => 'fas fa-truck'];
$lap = mysqli_query($koneksi, "SELECT created_at, judul as text FROM laporan ORDER BY created_at DESC LIMIT 3");
while($row = mysqli_fetch_assoc($lap)) $aktivitas[] = ['tgl' => $row['created_at'], 'text' => $row['text'], 'icon' => 'fas fa-chart-line'];
usort($aktivitas, function($a,$b){ return strtotime($b['tgl']) - strtotime($a['tgl']); });
$aktivitas = array_slice($aktivitas, 0, 6);
?>

<div class="w-full pb-8">
    <div class="relative h-48 md:h-56 bg-gradient-to-br from-primary to-dark rounded-t-3xl overflow-hidden shadow-sm">
        <div class="absolute -bottom-14 left-6 md:left-10">
            <img class="w-28 h-28 md:w-32 md:h-32 rounded-full border-4 border-white shadow-lg bg-white object-cover" 
                 src="https://ui-avatars.com/api/?background=1e4a3b&color=fff&name=<?= urlencode($user['nama']) ?>&size=130&bold=true" alt="avatar">
        </div>
    </div>

    <div class="mt-16 md:mt-20 px-6 md:px-10 flex flex-col md:flex-row md:items-start justify-between gap-4 border-b border-slate-100 pb-6">
        <div>
            <h2 class="text-2xl md:text-3xl font-black text-slate-800"><?= htmlspecialchars($user['nama']) ?></h2>
            <p class="text-sm text-slate-500 font-medium mt-1 mb-3"><?= htmlspecialchars($user['bio'] ?? 'Petugas Distribusi Pupuk Subsidi | Dinas Tanaman Pangan & Holtikultura') ?></p>
            <div class="flex flex-wrap gap-4 text-xs font-semibold text-primary">
                <span class="flex items-center gap-1.5"><i class="fas fa-building text-secondary"></i> <?= htmlspecialchars($user['instansi'] ?? 'Dinas Pertanian') ?></span>
                <span class="flex items-center gap-1.5"><i class="fas fa-calendar-alt text-secondary"></i> Bergabung <?= date('Y', strtotime($user['created_at'] ?? '2024')) ?></span>
            </div>
        </div>
        <div class="flex flex-wrap gap-3 mt-2 md:mt-0">
            <button id="editProfileBtn" class="bg-slate-100 hover:bg-slate-200 text-primary px-5 py-2.5 rounded-full font-bold text-sm transition-all shadow-sm"><i class="fas fa-pen mr-1.5"></i> Edit Profil</button>
            <a href="logout.php" class="bg-red-50 hover:bg-red-100 text-red-600 px-5 py-2.5 rounded-full font-bold text-sm transition-all shadow-sm flex items-center gap-1.5"><i class="fas fa-sign-out-alt"></i> Keluar</a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 bg-slate-50 rounded-3xl p-6 mx-4 md:mx-8 mt-8 border border-slate-100">
        <div class="flex items-center gap-3 text-sm text-slate-700 font-medium"><i class="fas fa-envelope w-6 text-center text-primary text-lg"></i> <?= htmlspecialchars($user['email']) ?></div>
        <div class="flex items-center gap-3 text-sm text-slate-700 font-medium"><i class="fas fa-phone-alt w-6 text-center text-primary text-lg"></i> <?= htmlspecialchars($user['phone'] ?? '+62 812 3456 7890') ?></div>
        <div class="flex items-center gap-3 text-sm text-slate-700 font-medium"><i class="fas fa-id-card w-6 text-center text-primary text-lg"></i> NIP: <?= htmlspecialchars($user['nip'] ?? '198504102023011001') ?></div>
        <div class="flex items-center gap-3 text-sm text-slate-700 font-medium lg:col-span-2"><i class="fas fa-map-marker-alt w-6 text-center text-primary text-lg"></i> <?= htmlspecialchars($user['address'] ?? 'Jl. Pertanian No. 45, Jakarta') ?></div>
    </div>

    <div class="flex flex-wrap gap-3 mx-4 md:mx-8 mt-6">
        <span class="bg-blue-50 text-blue-700 border border-blue-100 px-4 py-2 rounded-full text-xs font-bold shadow-sm"><i class="fas fa-tasks mr-1"></i> <?= $totalDistribusi ?> Distribusi Tercatat</span>
        <span class="bg-emerald-50 text-emerald-700 border border-emerald-100 px-4 py-2 rounded-full text-xs font-bold shadow-sm"><i class="fas fa-users mr-1"></i> <?= number_format($totalPetani) ?> Petani Terlayani</span>
        <span class="bg-purple-50 text-purple-700 border border-purple-100 px-4 py-2 rounded-full text-xs font-bold shadow-sm"><i class="fas fa-shield-alt mr-1"></i> Terverifikasi sebagai <?= (isset($_COOKIE['role']) && $_COOKIE['role'] == 'admin') ? 'Administrator' : 'Petugas' ?></span>
    </div>

    <div class="mx-4 md:mx-8 mt-10">
        <h3 class="text-lg font-extrabold text-primary mb-6 flex items-center gap-2"><i class="fas fa-history text-secondary"></i> Aktivitas Terbaru</h3>
        <div class="space-y-4">
            <?php foreach($aktivitas as $akt): ?>
            <div class="flex gap-4 items-start pb-4 border-b border-slate-100 last:border-0">
                <div class="w-10 h-10 rounded-full bg-emerald-50 text-primary flex items-center justify-center flex-shrink-0"><i class="<?= $akt['icon'] ?>"></i></div>
                <div>
                    <p class="text-sm font-bold text-slate-700 leading-snug"><?= htmlspecialchars($akt['text']) ?></p>
                    <p class="text-[11px] text-slate-400 mt-1 font-medium"><?= date('d F Y, H:i', strtotime($akt['tgl'])) ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<div id="editProfileModal" class="fixed inset-0 z-[200] hidden items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4">
    <div class="bg-white w-full max-w-2xl rounded-3xl p-6 md:p-8 shadow-2xl max-h-[90vh] overflow-y-auto relative">
        <h2 class="text-2xl font-black text-primary border-l-4 border-secondary pl-3 mb-6"><i class="fas fa-user-edit mr-2 text-secondary"></i>Edit Profil</h2>
        <form id="editProfileForm" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div><label class="block text-xs font-bold text-slate-600 mb-1">Nama Lengkap</label><input type="text" name="nama" value="<?= htmlspecialchars($user['nama']) ?>" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/20"></div>
                <div><label class="block text-xs font-bold text-slate-600 mb-1">Email</label><input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/20"></div>
                <div><label class="block text-xs font-bold text-slate-600 mb-1">Nomor Telepon</label><input type="text" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '+62 812 3456 7890') ?>" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/20"></div>
                <div><label class="block text-xs font-bold text-slate-600 mb-1">NIP</label><input type="text" name="nip" value="<?= htmlspecialchars($user['nip'] ?? '198504102023011001') ?>" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/20"></div>
                <div><label class="block text-xs font-bold text-slate-600 mb-1">Instansi</label><input type="text" name="instansi" value="<?= htmlspecialchars($user['instansi'] ?? 'Dinas Tanaman Pangan & Holtikultura') ?>" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/20"></div>
                <div><label class="block text-xs font-bold text-slate-600 mb-1">Alamat Kantor</label><input type="text" name="address" value="<?= htmlspecialchars($user['address'] ?? 'Jl. Pertanian No. 45, Jakarta') ?>" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/20"></div>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">Bio / Jabatan</label>
                <textarea name="bio" rows="3" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 resize-y"><?= htmlspecialchars($user['bio'] ?? 'Petugas Distribusi Pupuk Subsidi') ?></textarea>
            </div>
            <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-slate-100">
                <button type="button" id="closeModalBtn" class="bg-slate-100 hover:bg-slate-200 text-slate-600 px-6 py-2.5 rounded-full font-bold text-sm transition-colors">Batal</button>
                <button type="submit" class="bg-primary hover:bg-dark text-white px-6 py-2.5 rounded-full font-bold text-sm transition-colors shadow-md">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#editProfileBtn').click(function() { $('#editProfileModal').fadeIn(200).css('display','flex'); });
    $('#closeModalBtn, #editProfileModal').click(function(e) { if (e.target == this) $('#editProfileModal').fadeOut(200); });
    $('#editProfileForm').submit(function(e) {
        e.preventDefault();
        $.post('proses/ajax_handler.php', $(this).serialize() + '&action=updateProfile', function(res) {
            if (res.status === 'success') { alert('Profil berhasil diperbarui!'); loadPage('profile'); } 
            else { alert('Gagal: ' + (res.msg || 'Terjadi kesalahan')); }
        }, 'json');
    });
});
</script>