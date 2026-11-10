<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="narrow">
    <h1>Beri rating</h1>
    <p class="muted">Untuk <strong><?= esc($ratee['name']) ?></strong>, transaksi "<?= esc($offer['listing_title']) ?>". Rating membantu pengguna lain memilih mitra yang bisa dipercaya.</p>
    <form class="panel" action="<?= site_url('ratings/' . $offer['id']) ?>" method="post">
        <?= csrf_field() ?>
        <fieldset style="border:0;padding:0;margin:0 0 1rem">
            <legend class="label">Seberapa puas kamu?</legend>
            <div class="stars-input">
                <?php for ($i = 5; $i >= 1; $i--): ?>
                    <input type="radio" id="s<?= $i ?>" name="score" value="<?= $i ?>" <?= (int) old('score') === $i ? 'checked' : '' ?>>
                    <label for="s<?= $i ?>" title="<?= $i ?> bintang"><span class="sr-only"><?= $i ?> bintang</span>★</label>
                <?php endfor; ?>
            </div>
            <?= field_error('score') ?>
        </fieldset>
        <div class="field">
            <label for="comment">Komentar (opsional)</label>
            <textarea id="comment" name="comment" maxlength="500" placeholder="Tepat waktu, barang sesuai foto, komunikatif..."><?= esc(old('comment')) ?></textarea>
        </div>
        <button class="btn btn-primary" type="submit">Kirim rating</button>
        <a class="btn btn-ghost" href="<?= site_url('offers/' . $offer['id']) ?>">Nanti saja</a>
    </form>
</div>
<?= $this->endSection() ?>
