import React from "react";
import { T } from "../theme";

/** Kursor tiruan. `press` 0..1 memicu ripple klik. */
export const Cursor: React.FC<{
  x: number;
  y: number;
  opacity?: number;
  press?: number;
  scale?: number;
}> = ({ x, y, opacity = 1, press = 0, scale = 1 }) => (
  <div
    style={{
      position: "absolute",
      left: x,
      top: y,
      opacity,
      zIndex: 90,
      pointerEvents: "none",
      transform: `translate(-6px, -4px) scale(${scale * (1 - press * 0.1)})`,
      transformOrigin: "6px 4px",
    }}
  >
    <div style={{ position: "relative" }}>
      {press > 0 ? (
        <span
          style={{
            position: "absolute",
            left: -34,
            top: -32,
            width: 96,
            height: 96,
            borderRadius: 999,
            border: `3px solid ${T.amber}`,
            background: `rgba(232,163,61,${0.20 * (1 - press)})`,
            transform: `scale(${0.35 + press * 0.75})`,
            opacity: 1 - press,
          }}
        />
      ) : null}
      <svg width="46" height="54" viewBox="0 0 24 26" style={{ display: "block" }}>
        <path
          d="M5.2 2.4 20 12.1l-6.6 1 3.2 6.9-3.1 1.4-3.2-7-4.9 4z"
          fill="#FFFFFF"
          stroke="#173F35"
          strokeWidth="1.5"
          strokeLinejoin="round"
        />
      </svg>
    </div>
  </div>
);
