import React from "react";
import { useCurrentFrame } from "remotion";
import { T } from "../theme";
import { body, display } from "../fonts";
import { Icon } from "../components/Icon";
import { AppCard, Btn, Field } from "../components/Card";
import { SceneShell } from "../components/Shell";
import { Cursor } from "../components/Cursor";
import { progress, rise, springIn, typed } from "../utils";
import { NARASI } from "../timing";
import { FORM } from "../data";

const caption = NARASI.find((n) => n.key === "register")!.caption;

const CLICK = 142;

export const RegisterScene: React.FC = () => {
  const frame = useCurrentFrame();

  const cursorX = progress(frame, 112, 28) * -330;
  const cursorY = progress(frame, 112, 28) * -54;
  const press = Math.max(
    0,
    1 - Math.abs(frame - CLICK) / 7,
  );
  const ticketIn = springIn(frame, CLICK + 10, 12);

  const driver = typed(FORM.driver, frame, 18, 0.9);
  const plat = typed(FORM.plat, frame, 38, 0.9);
  const company = typed(FORM.perusahaan, frame, 62, 0.9);
  const kegiatan = typed(FORM.kegiatan, frame, 96, 1.1);

  return (
    <SceneShell
      frame={frame}
      step="02/06"
      eyebrow="Pendaftaran"
      title={
        <>
          Empat kolom,
          <br />
          nomor langsung terbit.
        </>
      }
      caption={caption}
    >
      <div style={{ position: "relative", width: "100%", ...rise(frame, 6, 18, 26) }}>
        <AppCard padding="34px 34px 30px">
          <div style={{ display: "flex", alignItems: "center", gap: 10, marginBottom: 20 }}>
            <span
              style={{
                background: T.amberSoft,
                color: T.amberInk,
                borderRadius: 6,
                padding: "4px 10px",
                fontSize: 18,
                fontWeight: 700,
                letterSpacing: "0.06em",
              }}
            >
              01
            </span>
            <span style={{ fontSize: 19, color: T.inkSoft, fontWeight: 500 }}>
              Mulai perjalanan Anda
            </span>
            <Icon name="arrow" size={18} color={T.inkFaint} style={{ marginLeft: "auto" }} />
          </div>

          <h3
            style={{
              margin: 0,
              fontFamily: display,
              fontSize: 44,
              fontWeight: 600,
              letterSpacing: "-0.04em",
              color: T.primaryDark,
            }}
          >
            Daftarkan kendaraan
          </h3>
          <p style={{ margin: "8px 0 24px", fontSize: 20, color: T.inkSoft }}>
            Lengkapi data berikut untuk mendapatkan nomor antrian.
          </p>

          <div style={{ display: "flex", flexDirection: "column", gap: 18 }}>
            <Field label="Nama driver" value={driver} placeholder="Nama lengkap driver" caret={frame < 46} height={56} />
            <Field
              label="Nomor polisi"
              value={plat}
              placeholder="B 1234 XYZ"
              caret={frame >= 46 && frame < 70}
              height={56}
            />
            <Field
              label="Perusahaan"
              value={company}
              placeholder="Nama perusahaan atau ekspedisi"
              caret={frame >= 70 && frame < 104}
              height={56}
            />
            <Field
              label="Jenis kegiatan"
              value={kegiatan}
              placeholder="Pilih kegiatan kendaraan"
              selected={frame >= 104}
              caret={frame >= 104 && frame < 128}
              chevron
              height={56}
              fontSize={22}
            />
          </div>

          <div style={{ marginTop: 22 }}>
            <Btn full icon="arrow" size={24} style={{ height: 62 }}>
              Ambil Nomor Antrian
            </Btn>
          </div>

          <p style={{ margin: "18px 0 0", textAlign: "center", fontSize: 19, color: T.inkSoft }}>
            Sudah mendaftar? <span style={{ color: T.primary, fontWeight: 600 }}>Cek status antrian →</span>
          </p>
        </AppCard>

        <div style={{ position: "absolute", left: 0, top: "calc(100% - 76px)" }}>
          <Cursor
            x={cursorX}
            y={cursorY}
            press={press}
            opacity={progress(frame, 104, 12) * (1 - progress(frame, CLICK + 4, 10))}
          />
        </div>

        <div
          style={{
            position: "absolute",
            inset: 0,
            background: "rgba(23,63,53,0.42)",
            borderRadius: 30,
            opacity: frame > CLICK + 4 ? Math.min(1, (frame - CLICK - 4) / 8) : 0,
          }}
        />

        <div
          style={{
            position: "absolute",
            left: 0,
            right: 0,
            top: "50%",
            display: "flex",
            justifyContent: "center",
            transform: `translateY(-50%) scale(${0.86 + ticketIn * 0.14})`,
            opacity: Math.min(1, ticketIn * 1.4),
          }}
        >
          <div
            style={{
              width: 620,
              background: "#FFFFFF",
              borderRadius: 26,
              padding: "38px 40px 34px",
              boxShadow: "0 40px 90px rgba(0,0,0,0.35)",
              textAlign: "center",
            }}
          >
            <span
              style={{
                display: "inline-flex",
                alignItems: "center",
                gap: 8,
                background: T.greenSoft,
                color: T.green,
                borderRadius: 999,
                padding: "8px 18px",
                fontSize: 19,
                fontWeight: 600,
              }}
            >
              <Icon name="check-circle" size={19} />
              Pendaftaran berhasil
            </span>

            <p style={{ margin: "24px 0 0", fontSize: 20, color: T.inkSoft }}>Nomor antrian Anda</p>
            <strong
              style={{
                display: "block",
                fontFamily: display,
                fontSize: 132,
                lineHeight: 1.05,
                fontWeight: 600,
                letterSpacing: "-0.05em",
                color: T.primary,
              }}
            >
              B002
            </strong>

            <div
              style={{
                marginTop: 20,
                padding: "18px 20px",
                borderRadius: 14,
                border: `1px dashed ${T.border}`,
                background: T.bg,
                display: "grid",
                gridTemplateColumns: "1fr 1fr",
                gap: 12,
                textAlign: "left",
                fontFamily: body,
              }}
            >
              <div>
                <small style={{ fontSize: 17, color: T.inkSoft }}>Driver</small>
                <div style={{ fontSize: 21, fontWeight: 600 }}>Sugianto</div>
              </div>
              <div>
                <small style={{ fontSize: 17, color: T.inkSoft }}>Nomor polisi</small>
                <div style={{ fontSize: 21, fontWeight: 600 }}>B 9012 KHD</div>
              </div>
              <div>
                <small style={{ fontSize: 17, color: T.inkSoft }}>Kegiatan</small>
                <div style={{ fontSize: 21, fontWeight: 600 }}>Bongkar</div>
              </div>
              <div>
                <small style={{ fontSize: 17, color: T.inkSoft }}>Status</small>
                <div style={{ fontSize: 21, fontWeight: 600, color: T.amberInk }}>Menunggu</div>
              </div>
            </div>

            <p style={{ margin: "20px 0 0", fontSize: 18, color: T.inkSoft }}>
              Nomor B untuk bongkar, M untuk muat. Simpan nomor untuk cek status.
            </p>
          </div>
        </div>
      </div>
    </SceneShell>
  );
};
