/**
 * Narasi dengan ElevenLabs Text to Speech (API key di .env).
 *
 * Mode:
 *   node scripts/tts-elevenlabs.mjs --samples            # 3 voice premade, untuk dengar
 *   node scripts/tts-elevenlabs.mjs                       # 8 segmen -> public/vo/*.mp3
 *   node scripts/tts-elevenlabs.mjs --voice charlie --model eleven_v3
 *
 * Flag: --voice [george|charlie|sarah|id <voice_id>] --model [eleven_multilingual_v2|eleven_v3]
 *       --dir <target dir> (default public/vo)
 *
 * Batas tier gratis: voice library tidak boleh, request sekuensial (retry 429).
 */
import fs from "node:fs";
import path from "node:path";
import { promisify } from "node:util";
import { execFile } from "node:child_process";

const exec = promisify(execFile);
const root = path.resolve(import.meta.dirname, "..");

const args = process.argv.slice(2);
const flag = (name, fb) => {
  const i = args.indexOf(`--${name}`);
  return i >= 0 && args[i + 1] ? args[i + 1] : fb;
};
const has = (name) => args.includes(`--${name}`);

const VOICES = {
  george: { id: "JBFqnCBsd6RMkjVDRZzb", label: "George - Warm storyteller (pria)" },
  charlie: { id: "IKne3meq5aSn9XLyUdCD", label: "Charlie - Deep, confident (pria)" },
  sarah: { id: "EXAVITQu4vr4xnSDxMaL", label: "Sarah - Mature, reassuring (wanita)" },
};

// --- key dari .env -------------------------------------------------------
function loadKey() {
  if (process.env.ELEVENLABS_API_KEY) return process.env.ELEVENLABS_API_KEY;
  const envFile = path.join(root, ".env");
  if (fs.existsSync(envFile)) {
    for (const line of fs.readFileSync(envFile, "utf8").split(/\r?\n/)) {
      const m = /^ELEVENLABS_API_KEY\s*=\s*(.+)\s*$/.exec(line);
      if (m) return m[1].trim();
    }
  }
  console.error("ELEVENLABS_API_KEY tidak ditemukan. Isi remotion/.env atau set env var.");
  process.exit(1);
}
const KEY = loadKey();

const MODEL = flag("model", "eleven_multilingual_v2");
const SETTINGS = {
  stability: 0.45,
  similarity_boost: 0.8,
  style: 0.25,
  use_speaker_boost: true,
};

const sleep = (ms) => new Promise((r) => setTimeout(r, ms));

async function synthesize(text, voiceId, outFile) {
  const url = `https://api.elevenlabs.io/v1/text-to-speech/${voiceId}?output_format=mp3_44100_128`;
  for (let attempt = 1; attempt <= 5; attempt++) {
    const r = await fetch(url, {
      method: "POST",
      headers: { "xi-api-key": KEY, "Content-Type": "application/json" },
      body: JSON.stringify({ text, model_id: MODEL, voice_settings: SETTINGS }),
    });
    const ct = r.headers.get("content-type") || "";
    if (ct.includes("audio")) {
      const buf = Buffer.from(await r.arrayBuffer());
      fs.writeFileSync(outFile, buf);
      return buf.length;
    }
    const err = (await r.text()).slice(0, 200);
    if (r.status === 429) {
      console.log(`  429, retry ${attempt}/5 dalam ${attempt * 2}s`);
      await sleep(attempt * 2000);
      continue;
    }
    throw new Error(`ElevenLabs ${r.status}: ${err}`);
  }
  throw new Error("Gagal setelah retry 429");
}

async function durationOf(file) {
  const ff = path.join(root, "node_modules", "@remotion", "compositor-win32-x64-msvc", "ffprobe.exe");
  try {
    const { stdout } = await exec(ff, ["-v", "error", "-show_entries", "format=duration", "-of", "default=noprint_wrappers=1:nokey=1", file]);
    const s = Number(stdout.trim());
    if (s > 0) return s;
  } catch {}
  // CBR 128 kbps fallback
  return (fs.statSync(file).size * 8) / 128000;
}

// --- mode samples --------------------------------------------------------
if (has("samples")) {
  const dir = path.join(root, "out", "samples");
  fs.mkdirSync(dir, { recursive: true });
  const text = "Gudang. Sistem antrian kendaraan bongkar muat. Antrian tertata, gudang lebih lancar.";
  for (const [key, v] of Object.entries(VOICES)) {
    const file = path.join(dir, `${key}.mp3`);
    console.log(`synthesize ${v.label} -> ${path.relative(root, file)}`);
    const bytes = await synthesize(text, v.id, file);
    const d = await durationOf(file);
    console.log(`  ${(bytes / 1024).toFixed(0)}KB, ${d.toFixed(2)}s`);
    await sleep(1500);
  }
  console.log("\nDengarkan: remotion/out/samples/{george,charlie,sarah}.mp3");
  console.log("Lalu pilih: --voice george|charlie|sarah");
  process.exit(0);
}

// --- mode penuh: 8 segmen narasi -----------------------------------------
const voiceName = flag("voice", "george");
const voice = VOICES[voiceName] ?? { id: voiceName, label: `custom ${voiceName}` };
const dir = flag("dir", "public/vo").startsWith(root) ? flag("dir", "public/vo") : path.join(root, flag("dir", "public/vo"));
fs.mkdirSync(dir, { recursive: true });

// backup Edge TTS sekali saja
const edgeBackup = path.join(root, "public", "vo-edge");
if (fs.existsSync(dir) && fs.readdirSync(dir).some((f) => f.endsWith(".mp3")) && !fs.existsSync(edgeBackup)) {
  fs.mkdirSync(edgeBackup, { recursive: true });
  for (const f of fs.readdirSync(dir)) {
    if (f.endsWith(".mp3")) fs.copyFileSync(path.join(dir, f), path.join(edgeBackup, f));
  }
  console.log(`Backup voice lama -> ${path.relative(root, edgeBackup)}`);
}

const narasi = JSON.parse(fs.readFileSync(path.join(root, "src", "narasi.json"), "utf8"));
const durations = {};

for (const item of narasi) {
  const file = path.join(dir, `${item.file}.mp3`);
  console.log(`(${item.key}) ${voice.label} [${MODEL}]`);
  const bytes = await synthesize(item.text, voice.id, file);
  const d = await durationOf(file);
  durations[item.file] = Number(d.toFixed(2));
  console.log(`  ${item.file}.mp3  ${d.toFixed(2)}s  ${(bytes / 1024).toFixed(0)}KB`);
  await sleep(1500);
}

const durFile = path.join(root, "src", "vo-durations.json");
fs.writeFileSync(durFile, `${JSON.stringify(durations, null, 2)}\n`);
const total = Object.values(durations).reduce((a, b) => a + b, 0);
console.log(`\n${narasi.length} segmen, total ${total.toFixed(1)}s -> ${path.relative(root, durFile)}`);
console.log("Render ulang: npx remotion render GudangPromo out/gudang-promo.mp4");
