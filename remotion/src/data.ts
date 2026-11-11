export type AntrianRow = {
  nomor: string;
  driver: string;
  plat: string;
  perusahaan: string;
  kegiatan: "Bongkar" | "Muat";
  status: "Menunggu" | "Diproses" | "Selesai";
  jam: string;
};

export const ANTRIAN: AntrianRow[] = [
  {
    nomor: "B001",
    driver: "Sugianto",
    plat: "B 9012 KHD",
    perusahaan: "PT Logistik Nusantara",
    kegiatan: "Bongkar",
    status: "Diproses",
    jam: "08:12",
  },
  {
    nomor: "M001",
    driver: "Rahmat Hidayat",
    plat: "H 8817 UA",
    perusahaan: "CV Sinar Jaya",
    kegiatan: "Muat",
    status: "Menunggu",
    jam: "08:24",
  },
  {
    nomor: "B002",
    driver: "Dwi Prasetyo",
    plat: "AB 2145 TK",
    perusahaan: "PT Trans Semarang",
    kegiatan: "Bongkar",
    status: "Selesai",
    jam: "08:41",
  },
  {
    nomor: "B003",
    driver: "Yusuf Maulana",
    plat: "K 7740 QP",
    perusahaan: "PT Logistik Nusantara",
    kegiatan: "Bongkar",
    status: "Menunggu",
    jam: "09:02",
  },
];

export type BarangRow = {
  kode: string;
  nama: string;
  stok: number;
  satuan: string;
  status: "Aman" | "Menipis";
};

export const BARANG: BarangRow[] = [
  { kode: "BRG-001", nama: "Semen Portland 50 kg", stok: 240, satuan: "sak", status: "Aman" },
  { kode: "BRG-002", nama: "Karton Indomie Goreng", stok: 86, satuan: "karton", status: "Menipis" },
  { kode: "BRG-003", nama: "Air Mineral 600 ml", stok: 512, satuan: "dus", status: "Aman" },
  { kode: "BRG-004", nama: "Beras Premium 25 kg", stok: 38, satuan: "karung", status: "Menipis" },
];

export const CHART = {
  labels: ["Sen", "Sel", "Rab", "Kam", "Jum", "Sab", "Min"],
  bongkar: [6, 9, 7, 12, 10, 14, 8],
  muat: [4, 6, 5, 9, 7, 11, 6],
};

export const LAPORAN = [
  { tanggal: "03 Nov", bongkar: 9, muat: 6, selesai: 15 },
  { tanggal: "04 Nov", bongkar: 7, muat: 5, selesai: 12 },
  { tanggal: "05 Nov", bongkar: 12, muat: 9, selesai: 21 },
  { tanggal: "06 Nov", bongkar: 10, muat: 7, selesai: 17 },
  { tanggal: "07 Nov", bongkar: 14, muat: 11, selesai: 25 },
];

export const FORM = {
  driver: "Sugianto",
  plat: "B 9012 KHD",
  perusahaan: "PT Logistik Nusantara",
  kegiatan: "Bongkar — barang masuk",
};
