"""SG-005: pull WHOLE pictures out of the client catalogue decks.

Rule (operator, 2026-10-17): an image taken from a deck must be one whole
picture from inside it. Never screenshot or crop a slide/page, never split a
picture. Where a PDF page is one flattened scan (hotel supplies, JW profiles),
each photo is cut exactly at its own frame edges (boxes measured by edge
detection on the page raster, see ops/memory/sino-good/ SG-005 note).

Usage:  python -I scripts/extract_whole_images.py <downloads_dir> <out_dir>
Output: <out_dir>/<name>.jpg + cut-log.json (source deck, page, xref, box).
"""
import json
import sys
from pathlib import Path

import pymupdf
from PIL import Image

HOTEL_FULL = "挚心成酒店用品画册.pdf"  # full-resolution edition (2481px pages)
JW = "JW定制型材.pdf"
OVERVIEW = "SINO GOOD QY Supply Chain - One-stop Building Materials Sourcing Partner.pdf"
JIAYI = "嘉翊电子版_第8版_2026_B.pdf"
WALL = "墙板产品介绍Introduction to Wall Panel Products.pdf"

# (deck, page, xref) -> {output name: inclusive box (x0, y0, x1, y1)} or None = whole embedded image
JOBS = [
    (HOTEL_FULL, 15, 69, {
        "hotel-supplies_coffee-grounds-amenities": (140, 295, 1800, 1954),
        "hotel-supplies_coffee-grounds-toiletries": (1281, 1997, 2385, 3100),
    }),
    (HOTEL_FULL, 23, 109, {"hotel-supplies_acrylic-amenity-kit": (140, 299, 1800, 1958)}),
    (HOTEL_FULL, 60, 294, {"hotel-supplies_hilton-memory-foam-pillow": (165, 278, 2314, 3110)}),
    (JW, 8, 21, {
        "jw-profiles_p08-room1": (1116, 383, 2284, 1099),
        "jw-profiles_p08-room2": (1116, 1110, 2284, 1847),
        "jw-profiles_p08-room3": (1117, 1882, 2284, 2619),
        "jw-profiles_p08-room4": (1117, 2658, 2284, 3395),
    }),
    (JW, 20, 57, {
        "jw-profiles_p20-room1": (1112, 381, 2270, 1077),
        "jw-profiles_p20-room2": (1113, 1106, 2270, 1835),
        "jw-profiles_p20-room3": (1109, 1874, 2260, 2603),
        "jw-profiles_p20-room4": (1111, 2642, 2260, 3371),
    }),
    # Whole embedded pictures replacing slide screenshots used as banners / page heroes.
    (OVERVIEW, 6, 22, {"banner_villa-dusk": None}),
    (JIAYI, 36, 209, {"banner_rattan-lounge": None}),
    (WALL, 10, 71, {"banner_wall-panel-living": None}),
    # Jiayi cover is one flattened page: company text on top, the poolside photo below
    # (its top edge fades into the page white from row 669). Cut the photo only.
    (JIAYI, 1, 1024, {"banner_infinity-pool": (0, 669, 1180, 1643)}),
]


def page_image(doc, page_no, xref):
    page = doc[page_no - 1]
    if xref is None:  # largest picture on the page
        xref = max(page.get_images(full=True), key=lambda im: im[2] * im[3])[0]
    pix = pymupdf.Pixmap(doc, xref)
    if pix.n - pix.alpha != 3:
        pix = pymupdf.Pixmap(pymupdf.csRGB, pix)
    if pix.alpha:
        pix = pymupdf.Pixmap(pix, 0)
    return xref, Image.frombytes("RGB", (pix.width, pix.height), pix.samples)


def main(src_dir: Path, out_dir: Path) -> None:
    out_dir.mkdir(parents=True, exist_ok=True)
    log = {}
    docs = {}
    for deck, page_no, xref, cuts in JOBS:
        doc = docs.setdefault(deck, pymupdf.open(src_dir / deck))
        used_xref, im = page_image(doc, page_no, xref)
        for name, box in cuts.items():
            pic = im if box is None else im.crop((box[0], box[1], box[2] + 1, box[3] + 1))
            pic.save(out_dir / f"{name}.jpg", quality=92, optimize=True)
            log[f"{name}.jpg"] = {
                "deck": deck, "page": page_no, "xref": used_xref,
                "box_inclusive": box, "size": pic.size,
                "method": "whole embedded image" if box is None else "cut at the photo's own frame edges",
            }
            print(f"{name}.jpg", pic.size)
    (out_dir / "cut-log.json").write_text(json.dumps(log, ensure_ascii=False, indent=1), encoding="utf-8")


if __name__ == "__main__":
    main(Path(sys.argv[1]), Path(sys.argv[2]))
