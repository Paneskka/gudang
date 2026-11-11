import React from "react";
import { T } from "../theme";
import { body, display } from "../fonts";
import { GridBg } from "./GridBg";
import { Icon, LogoMark } from "./Icon";
import { progress, rise } from "../utils";

export const Caption: React.FC<{ frame: number; text: string; dark?: boolean }> = ({
  frame,
  text,
  dark = false,
}) => {
  const p = progress(frame, 18, 16);
  return (
    <div
      style={{
        position: "absolute",
        left: 0,
        right: 0,
        bottom: 62,
        padding: "0 96px",
        display: "flex",
        justifyContent: "center",
        opacity: p,
        transform: `translateY(${(1 - p) * 14}px)`,
      }}
    >
      <p
        style={{
          margin: 0,
          fontFamily: body,
          fontSize: 33,
          lineHeight: 1.45,
          fontWeight: 500,
          textAlign: "center",
          color: dark ? "#D8E2D7" : T.inkSoft,
          maxWidth: 880,
        }}
      >
        {text}
      </p>
    </div>
  );
};

export const SceneShell: React.FC<{
  frame: number;
  eyebrow: string;
  title: React.ReactNode;
  caption: string;
  children: React.ReactNode;
  step?: string;
}> = ({ frame, eyebrow, title, caption, children, step }) => (
  <div style={{ position: "absolute", inset: 0, background: T.bg, fontFamily: body, color: T.ink }}>
    <GridBg />
    <div
      style={{
        position: "absolute",
        inset: 0,
        padding: "86px 66px 168px",
        display: "flex",
        flexDirection: "column",
      }}
    >
      <div
        style={{
          display: "flex",
          alignItems: "center",
          justifyContent: "space-between",
          ...rise(frame, 0, 14, 18),
        }}
      >
        <span
          style={{
            display: "inline-flex",
            alignItems: "center",
            gap: 10,
            border: `1px solid #D6E1D3`,
            background: "rgba(255,255,255,0.72)",
            padding: "8px 16px",
            borderRadius: 8,
            fontSize: 21,
            fontWeight: 600,
            letterSpacing: "0.16em",
            textTransform: "uppercase",
            color: T.primary,
          }}
        >
          <span style={{ width: 9, height: 9, borderRadius: 999, background: T.primary }} />
          {eyebrow}
        </span>
        <span style={{ display: "inline-flex", alignItems: "center", gap: 10 }}>
          {step ? (
            <span
              style={{
                fontFamily: display,
                fontSize: 24,
                fontWeight: 600,
                color: T.inkFaint,
                letterSpacing: "-0.02em",
              }}
            >
              {step}
            </span>
          ) : null}
          <LogoMark size={42} />
        </span>
      </div>

      <h2
        style={{
          margin: "34px 0 0",
          fontFamily: display,
          fontSize: 82,
          lineHeight: 1.05,
          fontWeight: 600,
          letterSpacing: "-0.045em",
          color: T.primaryDark,
          ...rise(frame, 4, 18, 40),
        }}
      >
        {title}
      </h2>

      <div
        style={{
          flex: 1,
          minHeight: 0,
          marginTop: 54,
          display: "flex",
          alignItems: "center",
          justifyContent: "center",
        }}
      >
        {children}
      </div>
    </div>
    <Caption frame={frame} text={caption} />
  </div>
);

export const BrandLockup: React.FC<{ size?: number; dark?: boolean; sub?: string }> = ({
  size = 40,
  dark = false,
  sub = "SISTEM ANTRIAN",
}) => (
  <span style={{ display: "inline-flex", alignItems: "center", gap: 12 }}>
    <LogoMark size={size} bg={dark ? "rgba(255,255,255,0.10)" : "#EAF2EB"} stroke={dark ? "#FFFFFF" : "#173F35"} />
    <span>
      <strong
        style={{
          display: "block",
          fontFamily: display,
          fontSize: size * 0.46,
          fontWeight: 700,
          letterSpacing: "-0.03em",
          color: dark ? "#FFFFFF" : T.primaryDark,
          lineHeight: 1.25,
        }}
      >
        Gudang.
      </strong>
      <small
        style={{
          display: "block",
          fontSize: size * 0.24,
          fontWeight: 600,
          letterSpacing: "0.14em",
          color: dark ? "#A9BFB1" : T.inkSoft,
        }}
      >
        {sub}
      </small>
    </span>
  </span>
);

export const ArrowText: React.FC<{ children: React.ReactNode; color?: string; size?: number }> = ({
  children,
  color = T.primary,
  size = 22,
}) => (
  <span
    style={{
      display: "inline-flex",
      alignItems: "center",
      gap: 8,
      color,
      fontFamily: body,
      fontSize: size,
      fontWeight: 600,
    }}
  >
    {children}
    <Icon name="arrow" size={size} />
  </span>
);
