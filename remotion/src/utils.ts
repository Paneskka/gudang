import React from "react";
import { interpolate, spring } from "remotion";
import { FPS } from "./theme";

export const clamp01 = (v: number) => Math.min(1, Math.max(0, v));

export const easeOut = (p: number) => 1 - Math.pow(1 - clamp01(p), 3);

/** Progres 0..1 dengan easing, dipakai untuk animasi masuk. */
export const progress = (frame: number, start: number, duration: number) =>
  easeOut(interpolate(frame, [start, start + duration], [0, 1], {
    extrapolateLeft: "clamp",
    extrapolateRight: "clamp",
  }));

/** Style masuk: naik + fade. */
export const rise = (
  frame: number,
  start = 0,
  duration = 16,
  distance = 34,
): React.CSSProperties => {
  const p = progress(frame, start, duration);
  return {
    opacity: p,
    transform: `translateY(${(1 - p) * distance}px)`,
  };
};

/** Style masuk: skala + fade, untuk nomor antrian / badge. */
export const pop = (frame: number, start = 0, duration = 14): React.CSSProperties => {
  const p = progress(frame, start, duration);
  return {
    opacity: p,
    transform: `scale(${0.82 + p * 0.18})`,
  };
};

export const springIn = (frame: number, start = 0, damping = 14) =>
  spring({ frame: frame - start, fps: FPS, config: { damping, mass: 0.7, stiffness: 120 } });

/** Efek mengetik: potong teks sesuai frame. */
export const typed = (text: string, frame: number, start: number, charsPerFrame = 0.9) => {
  const count = Math.floor(Math.max(0, frame - start) * charsPerFrame);
  return text.slice(0, Math.min(text.length, count));
};

export const countUp = (
  frame: number,
  start: number,
  duration: number,
  target: number,
  decimals = 0,
) => {
  const p = progress(frame, start, duration);
  return (target * p).toFixed(decimals).replace(".", ",");
};

export const thousand = (frame: number, start: number, duration: number, target: number) =>
  Number(countUp(frame, start, duration, target, 0)).toLocaleString("id-ID");
