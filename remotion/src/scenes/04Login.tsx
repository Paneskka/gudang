import React from "react";
import { useCurrentFrame } from "remotion";
import { T } from "../theme";
import { body, display } from "../fonts";
import { Icon } from "../components/Icon";
import { AppCard, Badge, Btn } from "../components/Card";
import { BrandLockup, SceneShell } from "../components/Shell";
import { Cursor } from "../components/Cursor";
import { progress, rise, springIn, typed } from "../utils";
import { NARASI } from "../timing";

const caption = NARASI.find((n) => n.key === "login")!.caption;
const CLICK = 96;

export const LoginScene: React.FC = () => {
  const frame = useCurrentFrame();
  const user = typed("admin", frame, 14, 1.4);
  const pass = "•".repeat(Math.round(progress(frame, 36, 22) * 8));
  const press = Math.max(0, 1 - Math.abs(frame - CLICK) / 7);
  const ok = springIn(frame, CLICK + 14, 12);

  return (
    <SceneShell
      frame={frame}
      step="03/06"
      eyebrow="Akses petugas"
      title={
        <>
          Satu login,
          <br />
          peran yang jelas.
        </>
      }
      caption={caption}
    >
      <div style={{ position: "relative", width: "100%", ...rise(frame, 6, 18, 26) }}>
        <AppCard chrome url="gudang.test/login" padding="38px 40px 34px">
          <BrandLockup size={52} />
          <h3
            style={{
              margin: "26px 0 0",
              fontFamily: display,
              fontSize: 42,
              fontWeight: 600,
              letterSpacing: "-0.04em",
              color: T.primaryDark,
            }}
          >
            Masuk ke dashboard
          </h3>
          <p style={{ margin: "8px 0 26px", fontSize: 20, color: T.inkSoft }}>
            Gunakan akun admin atau petugas gudang.
          </p>

          <div style={{ display: "flex", flexDirection: "column", gap: 18 }}>
            <div>
              <span style={{ fontSize: 21, fontWeight: 500 }}>Username</span>
              <div
                style={{
                  marginTop: 8,
                  height: 58,
                  display: "flex",
                  alignItems: "center",
                  gap: 10,
                  border: `1px solid ${frame >= 12 && frame < 36 ? T.primary : "#DDE5DB"}`,
                  borderRadius: 10,
                  background: "#FCFDFA",
                  padding: "0 16px",
                  fontSize: 24,
                }}
              >
                <Icon name="users" size={20} color={T.inkFaint} />
                <span style={{ display: "inline-flex", alignItems: "center", gap: 2 }}>
                  {user || <span style={{ color: T.inkFaint }}>admin</span>}
                  {frame < 36 ? <span style={{ width: 2, height: 24, background: T.primary }} /> : null}
                </span>
              </div>
            </div>

            <div>
              <span style={{ fontSize: 21, fontWeight: 500 }}>Password</span>
              <div
                style={{
                  marginTop: 8,
                  height: 58,
                  display: "flex",
                  alignItems: "center",
                  gap: 10,
                  border: `1px solid ${frame >= 36 && frame < CLICK ? T.primary : "#DDE5DB"}`,
                  borderRadius: 10,
                  background: "#FCFDFA",
                  padding: "0 16px",
                  fontSize: 26,
                  letterSpacing: "0.18em",
                }}
              >
                <Icon name="lock" size={20} color={T.inkFaint} />
                <span style={{ display: "inline-flex", alignItems: "center", gap: 2 }}>
                  {pass || <span style={{ color: T.inkFaint, letterSpacing: "0", fontSize: 21 }}>••••••••</span>}
                  {frame >= 36 && frame < CLICK ? (
                    <span style={{ width: 2, height: 24, background: T.primary, letterSpacing: "0" }} />
                  ) : null}
                </span>
              </div>
            </div>
          </div>

          <div style={{ marginTop: 24 }}>
            <Btn full icon="lock" size={24} style={{ height: 62 }}>
              Masuk
            </Btn>
          </div>

          <div
            style={{
              marginTop: 22,
              padding: "16px 18px",
              borderRadius: 12,
              background: T.amberSoft,
              display: "flex",
              gap: 10,
              alignItems: "flex-start",
            }}
          >
            <Icon name="lock" size={18} color={T.amberInk} style={{ marginTop: 3 }} />
            <p style={{ margin: 0, fontSize: 18, lineHeight: 1.55, color: T.amberInk }}>
              Password disimpan dengan <strong>password_hash()</strong> bcrypt. Ganti password default setelah login pertama.
            </p>
          </div>

          <div
            style={{
              display: "flex",
              alignItems: "center",
              justifyContent: "space-between",
              marginTop: 22,
            }}
          >
            <Badge tone="diproses" size={19}>
              Admin — akses penuh
            </Badge>
            <Badge tone="netral" size={19}>
              Petugas — operasional
            </Badge>
          </div>
        </AppCard>

        <div style={{ position: "absolute", left: 0, top: "calc(100% - 220px)" }}>
          <Cursor x={280} y={0} press={press} opacity={progress(frame, 56, 12)} />
        </div>

        <div
          style={{
            position: "absolute",
            left: 0,
            right: 0,
            bottom: -30,
            display: "flex",
            justifyContent: "center",
            opacity: Math.min(1, ok * 1.2),
            transform: `translateY(${(1 - ok) * 22}px)`,
          }}
        >
          <div
            style={{
              display: "inline-flex",
              alignItems: "center",
              gap: 12,
              background: T.greenSoft,
              color: T.green,
              border: "1px solid #C9DFC7",
              borderRadius: 999,
              padding: "16px 26px",
              fontSize: 22,
              fontWeight: 600,
              boxShadow: "0 18px 40px rgba(23,63,53,0.12)",
            }}
          >
            <Icon name="check-circle" size={24} />
            Berhasil masuk — selamat datang, admin.
          </div>
        </div>
      </div>
    </SceneShell>
  );
};
