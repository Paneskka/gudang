import React from "react";
import { useCurrentFrame, useVideoConfig } from "remotion";
import { T } from "../theme";
import { body, display } from "../fonts";
import { Icon, LogoMark } from "../components/Icon";
import { Caption } from "../components/Shell";
import { progress, rise, springIn } from "../utils";
import { NARASI } from "../timing";

const caption = NARASI.find((n) => n.key === "intro")!.caption;

const PILLARS = [
  { icon: "truck", label: "Bongkar & muat" },
  { icon: "clock", label: "Panggilan realtime" },
  { icon: "box", label: "Stok barang" },
] as const;

export const IntroScene: React.FC = () => {
  const frame = useCurrentFrame();
  const { durationInFrames } = useVideoConfig();
  const out = progress(frame, durationInFrames - 18, 18);
  const mark = springIn(frame, 0, 13);
  const dotPulse = 1 + Math.sin(frame / 6) * 0.06;

  return (
    <div
      style={{
        position: "absolute",
        inset: 0,
        fontFamily: body,
        color: "#FFFFFF",
        overflow: "hidden",
        background: `radial-gradient(120% 80% at 50% -10%, #235445 0%, ${T.primaryDark} 55%, #102C25 100%)`,
      }}
    >
      <div
        style={{
          position: "absolute",
          inset: 0,
          backgroundImage:
            "linear-gradient(rgba(255,255,255,0.055) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,0.055) 1px, transparent 1px)",
          backgroundSize: "96px 96px",
          opacity: 0.9,
        }}
      />
      <div
        style={{
          position: "absolute",
          inset: 0,
          background: "radial-gradient(80% 50% at 50% 42%, rgba(232,163,61,0.10) 0%, rgba(232,163,61,0) 70%)",
        }}
      />

      <div
        style={{
          position: "absolute",
          inset: 0,
          padding: "0 96px",
          display: "flex",
          flexDirection: "column",
          alignItems: "center",
          justifyContent: "center",
          gap: 0,
          opacity: 1 - out,
          transform: `scale(${1 + out * 0.03})`,
        }}
      >
        <div
          style={{
            transform: `scale(${0.9 + mark * 0.1})`,
            opacity: mark,
            boxShadow: "0 24px 70px rgba(0,0,0,0.35)",
            borderRadius: 44,
          }}
        >
          <LogoMark size={188} bg="rgba(255,255,255,0.08)" stroke="#EAF2EB" radius={44} />
        </div>

        <h1
          style={{
            margin: "64px 0 0",
            fontFamily: display,
            fontSize: 158,
            lineHeight: 1,
            fontWeight: 700,
            letterSpacing: "-0.055em",
            display: "flex",
            alignItems: "baseline",
            gap: 8,
            ...rise(frame, 8, 22, 46),
          }}
        >
          Gudang
          <span
            style={{
              display: "inline-block",
              width: 34,
              height: 34,
              borderRadius: 999,
              background: T.amber,
              transform: `scale(${dotPulse})`,
              transformOrigin: "bottom",
              marginBottom: 22,
            }}
          />
        </h1>

        <p
          style={{
            margin: "22px 0 0",
            fontFamily: body,
            fontSize: 40,
            fontWeight: 500,
            letterSpacing: "0.028em",
            color: "#C6D8C8",
            textAlign: "center",
            ...rise(frame, 18, 22, 26),
          }}
        >
          Sistem antrian kendaraan bongkar muat
        </p>

        <div
          style={{
            marginTop: 54,
            width: 560,
            height: 1,
            background: "rgba(255,255,255,0.18)",
            ...rise(frame, 26, 18, 0),
          }}
        />

        <div
          style={{
            marginTop: 46,
            display: "flex",
            gap: 18,
            ...rise(frame, 34, 20, 22),
          }}
        >
          {PILLARS.map((p) => (
            <span
              key={p.label}
              style={{
                display: "inline-flex",
                alignItems: "center",
                gap: 10,
                border: "1px solid rgba(255,255,255,0.20)",
                background: "rgba(255,255,255,0.06)",
                borderRadius: 999,
                padding: "14px 24px",
                fontSize: 24,
                fontWeight: 500,
                color: "#E4EDE3",
              }}
            >
              <Icon name={p.icon} size={22} color={T.amber} />
              {p.label}
            </span>
          ))}
        </div>
      </div>

      <div
        style={{
          position: "absolute",
          left: 0,
          right: 0,
          bottom: 74,
          display: "flex",
          justifyContent: "center",
          ...rise(frame, 44, 20, 16),
        }}
      >
        <span
          style={{
            fontSize: 20,
            fontWeight: 600,
            letterSpacing: "0.34em",
            textTransform: "uppercase",
            color: "#8AA595",
          }}
        >
          PHP native · MySQL · Tanpa framework
        </span>
      </div>

      <Caption frame={frame} text={caption} dark />
    </div>
  );
};
