/**
 * Unduh woff2 Space Grotesk + Inter (subset latin) ke public/fonts,
 * lalu tulis src/fonts.json supaya render tidak butuh internet.
 *
 * Pakai: node scripts/fetch-fonts.mjs
 */
import fs from "node:fs";
import path from "node:path";

const root = path.resolve(import.meta.dirname, "..");
const outDir = path.join(root, "public", "fonts");
const manifestFile = path.join(root, "src", "fonts.json");

fs.mkdirSync(outDir, { recursive: true });

const FAMILIES = [
  { name: "Space Grotesk", query: "Space+Grotesk:wght@400;500;600;700", slug: "space-grotesk" },
  { name: "Inter", query: "Inter:wght@400;500;600;700", slug: "inter" },
];

const UA =
  "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36";

const manifest = [];

for (const family of FAMILIES) {
  const cssUrl = `https://fonts.googleapis.com/css2?family=${family.query}&display=swap`;
  const css = await (await fetch(cssUrl, { headers: { "User-Agent": UA } })).text();

  const blocks = css.split("@font-face").slice(1);
  const seen = new Set();

  for (const block of blocks) {
    const weight = /font-weight:\s*(\d+)/.exec(block)?.[1];
    const url = /src:\s*url\((https:[^)]+\.woff2)\)/.exec(block)?.[1];
    const range = /unicode-range:\s*([^;]+);/.exec(block)?.[1] ?? "";
    const isLatin = range.trim().startsWith("U+0000-00FF") && !range.includes("U+0100");
    if (!weight || !url || !isLatin || seen.has(weight)) continue;
    seen.add(weight);

    const file = `${family.slug}-${weight}.woff2`;
    const target = path.join(outDir, file);
    const buf = Buffer.from(await (await fetch(url, { headers: { "User-Agent": UA } })).arrayBuffer());
    fs.writeFileSync(target, buf);
    console.log(`${file}  ${(buf.length / 1024).toFixed(0)}KB`);
    manifest.push({ family: family.name, weight, file: `fonts/${file}` });
  }
}

fs.writeFileSync(manifestFile, `${JSON.stringify(manifest, null, 2)}\n`);
console.log(`\n${manifest.length} berkas font ditulis, manifest: ${path.relative(root, manifestFile)}`);
