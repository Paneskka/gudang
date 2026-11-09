<?php
// Data yang dibagikan ke papan publik hanya nomor, plat, dan waktu panggilan.
function panggilan_aktif(mysqli $conn): array
{
    $panggilan = ["bongkar" => null, "muat" => null];
    $result = $conn->query(
        "SELECT p.jenis_kegiatan, a.jenis_kegiatan AS jenis_antrian, a.id, a.nomor_antrian, a.plat_nomor, p.dipanggil_at
         FROM antrian_panggilan p
         JOIN antrian a ON a.id = p.antrian_id
         WHERE a.status = 'diproses'"
    );
    while ($row = $result->fetch_assoc()) {
        if ($row["jenis_antrian"] !== $row["jenis_kegiatan"]) continue;
        $panggilan[$row["jenis_kegiatan"]] = [
            "id" => (int) $row["id"],
            "nomor_antrian" => $row["nomor_antrian"],
            "plat_nomor" => $row["plat_nomor"],
            "dipanggil_at" => $row["dipanggil_at"],
        ];
    }
    return $panggilan;
}

function panggil_antrian(mysqli $conn, int $id, string $jenis): string
{
    $conn->begin_transaction();
    try {
        $stmt = $conn->prepare(
            "SELECT nomor_antrian FROM antrian
             WHERE id = ? AND jenis_kegiatan = ? AND status IN ('menunggu', 'diproses') FOR UPDATE"
        );
        $stmt->bind_param("is", $id, $jenis);
        $stmt->execute();
        $antrian = $stmt->get_result()->fetch_assoc();
        if (!$antrian) {
            throw new DomainException("Antrian tidak tersedia atau sudah selesai.");
        }

        $stmt = $conn->prepare("UPDATE antrian SET status = 'diproses' WHERE id = ? AND jenis_kegiatan = ?");
        $stmt->bind_param("is", $id, $jenis);
        $stmt->execute();

        // Mengganti panggilan pada jalur yang sama; jalur lain tetap independen.
        $stmt = $conn->prepare(
            "INSERT INTO antrian_panggilan (jenis_kegiatan, antrian_id, dipanggil_at) VALUES (?, ?, CURRENT_TIMESTAMP(6))
             ON DUPLICATE KEY UPDATE antrian_id = VALUES(antrian_id), dipanggil_at = VALUES(dipanggil_at)"
        );
        $stmt->bind_param("si", $jenis, $id);
        $stmt->execute();
        $conn->commit();
        return $antrian["nomor_antrian"];
    } catch (Throwable $e) {
        $conn->rollback();
        throw $e;
    }
}

// Dipanggil dalam transaksi perubahan status/penghapusan antrian.
function hapus_panggilan(mysqli $conn, int $id, string $jenis): void
{
    $stmt = $conn->prepare("DELETE FROM antrian_panggilan WHERE antrian_id = ? AND jenis_kegiatan = ?");
    $stmt->bind_param("is", $id, $jenis);
    $stmt->execute();
}
