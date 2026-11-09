<?php
// Modal input dan detail barang, dipakai kedua halaman antrian.
$list_barang = [];
$res_tbl = $conn->query("SHOW TABLES LIKE 'barang'");
if ($res_tbl && $res_tbl->num_rows > 0) {
    $list_barang = $conn->query("SELECT id, kode_barang, nama_barang, satuan, jumlah FROM barang ORDER BY nama_barang ASC")->fetch_all(MYSQLI_ASSOC);
}
?>
<div id="modalBarang" class="modal-overlay" style="display:none;">
    <div class="modal-box" role="dialog" aria-modal="true" aria-labelledby="modalTitle">
        <div class="modal-header">
            <h3 id="modalTitle">Periksa barang antrian</h3>
            <button type="button" class="modal-close" onclick="tutupModal()" aria-label="Tutup input barang">&times;</button>
        </div>
        <form id="formBarangAntrian" method="post">
            <input type="hidden" name="aksi" value="simpan_barang">
            <input type="hidden" name="antrian_id" id="inputAntrianId" value="">
            <input type="hidden" name="jenis_kegiatan_modal" id="inputJenisKegiatan" value="">
            <div id="listBarangContainer" aria-live="polite"></div>
            <div id="itemError" class="alert alert-error" role="alert" hidden></div>
            <div class="modal-form-row">
                <div class="field" style="flex:1;">
                    <label for="pilihBarang">Pilih dari inventaris</label>
                    <select id="pilihBarang" onchange="pilihBarangChange()">
                        <option value="">Pilih barang...</option>
                        <?php foreach ($list_barang as $b): ?>
                            <option value="<?= (int) $b['id'] ?>" data-kode="<?= htmlspecialchars($b['kode_barang']) ?>" data-nama="<?= htmlspecialchars($b['nama_barang']) ?>" data-satuan="<?= htmlspecialchars($b['satuan'] ?? '') ?>">
                                <?= htmlspecialchars($b['kode_barang'] . ' — ' . $b['nama_barang']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="field" style="flex:1;">
                    <label for="namaBarangManual">Atau tulis nama barang</label>
                    <input type="text" id="namaBarangManual" maxlength="120" placeholder="Nama barang manual">
                </div>
            </div>
            <div class="modal-form-row">
                <div class="field" style="flex:1;">
                    <label for="jumlahBarang">Jumlah</label>
                    <input type="number" id="jumlahBarang" min="1" step="1" value="1">
                </div>
                <div class="field" style="flex:1;">
                    <label for="satuanBarang">Satuan</label>
                    <input type="text" id="satuanBarang" maxlength="20" placeholder="pcs, dus, kg">
                </div>
                <div class="field"><button type="button" class="btn btn-ghost" onclick="tambahBarangKeList()"><?= ui_icon('plus') ?> Tambah</button></div>
            </div>
            <div class="modal-actions">
                <button type="submit" class="btn btn-primary" id="btnSimpanBarang" disabled><?= ui_icon('check') ?> Simpan & Selesaikan</button>
                <button type="button" class="btn btn-ghost" onclick="tutupModal()">Batal</button>
            </div>
        </form>
    </div>
</div>
<div id="modalDetail" class="modal-overlay" style="display:none;">
    <div class="modal-box modal-box-sm" role="dialog" aria-modal="true" aria-labelledby="detailTitle">
        <div class="modal-header">
            <h3 id="detailTitle">Detail barang</h3>
            <button type="button" class="modal-close" onclick="tutupDetail()" aria-label="Tutup detail barang">&times;</button>
        </div>
        <div class="modal-body"><div id="detailListContainer"></div></div>
    </div>
</div>
<script>
let barangList = [];
let activeModal = null;
let modalTrigger = null;

function tampilkanDialog(id) {
    modalTrigger = document.activeElement;
    activeModal = document.getElementById(id);
    activeModal.style.display = 'flex';
    document.body.classList.add('modal-open');
    activeModal.querySelector('.modal-close').focus();
}

function tutupDialog() {
    if (!activeModal) return;
    activeModal.style.display = 'none';
    activeModal = null;
    document.body.classList.remove('modal-open');
    modalTrigger?.focus();
}

function bukaDetail(antrianId, nomorAntrian) {
    document.getElementById('detailTitle').textContent = 'Barang antrian ' + nomorAntrian;
    const container = document.getElementById('detailListContainer');
    const items = (window.detailBarangData || {})[antrianId] || [];
    if (!items.length) {
        container.innerHTML = '<p class="modal-info">Belum ada data barang untuk antrian ini.</p>';
    } else {
        container.innerHTML = '<table class="detail-table"><thead><tr><th>Nama barang</th><th>Jumlah</th><th>Satuan</th></tr></thead><tbody>' + items.map(item =>
            '<tr><td class="nama-brg">' + escapeHtml(item.nama || '-') + '</td><td class="jumlah-brg">' + escapeHtml(String(item.jumlah)) + '</td><td>' + escapeHtml(item.satuan || '-') + '</td></tr>'
        ).join('') + '</tbody></table>';
    }
    tampilkanDialog('modalDetail');
}

function tutupDetail() { tutupDialog(); }

function bukaModal(antrianId, jenisKegiatan) {
    document.getElementById('inputAntrianId').value = antrianId;
    document.getElementById('inputJenisKegiatan').value = jenisKegiatan;
    document.getElementById('modalTitle').textContent = jenisKegiatan === 'bongkar' ? 'Periksa barang masuk' : 'Periksa barang keluar';
    barangList = [];
    resetInputBarang();
    renderBarangList();
    tampilkanDialog('modalBarang');
    document.getElementById('formBarangAntrian').scrollTop = 0;
}

function tutupModal() { tutupDialog(); barangList = []; }

function resetInputBarang() {
    document.getElementById('pilihBarang').value = '';
    document.getElementById('namaBarangManual').value = '';
    document.getElementById('namaBarangManual').readOnly = false;
    document.getElementById('jumlahBarang').value = '1';
    document.getElementById('satuanBarang').value = '';
    document.getElementById('itemError').hidden = true;
}

function pilihBarangChange() {
    const select = document.getElementById('pilihBarang');
    const selected = select.options[select.selectedIndex];
    document.getElementById('namaBarangManual').value = selected.value ? selected.dataset.nama : '';
    document.getElementById('satuanBarang').value = selected.value ? selected.dataset.satuan : '';
    document.getElementById('namaBarangManual').readOnly = Boolean(selected.value);
}

function tampilkanErrorBarang(message) {
    const error = document.getElementById('itemError');
    error.textContent = message;
    error.hidden = false;
}

function tambahBarangKeList() {
    const select = document.getElementById('pilihBarang');
    const selected = select.options[select.selectedIndex];
    const nama = select.value ? selected.dataset.nama : document.getElementById('namaBarangManual').value.trim();
    const jumlah = Number(document.getElementById('jumlahBarang').value);
    if (!nama) { tampilkanErrorBarang('Pilih barang atau tulis nama barang terlebih dahulu.'); return; }
    if (!Number.isInteger(jumlah) || jumlah < 1) { tampilkanErrorBarang('Jumlah barang harus berupa bilangan bulat minimal 1.'); return; }
    const duplikat = barangList.find(item => item.namaBarang === nama);
    if (duplikat) duplikat.jumlah += jumlah;
    else barangList.push({
        barangId: select.value ? Number(select.value) : null,
        kodeBarang: select.value ? selected.dataset.kode : 'MANUAL',
        namaBarang: nama,
        jumlah,
        satuan: document.getElementById('satuanBarang').value.trim()
    });
    resetInputBarang();
    renderBarangList();
    select.focus();
}

function hapusBarang(index) {
    barangList.splice(index, 1);
    renderBarangList();
    const buttons = document.querySelectorAll('.item-entry-remove');
    (buttons[Math.min(index, buttons.length - 1)] || document.getElementById('pilihBarang')).focus();
}

function renderBarangList() {
    const container = document.getElementById('listBarangContainer');
    document.getElementById('btnSimpanBarang').disabled = barangList.length === 0;
    if (!barangList.length) {
        container.innerHTML = '<p class="modal-info">Tambahkan barang yang sudah diperiksa. Stok akan diperbarui saat antrian diselesaikan.</p>';
        return;
    }
    container.innerHTML = '<div class="item-list">' + barangList.map((b, i) => `
        <div class="item-entry">
            <div class="item-entry-info"><div class="item-entry-nama">${escapeHtml(b.namaBarang)}</div><div class="item-entry-detail">${escapeHtml(b.kodeBarang)}</div></div>
            <div class="item-entry-jumlah">${b.jumlah} ${escapeHtml(b.satuan)}</div>
            <button type="button" class="item-entry-remove" onclick="hapusBarang(${i})" aria-label="Hapus barang ke-${i + 1}">&times;</button>
        </div>`).join('') + '</div>';
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

document.getElementById('formBarangAntrian').addEventListener('submit', function(event) {
    event.preventDefault();
    if (!barangList.length) { tampilkanErrorBarang('Tambahkan minimal satu barang.'); return; }
    this.querySelectorAll('input[name^="items"]').forEach(input => input.remove());
    barangList.forEach((b, i) => {
        ['barangId', 'kodeBarang', 'namaBarang', 'jumlah', 'satuan'].forEach(key => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = `items[${i}][${key}]`;
            input.value = b[key] ?? '';
            this.appendChild(input);
        });
    });
    this.submit();
});

document.querySelectorAll('.modal-overlay').forEach(overlay => {
    overlay.addEventListener('click', event => { if (event.target === overlay) tutupDialog(); });
});
document.addEventListener('keydown', event => {
    if (!activeModal) return;
    if (event.key === 'Escape') tutupDialog();
    if (event.key === 'Tab') {
        const controls = [...activeModal.querySelectorAll('button:not([disabled]), input:not([type="hidden"]), select')].filter(el => el.getClientRects().length);
        const first = controls[0], last = controls[controls.length - 1];
        if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last.focus(); }
        else if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first.focus(); }
    }
});
</script>
