import React from "react";
import { Img, staticFile, useCurrentFrame, useVideoConfig } from "remotion";
import { T } from "../theme";
import { body, display } from "../fonts";
import { Icon, LogoMark } from "../components/Icon";
import { Caption } from "../components/Shell";
import { progress, rise, springIn } from "../utils";
import { NARASI } from "../timing";
import qr from "../qr.json";

const caption = NARASI.find((n) => n.key === "outro")!.caption;

const STEPS = [
  { no: "01", title: "Daftarkan kendaraan", note: "Isi data driver dan jenis kegiatan." },
  { no: "02", title: "Simpan nomor antrian", note: "Nomor B untuk bongkar, M untuk muat." },
  { no: "03", title: "Ikuti proses gudang", note: "Pantau panggilan di papan antrian." },
];

const QR_SIZE = 380;

const CORNERS: ({ key: string } & React.CSSProperties)[] = [
  { key: "tl", top: -13, left: -13, borderTopWidth: 5, borderLeftWidth: 5, borderTopLeftRadius: 0 },
  { key: "tr", top: -13, right: -13, borderTopWidth: 5, borderRightWidth: 5, borderTopRightRadius: 0 },
  { key: "bl", bottom: -13, left: -13, borderBottomWidth: 5, borderLeftWidth: 5, borderBottomLeftRadius: 0 },
  { key: "br", bottom: -13, right: -13, borderBottomWidth: 5, borderRightWidth: 5, borderBottomRightRadius: 0 },
];

export const OutroScene: React.FC = () => {
  const frame = useCurrentFrame();
  const { durationInFrames } = useVideoConfig();
  const mark = springIn(frame, 0, 13);
  const fadeOut = progress(frame, durationInFrames - 16, 16);

  // Fase 2: recap memudar, kartu QR masuk
  const qrStart = Math.round(durationInFrames * 0.46);
  const stepsOut = progress(frame, qrStart, 18);
  const qrIn = springIn(frame, qrStart + 6, 13);

  return (
    <div
      style={{
        position: "absolute",
        inset: 0,
        overflow: "hidden",
        fontFamily: body,
        color: "#FFFFFF",
        background: `radial-gradient(120% 80% at 50% 110%, #23533F 0%, ${T.primaryDark} 55%, #0F2A23 100%)`,
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
          background: `radial-gradient(60% 40% at 50% 46%, rgba(232,163,61,${0.05 + qrIn * 0.1}) 0%, rgba(232,163,61,0) 70%)`,
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
        <div
          style={{
            display: "flex",
            flexDirection: "column",
            alignItems: "center",
            transform: `translateY(${-qrIn * 205}px) scale(${1 - qrIn * 0.5})`,
            opacity: 1 - qrIn * 0.1,
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
        </div>

        {/* Fase 1: recap tiga langkah */}
        <div
          style={{
            marginTop: 54,
            width: "100%",
            display: "flex",
            flexDirection: "column",
            gap: 16,
            opacity: 1 - stepsOut,
            transform: `translateY(${-30 * stepsOut}px)`,
          }}
        >
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
              <Icon name="check-circle" size={26} color="#7FB08C" style={{ marginLeft: "auto" }} />
            </div>
          ))}
        </div>

        {/* Fase 2: kartu QR repo GitHub */}
        <div
          style={{
            position: "absolute",
            left: 0,
            right: 0,
            top: "58%",
            display: "flex",
            flexDirection: "column",
            alignItems: "center",
            transform: `translateY(-50%) translateY(${(1 - qrIn) * 70}px) scale(${0.88 + qrIn * 0.12})`,
            opacity: qrIn,
          }}
        >
          <span
            style={{
              display: "inline-flex",
              alignItems: "center",
              gap: 10,
              fontSize: 21,
              fontWeight: 600,
              letterSpacing: "0.2em",
              textTransform: "uppercase",
              color: "#9FB8A8",
              marginBottom: 22,
            }}
          >
            <Icon name="search" size={19} color="#F2C97E" />
            Pindai untuk lihat kode
          </span>

          <div
            style={{
              position: "relative",
              padding: 26,
              borderRadius: 30,
              background: "#FFFFFF",
              boxShadow: "0 30px 80px rgba(0,0,0,0.45)",
            }}
          >
            <Img
              src={staticFile("qr-github.png")}
              style={{ width: QR_SIZE, height: QR_SIZE, display: "block", borderRadius: 10 }}
            />
            {CORNERS.map(({ key, borderTopWidth, borderBottomWidth, borderLeftWidth, borderRightWidth, borderTopLeftRadius, borderTopRightRadius, borderBottomLeftRadius, borderBottomRightRadius, ...pos }) => (
              <span
                key={key}
                style={{
                  position: "absolute",
                  width: 62,
                  height: 62,
                  borderColor: T.amber,
                  borderStyle: "solid",
                  borderWidth: 0,
                  borderTopWidth,
                  borderBottomWidth,
                  borderLeftWidth,
                  borderRightWidth,
                  borderTopLeftRadius,
                  borderTopRightRadius,
                  borderBottomLeftRadius,
                  borderBottomRightRadius,
                  ...pos,
                }}
              />
            ))}
          </div>

          <div
            style={{
              marginTop: 30,
              display: "inline-flex",
              alignItems: "center",
              gap: 12,
              borderRadius: 999,
              border: "1px solid rgba(255,255,255,0.20)",
              background: "rgba(255,255,255,0.06)",
              padding: "14px 24px",
            }}
          >
            <LogoMark size={30} bg="rgba(255,255,255,0.10)" stroke="#FFFFFF" radius={8} />
            <span style={{ fontSize: 24, fontWeight: 600, letterSpacing: "-0.01em" }}>{qr.label}</span>
          </div>

          <span style={{ marginTop: 16, fontSize: 19, color: "#9FB8A8" }}>
            PHP native · MySQL · tanpa framework
          </span>
        </div>
      </div>

      <Caption frame={frame} text={caption} dark />
    </div>
  );
};
