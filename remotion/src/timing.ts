import voSeconds from "./vo-durations.json";
import narasi from "./narasi.json";
import { FPS } from "./theme";

export type SceneKey =
  | "intro"
  | "public"
  | "register"
  | "login"
  | "dashboard"
  | "barang"
  | "laporan"
  | "outro";

export type SceneSpec = {
  key: SceneKey;
  /** nama berkas narasi di public/vo/<file>.mp3 */
  file: string;
  /** teks narasi, dipakai juga sebagai caption */
  caption: string;
  /** durasi minimum kalau audio gagal dibuat */
  minFrames: number;
};

/** Sumber teks narasi: src/narasi.json (dipakai juga oleh scripts/tts.mjs). */
export const NARASI: SceneSpec[] = narasi.map((item) => ({
  key: item.key as SceneKey,
  file: item.file,
  caption: item.text,
  minFrames: item.minFrames,
}));

/** Jeda sebelum narasi mulai, dan ruang napas setelahnya. */
export const VO_LEAD = 10;
export const VO_TAIL = 20;
/** Overlap TransitionSeries antar scene. */
export const TRANSITION = 12;

const durations = voSeconds as Record<string, number>;

export const sceneFrames: Record<SceneKey, number> = NARASI.reduce(
  (acc, spec) => {
    const seconds = durations[spec.file] ?? 0;
    const audioFrames = Math.ceil(seconds * FPS) + VO_LEAD + VO_TAIL;
    acc[spec.key] = Math.max(spec.minFrames, audioFrames);
    return acc;
  },
  {} as Record<SceneKey, number>,
);

export const TOTAL_FRAMES =
  NARASI.reduce((sum, spec) => sum + sceneFrames[spec.key], 0) -
  TRANSITION * (NARASI.length - 1);

export const TOTAL_SECONDS = TOTAL_FRAMES / FPS;
