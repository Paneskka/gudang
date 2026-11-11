import { loadFont } from "@remotion/fonts";
import { staticFile } from "remotion";
import files from "./fonts.json";

// Font disajikan lokal dari public/fonts supaya render tidak butuh internet.
for (const font of files) {
  loadFont({
    family: font.family,
    url: staticFile(font.file),
    weight: font.weight,
    format: "woff2",
  });
}

export const display = "Space Grotesk";
export const body = "Inter";
