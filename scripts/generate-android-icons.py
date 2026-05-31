#!/usr/bin/env python3
"""Generate Android launcher icons from the MMHC logo splash asset."""

from __future__ import annotations

from pathlib import Path

from PIL import Image

ROOT = Path(__file__).resolve().parents[1]
SOURCE = ROOT / "android/app/src/main/res/drawable-nodpi/mmhc_logo_splash.png"
RES = ROOT / "android/app/src/main/res"
RESOURCES = ROOT / "resources"
PUBLIC = ROOT / "public"

DENSITIES = {
    "mipmap-mdpi": 48,
    "mipmap-hdpi": 72,
    "mipmap-xhdpi": 96,
    "mipmap-xxhdpi": 144,
    "mipmap-xxxhdpi": 192,
}

CANVAS = 1024
PADDING_RATIO = 0.12


def load_logo_rgba() -> Image.Image:
    img = Image.open(SOURCE).convert("RGBA")
    pixels = img.load()
    width, height = img.size
    for y in range(height):
        for x in range(width):
            r, g, b, a = pixels[x, y]
            if r < 40 and g < 40 and b < 40:
                pixels[x, y] = (r, g, b, 0)
    bbox = img.getbbox()
    if bbox:
        img = img.crop(bbox)
    return img


def fit_logo(logo: Image.Image, size: int, background: str | None) -> Image.Image:
    canvas = Image.new("RGBA", (size, size), background or (0, 0, 0, 0))
    pad = int(size * PADDING_RATIO)
    target_w = size - pad * 2
    target_h = size - pad * 2
    scale = min(target_w / logo.width, target_h / logo.height)
    new_w = max(1, int(logo.width * scale))
    new_h = max(1, int(logo.height * scale))
    resized = logo.resize((new_w, new_h), Image.Resampling.LANCZOS)
    x = (size - new_w) // 2
    y = (size - new_h) // 2
    canvas.paste(resized, (x, y), resized)
    return canvas


def save_png(img: Image.Image, path: Path) -> None:
    path.parent.mkdir(parents=True, exist_ok=True)
    if img.mode == "RGBA" and path.name.endswith("_foreground.png"):
        img.save(path, format="PNG", optimize=True)
    else:
        img.convert("RGB").save(path, format="PNG", optimize=True)


def main() -> None:
    logo = load_logo_rgba()

    master_white = fit_logo(logo, CANVAS, "#FFFFFF")
    master_foreground = fit_logo(logo, CANVAS, None)

    RESOURCES.mkdir(parents=True, exist_ok=True)
    PUBLIC_IMAGES = PUBLIC / "images"
    PUBLIC_IMAGES.mkdir(parents=True, exist_ok=True)

    master_white.save(RESOURCES / "icon.png", format="PNG", optimize=True)
    master_foreground.save(RESOURCES / "icon-foreground.png", format="PNG", optimize=True)
    master_white.save(PUBLIC_IMAGES / "med-logo.png", format="PNG", optimize=True)
    logo_rgb = logo.convert("RGB")
    logo_rgb.save(PUBLIC_IMAGES / "med-logo-app.png", format="PNG", optimize=True)

    for folder, px in DENSITIES.items():
        out_dir = RES / folder
        save_png(fit_logo(logo, px, "#FFFFFF"), out_dir / "ic_launcher.png")
        save_png(fit_logo(logo, px, "#FFFFFF"), out_dir / "ic_launcher_round.png")
        save_png(fit_logo(logo, px, None), out_dir / "ic_launcher_foreground.png")

    print("Generated MMHC launcher icons in android/app/src/main/res/mipmap-*")
    print(f"Source logo: {SOURCE}")
    print(f"Master icon: {RESOURCES / 'icon.png'}")


if __name__ == "__main__":
    main()
