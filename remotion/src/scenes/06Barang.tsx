import React from "react";
import { useCurrentFrame } from "remotion";
import { T } from "../theme";
import { body, display } from "../fonts";
import { Icon } from "../components/Icon";
import { AppCard, Badge, Btn } from "../components/Card";
import { SceneShell } from "../components/Shell";
import { Cursor } from "../components/Cursor";
import { progress, rise, springIn, thousand, typed } from "../utils";
import { NARASI } from "../timing";
import { BARANG } from "../data";

const caption = NARASI.find((n) => n.key === "barang")!.caption;

const MODAL_IN = 84;
const CLICK = 156;
const MODAL_OUT = CLICK + 16;

export const BarangScene: React.FC = () => {
  const frame = useCurrentFrame();
  const modal = springIn(frame, MODAL_IN, 12);
  const press = Math.max(0, 1 - Math.abs(frame - CLICK) / 7);
  const closed = progress(frame, MODAL_OUT, 14);
  const toast = springIn(frame, MODAL_OUT + 4, 12);
  const cursorIn = progress(frame, 112, 12) * (1 - closed);
  const cursorX = 360 + progress(frame, 118, 34) * 285;
  const cursorY = 0;

  return (
    <SceneShell
      frame={frame}
      step="05/06"
      eyebrow="Data barang"
      title={
        <>
          Stok rapi,
          <br />
          keputusan cepat.
        </>
      }
      caption={caption}
    >
      <div style={{ position: "relative", width: "100%", ...rise(frame, 6, 18, 26) }}>
        <AppCard chrome url="gudang.test/barang" padding="24px 24px 26px">
          <div style={{ display: "flex", alignItems: "center", justifyContent: "space-between", gap: 12 }}>
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
                Data barang
              </h3>
              <p style={{ margin: "4px 0 0", fontSize: 18, color: T.inkSoft }}>
                {thousand(frame, 12, 30, 1284)} jenis barang terdaftar
              </p>
            </div>
            <Btn size={19} icon="plus">
              Tambah barang
            </Btn>
          </div>

          <div
            style={{
              marginTop: 16,
              height: 54,
              display: "flex",
              alignItems: "center",
              gap: 10,
              border: `1px solid ${T.border}`,
              borderRadius: 10,
              background: "#FCFDFA",
              padding: "0 16px",
              fontSize: 20,
              color: T.inkFaint,
            }}
          >
            <Icon name="search" size={19} color={T.inkFaint} />
            Cari kode atau nama barang…
          </div>

          <div style={{ marginTop: 18, border: `1px solid ${T.border}`, borderRadius: 14, overflow: "hidden" }}>
            <div
              style={{
                display: "grid",
                gridTemplateColumns: "150px 1fr 96px 116px",
                gap: 10,
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
              <span>Kode</span>
              <span>Nama barang</span>
              <span style={{ textAlign: "right" }}>Stok</span>
              <span style={{ textAlign: "right" }}>Status</span>
            </div>

            {BARANG.map((row, i) => (
              <div
                key={row.kode}
                style={{
                  display: "grid",
                  gridTemplateColumns: "150px 1fr 96px 116px",
                  gap: 10,
                  alignItems: "center",
                  padding: "14px 18px",
                  borderBottom: i === BARANG.length - 1 ? "none" : "1px solid #EBEFE8",
                  background: i % 2 === 1 ? "#FDFEFB" : "#FFFFFF",
                  ...rise(frame, 16 + i * 5, 16, 16),
                }}
              >
                <span
                  style={{
                    fontFamily: display,
                    fontSize: 19,
                    fontWeight: 600,
                    color: T.inkFaint,
                    letterSpacing: "0.02em",
                  }}
                >
                  {row.kode}
                </span>
                <span style={{ fontSize: 20, fontWeight: 500 }}>{row.nama}</span>
                <span style={{ textAlign: "right", fontSize: 21, fontWeight: 600 }}>
                  {row.stok}
                  <span style={{ fontSize: 16, color: T.inkSoft, fontWeight: 400 }}> {row.satuan}</span>
                </span>
                <span style={{ textAlign: "right" }}>
                  <Badge tone={row.status === "Aman" ? "aman" : "menipis"} size={17}>
                    {row.status}
                  </Badge>
                </span>
              </div>
            ))}
          </div>

          <div style={{ display: "flex", justifyContent: "space-between", marginTop: 16 }}>
            <span style={{ fontSize: 17, color: T.inkSoft }}>Menampilkan 4 dari 1.284 barang</span>
            <span style={{ display: "inline-flex", gap: 10 }}>
              <Badge tone="netral" size={17}>
                Sebelumnya
              </Badge>
              <Badge tone="diproses" size={17}>
                Berikutnya
              </Badge>
            </span>
          </div>
        </AppCard>

        {/* Tutup seluruh kartu (termasuk bar browser) saat modal terbuka */}
        <div
          style={{
            position: "absolute",
            inset: 0,
            background: "rgba(23,63,53,0.42)",
            opacity: Math.min(1, modal * 1.4) * (1 - closed),
            borderRadius: 30,
          }}
        />

        {/* Sheet "Tambah barang" dari bawah */}
        <div
          style={{
            position: "absolute",
            left: 0,
            right: 0,
            bottom: 0,
            display: "flex",
            justifyContent: "center",
            opacity: Math.min(1, modal * 1.4) * (1 - closed),
            transform: `translateY(${(1 - modal) * 160 + closed * 80}px)`,
          }}
        >
          <div
            style={{
              width: "100%",
              background: "#FFFFFF",
              borderRadius: "22px 22px 0 0",
              border: `1px solid ${T.border}`,
              boxShadow: "0 -20px 60px rgba(23,63,53,0.25)",
              padding: "26px 28px 30px",
            }}
          >
            <div style={{ display: "flex", alignItems: "center", justifyContent: "space-between" }}>
              <h4
                style={{
                  margin: 0,
                  fontFamily: display,
                  fontSize: 30,
                  fontWeight: 600,
                  letterSpacing: "-0.03em",
                  color: T.primaryDark,
                }}
              >
                Tambah barang
              </h4>
              <span
                style={{
                  width: 34,
                  height: 34,
                  borderRadius: 9,
                  background: "#F3F6F1",
                  display: "grid",
                  placeItems: "center",
                }}
              >
                <Icon name="close" size={18} color={T.inkSoft} />
              </span>
            </div>

            <div style={{ display: "grid", gridTemplateColumns: "1fr 1fr", gap: 14, marginTop: 18 }}>
              {[
                { label: "Kode barang", value: typed("BRG-005", frame, MODAL_IN + 10, 0.9) },
                { label: "Satuan", value: typed("karung", frame, MODAL_IN + 22, 1) },
              ].map((f) => (
                <div key={f.label}>
                  <span style={{ fontSize: 17, color: T.inkSoft }}>{f.label}</span>
                  <div
                    style={{
                      marginTop: 6,
                      height: 50,
                      borderRadius: 9,
                      border: `1px solid #DDE5DB`,
                      background: "#FCFDFA",
                      display: "flex",
                      alignItems: "center",
                      padding: "0 14px",
                      fontSize: 21,
                    }}
                  >
                    {f.value}
                  </div>
                </div>
              ))}
              <div style={{ gridColumn: "1 / -1" }}>
                <span style={{ fontSize: 17, color: T.inkSoft }}>Nama barang</span>
                <div
                  style={{
                    marginTop: 6,
                    height: 50,
                    borderRadius: 9,
                    border: `1px solid #DDE5DB`,
                    background: "#FCFDFA",
                    display: "flex",
                    alignItems: "center",
                    padding: "0 14px",
                    fontSize: 21,
                  }}
                >
                  {typed("Karung Gula 50 kg", frame, MODAL_IN + 30, 1)}
                </div>
              </div>
              <div>
                <span style={{ fontSize: 17, color: T.inkSoft }}>Stok awal</span>
                <div
                  style={{
                    marginTop: 6,
                    height: 50,
                    borderRadius: 9,
                    border: `1px solid #DDE5DB`,
                    background: "#FCFDFA",
                    display: "flex",
                    alignItems: "center",
                    padding: "0 14px",
                    fontSize: 21,
                  }}
                >
                  {typed("120", frame, MODAL_IN + 42, 0.8)}
                </div>
              </div>
              <div style={{ display: "flex", alignItems: "flex-end" }}>
                <Btn full icon="check" size={21} style={{ height: 50 }}>
                  Simpan barang
                </Btn>
              </div>
            </div>
          </div>
        </div>

        <div style={{ position: "absolute", left: 0, top: "calc(100% - 76px)" }}>
          <Cursor x={cursorX} y={cursorY} press={press} opacity={cursorIn} />
        </div>

        <div
          style={{
            position: "absolute",
            left: 0,
            right: 0,
            bottom: -34,
            display: "flex",
            justifyContent: "center",
            opacity: Math.min(1, toast * 1.3),
            transform: `translateY(${(1 - toast) * 20}px)`,
          }}
        >
          <span
            style={{
              display: "inline-flex",
              alignItems: "center",
              gap: 10,
              background: T.greenSoft,
              color: T.green,
              border: "1px solid #C9DFC7",
              borderRadius: 999,
              padding: "13px 22px",
              fontSize: 20,
              fontWeight: 600,
              boxShadow: "0 16px 36px rgba(23,63,53,0.12)",
            }}
          >
            <Icon name="check-circle" size={21} />
            BRG-005 Karung Gula 50 kg ditambahkan.
          </span>
        </div>
      </div>
    </SceneShell>
  );
};
