from __future__ import annotations

from collections import deque
from pathlib import Path

from PIL import Image, ImageFilter


ROOT = Path(__file__).resolve().parents[4]

SOURCE_IMAGES = [
    "wp-content/uploads/2026/06/autel-alpha-1.jpg",
    "wp-content/uploads/2026/06/autel-dragonfish.jpg",
    "wp-content/uploads/2026/06/autel-evo-lite-640t-1.jpg",
    "wp-content/uploads/2026/06/autel-evo-max-4n.jpg",
    "wp-content/uploads/2026/06/01-DJI-Agras-T10-drone-1.jpg",
    "wp-content/uploads/2026/06/01-DJI-Agras-T30-drone.jpg",
    "wp-content/uploads/2026/06/dji-agras-t25.png",
    "wp-content/uploads/2026/06/dji-agras-t25p-scaled-1.png",
    "wp-content/uploads/2026/06/dji-agras-t70p.png",
    "wp-content/uploads/2026/06/02-DJI-Matrice-30-drone.png",
    "wp-content/uploads/2026/06/02-DJI-Mavic-30T-drone.jpg",
    "wp-content/uploads/2026/06/02-DJI-Matrice-350-RTK-dron.jpg",
    "wp-content/uploads/2026/06/dji-matrice-4e.jpg",
    "wp-content/uploads/2026/06/dji-matrice-4t-6.jpg",
    "wp-content/uploads/2026/06/03-Mavic-3E-drone-scaled-1.jpg",
    "wp-content/uploads/2026/06/01-DJI-Mavic-3T-drone.jpg",
    "wp-content/uploads/2026/06/dji-agras-t100-body-2.png",
    "wp-content/uploads/2026/06/dji_agras_t50_enterra_agrodrony_render_2-1.png",
    "wp-content/uploads/2026/06/dji-matrice-400-rtk.jpg",
    "wp-content/uploads/2026/06/01-DJI-Mavic-3M-drone-1.jpg",
]


def corner_average(pixels, width: int, height: int) -> tuple[int, int, int]:
    points = [
        (0, 0),
        (width - 1, 0),
        (0, height - 1),
        (width - 1, height - 1),
        (width // 2, 0),
        (width // 2, height - 1),
        (0, height // 2),
        (width - 1, height // 2),
    ]
    samples = [pixels[x, y][:3] for x, y in points]
    count = len(samples)
    return (
        sum(color[0] for color in samples) // count,
        sum(color[1] for color in samples) // count,
        sum(color[2] for color in samples) // count,
    )


def is_background(pixel: tuple[int, int, int, int], background: tuple[int, int, int]) -> bool:
    r, g, b = pixel[:3]
    br, bg, bb = background
    max_delta = max(abs(r - br), abs(g - bg), abs(b - bb))
    min_channel = min(r, g, b)
    return max_delta <= 34 and min_channel >= 210


def build_background_mask(image: Image.Image) -> Image.Image:
    rgba = image.convert("RGBA")
    width, height = rgba.size
    pixels = rgba.load()
    background = corner_average(pixels, width, height)

    visited = bytearray(width * height)
    queue: deque[tuple[int, int]] = deque()

    def add_seed(x: int, y: int) -> None:
        index = y * width + x
        if visited[index]:
            return
        if not is_background(pixels[x, y], background):
            return
        visited[index] = 1
        queue.append((x, y))

    for x in range(width):
        add_seed(x, 0)
        add_seed(x, height - 1)

    for y in range(height):
        add_seed(0, y)
        add_seed(width - 1, y)

    while queue:
        x, y = queue.popleft()
        for nx, ny in ((x - 1, y), (x + 1, y), (x, y - 1), (x, y + 1)):
            if nx < 0 or ny < 0 or nx >= width or ny >= height:
                continue
            index = ny * width + nx
            if visited[index]:
                continue
            if not is_background(pixels[nx, ny], background):
                continue
            visited[index] = 1
            queue.append((nx, ny))

    mask = Image.new("L", (width, height), 255)
    mask_pixels = mask.load()
    for y in range(height):
        row_offset = y * width
        for x in range(width):
            if visited[row_offset + x]:
                mask_pixels[x, y] = 0

    return mask.filter(ImageFilter.GaussianBlur(radius=1.2))


def export_transparent_png(source_path: Path) -> Path:
    image = Image.open(source_path).convert("RGBA")
    alpha_mask = build_background_mask(image)
    image.putalpha(alpha_mask)
    target_path = source_path.with_suffix("").with_name(source_path.stem + "-transparent").with_suffix(".png")
    image.save(target_path)
    return target_path


def main() -> None:
    for relative_path in SOURCE_IMAGES:
        source_path = ROOT / relative_path
        if not source_path.exists():
            print(f"missing\t{relative_path}")
            continue
        target_path = export_transparent_png(source_path)
        print(f"created\t{target_path.relative_to(ROOT)}")


if __name__ == "__main__":
    main()
