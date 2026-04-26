<?php
$userId = $_SESSION['id'];
$query = mysqli_query($koneksi, "SELECT * FROM users WHERE id = $userId");
$user = mysqli_fetch_assoc($query);

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

<style>
/* ===== FULL WIDTH PROFILE STYLES ===== */
.profile-fullwidth {
    width: 100%;
    margin: 0;
    padding: 0;
    background: transparent;
}
.cover-area {
    position: relative;
    height: 220px;
    background: linear-gradient(145deg, #2d6a4f, #1b4d3e);
    border-radius: 28px 28px 0 0;
    overflow: hidden;
}
.avatar-wrapper {
    position: absolute;
    bottom: -50px;
    left: 40px;
}
.avatar {
    width: 130px;
    height: 130px;
    border-radius: 50%;
    border: 5px solid white;
    background: white;
    object-fit: cover;
    box-shadow: 0 4px 12px rgba(0,0,0,0.2);
}
.profile-header {
    margin-top: 70px;
    padding: 0 30px 20px 30px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    border-bottom: 1px solid #eef2f6;
}
.profile-name-section h2 {
    font-size: 28px;
    font-weight: 800;
    margin-bottom: 5px;
}
.profile-bio {
    color: #4a5568;
    font-size: 14px;
    margin-bottom: 8px;
}
.profile-meta {
    display: flex;
    gap: 20px;
    font-size: 13px;
    color: #2d6a4f;
}
.profile-actions {
    display: flex;
    gap: 12px;
}
.btn-edit-profile {
    background: #eef2ff;
    border: none;
    padding: 8px 24px;
    border-radius: 40px;
    font-weight: 600;
    cursor: pointer;
    transition: 0.2s;
}
.btn-edit-profile:hover {
    background: #e2e8f0;
}
.btn-logout-profile {
    background: #fee2e2;
    color: #b91c1c;
    border: none;
    padding: 8px 24px;
    border-radius: 40px;
    font-weight: 600;
    cursor: pointer;
    transition: 0.2s;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}
.btn-logout-profile:hover {
    background: #fecaca;
}
.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
    background: #f8fafc;
    border-radius: 28px;
    padding: 24px 30px;
    margin: 25px 30px;
}
.info-item {
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 14px;
    color: #1e293b;
}
.info-item i {
    width: 28px;
    color: #2d6a4f;
    font-size: 18px;
}
.stats-row {
    display: flex;
    gap: 20px;
    flex-wrap: wrap;
    margin: 0 30px 25px 30px;
}
.stat-badge {
    background: #eef2ff;
    border-radius: 40px;
    padding: 8px 20px;
    font-size: 14px;
    font-weight: 500;
    color: #1e4a3b;
}
.timeline {
    margin: 0 30px 30px 30px;
}
.timeline h3 {
    margin-bottom: 20px;
    font-size: 20px;
}
.timeline-item {
    display: flex;
    gap: 16px;
    padding: 16px 0;
    border-bottom: 1px solid #edf2f7;
}
.timeline-icon {
    width: 40px;
    height: 40px;
    background: #e9f5ef;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #1e4a3b;
}
.timeline-item strong {
    font-size: 15px;
}
.timeline-item small {
    font-size: 12px;
    color: #64748b;
}
.info-box {
    background: #f0f6f3;
    border-radius: 28px;
    padding: 18px 24px;
    margin: 0 30px 30px 30px;
    border-left: 5px solid #f5e7a4;
    font-size: 14px;
}

/* ===== MODAL EDIT PROFIL - CENTER & LEBAR ===== */
.modal-edit {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.75);
    backdrop-filter: blur(8px);
    z-index: 9999;
    /* Pusatkan secara horizontal dan vertikal */
    justify-content: center;
    align-items: center;
}
.modal-card {
    background: white;
    width: 90%;
    max-width: 750px;
    max-height: 90vh;
    overflow-y: auto;
    border-radius: 40px;
    padding: 35px;
    box-shadow: 0 30px 60px rgba(0, 0, 0, 0.4);
    animation: modalPop 0.25s cubic-bezier(0.2, 0.9, 0.4, 1.1);
}
@keyframes modalPop {
    from {
        opacity: 0;
        transform: scale(0.92);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}
.modal-card h2 {
    font-size: 28px;
    font-weight: 800;
    margin-bottom: 25px;
    color: #1e4a3b;
    border-left: 5px solid #f5e7a4;
    padding-left: 18px;
}
.modal-card .form-row {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    margin-bottom: 10px;
}
.modal-card .form-group {
    flex: 1;
    min-width: 200px;
}
.modal-card label {
    display: block;
    font-weight: 600;
    font-size: 14px;
    margin-bottom: 6px;
    color: #1e293b;
}
.modal-card input,
.modal-card textarea {
    width: 100%;
    padding: 14px 18px;
    margin-bottom: 18px;
    border: 1px solid #cbd5e1;
    border-radius: 24px;
    font-size: 15px;
    font-family: 'Inter', sans-serif;
    transition: all 0.2s;
    background-color: #fefefe;
}
.modal-card input:focus,
.modal-card textarea:focus {
    outline: none;
    border-color: #2d6a4f;
    box-shadow: 0 0 0 4px rgba(45, 106, 79, 0.15);
}
.modal-card textarea {
    resize: vertical;
    min-height: 100px;
}
.modal-buttons {
    display: flex;
    justify-content: flex-end;
    gap: 18px;
    margin-top: 30px;
    padding-top: 15px;
    border-top: 1px solid #eef2f6;
}
.modal-buttons button {
    padding: 12px 32px;
    border-radius: 40px;
    font-weight: 700;
    font-size: 15px;
    cursor: pointer;
    transition: all 0.2s;
    border: none;
}
.modal-buttons button:first-child {
    background: #f1f5f9;
    color: #334155;
}
.modal-buttons button:first-child:hover {
    background: #e2e8f0;
    transform: translateY(-2px);
}
.modal-buttons button:last-child {
    background: #1e4a3b;
    color: white;
}
.modal-buttons button:last-child:hover {
    background: #0f3b2f;
    transform: translateY(-2px);
    box-shadow: 0 6px 14px rgba(0, 0, 0, 0.1);
}
/* Responsif */
@media (max-width: 640px) {
    .modal-card .form-row {
        flex-direction: column;
        gap: 0;
    }
    .modal-card {
        padding: 25px;
    }
    .modal-buttons button {
        padding: 10px 24px;
    }
}
</style>

<div class="profile-fullwidth">
    <!-- Cover -->
    <div class="cover-area">
        <div class="avatar-wrapper">
            <img class="avatar" src="https://ui-avatars.com/api/?background=1e4a3b&color=fff&name=<?= urlencode($user['nama']) ?>&size=130&bold=true" alt="avatar">
        </div>
    </div>

    <!-- Header -->
    <div class="profile-header">
        <div class="profile-name-section">
            <h2><?= htmlspecialchars($user['nama']) ?></h2>
            <div class="profile-bio"><?= htmlspecialchars($user['bio'] ?? 'Petugas Distribusi Pupuk Subsidi | Dinas Tanaman Pangan & Holtikultura') ?></div>
            <div class="profile-meta">
                <span><i class="fas fa-building"></i> <?= htmlspecialchars($user['instansi'] ?? 'Dinas Pertanian') ?></span>
                <span><i class="fas fa-calendar-alt"></i> Bergabung <?= date('Y', strtotime($user['created_at'] ?? '2024')) ?></span>
            </div>
        </div>
        <div class="profile-actions">
            <button class="btn-edit-profile" id="editProfileBtn"><i class="fas fa-pen"></i> Edit Profil</button>
            <a href="logout.php" class="btn-logout-profile"><i class="fas fa-sign-out-alt"></i> Keluar</a>
        </div>
    </div>

    <!-- Info Grid -->
    <div class="info-grid">
        <div class="info-item"><i class="fas fa-envelope"></i> <?= htmlspecialchars($user['email']) ?></div>
        <div class="info-item"><i class="fas fa-phone-alt"></i> <?= htmlspecialchars($user['phone'] ?? '+62 812 3456 7890') ?></div>
        <div class="info-item"><i class="fas fa-id-card"></i> NIP: <?= htmlspecialchars($user['nip'] ?? '198504102023011001') ?></div>
        <div class="info-item"><i class="fas fa-building"></i> <?= htmlspecialchars($user['instansi'] ?? 'Dinas Tanaman Pangan & Holtikultura') ?></div>
        <div class="info-item"><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($user['address'] ?? 'Jl. Pertanian No. 45, Jakarta') ?></div>
    </div>

    <!-- Stats -->
    <div class="stats-row">
        <div class="stat-badge"><i class="fas fa-tasks"></i> <?= $totalDistribusi ?> Distribusi Tercatat</div>
        <div class="stat-badge"><i class="fas fa-users"></i> <?= number_format($totalPetani) ?> Petani Terlayani</div>
        <div class="stat-badge"><i class="fas fa-truck"></i> 24 Armada Aktif</div>
    </div>

    <!-- Timeline -->
    <div class="timeline">
        <h3><i class="fas fa-history"></i> Aktivitas Terbaru</h3>
        <?php foreach($aktivitas as $akt): ?>
        <div class="timeline-item">
            <div class="timeline-icon"><i class="<?= $akt['icon'] ?>"></i></div>
            <div>
                <strong><?= htmlspecialchars($akt['text']) ?></strong><br>
                <small><?= date('d F Y, H:i', strtotime($akt['tgl'])) ?></small>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Info Box -->
    <div class="info-box">
        <i class="fas fa-shield-alt"></i> Akun terverifikasi sebagai <?= $_SESSION['role'] == 'admin' ? 'Administrator' : 'Petugas Distribusi' ?>.
    </div>
</div>

<!-- MODAL EDIT PROFIL (CENTER & LEBAR) -->
<div id="editProfileModal" class="modal-edit">
    <div class="modal-card">
        <h2><i class="fas fa-user-edit"></i> Edit Profil</h2>
        <form id="editProfileForm">
            <div class="form-row">
                <div class="form-group">
                    <label>Nama Lengkap</label>
                    <input type="text" name="nama" value="<?= htmlspecialchars($user['nama']) ?>" required>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Nomor Telepon</label>
                    <input type="text" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '+62 812 3456 7890') ?>">
                </div>
                <div class="form-group">
                    <label>NIP</label>
                    <input type="text" name="nip" value="<?= htmlspecialchars($user['nip'] ?? '198504102023011001') ?>">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Instansi</label>
                    <input type="text" name="instansi" value="<?= htmlspecialchars($user['instansi'] ?? 'Dinas Tanaman Pangan & Holtikultura') ?>">
                </div>
                <div class="form-group">
                    <label>Alamat Kantor</label>
                    <input type="text" name="address" value="<?= htmlspecialchars($user['address'] ?? 'Jl. Pertanian No. 45, Jakarta') ?>">
                </div>
            </div>
            <div class="form-group">
                <label>Bio / Jabatan</label>
                <textarea name="bio" rows="3"><?= htmlspecialchars($user['bio'] ?? 'Petugas Distribusi Pupuk Subsidi | Dinas Tanaman Pangan & Holtikultura') ?></textarea>
            </div>
            <div class="modal-buttons">
                <button type="button" id="closeModalBtn">Batal</button>
                <button type="submit">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#editProfileBtn').click(function() {
        $('#editProfileModal').css('display', 'flex'); // pastikan display flex
    });
    $('#closeModalBtn, #editProfileModal').click(function(e) {
        if (e.target == this) $('#editProfileModal').hide();
    });
    $('#editProfileForm').submit(function(e) {
        e.preventDefault();
        var formData = $(this).serialize();
        $.post('proses/ajax_handler.php', formData + '&action=updateProfile', function(res) {
            if (res.status === 'success') {
                alert('Profil berhasil diperbarui!');
                location.reload();
            } else {
                alert('Gagal: ' + (res.msg || 'Terjadi kesalahan'));
            }
        }, 'json').fail(function() {
            alert('Koneksi error.');
        });
    });
});
</script>