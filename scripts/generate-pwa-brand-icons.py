#!/usr/bin/env python3
"""Paint default MeD PWA PNG icons (blue bg + green heart + white pills).

Run on your machine or the server:
  pip install pillow
  python scripts/generate-pwa-brand-icons.py
"""

from __future__ import annotations

from pathlib import Path

from PIL import Image, ImageDraw

ROOT = Path(__file__).resolve().parents[1]
OUT = ROOT / "public" / "icons"
BRAND_BLUE = (0x2E, 0x48, 0xA2, 255)
GREEN = (0x55, 0xB7, 0x76, 255)
WHITE = (255, 255, 255, 255)

SIZES = {
    "icon-192.png": 192,
    "icon-512.png": 512,
    "apple-touch-icon.png": 180,
}


def draw_mark(size: int) -> Image.Image:
    img = Image.new("RGBA", (size, size), BRAND_BLUE)
    draw = ImageDraw.Draw(img)

    cx = cy = size / 2
    # White crossed pills
    pill_w = size * 0.11
    pill_h = size * 0.42
    for angle in (-38, 38):
        # Draw via rotated rectangle: paste a rotated pill onto canvas
        pill = Image.new("RGBA", (int(pill_w), int(pill_h)), (0, 0, 0, 0))
        pd = ImageDraw.Draw(pill)
        pd.rounded_rectangle([0, 0, pill_w - 1, pill_h - 1], radius=pill_w / 2, fill=WHITE)
        rotated = pill.rotate(angle, expand=True, resample=Image.Resampling.BICUBIC)
        rx = int(cx - rotated.width / 2)
        ry = int(cy - rotated.height / 2 + size * 0.06)
        img.alpha_composite(rotated, (rx, ry))

    # Green heart (simplified two lobes + triangle point)
    hs = size * 0.28
    hx, hy = cx, cy - size * 0.12
    # lobes
    r = hs * 0.42
    draw.ellipse([hx - hs * 0.55, hy - r, hx - hs * 0.55 + 2 * r, hy + r], fill=GREEN)
    draw.ellipse([hx + hs * 0.55 - 2 * r, hy - r, hx + hs * 0.55, hy + r], fill=GREEN)
    # lower diamond/triangle body
    draw.polygon(
        [
            (hx - hs * 0.72, hy + r * 0.15),
            (hx + hs * 0.72, hy + r * 0.15),
            (hx, hy + hs * 0.95),
        ],
        fill=GREEN,
    )

    return img.convert("RGB")


def main() -> None:
    OUT.mkdir(parents=True, exist_ok=True)
    for name, px in SIZES.items():
        path = OUT / name
        draw_mark(px).save(path, format="PNG", optimize=True)
        print(f"Wrote {path}")
    # root apple-touch copy
    apple = OUT / "apple-touch-icon.png"
    (ROOT / "public" / "apple-touch-icon.png").write_bytes(apple.read_bytes())
    print(f"Wrote {ROOT / 'public' / 'apple-touch-icon.png'}")


if __name__ == "__main__":
    main()
