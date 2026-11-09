<?php
$total_bongkar_grafik = array_sum(array_column($grafik_antrian, "bongkar"));
$total_muat_grafik = array_sum(array_column($grafik_antrian, "muat"));
$puncak_grafik = max(array_merge(array_column($grafik_antrian, "bongkar"), array_column($grafik_antrian, "muat")));
$langkah_grafik = max(1, (int) ceil($puncak_grafik / 4));
$batas_grafik = $langkah_grafik * 4;
$titik_grafik = ["bongkar" => [], "muat" => []];
$hari_grafik = [];
foreach ($grafik_antrian as $tanggal => $jumlah) {
    $index = count($hari_grafik);
    $x = number_format(($index + .5) * 100 / 7, 4, ".", "");
    $y = [];
    foreach (["bongkar", "muat"] as $jenis) {
        $y[$jenis] = number_format(100 - ($jumlah[$jenis] / $batas_grafik * 100), 4, ".", "");
        $titik_grafik[$jenis][] = $x . "," . $y[$jenis];
    }
    $hari_grafik[] = ["tanggal" => $tanggal, "jumlah" => $jumlah, "y" => $y];
}
$tanggal_grafik = array_keys($grafik_antrian);
$periode_grafik = ui_date($tanggal_grafik[0]) . " – " . ui_date($tanggal_grafik[6]);
?>
<section class="queue-chart-panel" aria-labelledby="queue-chart-title">
    <div class="queue-chart-header">
        <div><h2 id="queue-chart-title">Tren antrian</h2><p>Pendaftaran kendaraan dalam 7 hari terakhir.</p></div>
        <ul class="queue-chart-legend" aria-label="Total antrian selama periode grafik">
            <li><span class="chart-key chart-key-bongkar" aria-hidden="true"></span>Bongkar <strong><?= $total_bongkar_grafik ?></strong></li>
            <li><span class="chart-key chart-key-muat" aria-hidden="true"></span>Muat <strong><?= $total_muat_grafik ?></strong></li>
        </ul>
    </div>
    <div class="queue-chart-stage" role="group" aria-label="Grafik jumlah antrian per hari. Pilih tanggal untuk melihat rinciannya.">
        <div class="queue-chart-axis" aria-hidden="true">
            <?php for ($tick = 0; $tick <= 4; $tick++): ?>
                <span style="top: <?= $tick * 25 ?>%;"><?= $batas_grafik - ($tick * $langkah_grafik) ?></span>
            <?php endfor; ?>
        </div>
        <div class="queue-chart-plot">
            <svg class="queue-chart-lines" viewBox="0 0 100 100" preserveAspectRatio="none" aria-hidden="true" focusable="false">
                <?php for ($tick = 0; $tick <= 4; $tick++): ?>
                    <line class="chart-gridline" x1="0" y1="<?= $tick * 25 ?>" x2="100" y2="<?= $tick * 25 ?>" vector-effect="non-scaling-stroke"/>
                <?php endfor; ?>
                <polygon class="chart-area" points="7.1429,100 <?= implode(' ', $titik_grafik['bongkar']) ?> 92.8571,100"/>
                <polyline class="chart-line chart-line-bongkar" points="<?= implode(' ', $titik_grafik['bongkar']) ?>" vector-effect="non-scaling-stroke"/>
                <polyline class="chart-line chart-line-muat" points="<?= implode(' ', $titik_grafik['muat']) ?>" vector-effect="non-scaling-stroke"/>
            </svg>
            <div class="queue-chart-days">
                <?php foreach ($hari_grafik as $index => $hari): ?>
                    <?php $label_tanggal = explode(' ', ui_date($hari['tanggal'])); ?>
                    <button type="button" class="queue-chart-day" aria-label="Antrian tanggal <?= ui_date($hari['tanggal']) ?>" aria-describedby="chart-detail-<?= $index ?>" style="--bongkar-y: <?= $hari['y']['bongkar'] ?>%; --muat-y: <?= $hari['y']['muat'] ?>%;">
                        <span class="chart-dot chart-dot-bongkar" aria-hidden="true"></span>
                        <span class="chart-dot chart-dot-muat" aria-hidden="true"></span>
                        <span class="chart-date" aria-hidden="true"><span><?= htmlspecialchars($label_tanggal[0]) ?></span> <span><?= htmlspecialchars($label_tanggal[1]) ?></span></span>
                        <span class="chart-tooltip" id="chart-detail-<?= $index ?>" role="tooltip">
                            <strong><?= ui_date($hari['tanggal']) ?></strong>
                            <span>Bongkar <b><?= $hari['jumlah']['bongkar'] ?></b></span>
                            <span>Muat <b><?= $hari['jumlah']['muat'] ?></b></span>
                        </span>
                    </button>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <div class="queue-chart-footer">
        <p><?= $total_bongkar_grafik + $total_muat_grafik === 0 ? 'Belum ada pendaftaran pada periode ini.' : htmlspecialchars($periode_grafik) ?></p>
        <details class="queue-chart-data">
            <summary>Rincian data</summary>
            <div class="table-wrap">
                <table>
                    <caption class="sr-only">Jumlah pendaftaran antrian: <?= htmlspecialchars($periode_grafik) ?></caption>
                    <thead><tr><th scope="col">Tanggal</th><th scope="col">Bongkar</th><th scope="col">Muat</th></tr></thead>
                    <tbody>
                        <?php foreach ($hari_grafik as $hari): ?>
                            <tr><th scope="row"><?= ui_date($hari['tanggal']) ?></th><td><?= $hari['jumlah']['bongkar'] ?></td><td><?= $hari['jumlah']['muat'] ?></td></tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </details>
    </div>
</section>
<script src="<?= ui_asset('assets/js/dashboard-chart.js') ?>" defer></script>
