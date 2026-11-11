import React from "react";
import { T } from "../theme";

/** Latar bertekstur: gradien halus + garis grid tipis, sama seperti latar aplikasi. */
export const GridBg: React.FC<{
  color?: string;
  line?: string;
  size?: number;
  opacity?: number;
}> = ({ color = T.bg, line = "#2F6E4E", size = 80, opacity = 0.055 }) => (
  <div
    style={{
      position: "absolute",
      inset: 0,
      background: color,
    }}
  >
    <div
      style={{
        position: "absolute",
        inset: 0,
        backgroundImage: `linear-gradient(${line} 1px, transparent 1px), linear-gradient(90deg, ${line} 1px, transparent 1px)`,
        backgroundSize: `${size}px ${size}px`,
        opacity,
      }}
    />
    <div
      style={{
        position: "absolute",
        inset: 0,
        background: `radial-gradient(120% 70% at 50% 0%, rgba(255,255,255,0.85) 0%, rgba(255,255,255,0) 60%)`,
      }}
    />
  </div>
);
