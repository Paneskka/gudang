import React from "react";
import { Audio, Sequence, staticFile } from "remotion";
import { TransitionSeries, linearTiming } from "@remotion/transitions";
import { fade } from "@remotion/transitions/fade";
import { slide } from "@remotion/transitions/slide";
import { NARASI, TRANSITION, VO_LEAD, sceneFrames } from "./timing";
import { IntroScene } from "./scenes/01Intro";
import { PublicScene } from "./scenes/02Public";
import { RegisterScene } from "./scenes/03Register";
import { LoginScene } from "./scenes/04Login";
import { DashboardScene } from "./scenes/05Dashboard";
import { BarangScene } from "./scenes/06Barang";
import { LaporanScene } from "./scenes/07Laporan";
import { OutroScene } from "./scenes/08Outro";

const SCENES = {
  intro: IntroScene,
  public: PublicScene,
  register: RegisterScene,
  login: LoginScene,
  dashboard: DashboardScene,
  barang: BarangScene,
  laporan: LaporanScene,
  outro: OutroScene,
} as const;

const Vo: React.FC<{ file: string; children: React.ReactNode }> = ({ file, children }) => (
  <>
    {children}
    <Sequence from={VO_LEAD} layout="none" name={`Narasi ${file}`}>
      <Audio src={staticFile(`vo/${file}.mp3`)} />
    </Sequence>
  </>
);

export const GudangPromo: React.FC = () => (
  <TransitionSeries>
    {NARASI.map((spec, i) => {
      const Scene = SCENES[spec.key];
      const isLast = i === NARASI.length - 1;
      return (
        <React.Fragment key={spec.key}>
          <TransitionSeries.Sequence durationInFrames={sceneFrames[spec.key]}>
            <Vo file={spec.file}>
              <Scene />
            </Vo>
          </TransitionSeries.Sequence>
          {isLast ? null : (
            <TransitionSeries.Transition
              presentation={i === 0 || i === NARASI.length - 2 ? fade() : slide({ direction: "from-right" })}
              timing={linearTiming({ durationInFrames: TRANSITION })}
            />
          )}
        </React.Fragment>
      );
    })}
  </TransitionSeries>
);
