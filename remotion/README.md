# GudangPromo — video Remotion

Video promosi vertikal (1080×1920, 30 fps) untuk **Sistem Antrian Gudang**.
UI di-recreate jadi komponen React memakai token warna dan ikon asli aplikasi
(`assets/css/base.css`, `config/ui.php`), jadi tidak butuh screenshot.

> **Setup:** `npm install --ignore-scripts` — `msedge-tts` punya preinstall script
> yang minta `pnpm`; flag `--ignore-scripts` menghindari itu tanpa dampak lain.

## Isi video

| # | Scene | Durasi | Isi |
|---|-------|--------|-----|
| 1 | Intro | ~3s | Logo `Gudang.` + tagline, latar hijau gelap |
| 2 | Halaman publik | ~8s | Hero + papan antrian `B001`/`M001`, dot live |
| 3 | Pendaftaran | ~9s | Form terisi, klik tombol, tiket nomor `B002` |
| 4 | Login | ~5s | Kartu login petugas + role admin/petugas |
| 5 | Dashboard | ~9s | Banner antrian aktif, 4 stat count-up, grafik 7 hari, aktivitas |
| 6 | Data barang | ~7s | Tabel stok + modal tambah barang + toast |
| 7 | Laporan | ~5s | Rekap bongkar/muat + bar beban + total periode |
| 8 | Outro | ~5s | 3 langkah pemakaian + URL aplikasi |

Durasi tiap scene dihitung otomatis dari panjang narasi (`src/vo-durations.json`).

## Perintah

```bash
npm install --ignore-scripts
npm run fonts      # unduh woff2 Space Grotesk + Inter ke public/fonts (sekali)
npm run tts        # buat public/vo/*.mp3 + src/vo-durations.json
npm run dev        # Remotion Studio (preview interaktif, http://localhost:3000)
npm run render     # out/gudang-promo.mp4
npm run typecheck
```

Render masih (frame tertentu):

```bash
npx remotion still GudangPromo out/frame-120.png --frame=120
```

Render potongan cepat untuk cek:

```bash
npx remotion render GudangPromo out/preview.mp4 --frames=0-120
```

## Narasi

`scripts/tts.mjs` memakai Microsoft Edge Read Aloud (gratis, tanpa API key) dengan
voice `id-ID-ArdiNeural`. Ganti voice/kecepatan:

```bash
node scripts/tts.mjs --voice id-ID-GadisNeural --rate -2%
```

Teks narasi ada di `src/narasi.json` — satu file, dipakai TTS sekaligus caption
di video. Kalau endpoint TTS diblokir, isi sendiri `public/vo/01.mp3` … `08.mp3`
dan tulis durasinya (detik) di `src/vo-durations.json`; video tetap jalan.

## Font

`scripts/fetch-fonts.mjs` mengunduh Space Grotesk + Inter (subset latin) dari
Google Fonts sekali saja ke `public/fonts/`. Render tidak perlu internet untuk
font (beda dengan `@remotion/google-fonts` yang bermasalah di setup ini).

## Struktur

```
src/
  index.ts          registerRoot
  Root.tsx          <Composition id="GudangPromo" />
  GudangPromo.tsx   TransitionSeries + audio per scene
  timing.ts         durasi scene dari durasi narasi
  narasi.json       teks narasi (sumber tunggal)
  vo-durations.json hasil tts
  theme.ts          token warna dari assets/css/base.css
  fonts.ts          Space Grotesk + Inter
  data.ts           data contoh (plat, driver, barang, grafik)
  utils.ts          helper animasi (rise, pop, typed, countUp)
  components/       Icon, Shell, Card, Chart, Cursor, GridBg
  scenes/           01Intro … 08Outro
scripts/tts.mjs     generator narasi
```

## Catatan

- Render pertama mengunduh Chrome Headless Shell (~100 MB), disimpan di `node_modules/.remotion/` (tidak di-track git).
- Audio narasi + font lokal, render bisa jalan offline setelah `npm run fonts` dan `npm run tts`.
- Data di video adalah contoh, bukan data dari database.
- Video 9:16 dioptimalkan untuk Reels/Shorts/TikTok. Versi 16:9 bisa ditambah nanti via komposisi kedua.
