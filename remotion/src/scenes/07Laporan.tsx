import React from "react";
import { useCurrentFrame } from "remotion";
import { T } from "../theme";
import { body, display } from "../fonts";
import { Icon } from "../components/Icon";
import { AppCard, Badge, Btn } from "../components/Card";
import { SceneShell } from "../components/Shell";
import { progress, rise, thousand } from "../utils";
import { NARASI } from "../timing";
import { LAPORAN } from "../data";

const caption = NARASI.find((n) => n.key === "laporan")!.caption;

const TILES = [
  { label: "Total bongkar", value: 52, tone: T.primary, soft: T.primarySoft, icon: "in" as const },
  { label: "Total muat", value: 38, tone: T.amberInk, soft: T.amberSoft, icon: "out" as const },
  { label: "Selesai", value: 90, tone: T.green, soft: T.greenSoft, icon: "check-circle" as const },
];

export const LaporanScene: React.FC = () => {
  const frame = useCurrentFrame();

  return (
    <SceneShell
      frame={frame}
      step="06/06"
      eyebrow="Laporan"
      title={
        <>
          Rekap bongkar muat,
          <br />
          siap dicetak.
        </>
      }
      caption={caption}
    >
      <div style={{ width: "100%", ...rise(frame, 6, 18, 26) }}>
        <AppCard chrome url="gudang.test/laporan" padding="26px 26px 28px">
          <div style={{ display: "flex", alignItems: "flex-start", justifyContent: "space-between" }}>
            <div>
              <h3
                style={{
                  margin: 0,
                  fontFamily: display,
                  fontSize: 34,
                  fontWeight: 600,
                  letterSpacing: "-0.04em",
                  color: T.primaryDark,
                }}
              >
                Laporan antrian
              </h3>
              <p style={{ margin: "4px 0 0", fontSize: 18, color: T.inkSoft }}>
                Rekap harian kegiatan bongkar dan muat.
              </p>
            </div>
            <div style={{ display: "flex", gap: 10 }}>
              <span
                style={{
                  display: "inline-flex",
                  alignItems: "center",
                  gap: 8,
                  border: `1px solid ${T.border}`,
                  borderRadius: 10,
                  padding: "10px 14px",
                  fontSize: 18,
                  color: T.ink,
                  background: "#FCFDFA",
                }}
              >
                <Icon name="calendar" size={18} color={T.inkSoft} />
                03 — 09 Nov 2026
              </span>
              <Btn size={18} variant="ghost" icon="print">
                Cetak
              </Btn>
            </div>
          </div>

          <div style={{ display: "grid", gridTemplateColumns: "repeat(3, 1fr)", gap: 12, marginTop: 18 }}>
            {TILES.map((t, i) => (
              <div
                key={t.label}
                style={{
                  border: `1px solid ${T.border}`,
                  borderRadius: 14,
                  padding: "14px 16px",
                  ...rise(frame, 14 + i * 5, 16, 18),
                }}
              >
                <span
                  style={{
                    width: 30,
                    height: 30,
                    borderRadius: 9,
                    background: t.soft,
                    color: t.tone,
                    display: "grid",
                    placeItems: "center",
                  }}
                >
                  <Icon name={t.icon} size={17} color={t.tone} />
                </span>
                <strong
                  style={{
                    display: "block",
                    marginTop: 8,
                    fontFamily: display,
                    fontSize: 40,
                    fontWeight: 600,
                    letterSpacing: "-0.04em",
                    lineHeight: 1.1,
                    color: T.primaryDark,
                  }}
                >
                  {thousand(frame, 20 + i * 4, 36, t.value)}
                </strong>
                <span style={{ fontSize: 17, color: T.inkSoft }}>{t.label}</span>
              </div>
            ))}
          </div>

          <div style={{ marginTop: 18, border: `1px solid ${T.border}`, borderRadius: 14, overflow: "hidden" }}>
            <div
              style={{
                display: "grid",
                gridTemplateColumns: "1fr 110px 110px 110px 120px",
                gap: 8,
                padding: "12px 18px",
                background: "#FAFCF8",
                borderBottom: `1px solid ${T.border}`,
                fontSize: 15,
                fontWeight: 600,
                letterSpacing: "0.1em",
                textTransform: "uppercase",
                color: T.inkSoft,
              }}
            >
              <span>Tanggal</span>
              <span style={{ textAlign: "right" }}>Bongkar</span>
              <span style={{ textAlign: "right" }}>Muat</span>
              <span style={{ textAlign: "right" }}>Selesai</span>
              <span style={{ textAlign: "right" }}>Beban</span>
            </div>

            {LAPORAN.map((row, i) => {
              const grow = progress(frame, 30 + i * 5, 22);
              const pct = Math.round(((row.bongkar + row.muat) / 25) * 100);
              return (
                <div
                  key={row.tanggal}
                  style={{
                    display: "grid",
                    gridTemplateColumns: "1fr 110px 110px 110px 120px",
                    gap: 8,
                    alignItems: "center",
                    padding: "13px 18px",
                    borderBottom: i === LAPORAN.length - 1 ? "none" : "1px solid #EBEFE8",
                    fontSize: 20,
                    ...rise(frame, 26 + i * 5, 16, 14),
                  }}
                >
                  <span style={{ fontWeight: 600 }}>{row.tanggal}</span>
                  <span style={{ textAlign: "right", color: T.primary, fontWeight: 600 }}>{row.bongkar}</span>
                  <span style={{ textAlign: "right", color: T.amberInk, fontWeight: 600 }}>{row.muat}</span>
                  <span style={{ textAlign: "right" }}>{row.selesai}</span>
                  <span style={{ display: "flex", justifyContent: "flex-end" }}>
                    <span
                      style={{
                        display: "block",
                        width: 92,
                        height: 9,
                        borderRadius: 999,
                        background: "#EDF2EA",
                        overflow: "hidden",
                      }}
                    >
                      <span
                        style={{
                          display: "block",
                          width: `${pct * grow}%`,
                          height: "100%",
                          borderRadius: 999,
                          background: `linear-gradient(90deg, ${T.primary}, ${T.amber})`,
                        }}
                      />
                    </span>
                  </span>
                </div>
              );
            })}

            <div
              style={{
                display: "grid",
                gridTemplateColumns: "1fr 110px 110px 110px 120px",
                gap: 8,
                padding: "15px 18px",
                background: T.primaryDark,
                color: "#FFFFFF",
                fontSize: 20,
                fontWeight: 600,
                ...rise(frame, 60, 18, 14),
              }}
            >
              <span>Total periode</span>
              <span style={{ textAlign: "right", color: "#F2C97E" }}>52</span>
              <span style={{ textAlign: "right", color: "#F2C97E" }}>38</span>
              <span style={{ textAlign: "right" }}>90</span>
              <span style={{ textAlign: "right", fontSize: 17, color: "#BFD0C2" }}>Rata-rata 18</span>
            </div>
          </div>

          <div style={{ display: "flex", alignItems: "center", justifyContent: "space-between", marginTop: 18 }}>
            <span style={{ display: "inline-flex", alignItems: "center", gap: 8, fontSize: 17, color: T.inkSoft }}>
              <Icon name="clock" size={17} color={T.inkFaint} />
              Dihitung dari antrian berstatus selesai pada periode.
            </span>
            <Badge tone="selesai" size={17}>
              Siap cetak A4
            </Badge>
          </div>
        </AppCard>
      </div>
    </SceneShell>
  );
};
