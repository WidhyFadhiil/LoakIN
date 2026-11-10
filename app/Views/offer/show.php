<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php
$st      = $offer['status'];
$price   = \App\Models\OfferModel::currentPrice($offer);
$other   = $isOwner ? ['name' => $offer['recycler_name'], 'phone' => $offer['recycler_phone'], 'id' => $offer['recycler_id']] : ['name' => $offer['owner_name'], 'phone' => $offer['owner_phone'], 'id' => $offer['owner_id']];
$progress = ['menunggu' => 1, 'counter' => 1, 'diterima' => 2, 'selesai' => 4];
$step = $progress[$st] ?? 0;
?>
<div class="mid">
    <p class="small"><a href="<?= site_url($isOwner ? 'offers/incoming' : 'my/offers') ?>">Kembali ke daftar</a></p>
    <div class="flex flex-between">
        <h1 class="mb-0"><?= esc($offer['listing_title']) ?></h1>
        <span class="badge <?= status_class($st) ?>"><?= esc(offer_status_label($st)) ?></span>
    </div>
    <p class="muted mt-1"><a href="<?= site_url('listings/' . $offer['listing_id']) ?>">Lihat postingan barang</a></p>

    <?php if ($step): ?>
    <ol class="steps" aria-label="Tahapan">
        <li class="<?= $step > 1 ? 'done' : 'now' ?>">Pengajuan dan tawar</li>
        <li class="<?= $step > 2 ? 'done' : ($step === 2 ? 'now' : '') ?>">Diproses, atur serah terima</li>
        <li class="<?= $step === 4 ? 'done' : '' ?>">Selesai</li>
        <li class="<?= $rated ? 'done' : ($step === 4 ? 'now' : '') ?>">Rating</li>
    </ol>
    <?php endif; ?>

    <div class="panel">
        <dl class="facts mt-0">
            <dt><?= $isOwner ? 'Pendaur ulang' : 'Penyetor' ?></dt>
            <dd><a href="<?= site_url('u/' . $other['id']) ?>"><?= esc($other['name']) ?></a></dd>
            <dt>Harga barang</dt>
            <dd><?= $offer['price_type'] === 'gratis' ? 'Gratis' : esc(rupiah($offer['listing_price'])) . ($offer['price_type'] === 'nego' ? ' (nego)' : '') ?></dd>
            <dt>Tawaran pendaur</dt><dd><?= $offer['offered_price'] > 0 ? esc(rupiah($offer['offered_price'])) : 'Gratis' ?></dd>
            <?php if ($offer['counter_price'] !== null): ?>
                <dt>Harga balasan</dt><dd><?= esc(rupiah($offer['counter_price'])) ?></dd>
            <?php endif; ?>
            <?php if ($offer['final_price'] !== null): ?>
                <dt>Harga disepakati</dt><dd><strong><?= $offer['final_price'] > 0 ? esc(rupiah($offer['final_price'])) : 'Gratis' ?></strong>, dibayar langsung saat serah terima</dd>
            <?php endif; ?>
            <dt>Serah terima</dt><dd><?= esc(config('Loakin')->handovers[$offer['handover']] ?? $offer['handover']) ?></dd>
        </dl>
        <?php if ($offer['note']): ?><p class="mb-0"><span class="muted">Pesan pengaju:</span> <?= esc($offer['note']) ?></p><?php endif; ?>
    </div>

    <?php /* ---------- Kontak WA: hanya setelah diterima ---------- */ ?>
    <?php if ($showPhone): ?>
        <div class="callout ok">
            <strong>Nomor WA sudah terbuka</strong>
            <?= esc($other['name']) ?>: <?= esc($other['phone']) ?>
            <?php if ($link = wa_link($other['phone'], 'Halo, saya dari LoakIN soal "' . $offer['listing_title'] . '".')): ?>
                <a class="btn btn-primary btn-sm" style="margin-left:.5rem" href="<?= esc($link, 'attr') ?>" target="_blank" rel="noopener noreferrer">Chat lewat WA</a>
            <?php endif; ?>
            <?php if (! empty($offer['listing_address']) && $isOwner === false): ?><div class="small mt-1">Patokan lokasi: <?= esc($offer['listing_address']) ?>, <?= esc($offer['listing_city']) ?></div><?php endif; ?>
        </div>
    <?php elseif (in_array($st, ['menunggu', 'counter'], true)): ?>
        <p class="small muted">Nomor WA kedua pihak baru terbuka setelah pengajuan diterima.</p>
    <?php endif; ?>

    <?php /* ---------- Aksi ---------- */ ?>
    <div class="panel">
        <h2>Tindakan</h2>
        <?php if ($isOwner): ?>
            <?php if ($st === 'menunggu'): ?>
                <div class="callout"><strong>Menunggu jawabanmu</strong><?= esc($offer['recycler_name']) ?> menawar <?= $offer['offered_price'] > 0 ? esc(rupiah($offer['offered_price'])) : 'gratis' ?>.</div>
                <div class="flex">
                    <form class="inline" action="<?= site_url('offers/' . $offer['id'] . '/accept') ?>" method="post" data-confirm="Terima pengajuan ini? Pengajuan lain akan otomatis ditolak.">
                        <?= csrf_field() ?><button class="btn btn-primary" type="submit">Terima<?= $offer['offered_price'] > 0 ? ' ' . esc(rupiah($offer['offered_price'])) : '' ?></button>
                    </form>
                    <form class="inline" action="<?= site_url('offers/' . $offer['id'] . '/reject') ?>" method="post" data-confirm="Tolak pengajuan ini?">
                        <?= csrf_field() ?><button class="btn btn-danger" type="submit">Tolak</button>
                    </form>
                </div>
                <?php if ($offer['price_type'] === 'nego'): ?>
                    <form class="mt-2" action="<?= site_url('offers/' . $offer['id'] . '/counter') ?>" method="post">
                        <?= csrf_field() ?>
                        <label for="counter_price">Atau balas dengan harga lain (Rp)</label>
                        <div class="flex"><input id="counter_price" style="max-width:220px" type="text" inputmode="numeric" name="counter_price" value="<?= esc((string) $offer['listing_price']) ?>"><button class="btn btn-tag" type="submit">Kirim harga balasan</button></div>
                    </form>
                <?php endif; ?>
            <?php elseif ($st === 'counter'): ?>
                <div class="callout"><strong>Menunggu pendaur ulang</strong>Kamu membalas dengan <?= esc(rupiah($offer['counter_price'])) ?>. Tunggu <?= esc($offer['recycler_name']) ?> menerima atau menawar lagi.</div>
                <form class="inline" action="<?= site_url('offers/' . $offer['id'] . '/reject') ?>" method="post" data-confirm="Tolak pengajuan ini?">
                    <?= csrf_field() ?><button class="btn btn-danger" type="submit">Tolak pengajuan</button>
                </form>
            <?php elseif ($st === 'diterima'): ?>
                <div class="callout"><strong>Atur serah terima</strong>Sepakati jadwal dan lokasi lewat pesan atau WA. Setelah barang benar-benar diserahkan, tekan konfirmasi supaya masuk dashboard dampak.</div>
                <div class="flex">
                    <form class="inline" action="<?= site_url('offers/' . $offer['id'] . '/complete') ?>" method="post" data-confirm="Barang sudah diserahkan dan harga sudah dibayar? Konfirmasi tidak bisa dibatalkan.">
                        <?= csrf_field() ?><button class="btn btn-primary" type="submit">Konfirmasi selesai</button>
                    </form>
                    <form class="inline" action="<?= site_url('offers/' . $offer['id'] . '/cancel-deal') ?>" method="post" data-confirm="Batalkan kesepakatan? Barang kembali berstatus Tersedia.">
                        <?= csrf_field() ?><button class="btn btn-danger" type="submit">Batalkan kesepakatan</button>
                    </form>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <?php if ($st === 'menunggu'): ?>
                <p>Pengajuanmu sudah terkirim. Menunggu jawaban penyetor.</p>
                <form class="inline" action="<?= site_url('offers/' . $offer['id'] . '/withdraw') ?>" method="post" data-confirm="Batalkan pengajuan ini?">
                    <?= csrf_field() ?><button class="btn btn-danger" type="submit">Batalkan pengajuan</button>
                </form>
            <?php elseif ($st === 'counter'): ?>
                <div class="callout"><strong>Penyetor membalas dengan <?= esc(rupiah($offer['counter_price'])) ?></strong>Terima harga itu, tawar lagi, atau batalkan.</div>
                <div class="flex">
                    <form class="inline" action="<?= site_url('offers/' . $offer['id'] . '/accept-counter') ?>" method="post">
                        <?= csrf_field() ?><button class="btn btn-primary" type="submit">Terima <?= esc(rupiah($offer['counter_price'])) ?></button>
                    </form>
                    <form class="inline" action="<?= site_url('offers/' . $offer['id'] . '/withdraw') ?>" method="post" data-confirm="Batalkan pengajuan ini?">
                        <?= csrf_field() ?><button class="btn btn-danger" type="submit">Batalkan pengajuan</button>
                    </form>
                </div>
                <form class="mt-2" action="<?= site_url('offers/' . $offer['id'] . '/rebid') ?>" method="post">
                    <?= csrf_field() ?>
                    <label for="offered_price">Tawar lagi (Rp)</label>
                    <div class="flex"><input id="offered_price" style="max-width:220px" type="text" inputmode="numeric" name="offered_price" value="<?= esc((string) $offer['counter_price']) ?>"><button class="btn btn-tag" type="submit">Kirim tawaran baru</button></div>
                </form>
            <?php elseif ($st === 'diterima'): ?>
                <div class="callout ok"><strong>Pengajuanmu diterima</strong>Atur jadwal dan lokasi dengan penyetor. Penyetor yang menekan konfirmasi selesai setelah serah terima.</div>
                <form class="inline" action="<?= site_url('offers/' . $offer['id'] . '/cancel-deal') ?>" method="post" data-confirm="Batalkan kesepakatan?">
                    <?= csrf_field() ?><button class="btn btn-danger" type="submit">Batalkan kesepakatan</button>
                </form>
            <?php endif; ?>
        <?php endif; ?>

        <?php if ($st === 'selesai'): ?>
            <div class="callout ok"><strong>Transaksi selesai</strong>Berat barang sudah masuk ke dashboard dampak lingkungan.</div>
            <?php if (! $rated): ?>
                <a class="btn btn-tag" href="<?= site_url('ratings/' . $offer['id']) ?>">Beri rating untuk <?= esc($other['name']) ?></a>
            <?php else: ?>
                <p class="mb-0 muted">Kamu sudah memberi rating. Terima kasih.</p>
            <?php endif; ?>
        <?php elseif (in_array($st, ['ditolak', 'dibatalkan'], true)): ?>
            <p class="mb-0 muted">Pengajuan ini sudah <?= $st === 'ditolak' ? 'ditolak' : 'dibatalkan' ?>. <a href="<?= site_url('listings') ?>">Cari barang lain</a>.</p>
        <?php endif; ?>
    </div>

    <?php /* ---------- Pesan ---------- */ ?>
    <div class="panel" id="pesan">
        <h2>Pesan</h2>
        <?php if (! $messages): ?>
            <p class="muted">Belum ada pesan. Tanyakan jadwal, lokasi, atau kondisi barang di sini. Percakapan tersimpan sebagai bukti kalau ada masalah.</p>
        <?php else: ?>
            <div class="thread" tabindex="0" aria-label="Riwayat pesan">
                <?php foreach ($messages as $m): ?>
                    <div class="bubble<?= (int) $m['sender_id'] === (int) session('user_id') ? ' me' : '' ?>">
                        <div class="who-line"><?= esc($m['sender_name']) ?>, <?= esc(time_ago($m['created_at'])) ?></div>
                        <p><?= esc($m['body']) ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <?php if ($canChat): ?>
            <form action="<?= site_url('offers/' . $offer['id'] . '/messages') ?>" method="post">
                <?= csrf_field() ?>
                <label class="sr-only" for="body">Tulis pesan</label>
                <textarea id="body" name="body" rows="3" maxlength="1000" placeholder="Tulis pesan..." required></textarea>
                <button class="btn btn-primary mt-1" type="submit">Kirim pesan</button>
            </form>
        <?php else: ?>
            <p class="small muted mb-0">Percakapan ditutup karena pengajuan sudah <?= esc(offer_status_label($st)) ?>.</p>
        <?php endif; ?>
    </div>
</div>
<?= $this->endSection() ?>
