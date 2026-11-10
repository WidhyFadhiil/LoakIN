<?php
/** Form posting/ubah barang. @var array|null $listing  @var array $categories  @var array $images */
$cfg = config('Loakin');
$v = static fn (string $k, $default = '') => old($k, $listing[$k] ?? $default);
?>
<form action="<?= esc($action) ?>" method="post" enctype="multipart/form-data" novalidate>
    <?= csrf_field() ?>
    <div class="panel">
        <h2>Tentang barangnya</h2>
        <div class="field">
            <label for="title">Judul</label>
            <input id="title" class="<?= err_class('title') ?>" type="text" name="title" maxlength="150" value="<?= esc($v('title')) ?>" placeholder="Contoh: Botol plastik bekas, sekitar 100 buah" required>
            <?= field_error('title') ?>
        </div>
        <div class="row">
            <div class="field">
                <label for="category_id">Kategori</label>
                <select id="category_id" class="<?= err_class('category_id') ?>" name="category_id" required>
                    <option value="">Pilih kategori</option>
                    <?php foreach ($categories as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= (string) $v('category_id') === (string) $c['id'] ? 'selected' : '' ?>><?= esc($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <?= field_error('category_id') ?>
            </div>
            <div class="field">
                <label for="weight_kg">Berat estimasi (kg)</label>
                <input id="weight_kg" class="<?= err_class('weight_kg') ?>" type="text" inputmode="decimal" name="weight_kg" value="<?= esc($v('weight_kg') !== '' ? rtrim(rtrim((string) $v('weight_kg'), '0'), '.') : '') ?>" placeholder="2,5" required>
                <p class="hint">Dipakai untuk menghitung dampak lingkungan.</p>
                <?= field_error('weight_kg') ?>
            </div>
        </div>
        <div class="field">
            <label for="item_condition">Kondisi</label>
            <select id="item_condition" name="item_condition">
                <?php foreach ($cfg->conditions as $k => $label): ?>
                    <option value="<?= $k ?>" <?= $v('item_condition', 'layak') === $k ? 'selected' : '' ?>><?= esc($label) ?></option>
                <?php endforeach; ?>
            </select>
            <?= field_error('item_condition') ?>
        </div>
        <div class="field">
            <label for="description">Deskripsi</label>
            <textarea id="description" class="<?= err_class('description') ?>" name="description" maxlength="2000" placeholder="Jumlah, ukuran, kondisi, dan apa yang perlu diketahui pendaur ulang."><?= esc($v('description')) ?></textarea>
            <?= field_error('description') ?>
        </div>
    </div>

    <div class="panel">
        <h2>Foto</h2>
        <?php if (! empty($images)): ?>
            <p class="label">Foto saat ini (centang untuk menghapus)</p>
            <div class="flex">
                <?php foreach ($images as $img): ?>
                    <label style="font-weight:400;margin:0">
                        <img src="<?= cover_url($img['filename']) ?>" alt="" style="width:96px;height:72px;object-fit:cover;border-radius:8px;border:1px solid var(--line)">
                        <input type="checkbox" name="remove_images[]" value="<?= $img['id'] ?>"> Hapus
                    </label>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <div class="field mt-1">
            <label for="images">Tambah foto (maksimal <?= $cfg->maxImages ?>, JPG/PNG/WEBP, <?= $cfg->maxImageKb / 1024 ?> MB per foto)</label>
            <input id="images" type="file" name="images[]" accept="image/jpeg,image/png,image/webp" multiple>
            <div id="preview" class="flex mt-1"></div>
            <p class="hint">Foto yang jelas membantu pendaur ulang menilai barang sebelum datang.</p>
        </div>
    </div>

    <div class="panel">
        <h2>Harga</h2>
        <div class="field">
            <?php foreach ($cfg->priceTypes as $k => $label): ?>
                <label class="choice">
                    <input type="radio" name="price_type" value="<?= $k ?>" <?= $v('price_type', 'gratis') === $k ? 'checked' : '' ?>>
                    <span><?= esc($label) ?>
                        <small><?= ['gratis' => 'Diberikan cuma-cuma.', 'berbayar' => 'Harga tetap, dibayar langsung saat serah terima.', 'nego' => 'Pendaur ulang boleh menawar, kamu bisa membalas harga lain.'][$k] ?></small>
                    </span>
                </label>
            <?php endforeach; ?>
            <?= field_error('price_type') ?>
        </div>
        <div class="field" id="price-field">
            <label for="price">Harga (Rp)</label>
            <input id="price" class="<?= err_class('price') ?>" type="text" inputmode="numeric" name="price" value="<?= esc($v('price') ? (string) $v('price') : '') ?>" placeholder="25000">
            <p class="hint">Pembayaran dilakukan langsung (tunai atau transfer). Web hanya mencatat harga yang disepakati.</p>
            <?= field_error('price') ?>
        </div>
    </div>

    <div class="panel">
        <h2>Lokasi dan serah terima</h2>
        <div class="row">
            <div class="field">
                <label for="city">Kota</label>
                <input id="city" class="<?= err_class('city') ?>" type="text" name="city" value="<?= esc($v('city', $defaults['city'] ?? '')) ?>" required>
                <?= field_error('city') ?>
            </div>
            <div class="field">
                <label for="address">Patokan lokasi (opsional)</label>
                <input id="address" type="text" name="address" maxlength="255" value="<?= esc($v('address', $defaults['address'] ?? '')) ?>" placeholder="Kecamatan, dekat apa">
                <p class="hint">Cukup patokan umum. Alamat lengkap dibahas lewat pesan.</p>
                <?= field_error('address') ?>
            </div>
        </div>
        <div class="field">
            <span class="label">Cara serah terima</span>
            <?php foreach ($cfg->handovers as $k => $label): ?>
                <label class="choice">
                    <input type="radio" name="handover" value="<?= $k ?>" <?= $v('handover', 'pickup') === $k ? 'checked' : '' ?>>
                    <span><?= esc($label) ?></span>
                </label>
            <?php endforeach; ?>
            <?= field_error('handover') ?>
        </div>
    </div>

    <div class="flex">
        <button class="btn btn-primary" type="submit"><?= esc($submit) ?></button>
        <a class="btn btn-ghost" href="<?= site_url('my/listings') ?>">Batal</a>
    </div>
</form>
