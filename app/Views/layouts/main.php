<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($title ?? 'LoakIN') ?> | LoakIN</title>
    <meta name="description" content="LoakIN mempertemukan pemilik barang bekas dengan pendaur ulang. Posting, ajukan, sepakati, dan lihat dampaknya.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,500;12..96,700;12..96,800&family=Figtree:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('assets/css/loakin.css') ?>">
    <link rel="icon" href="<?= base_url('favicon.ico') ?>">
</head>
<body>
<?= $this->include('partials/navbar') ?>
<main>
    <div class="container">
        <?= $this->include('partials/flash') ?>
        <?= $this->renderSection('content') ?>
    </div>
</main>
<?= $this->include('partials/footer') ?>
<script src="<?= base_url('assets/js/loakin.js') ?>" defer></script>
</body>
</html>
