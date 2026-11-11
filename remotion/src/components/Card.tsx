import React from "react";
import { T } from "../theme";
import { body, display } from "../fonts";
import { Icon, IconName } from "./Icon";

export const AppCard: React.FC<{
  children: React.ReactNode;
  chrome?: boolean;
  url?: string;
  padding?: number | string;
  style?: React.CSSProperties;
  radius?: number;
}> = ({ children, chrome = false, url = "gudang.test", padding = "30px 32px 32px", style, radius = 30 }) => (
  <div
    style={{
      width: "100%",
      background: T.surface,
      border: `1px solid ${T.border}`,
      borderRadius: radius,
      boxShadow: "0 26px 70px rgba(23,63,53,0.12)",
      overflow: "hidden",
      ...style,
    }}
  >
    {chrome ? (
      <div
        style={{
          display: "flex",
          alignItems: "center",
          gap: 12,
          padding: "16px 20px",
          background: "#F1F4EF",
          borderBottom: `1px solid ${T.border}`,
        }}
      >
        {["#E4A9A0", "#EFD3A0", "#B6D6BC"].map((c) => (
          <span key={c} style={{ width: 13, height: 13, borderRadius: 999, background: c }} />
        ))}
        <div
          style={{
            flex: 1,
            display: "flex",
            alignItems: "center",
            gap: 8,
            background: "#FFFFFF",
            border: `1px solid ${T.border}`,
            borderRadius: 999,
            padding: "7px 16px",
            fontSize: 20,
            color: T.inkSoft,
            fontFamily: body,
          }}
        >
          <Icon name="lock" size={15} color={T.inkFaint} />
          <span style={{ fontWeight: 500 }}>{url}</span>
        </div>
      </div>
    ) : null}
    <div style={{ padding }}>{children}</div>
  </div>
);

type BadgeTone = "menunggu" | "diproses" | "selesai" | "aman" | "menipis" | "netral";

const TONES: Record<BadgeTone, { bg: string; fg: string }> = {
  menunggu: { bg: "#FFF2D9", fg: "#8C621C" },
  diproses: { bg: "#EAF1FA", fg: "#41658D" },
  selesai: { bg: "#EAF3E8", fg: "#39673E" },
  aman: { bg: "#EAF3E8", fg: "#39673E" },
  menipis: { bg: "#FFF2D9", fg: "#8C621C" },
  netral: { bg: "#EAF2EB", fg: T.primary },
};

export const Badge: React.FC<{ children: React.ReactNode; tone?: BadgeTone; size?: number }> = ({
  children,
  tone = "netral",
  size = 20,
}) => (
  <span
    style={{
      display: "inline-flex",
      alignItems: "center",
      gap: 6,
      background: TONES[tone].bg,
      color: TONES[tone].fg,
      borderRadius: 999,
      padding: `${size * 0.24}px ${size * 0.72}px`,
      fontSize: size,
      fontWeight: 600,
      whiteSpace: "nowrap",
      fontFamily: body,
    }}
  >
    {children}
  </span>
);

export const Btn: React.FC<{
  children?: React.ReactNode;
  icon?: IconName;
  variant?: "primary" | "ghost" | "amber";
  size?: number;
  full?: boolean;
  style?: React.CSSProperties;
}> = ({ children, icon, variant = "primary", size = 23, full = false, style }) => {
  const palette = {
    primary: { background: T.primary, color: "#FFFFFF", border: `1px solid ${T.primary}` },
    ghost: { background: "#FFFFFF", color: T.ink, border: `1px solid ${T.border}` },
    amber: { background: T.amberSoft, color: T.amberInk, border: "1px solid #F3DFB9" },
  }[variant];

  return (
    <span
      style={{
        display: "inline-flex",
        alignItems: "center",
        justifyContent: "center",
        gap: 10,
        padding: `${size * 0.62}px ${size * 1.05}px`,
        borderRadius: 10,
        fontFamily: body,
        fontSize: size,
        fontWeight: 600,
        width: full ? "100%" : undefined,
        boxSizing: "border-box",
        ...palette,
        ...style,
      }}
    >
      {children}
      {icon ? <Icon name={icon} size={size} /> : null}
    </span>
  );
};

export const Field: React.FC<{
  label: string;
  value?: string;
  placeholder?: string;
  hint?: string;
  caret?: boolean;
  selected?: boolean;
  chevron?: boolean;
  height?: number;
  fontSize?: number;
}> = ({
  label,
  value = "",
  placeholder = "",
  hint,
  caret = false,
  selected = false,
  chevron = false,
  height = 58,
  fontSize = 24,
}) => (
  <div style={{ display: "flex", flexDirection: "column", gap: 8 }}>
    <span style={{ fontSize: 21, fontWeight: 500, color: T.ink, fontFamily: body }}>
      {label}
      <span style={{ color: "#A97529", paddingLeft: 4 }}>*</span>
    </span>
    <div
      style={{
        height,
        display: "flex",
        alignItems: "center",
        justifyContent: "space-between",
        border: `1px solid ${selected ? T.primary : "#DDE5DB"}`,
        boxShadow: selected ? `0 0 0 4px rgba(47,110,78,0.12)` : "none",
        borderRadius: 10,
        background: "#FCFDFA",
        padding: "0 18px",
        fontSize,
        color: value ? T.ink : T.inkFaint,
        fontFamily: body,
        fontWeight: value ? 500 : 400,
        letterSpacing: value ? "0" : "0",
      }}
    >
      <span style={{ display: "inline-flex", alignItems: "center", gap: 2 }}>
        {value || placeholder}
        {caret ? (
          <span style={{ width: 2, height: fontSize * 1.05, background: T.primary }} />
        ) : null}
      </span>
      {chevron ? (
        <Icon name="in" size={fontSize * 0.7} color={T.inkFaint} />
      ) : null}
    </div>
    {hint ? (
      <span style={{ fontSize: 18, color: T.inkSoft, fontFamily: body }}>{hint}</span>
    ) : null}
  </div>
);

export const SectionTitle: React.FC<{ children: React.ReactNode; sub?: string }> = ({
  children,
  sub,
}) => (
  <div>
    <h3
      style={{
        margin: 0,
        fontFamily: display,
        fontSize: 28,
        fontWeight: 600,
        letterSpacing: "-0.03em",
        color: T.primaryDark,
      }}
    >
      {children}
    </h3>
    {sub ? (
      <p style={{ margin: "4px 0 0", fontSize: 19, color: T.inkSoft, fontFamily: body }}>{sub}</p>
    ) : null}
  </div>
);
