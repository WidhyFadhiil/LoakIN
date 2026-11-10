<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="mid">
    <h1>Profil saya</h1>
    <p class="muted"><?= esc(role_label($user['role'])) ?>, <?= esc($user['email']) ?>. <a href="<?= site_url('u/' . $user['id']) ?>">Lihat profil publik</a></p>

    <form class="panel" action="<?= site_url('profile/edit') ?>" method="post" enctype="multipart/form-data" novalidate>
        <?= csrf_field() ?>
        <h2>Data diri</h2>
        <div class="who mb-2">
            <div class="avatar lg"><?php if ($user['photo']): ?><img src="<?= base_url('uploads/avatars/' . $user['photo']) ?>" alt=""><?php else: ?><?= esc(initials($user['name'])) ?><?php endif; ?></div>
            <div class="field mb-0" style="flex:1">
                <label for="photo">Foto profil</label>
                <input id="photo" type="file" name="photo" accept="image/jpeg,image/png,image/webp">
                <?= field_error('photo') ?>
            </div>
        </div>
        <div class="field">
            <label for="name">Nama</label>
            <input id="name" class="<?= err_class('name') ?>" type="text" name="name" value="<?= esc(old('name', $user['name'])) ?>" required>
            <?= field_error('name') ?>
        </div>
        <div class="row">
            <div class="field">
                <label for="phone">Nomor WA</label>
                <input id="phone" class="<?= err_class('phone') ?>" type="tel" name="phone" value="<?= esc(old('phone', $user['phone'])) ?>" required>
                <p class="hint">Hanya terlihat oleh lawan transaksi setelah pengajuan diterima.</p>
                <?= field_error('phone') ?>
            </div>
            <div class="field">
                <label for="city">Kota</label>
                <input id="city" class="<?= err_class('city') ?>" type="text" name="city" value="<?= esc(old('city', $user['city'])) ?>" required>
                <?= field_error('city') ?>
            </div>
        </div>
        <div class="field">
            <label for="address">Alamat (hanya untukmu, dipakai sebagai isian awal saat posting)</label>
            <input id="address" type="text" name="address" value="<?= esc(old('address', $user['address'])) ?>">
        </div>
        <div class="field">
            <label for="bio">Tentang saya</label>
            <textarea id="bio" name="bio" maxlength="500" rows="3"><?= esc(old('bio', $user['bio'])) ?></textarea>
        </div>

        <h3 class="mt-2">Ganti password (opsional)</h3>
        <div class="row">
            <div class="field"><label for="current_password">Password saat ini</label><input id="current_password" class="<?= err_class('current_password') ?>" type="password" name="current_password" autocomplete="current-password"><?= field_error('current_password') ?></div>
            <div class="field"><label for="new_password">Password baru</label><input id="new_password" class="<?= err_class('new_password') ?>" type="password" name="new_password" autocomplete="new-password"><?= field_error('new_password') ?></div>
            <div class="field"><label for="new_password_confirm">Ulangi password baru</label><input id="new_password_confirm" class="<?= err_class('new_password_confirm') ?>" type="password" name="new_password_confirm" autocomplete="new-password"><?= field_error('new_password_confirm') ?></div>
        </div>
        <button class="btn btn-primary" type="submit">Simpan profil</button>
    </form>

    <?php if ($user['role'] === 'pendaur'): ?>
    <section class="panel" id="verifikasi">
        <h2>Verifikasi pendaur ulang</h2>
        <p>Status: <span class="badge <?= status_class($user['verification_status']) ?>"><?= esc(verification_label($user['verification_status'])) ?></span></p>
        <?php if ($user['verification_status'] === 'approved'): ?>
            <div class="callout ok"><strong>Akunmu terverifikasi</strong>Kamu bisa mengajukan pengambilan barang di <a href="<?= site_url('listings') ?>">halaman pencarian</a>.</div>
        <?php elseif ($user['verification_status'] === 'pending'): ?>
            <div class="callout"><strong>Sedang ditinjau admin</strong>Kamu akan mendapat notifikasi begitu ada hasilnya.</div>
        <?php else: ?>
            <?php if ($user['verification_status'] === 'rejected'): ?>
                <div class="callout"><strong>Pengajuan sebelumnya belum disetujui</strong>Baca alasan di notifikasi, perbaiki, lalu ajukan lagi.</div>
            <?php endif; ?>
            <p class="muted">Verifikasi mencegah sembarang orang mengambil barang penyetor. Unggah foto KTP atau bukti usaha (bank sampah, pengepul, komunitas). Dokumen hanya bisa dilihat admin.</p>
            <form action="<?= site_url('profile/verification') ?>" method="post" enctype="multipart/form-data" novalidate>
                <?= csrf_field() ?>
                <div class="field">
                    <label for="verification_note">Ceritakan kegiatan daur ulangmu</label>
                    <textarea id="verification_note" class="<?= err_class('verification_note') ?>" name="verification_note" maxlength="1000" placeholder="Jenis barang yang kamu ambil, untuk apa, dan sudah berapa lama."><?= esc(old('verification_note', $user['verification_note'])) ?></textarea>
                    <?= field_error('verification_note') ?>
                </div>
                <div class="field">
                    <label for="verification_doc">Foto KTP atau bukti usaha (JPG, PNG, atau PDF, maks. 3 MB)</label>
                    <input id="verification_doc" type="file" name="verification_doc" accept="image/jpeg,image/png,application/pdf">
                    <?= field_error('verification_doc') ?>
                </div>
                <button class="btn btn-primary" type="submit">Ajukan verifikasi</button>
            </form>
        <?php endif; ?>
    </section>
    <?php endif; ?>
</div>
<?= $this->endSection() ?>
