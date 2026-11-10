<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($title ?? 'Admin') ?> | Admin LoakIN</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,500;12..96,700;12..96,800&family=Figtree:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('assets/css/loakin.css') ?>">
    <link rel="icon" href="<?= base_url('favicon.ico') ?>">
</head>
<body>
<?= $this->include('partials/navbar') ?>
<main>
    <div class="container admin">
        <aside aria-label="Menu admin">
            <a href="<?= site_url('admin') ?>"<?= nav_current('admin', true) ?>>Ringkasan</a>
            <a href="<?= site_url('admin/verification') ?>"<?= nav_current('admin/verification') ?>>Verifikasi pendaur</a>
            <a href="<?= site_url('admin/users') ?>"<?= nav_current('admin/users') ?>>Pengguna</a>
            <a href="<?= site_url('admin/categories') ?>"<?= nav_current('admin/categories') ?>>Kategori</a>
            <a href="<?= site_url('impact') ?>">Dampak lingkungan</a>
        </aside>
        <section>
            <?= $this->include('partials/flash') ?>
            <?= $this->renderSection('content') ?>
        </section>
    </div>
</main>
<script src="<?= base_url('assets/js/loakin.js') ?>" defer></script>
</body>
</html>
