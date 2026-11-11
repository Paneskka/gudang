import React from "react";
import { useCurrentFrame } from "remotion";
import { T } from "../theme";
import { body, display } from "../fonts";
import { Icon, IconName } from "../components/Icon";
import { AppCard, Badge, Btn } from "../components/Card";
import { SceneShell } from "../components/Shell";
import { BarChart } from "../components/Chart";
import { progress, rise, thousand } from "../utils";
import { NARASI } from "../timing";
import { ANTRIAN, CHART } from "../data";

const caption = NARASI.find((n) => n.key === "dashboard")!.caption;

const Stat: React.FC<{
  label: string;
  value: string;
  note: string;
  icon: IconName;
  tone: string;
  soft: string;
  frame: number;
  delay: number;
}> = ({ label, value, note, icon, tone, soft, frame, delay }) => (
  <div
    style={{
      border: `1px solid ${T.border}`,
      borderRadius: 16,
      background: "#FFFFFF",
      padding: "16px 18px",
      ...rise(frame, delay, 16, 22),
    }}
  >
    <div style={{ display: "flex", alignItems: "center", justifyContent: "space-between" }}>
      <span style={{ fontSize: 19, color: T.inkSoft, fontWeight: 500 }}>{label}</span>
      <span
        style={{
          width: 34,
          height: 34,
          borderRadius: 10,
          background: soft,
          color: tone,
          display: "grid",
          placeItems: "center",
        }}
      >
        <Icon name={icon} size={19} color={tone} />
      </span>
    </div>
    <strong
      style={{
        display: "block",
        fontFamily: display,
        fontSize: 46,
        fontWeight: 600,
        letterSpacing: "-0.045em",
        color: T.primaryDark,
        lineHeight: 1.15,
        marginTop: 6,
      }}
    >
      {value}
    </strong>
    <span style={{ fontSize: 16, color: T.inkFaint }}>{note}</span>
  </div>
);

export const DashboardScene: React.FC = () => {
  const frame = useCurrentFrame();
  const drift = progress(frame, 0, 260) * -10;

  return (
    <SceneShell
      frame={frame}
      step="04/06"
      eyebrow="Dashboard"
      title={
        <>
          Semua angka,
          <br />
          satu layar.
        </>
      }
      caption={caption}
    >
      <div style={{ width: "100%", transform: `translateY(${drift}px)`, ...rise(frame, 6, 18, 26) }}>
        <AppCard chrome url="gudang.test/dashboard" padding="22px 24px 24px">
          <div style={{ display: "flex", alignItems: "flex-start", justifyContent: "space-between" }}>
            <div>
              <span
                style={{
                  fontSize: 16,
                  fontWeight: 700,
                  letterSpacing: "0.16em",
                  textTransform: "uppercase",
                  color: T.inkSoft,
                }}
              >
                Ringkasan operasional
              </span>
              <h3
                style={{
                  margin: "8px 0 0",
                  fontFamily: display,
                  fontSize: 38,
                  fontWeight: 600,
                  letterSpacing: "-0.04em",
                  color: T.primaryDark,
                }}
              >
                Selamat datang, admin.
              </h3>
            </div>
            <Btn size={19} icon="plus">
              Daftarkan
            </Btn>
          </div>

          <div
            style={{
              marginTop: 18,
              borderRadius: 18,
              padding: "20px 22px",
              color: "#FFFFFF",
              display: "flex",
              alignItems: "center",
              justifyContent: "space-between",
              background: `linear-gradient(105deg, ${T.primaryDark} 0%, #1E5143 60%, #2A6F57 100%)`,
              ...rise(frame, 16, 18, 20),
            }}
          >
            <div>
              <span
                style={{
                  display: "inline-flex",
                  alignItems: "center",
                  gap: 8,
                  fontSize: 16,
                  fontWeight: 600,
                  letterSpacing: "0.14em",
                  textTransform: "uppercase",
                  color: "#C2D3C6",
                }}
              >
                <span style={{ width: 7, height: 7, borderRadius: 999, background: T.amber }} />
                Pantau alur gudang
              </span>
              <div style={{ display: "flex", alignItems: "baseline", gap: 12, marginTop: 6 }}>
                <strong
                  style={{
                    fontFamily: display,
                    fontSize: 74,
                    fontWeight: 600,
                    letterSpacing: "-0.05em",
                    lineHeight: 1,
                    color: "#F2C97E",
                  }}
                >
                  {thousand(frame, 24, 40, 12)}
                </strong>
                <span style={{ fontSize: 22, color: "#D8E2D7" }}>antrian aktif</span>
              </div>
              <p style={{ margin: "6px 0 0", fontSize: 17, color: "#BFD0C2" }}>
                Menunggu atau sedang diproses di area bongkar muat.
              </p>
            </div>
            <span
              style={{
                display: "inline-flex",
                alignItems: "center",
                gap: 8,
                fontSize: 18,
                fontWeight: 600,
                color: "#F2C97E",
              }}
            >
              Kelola antrian <Icon name="arrow" size={18} />
            </span>
          </div>

          <div style={{ display: "grid", gridTemplateColumns: "1fr 1fr", gap: 14, marginTop: 16 }}>
            <Stat
              label="Total barang"
              value={thousand(frame, 30, 44, 1284)}
              note="Jenis barang terdaftar"
              icon="box"
              tone={T.primary}
              soft={T.primarySoft}
              frame={frame}
              delay={26}
            />
            <Stat
              label="Antrian masuk"
              value={thousand(frame, 34, 44, 32)}
              note="Kegiatan bongkar"
              icon="in"
              tone={T.blue}
              soft={T.blueSoft}
              frame={frame}
              delay={30}
            />
            <Stat
              label="Antrian keluar"
              value={thousand(frame, 38, 44, 18)}
              note="Kegiatan muat"
              icon="out"
              tone={T.amberInk}
              soft={T.amberSoft}
              frame={frame}
              delay={34}
            />
            <Stat
              label="Selesai hari ini"
              value={thousand(frame, 42, 44, 25)}
              note="Dari antrian hari ini"
              icon="check-circle"
              tone={T.green}
              soft={T.greenSoft}
              frame={frame}
              delay={38}
            />
          </div>

          <div
            style={{
              marginTop: 16,
              border: `1px solid ${T.border}`,
              borderRadius: 16,
              padding: "18px 18px 10px",
              ...rise(frame, 44, 18, 22),
            }}
          >
            <BarChart
              labels={CHART.labels}
              seriA={{ name: "Bongkar", color: T.primary, values: CHART.bongkar }}
              seriB={{ name: "Muat", color: T.amber, values: CHART.muat }}
              frame={frame}
              height={150}
              start={54}
            />
          </div>

          <div
            style={{
              marginTop: 16,
              border: `1px solid ${T.border}`,
              borderRadius: 16,
              overflow: "hidden",
              ...rise(frame, 58, 18, 22),
            }}
          >
            <div
              style={{
                display: "flex",
                alignItems: "center",
                justifyContent: "space-between",
                padding: "14px 18px",
                background: "#FAFCF8",
                borderBottom: `1px solid ${T.border}`,
              }}
            >
              <span style={{ fontSize: 19, fontWeight: 600, color: T.ink }}>Aktivitas terbaru</span>
              <span style={{ fontSize: 17, color: T.primary, fontWeight: 600 }}>Lihat laporan →</span>
            </div>
            {ANTRIAN.slice(0, 2).map((row, i) => (
              <div
                key={row.nomor}
                style={{
                  display: "grid",
                  gridTemplateColumns: "86px 1fr 120px 110px",
                  alignItems: "center",
                  gap: 10,
                  padding: "13px 18px",
                  borderBottom: i === 0 ? "1px solid #EBEFE8" : "none",
                  fontSize: 19,
                  ...rise(frame, 66 + i * 6, 16, 14),
                }}
              >
                <span style={{ fontFamily: display, fontWeight: 600, fontSize: 22, color: T.primary }}>
                  {row.nomor}
                </span>
                <span>
                  <span style={{ fontWeight: 600, display: "block" }}>{row.plat}</span>
                  <span style={{ fontSize: 16, color: T.inkSoft }}>{row.driver}</span>
                </span>
                <span style={{ fontSize: 17, color: T.inkSoft }}>{row.jam} WIB</span>
                <Badge tone={row.status === "Diproses" ? "diproses" : "menunggu"} size={17}>
                  {row.status}
                </Badge>
              </div>
            ))}
          </div>
        </AppCard>
      </div>
    </SceneShell>
  );
};
