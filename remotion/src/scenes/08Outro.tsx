import React from "react";
import { useCurrentFrame, useVideoConfig } from "remotion";
import { T } from "../theme";
import { body, display } from "../fonts";
import { Icon, LogoMark } from "../components/Icon";
import { Caption } from "../components/Shell";
import { progress, rise, springIn } from "../utils";
import { NARASI } from "../timing";

const caption = NARASI.find((n) => n.key === "outro")!.caption;

const STEPS = [
  { no: "01", title: "Daftarkan kendaraan", note: "Isi data driver dan jenis kegiatan." },
  { no: "02", title: "Simpan nomor antrian", note: "Nomor B untuk bongkar, M untuk muat." },
  { no: "03", title: "Ikuti proses gudang", note: "Pantau panggilan di papan antrian." },
];

export const OutroScene: React.FC = () => {
  const frame = useCurrentFrame();
  const { durationInFrames } = useVideoConfig();
  const mark = springIn(frame, 0, 13);
  const fadeOut = progress(frame, durationInFrames - 16, 16);

  return (
    <div
      style={{
        position: "absolute",
        inset: 0,
        overflow: "hidden",
        fontFamily: body,
        color: "#FFFFFF",
        background: `radial-gradient(120% 80% at 50% 110%, #23533F 0%, ${T.primaryDark} 55%, #0F2A23 100%)`,
        opacity: 1 - fadeOut * 0.0,
      }}
    >
      <div
        style={{
          position: "absolute",
          inset: 0,
          backgroundImage:
            "linear-gradient(rgba(255,255,255,0.05) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,0.05) 1px, transparent 1px)",
          backgroundSize: "96px 96px",
        }}
      />

      <div
        style={{
          position: "absolute",
          inset: 0,
          padding: "0 76px",
          display: "flex",
          flexDirection: "column",
          justifyContent: "center",
          alignItems: "center",
        }}
      >
        <div style={{ transform: `scale(${0.9 + mark * 0.1})`, opacity: mark }}>
          <LogoMark size={126} bg="rgba(255,255,255,0.08)" stroke="#EAF2EB" radius={32} />
        </div>

        <h2
          style={{
            margin: "40px 0 0",
            fontFamily: display,
            fontSize: 96,
            fontWeight: 700,
            letterSpacing: "-0.05em",
            lineHeight: 1.05,
            textAlign: "center",
            ...rise(frame, 6, 20, 34),
          }}
        >
          Antrian tertata.
          <br />
          <span style={{ color: "#F2C97E" }}>Gudang lebih lancar.</span>
        </h2>

        <div style={{ marginTop: 54, width: "100%", display: "flex", flexDirection: "column", gap: 16 }}>
          {STEPS.map((s, i) => (
            <div
              key={s.no}
              style={{
                display: "flex",
                alignItems: "center",
                gap: 18,
                background: "rgba(255,255,255,0.06)",
                border: "1px solid rgba(255,255,255,0.13)",
                borderRadius: 18,
                padding: "20px 24px",
                ...rise(frame, 26 + i * 8, 18, 26),
              }}
            >
              <span
                style={{
                  width: 52,
                  height: 52,
                  borderRadius: 999,
                  border: "1px solid rgba(255,255,255,0.18)",
                  background: "rgba(255,255,255,0.05)",
                  display: "grid",
                  placeItems: "center",
                  fontFamily: display,
                  fontSize: 22,
                  fontWeight: 600,
                  color: "#F2C97E",
                }}
              >
                {s.no}
              </span>
              <div>
                <strong style={{ display: "block", fontSize: 29, fontWeight: 600, letterSpacing: "-0.02em" }}>
                  {s.title}
                </strong>
                <span style={{ fontSize: 20, color: "#BFD0C2" }}>{s.note}</span>
              </div>
              <Icon
                name="check-circle"
                size={26}
                color="#7FB08C"
                style={{ marginLeft: "auto", opacity: rise(frame, 40 + i * 8, 14, 0).opacity }}
              />
            </div>
          ))}
        </div>

        <div
          style={{
            marginTop: 48,
            display: "inline-flex",
            alignItems: "center",
            gap: 14,
            borderRadius: 999,
            border: "1px solid rgba(255,255,255,0.20)",
            padding: "14px 24px",
            ...rise(frame, 54, 18, 18),
          }}
        >
          <Icon name="truck" size={24} color={T.amber} />
          <span style={{ fontSize: 24, fontWeight: 600, letterSpacing: "-0.01em" }}>gudang.test</span>
          <span style={{ width: 1, height: 24, background: "rgba(255,255,255,0.2)" }} />
          <span style={{ fontSize: 19, color: "#9FB8A8" }}>PHP native · MySQL</span>
        </div>
      </div>

      <Caption frame={frame} text={caption} dark />
    </div>
  );
};
