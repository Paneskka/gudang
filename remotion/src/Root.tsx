import React from "react";
import { Composition } from "remotion";
import { GudangPromo } from "./GudangPromo";
import { FPS, HEIGHT, WIDTH } from "./theme";
import { TOTAL_FRAMES } from "./timing";

export const RemotionRoot: React.FC = () => (
  <Composition
    id="GudangPromo"
    component={GudangPromo}
    durationInFrames={TOTAL_FRAMES}
    fps={FPS}
    width={WIDTH}
    height={HEIGHT}
  />
);
