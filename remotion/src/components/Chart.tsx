import React from "react";
import { T } from "../theme";
import { body, display } from "../fonts";

export const BarChart: React.FC<{
  labels: string[];
  seriA: { name: string; color: string; values: number[] };
  seriB: { name: string; color: string; values: number[] };
  frame: number;
  height?: number;
  start?: number;
}> = ({ labels, seriA, seriB, frame, height = 200, start = 0 }) => {
  const max = Math.max(...seriA.values, ...seriB.values, 1);
  const maxLabel = Math.ceil(max / 4) * 4;

  return (
    <div style={{ width: "100%", fontFamily: body }}>
      <div style={{ display: "flex", alignItems: "center", justifyContent: "space-between" }}>
        <div>
          <h4
            style={{
              margin: 0,
              fontFamily: display,
              fontSize: 26,
              fontWeight: 600,
              letterSpacing: "-0.03em",
              color: T.primaryDark,
            }}
          >
            Antrian 7 hari terakhir
          </h4>
          <p style={{ margin: "3px 0 0", fontSize: 18, color: T.inkSoft }}>
            Bongkar dan muat per hari
          </p>
        </div>
        <div style={{ display: "flex", gap: 16 }}>
          {[seriA, seriB].map((s) => (
            <span
              key={s.name}
              style={{ display: "inline-flex", alignItems: "center", gap: 7, fontSize: 18, color: T.inkSoft }}
            >
              <span style={{ width: 10, height: 10, borderRadius: 3, background: s.color }} />
              {s.name}
            </span>
          ))}
        </div>
      </div>

      <div style={{ display: "flex", gap: 12, marginTop: 20 }}>
        <div
          style={{
            width: 40,
            height,
            display: "flex",
            flexDirection: "column",
            justifyContent: "space-between",
            fontSize: 15,
            color: T.inkFaint,
            textAlign: "right",
            paddingBottom: 2,
          }}
        >
          {[maxLabel, Math.round((maxLabel * 2) / 3), Math.round(maxLabel / 3), 0].map((v, i) => (
            <span key={i} style={{ lineHeight: 1 }}>
              {v}
            </span>
          ))}
        </div>

        <div style={{ flex: 1, position: "relative" }}>
          <div style={{ position: "absolute", inset: 0, height }}>
            {[0, 1, 2, 3].map((i) => (
              <span
                key={i}
                style={{
                  position: "absolute",
                  left: 0,
                  right: 0,
                  top: `${(i / 3) * 100}%`,
                  height: 1,
                  background: i === 3 ? "#D9E3D6" : "#EDF2EA",
                }}
              />
            ))}
          </div>

          <div style={{ display: "flex", alignItems: "flex-end", height, gap: 6 }}>
            {labels.map((label, i) => {
              const grow = Math.min(
                1,
                Math.max(0, (frame - start - i * 3) / 22),
              );
              const e = 1 - Math.pow(1 - grow, 3);
              return (
                <div
                  key={label}
                  style={{
                    flex: 1,
                    display: "flex",
                    flexDirection: "column",
                    alignItems: "center",
                    justifyContent: "flex-end",
                    height: "100%",
                  }}
                >
                  <div style={{ display: "flex", alignItems: "flex-end", gap: 5, height: "100%" }}>
                    <span
                      style={{
                        width: 20,
                        borderRadius: "5px 5px 0 0",
                        height: `${(seriA.values[i] / maxLabel) * 100 * e}%`,
                        background: T.primary,
                      }}
                    />
                    <span
                      style={{
                        width: 20,
                        borderRadius: "5px 5px 0 0",
                        height: `${(seriB.values[i] / maxLabel) * 100 * e}%`,
                        background: T.amber,
                      }}
                    />
                  </div>
                  <span style={{ fontSize: 16, color: T.inkSoft, marginTop: 8 }}>{label}</span>
                </div>
              );
            })}
          </div>
        </div>
      </div>
    </div>
  );
};
