<?php
// Komponen modal untuk input barang saat menyelesaikan antrian
// Dipanggil oleh antrian_page.php

// Ambil daftar barang untuk dropdown
$list_barang = [];
$res_tbl = $conn->query("SHOW TABLES LIKE 'barang'");
if ($res_tbl && $res_tbl->num_rows > 0) {
    $res_barang = $conn->query("SELECT id, kode_barang, nama_barang, satuan, jumlah FROM barang ORDER BY nama_barang ASC");
    if ($res_barang && $res_barang->num_rows > 0) {
        $list_barang = $res_barang->fetch_all(MYSQLI_ASSOC);
    }
}
?>

<!-- Modal Input Barang -->
<div id="modalBarang" class="modal-overlay" style="display:none;">
    <div class="modal-box">
        <div class="modal-header">
            <h3 id="modalTitle">Input Barang</h3>
            <button type="button" class="modal-close" onclick="tutupModal()">&times;</button>
        </div>

        <form id="formBarangAntrian" method="post">
            <input type="hidden" name="aksi" value="simpan_barang">
            <input type="hidden" name="antrian_id" id="inputAntrianId" value="">
            <input type="hidden" name="jenis_kegiatan_modal" id="inputJenisKegiatan" value="">

            <!-- Daftar barang yang akan ditambahkan -->
            <div id="listBarangContainer">
                <p class="modal-info">Belum ada barang ditambahkan.</p>
            </div>

            <!-- Form tambah barang -->
            <div class="modal-form-row">
                <div class="field" style="flex:0 0 40%;">
                    <label for="pilihBarang">Pilih Barang</label>
                    <select id="pilihBarang" onchange="pilihBarangChange()">
                        <option value="">-- Pilih dari Data Barang --</option>
                        <?php foreach ($list_barang as $b): ?>
                            <option value="<?= $b['id'] ?>"
                                    data-kode="<?= htmlspecialchars($b['kode_barang']) ?>"
                                    data-nama="<?= htmlspecialchars($b['nama_barang']) ?>"
                                    data-satuan="<?= htmlspecialchars($b['satuan'] ?? '') ?>"
                                    data-stok="<?= $b['jumlah'] ?>">
                                <?= htmlspecialchars($b['kode_barang']) ?> - <?= htmlspecialchars($b['nama_barang']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="field" style="flex:1;">
                    <label for="namaBarangManual">Nama Barang (jika manual)</label>
                    <input type="text" id="namaBarangManual" placeholder="Ketik nama barang baru...">
                </div>
            </div>

            <div class="modal-form-row">
                <div class="field" style="flex:0 0 30%;">
                    <label for="jumlahBarang">Jumlah</label>
                    <input type="number" id="jumlahBarang" min="1" value="1" placeholder="0">
                </div>
                <div class="field" style="flex:0 0 25%;">
                    <label for="satuanBarang">Satuan</label>
                    <input type="text" id="satuanBarang" placeholder="pcs, kg, dll">
                </div>
                <div class="field" style="flex:1; align-self:flex-end;">
                    <button type="button" class="btn btn-primary" onclick="tambahBarangKeList()">
                        + Tambah
                    </button>
                </div>
            </div>

            <div class="modal-actions">
                <button type="submit" class="btn btn-success" id="btnSimpanBarang">
                    Simpan & Selesaikan
                </button>
                <button type="button" class="btn btn-ghost" onclick="tutupModal()">Batal</button>
            </div>
        </form>
    </div>
</div>

<style>
/* Modal Styles */
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
}

.modal-box {
    background: #fff;
    border-radius: 12px;
    width: 90%;
    max-width: 700px;
    max-height: 90vh;
    overflow: hidden;
    box-shadow: 0 20px 60px rgba(0,0,0,0.3);
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px 24px;
    background: #f8f9fa;
    border-bottom: 1px solid #e0e0e0;
}

.modal-header h3 {
    margin: 0;
    font-size: 18px;
    color: #333;
}

.modal-close {
    background: none;
    border: none;
    font-size: 28px;
    cursor: pointer;
    color: #666;
    line-height: 1;
}

.modal-close:hover {
    color: #333;
}

.modal-box form {
    padding: 20px 24px;
    max-height: 70vh;
    overflow-y: auto;
}

.modal-info {
    background: #f0f4ff;
    padding: 12px 16px;
    border-radius: 8px;
    color: #666;
    font-size: 14px;
    margin-bottom: 16px;
}

.modal-form-row {
    display: flex;
    gap: 12px;
    margin-bottom: 12px;
    align-items: flex-end;
}

.modal-form-row .field {
    margin-bottom: 0;
}

.modal-actions {
    display: flex;
    gap: 12px;
    padding-top: 16px;
    border-top: 1px solid #e0e0e0;
    margin-top: 16px;
}

/* Item List Styles */
.item-list {
    background: #f8f9fa;
    border-radius: 8px;
    margin-bottom: 16px;
    max-height: 200px;
    overflow-y: auto;
}

.item-list:empty:before {
    content: "Belum ada barang ditambahkan.";
    display: block;
    padding: 16px;
    color: #999;
    font-style: italic;
}

.item-entry {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px 14px;
    border-bottom: 1px solid #e8e8e8;
    gap: 12px;
}

.item-entry:last-child {
    border-bottom: none;
}

.item-entry-info {
    flex: 1;
}

.item-entry-nama {
    font-weight: 600;
    color: #333;
    font-size: 14px;
}

.item-entry-detail {
    color: #777;
    font-size: 12px;
    margin-top: 2px;
}

.item-entry-jumlah {
    background: #e3f2fd;
    color: #1565c0;
    padding: 4px 10px;
    border-radius: 6px;
    font-weight: 600;
    font-size: 14px;
    white-space: nowrap;
}

.item-entry-remove {
    background: #ffebee;
    color: #c62828;
    border: none;
    width: 28px;
    height: 28px;
    border-radius: 50%;
    cursor: pointer;
    font-size: 18px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.item-entry-remove:hover {
    background: #ffcdd2;
}

/* Warning for empty items */
.warning-empty {
    background: #fff3cd;
    color: #856404;
    padding: 10px 14px;
    border-radius: 8px;
    font-size: 13px;
    margin-bottom: 12px;
    display: none;
}
</style>

<!-- Modal Detail Barang -->
<div id="modalDetail" class="modal-overlay" style="display:none;">
    <div class="modal-box modal-box-sm">
        <div class="modal-header">
            <h3 id="detailTitle">Detail Barang</h3>
            <button type="button" class="modal-close" onclick="tutupDetail()">&times;</button>
        </div>
        <div class="modal-body">
            <div id="detailListContainer">
                <p class="modal-info">Memuat data...</p>
            </div>
        </div>
    </div>
</div>

<style>
.modal-box-sm {
    max-width: 480px;
}
.modal-body {
    padding: 20px 24px;
    max-height: 60vh;
    overflow-y: auto;
}
.detail-table {
    width: 100%;
    border-collapse: collapse;
}
.detail-table th, .detail-table td {
    padding: 10px 12px;
    text-align: left;
    border-bottom: 1px solid #eee;
    font-size: 14px;
    vertical-align: top;
}
.detail-table th {
    color: #666;
    font-weight: 600;
    background: #f8f9fa;
    white-space: nowrap;
}
.detail-table td.nama-brg {
    font-weight: 600;
    color: #333;
}
.detail-table td.jumlah-brg {
    white-space: nowrap;
    font-weight: 600;
    color: #1565c0;
}
</style>

<script>
function bukaDetail(antrianId, nomorAntrian) {
    document.getElementById('detailTitle').textContent = 'Detail Barang - ' + nomorAntrian;
    document.getElementById('modalDetail').style.display = 'flex';

    const container = document.getElementById('detailListContainer');
    const data = window.detailBarangData || {};

    if (!data[antrianId] || data[antrianId].length === 0) {
        container.innerHTML = '<p class="modal-info">Belum ada data barang.</p>';
        return;
    }

    const items = data[antrianId];
    let html = '<table class="detail-table"><thead><tr>';
    html += '<th>Nama Barang</th><th>Jumlah</th><th>Satuan</th>';
    html += '</tr></thead><tbody>';

    items.forEach(item => {
        html += '<tr>';
        html += '<td class="nama-brg">' + escapeHtml(item.nama || '-') + '</td>';
        html += '<td class="jumlah-brg">' + escapeHtml(String(item.jumlah)) + '</td>';
        html += '<td>' + escapeHtml(item.satuan || '-') + '</td>';
        html += '</tr>';
    });

    html += '</tbody></table>';
    container.innerHTML = html;
}

function tutupDetail() {
    document.getElementById('modalDetail').style.display = 'none';
}

// Close detail modal on outside click
document.getElementById('modalDetail').addEventListener('click', function(e) {
    if (e.target === this) {
        tutupDetail();
    }
});

// Data barang dari PHP
const dataBarang = <?= json_encode($list_barang) ?>;
let barangList = [];
let editIndex = -1;

function bukaModal(antrianId, jenisKegiatan, currentStatus) {
    document.getElementById('modalBarang').style.display = 'flex';
    document.getElementById('inputAntrianId').value = antrianId;
    document.getElementById('inputJenisKegiatan').value = jenisKegiatan;

    // Reset form
    barangList = [];
    document.getElementById('pilihBarang').value = '';
    document.getElementById('namaBarangManual').value = '';
    document.getElementById('jumlahBarang').value = '1';
    document.getElementById('satuanBarang').value = '';

    renderBarangList();

    // Scroll to top
    document.querySelector('.modal-box form').scrollTop = 0;
}

function tutupModal() {
    document.getElementById('modalBarang').style.display = 'none';
    barangList = [];
}

function pilihBarangChange() {
    const select = document.getElementById('pilihBarang');
    const selected = select.options[select.selectedIndex];

    if (selected.value) {
        document.getElementById('namaBarangManual').value = selected.dataset.nama;
        document.getElementById('satuanBarang').value = selected.dataset.satuan || '';
        document.getElementById('namaBarangManual').readOnly = true;
    } else {
        document.getElementById('namaBarangManual').value = '';
        document.getElementById('satuanBarang').value = '';
        document.getElementById('namaBarangManual').readOnly = false;
    }
}

function tambahBarangKeList() {
    const selectBarang = document.getElementById('pilihBarang');
    const namaManual = document.getElementById('namaBarangManual').value.trim();
    const jumlah = parseInt(document.getElementById('jumlahBarang').value) || 0;
    const satuan = document.getElementById('satuanBarang').value.trim();

    let barangId = selectBarang.value ? parseInt(selectBarang.value) : null;
    let kodeBarang = '';
    let namaBarang = '';

    if (barangId) {
        const selected = selectBarang.options[selectBarang.selectedIndex];
        kodeBarang = selected.dataset.kode;
        namaBarang = selected.dataset.nama;
    } else if (namaManual) {
        namaBarang = namaManual;
        kodeBarang = 'MANUAL';
    } else {
        alert('Pilih barang atau masukkan nama barang!');
        return;
    }

    if (jumlah <= 0) {
        alert('Jumlah harus lebih dari 0!');
        return;
    }

    // Cek duplikat
    const duplikat = barangList.find(b => b.namaBarang === namaBarang);
    if (duplikat) {
        duplikat.jumlah += jumlah;
    } else {
        barangList.push({
            barangId: barangId,
            kodeBarang: kodeBarang,
            namaBarang: namaBarang,
            jumlah: jumlah,
            satuan: satuan
        });
    }

    // Reset input
    selectBarang.value = '';
    document.getElementById('namaBarangManual').value = '';
    document.getElementById('namaBarangManual').readOnly = false;
    document.getElementById('jumlahBarang').value = '1';
    document.getElementById('satuanBarang').value = '';

    renderBarangList();
}

function hapusBarang(index) {
    barangList.splice(index, 1);
    renderBarangList();
}

function renderBarangList() {
    const container = document.getElementById('listBarangContainer');

    if (barangList.length === 0) {
        container.innerHTML = '<p class="modal-info">Belum ada barang ditambahkan. Pilih barang atau ketik manual, lalu klik "Tambah".</p>';
        return;
    }

    let html = '<div class="item-list">';
    barangList.forEach((b, i) => {
        html += `
            <div class="item-entry">
                <div class="item-entry-info">
                    <div class="item-entry-nama">${escapeHtml(b.namaBarang)}</div>
                    <div class="item-entry-detail">
                        ${b.kodeBarang !== 'MANUAL' ? 'Kode: ' + escapeHtml(b.kodeBarang) + ' | ' : ''}
                        Satuan: ${escapeHtml(b.satuan) || '-'}
                    </div>
                </div>
                <div class="item-entry-jumlah">${b.jumlah} ${escapeHtml(b.satuan)}</div>
                <button type="button" class="item-entry-remove" onclick="hapusBarang(${i})" title="Hapus">&times;</button>
            </div>
        `;
    });
    html += '</div>';
    container.innerHTML = html;
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Handle form submit
document.getElementById('formBarangAntrian').addEventListener('submit', function(e) {
    e.preventDefault();

    if (barangList.length === 0) {
        alert('Minimal harus ada 1 barang yang ditambahkan!');
        return;
    }

    // Create hidden inputs for each item
    const form = this;
    // Remove old hidden inputs
    form.querySelectorAll('input[name^="items"]').forEach(el => el.remove());

    barangList.forEach((b, i) => {
        ['barangId', 'kodeBarang', 'namaBarang', 'jumlah', 'satuan'].forEach(key => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = `items[${i}][${key}]`;
            input.value = b[key];
            form.appendChild(input);
        });
    });

    // Submit the form
    form.submit();
});

// Close modal on outside click
document.getElementById('modalBarang').addEventListener('click', function(e) {
    if (e.target === this) {
        tutupModal();
    }
});
</script>
