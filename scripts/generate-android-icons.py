#!/usr/bin/env python3
"""Generate Android + PWA launcher icons from the official MeD logo."""

from __future__ import annotations

from pathlib import Path

from PIL import Image

ROOT = Path(__file__).resolve().parents[1]
# Prefer the official blue-brand logo; fall back to legacy splash asset.
SOURCE_CANDIDATES = [
    ROOT / "resources" / "branding" / "med-logo-official.png",
    ROOT / "public" / "images" / "med-logo-official.png",
    ROOT / "android" / "app" / "src" / "main" / "res" / "drawable-nodpi" / "mmhc_logo_splash.png",
]
RES = ROOT / "android/app/src/main/res"
RESOURCES = ROOT / "resources"
PUBLIC = ROOT / "public"
PUBLIC_ICONS = PUBLIC / "icons"
PUBLIC_IMAGES = PUBLIC / "images"

# Brand royal blue from MeD logo
BRAND_BLUE = "#2E48A2"

DENSITIES = {
    "mipmap-mdpi": 48,
    "mipmap-hdpi": 72,
    "mipmap-xhdpi": 96,
    "mipmap-xxhdpi": 144,
    "mipmap-xxxhdpi": 192,
}

CANVAS = 1024
PADDING_RATIO = 0.10
PWA_SIZES = {
    "icon-192.png": 192,
    "icon-512.png": 512,
    "apple-touch-icon.png": 180,
}


def resolve_source() -> Path:
    for path in SOURCE_CANDIDATES:
        if path.is_file():
            return path
    raise FileNotFoundError(
        "No MeD logo source found. Place the official logo at:\n"
        f"  {SOURCE_CANDIDATES[0]}"
    )


def load_logo_rgba(source: Path) -> Image.Image:
    img = Image.open(source).convert("RGBA")
    # Only punch out near-black backgrounds (legacy splash). Leave brand blue intact.
    pixels = img.load()
    width, height = img.size
    sample = [
        pixels[2, 2][:3],
        pixels[width - 3, 2][:3],
        pixels[2, height - 3][:3],
        pixels[width - 3, height - 3][:3],
    ]
    corners_are_black = all(r < 40 and g < 40 and b < 40 for r, g, b in sample)
    if corners_are_black:
        for y in range(height):
            for x in range(width):
                r, g, b, a = pixels[x, y]
                if r < 40 and g < 40 and b < 40:
                    pixels[x, y] = (r, g, b, 0)
    bbox = img.getbbox()
    if bbox:
        img = img.crop(bbox)
    return img


def fit_logo(logo: Image.Image, size: int, background: str | tuple | None) -> Image.Image:
    if background is None:
        canvas = Image.new("RGBA", (size, size), (0, 0, 0, 0))
    elif isinstance(background, tuple):
        canvas = Image.new("RGBA", (size, size), background)
    else:
        canvas = Image.new("RGBA", (size, size), background)
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


def save_png(img: Image.Image, path: Path, *, keep_alpha: bool = False) -> None:
    path.parent.mkdir(parents=True, exist_ok=True)
    if keep_alpha or (img.mode == "RGBA" and path.name.endswith("_foreground.png")):
        img.save(path, format="PNG", optimize=True)
    else:
        img.convert("RGB").save(path, format="PNG", optimize=True)


def main() -> None:
    source = resolve_source()
    logo = load_logo_rgba(source)

    # Full-bleed brand blue icons (matches official MeD mark)
    master_blue = fit_logo(logo, CANVAS, BRAND_BLUE)
    # Soft white variant for in-app / light UI logo usage
    master_white = fit_logo(logo, CANVAS, "#FFFFFF")
    master_foreground = fit_logo(logo, CANVAS, None)

    RESOURCES.mkdir(parents=True, exist_ok=True)
    PUBLIC_IMAGES.mkdir(parents=True, exist_ok=True)
    PUBLIC_ICONS.mkdir(parents=True, exist_ok=True)

    master_blue.save(RESOURCES / "icon.png", format="PNG", optimize=True)
    master_foreground.save(RESOURCES / "icon-foreground.png", format="PNG", optimize=True)
    master_blue.save(PUBLIC_IMAGES / "med-logo-app.png", format="PNG", optimize=True)
    master_white.save(PUBLIC_IMAGES / "med-logo.png", format="PNG", optimize=True)

    # Keep splash / generator source in sync when using the official file
    splash_path = RES / "drawable-nodpi" / "mmhc_logo_splash.png"
    splash_path.parent.mkdir(parents=True, exist_ok=True)
    Image.open(source).convert("RGBA").save(splash_path, format="PNG", optimize=True)

    for folder, px in DENSITIES.items():
        out_dir = RES / folder
        save_png(fit_logo(logo, px, BRAND_BLUE), out_dir / "ic_launcher.png")
        save_png(fit_logo(logo, px, BRAND_BLUE), out_dir / "ic_launcher_round.png")
        save_png(fit_logo(logo, px, None), out_dir / "ic_launcher_foreground.png", keep_alpha=True)

    for name, px in PWA_SIZES.items():
        save_png(fit_logo(logo, px, BRAND_BLUE), PUBLIC_ICONS / name)
        if name == "apple-touch-icon.png":
            save_png(fit_logo(logo, px, BRAND_BLUE), PUBLIC / "apple-touch-icon.png")

    print("Generated MeD launcher icons (Android + PWA)")
    print(f"Source logo: {source}")
    print(f"Master icon: {RESOURCES / 'icon.png'}")
    print(f"PWA icons:   {PUBLIC_ICONS}")


if __name__ == "__main__":
    main()
