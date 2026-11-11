/**
 * Narasi bahasa Indonesia via Microsoft Edge Read Aloud (tanpa API key).
 * Hasil: public/vo/<nn>.mp3 + src/vo-durations.json (dipakai timing.ts).
 *
 * Pakai: node scripts/tts.mjs [--voice id-ID-GadisNeural] [--rate -4%]
 */
import fs from "node:fs";
import path from "node:path";
import { MsEdgeTTS, OUTPUT_FORMAT } from "msedge-tts";

const root = path.resolve(import.meta.dirname, "..");
const voDir = path.join(root, "public", "vo");
const narasiFile = path.join(root, "src", "narasi.json");
const durationsFile = path.join(root, "src", "vo-durations.json");

const args = process.argv.slice(2);
const argOf = (name, fallback) => {
  const i = args.indexOf(`--${name}`);
  return i >= 0 && args[i + 1] ? args[i + 1] : fallback;
};

const VOICE = argOf("voice", process.env.VOICE ?? "id-ID-ArdiNeural");
const RATE = argOf("rate", "-4%");
const PITCH = argOf("pitch", "+0Hz");

const narasi = JSON.parse(fs.readFileSync(narasiFile, "utf8"));
fs.mkdirSync(voDir, { recursive: true });

const durations = {};

for (const item of narasi) {
  const target = path.join(voDir, `${item.file}.mp3`);
  const tts = new MsEdgeTTS({ connectionTimeoutMs: 20000, synthesisTimeoutMs: 90000 });
  await tts.setMetadata(VOICE, OUTPUT_FORMAT.AUDIO_24KHZ_48KBITRATE_MONO_MP3, {
    sentenceBoundaryEnabled: true,
    wordBoundaryEnabled: false,
  });

  const { audioStream, metadataStream } = await tts.toStream(item.text, {
    rate: RATE,
    pitch: PITCH,
  });

  const chunks = [];
  audioStream.on("data", (d) => chunks.push(d));

  let durationTicks = 0;
  let meta = "";
  metadataStream.on("data", (d) => {
    meta += d.toString("utf8");
  });

  await new Promise((resolve, reject) => {
    let audioDone = false;
    let metaDone = false;
    let settled = false;
    const finish = () => {
      if (settled) return;
      if (audioDone && metaDone) {
        settled = true;
        clearTimeout(timer);
        resolve();
      }
    };
    // Jaga-jaga kalau stream metadata tidak pernah menutup.
    const timer = setTimeout(() => {
      if (!settled && audioDone) {
        settled = true;
        resolve();
      }
    }, 4000);
    audioStream.on("end", () => {
      audioDone = true;
      finish();
    });
    audioStream.on("error", reject);
    audioStream.on("close", () => {
      audioDone = true;
      finish();
    });
    metadataStream.on("end", () => {
      metaDone = true;
      finish();
    });
    metadataStream.on("error", () => {
      metaDone = true;
      finish();
    });
    metadataStream.on("close", () => {
      metaDone = true;
      finish();
    });
  });

  const buffer = Buffer.concat(chunks);
  if (buffer.length === 0) {
    throw new Error(`Audio kosong untuk segmen ${item.file} (${item.key})`);
  }
  fs.writeFileSync(target, buffer);

  for (const match of meta.matchAll(/"Duration"\s*:\s*(\d+)/g)) {
    durationTicks += Number(match[1]);
  }

  // Duration metadata dalam tick 100ns; fallback ke bitrate 48 kbps CBR.
  const fromMeta = durationTicks / 1e7;
  const fromBitrate = (buffer.length * 8) / 48000;
  const seconds = Math.max(fromMeta, fromBitrate);
  durations[item.file] = Number(seconds.toFixed(2));

  console.log(
    `${item.file}.mp3  ${seconds.toFixed(2)}s  ${(buffer.length / 1024).toFixed(0)}KB  "${item.text.slice(0, 48)}…"`,
  );
}

fs.writeFileSync(durationsFile, `${JSON.stringify(durations, null, 2)}\n`);

const total = Object.values(durations).reduce((a, b) => a + b, 0);
console.log(`\n${narasi.length} segmen, total narasi ${total.toFixed(1)}s`);
console.log(`voice: ${VOICE} | rate: ${RATE} | pitch: ${PITCH}`);
console.log(`durasi ditulis ke ${path.relative(root, durationsFile)}`);
