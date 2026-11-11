import React from "react";
import { useCurrentFrame } from "remotion";
import { T } from "../theme";
import { body, display } from "../fonts";
import { Icon } from "../components/Icon";
import { AppCard, Btn, Badge } from "../components/Card";
import { BrandLockup, SceneShell } from "../components/Shell";
import { progress, rise, pop } from "../utils";
import { NARASI } from "../timing";

const caption = NARASI.find((n) => n.key === "public")!.caption;

const Lane: React.FC<{
  title: string;
  kind: "Bongkar" | "Muat";
  nomor: string;
  plat: string;
  note: string;
  frame: number;
  delay: number;
}> = ({ title, kind, nomor, plat, note, frame, delay }) => (
  <div style={{ flex: 1, textAlign: "center", padding: "0 16px", ...rise(frame, delay, 16, 18) }}>
    <div
      style={{
        display: "flex",
        alignItems: "center",
        justifyContent: "center",
        gap: 8,
        fontSize: 28,
        fontWeight: 500,
        color: "#FFFFFF",
      }}
    >
      <Icon name={kind === "Bongkar" ? "in" : "out"} size={26} color="#F2C97E" />
      {title}
    </div>
    <p style={{ margin: "4px 0 0", fontSize: 19, color: "#BFD0C2" }}>
      {kind === "Bongkar" ? "Barang masuk" : "Barang keluar"}
    </p>
    <strong
      style={{
        display: "block",
        marginTop: 14,
        fontFamily: display,
        fontSize: 84,
        fontWeight: 500,
        letterSpacing: "-0.045em",
        color: "#F2C97E",
        lineHeight: 1.1,
        ...pop(frame, delay + 6, 14),
      }}
    >
      {nomor}
    </strong>
    <span style={{ display: "block", marginTop: 6, fontSize: 22, fontWeight: 600, color: "#FFFFFF" }}>
      {plat}
    </span>
    <p style={{ margin: "8px 0 0", fontSize: 18, color: "#BFD0C2" }}>{note}</p>
  </div>
);

export const PublicScene: React.FC = () => {
  const frame = useCurrentFrame();
  const live = 1 + Math.sin(frame / 5) * 0.18;
  const drift = progress(frame, 0, 220) * -14;

  return (
    <SceneShell
      frame={frame}
      step="01/06"
      eyebrow="Halaman publik"
      title={
        <>
          Daftar sendiri,
          <br />
          tanpa antre di loket.
        </>
      }
      caption={caption}
    >
      <div style={{ transform: `translateY(${drift}px)`, width: "100%", ...rise(frame, 6, 18, 26) }}>
        <AppCard chrome url="gudang.test" padding="26px 28px 28px">
          <div style={{ display: "flex", alignItems: "center", justifyContent: "space-between" }}>
            <BrandLockup size={40} />
            <div style={{ display: "flex", alignItems: "center", gap: 22 }}>
              <span style={{ fontSize: 21, color: T.inkSoft, fontWeight: 500 }}>Pendaftaran</span>
              <span style={{ fontSize: 21, color: T.inkSoft, fontWeight: 500 }}>Cek antrian</span>
              <Btn size={20} icon="lock">
                Login
              </Btn>
            </div>
          </div>

          <div style={{ marginTop: 30, ...rise(frame, 14, 18, 22) }}>
            <span
              style={{
                display: "inline-flex",
                alignItems: "center",
                gap: 8,
                border: "1px solid #D6E1D3",
                borderRadius: 6,
                padding: "6px 12px",
                fontSize: 18,
                fontWeight: 600,
                color: T.primary,
                letterSpacing: "0.04em",
              }}
            >
              <span style={{ width: 6, height: 6, borderRadius: 999, background: T.primary }} />
              SISTEM ANTRIAN KENDARAAN
            </span>

            <h3
              style={{
                margin: "18px 0 0",
                fontFamily: display,
                fontSize: 58,
                lineHeight: 1.12,
                fontWeight: 600,
                letterSpacing: "-0.05em",
                color: T.primaryDark,
              }}
            >
              Antrian tertata.
              <br />
              <span style={{ color: T.primary }}>Gudang lebih lancar.</span>
            </h3>

            <p style={{ margin: "14px 0 0", fontSize: 22, lineHeight: 1.6, color: T.inkSoft, maxWidth: 620 }}>
              Daftarkan kendaraan, dapatkan nomor antrian, dan pantau proses bongkar muat dalam satu tempat.
            </p>
          </div>

          <div
            style={{
              marginTop: 26,
              background: T.primaryDark,
              borderRadius: 18,
              padding: "22px 24px",
              color: "#FFFFFF",
              ...rise(frame, 24, 20, 24),
            }}
          >
            <div style={{ display: "flex", alignItems: "center", justifyContent: "space-between" }}>
              <span
                style={{
                  fontSize: 17,
                  fontWeight: 600,
                  letterSpacing: "0.16em",
                  color: "#C2D3C6",
                }}
              >
                ANTRIAN DIPANGGIL
              </span>
              <span style={{ display: "inline-flex", alignItems: "center", gap: 7, fontSize: 17, color: "#C5DDCA" }}>
                <span
                  style={{
                    width: 8,
                    height: 8,
                    borderRadius: 999,
                    background: "#A9D8AF",
                    transform: `scale(${live})`,
                  }}
                />
                Terkini
              </span>
            </div>

            <div
              style={{
                display: "flex",
                alignItems: "stretch",
                marginTop: 20,
                paddingTop: 18,
                borderTop: "1px solid rgba(255,255,255,0.14)",
              }}
            >
              <Lane
                title="Bongkar"
                kind="Bongkar"
                nomor="B001"
                plat="B 9012 KHD"
                note="Silakan menuju area bongkar."
                frame={frame}
                delay={34}
              />
              <div style={{ width: 1, background: "rgba(255,255,255,0.14)" }} />
              <Lane
                title="Muat"
                kind="Muat"
                nomor="M001"
                plat="H 8817 UA"
                note="Silakan menuju area muat."
                frame={frame}
                delay={40}
              />
            </div>

            <div
              style={{
                display: "flex",
                alignItems: "center",
                gap: 8,
                marginTop: 18,
                paddingTop: 16,
                borderTop: "1px solid rgba(255,255,255,0.14)",
                fontSize: 18,
                color: "#C2D3C6",
              }}
            >
              <Icon name="clock" size={16} color="#F2C97E" />
              Diperbarui otomatis setiap 5 detik tanpa muat ulang halaman.
            </div>
          </div>

          <div
            style={{
              display: "flex",
              alignItems: "center",
              justifyContent: "space-between",
              marginTop: 20,
              ...rise(frame, 46, 18, 18),
            }}
          >
            <span style={{ display: "inline-flex", alignItems: "center", gap: 8, fontSize: 19, color: T.inkSoft }}>
              <Icon name="clock" size={18} color={T.inkFaint} />
              Jam operasional <strong style={{ color: T.ink, fontWeight: 600 }}>Senin–Sabtu, 08.00–17.00 WIB</strong>
            </span>
            <Badge tone="netral" size={19}>
              Tanpa login
            </Badge>
          </div>
        </AppCard>
      </div>
    </SceneShell>
  );
};
