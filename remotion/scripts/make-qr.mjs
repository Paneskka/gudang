/**
 * Generate QR code repo GitHub untuk outro video.
 *
 * Pakai: node scripts/make-qr.mjs [--url https://github.com/Paneskka/gudang]
 *
 * Output: public/qr-github.png (1024px, EC level H, warna brand) + src/qr.json
 */
import fs from "node:fs";
import path from "node:path";
import QRCode from "qrcode";

const root = path.resolve(import.meta.dirname, "..");
const args = process.argv.slice(2);
const flag = (name, fb) => {
  const i = args.indexOf(`--${name}`);
  return i >= 0 && args[i + 1] ? args[i + 1] : fb;
};

const url = flag("url", "https://github.com/Paneskka/gudang");
const target = path.join(root, "public", "qr-github.png");

await QRCode.toFile(target, url, {
  errorCorrectionLevel: "H",
  type: "png",
  width: 1024,
  margin: 2,
  color: { dark: "#173F35FF", light: "#FFFFFFFF" },
});

// versi label untuk ditampilkan di video
const label = url.replace(/^https?:\/\//, "").replace(/\/$/, "");
fs.writeFileSync(path.join(root, "src", "qr.json"), `${JSON.stringify({ url, label }, null, 2)}\n`);

console.log(`QR -> ${path.relative(root, target)}`);
console.log(`URL: ${url}`);
console.log(`Label: ${label}`);
