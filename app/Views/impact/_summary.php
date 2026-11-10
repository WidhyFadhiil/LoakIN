<?php /** @var array $s */ $max = max(1, ...array_values($s['by_category'] ?: [1])); $mmax = max(1, ...array_values($s['monthly'])); ?>
<div class="stat-grid">
    <div class="stat"><strong><?= esc(kg($s['total_kg'])) ?></strong><span>barang berpindah tangan</span></div>
    <div class="stat"><strong><?= esc(kg($s['total_co2'])) ?></strong><span>estimasi CO2 yang dihemat</span></div>
    <div class="stat"><strong><?= (int) $s['trees'] ?></strong><span>setara penyerapan pohon selama setahun</span></div>
    <div class="stat"><strong><?= (int) $s['count'] ?></strong><span>transaksi selesai</span></div>
</div>
<?php if ($s['count'] === 0): ?>
    <div class="empty"><h3>Belum ada transaksi selesai</h3><p>Dampak dihitung dari barang yang sudah dikonfirmasi selesai oleh penyetor.</p></div>
<?php else: ?>
<div class="row">
    <div class="panel">
        <h3>Berat per kategori</h3>
        <div class="bars">
            <?php foreach ($s['by_category'] as $name => $w): ?>
                <div class="bar-row"><span><?= esc($name) ?></span><div class="bar-track"><div class="bar-fill" style="width:<?= round($w / $max * 100) ?>%"></div></div><span class="right"><?= esc(kg($w)) ?></span></div>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="panel">
        <h3>6 bulan terakhir (kg)</h3>
        <div class="columns" role="img" aria-label="Grafik berat per bulan">
            <?php foreach ($s['monthly'] as $ym => $w): ?>
                <div class="col"><span><?= esc(rtrim(rtrim(number_format($w, 1, ',', ''), '0'), ',')) ?></span><i style="height:<?= max(2, round($w / $mmax * 100)) ?>%"></i><span><?= esc(date('M', strtotime($ym . '-01'))) ?></span></div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>
