#!/usr/bin/env python3
"""
Parse AutoCAD DXF file containing PV module labels (T01-N##-S##)
and generate a CSV with visual_row, visual_col positions.

Usage:
    pip install ezdxf
    python3 scripts/parse_pv_dxf.py T01_layout.dxf pv_layout.csv
"""

import re
import csv
import sys

try:
    import ezdxf
except ImportError:
    print("Install ezdxf first: pip install ezdxf")
    sys.exit(1)


def cluster_values(values, threshold=50):
    """Group nearby coordinate values into discrete grid positions."""
    if not values:
        return []
    sorted_vals = sorted(set(values))
    clusters = []
    current = [sorted_vals[0]]

    for v in sorted_vals[1:]:
        if v - current[-1] <= threshold:
            current.append(v)
        else:
            clusters.append(sum(current) / len(current))
            current = [v]
    clusters.append(sum(current) / len(current))
    return clusters


def find_cluster_index(value, clusters, threshold):
    for i, c in enumerate(clusters):
        if abs(value - c) <= threshold:
            return i + 1
    return -1


def extract_pv_layout(dxf_path, output_csv, threshold=50):
    print(f"Reading: {dxf_path}")
    doc = ezdxf.readfile(dxf_path)
    msp = doc.modelspace()

    modules = []
    pattern = re.compile(r'T(\d+)[- ]N(\d+)[- ]S(\d+)', re.IGNORECASE)

    for entity in msp:
        text = None
        x = y = None

        if entity.dxftype() == 'TEXT':
            text = entity.dxf.text
            x = entity.dxf.insert.x
            y = entity.dxf.insert.y
        elif entity.dxftype() == 'MTEXT':
            try:
                text = entity.plain_mtext()
            except Exception:
                text = entity.dxf.text
            x = entity.dxf.insert.x
            y = entity.dxf.insert.y

        if text:
            match = pattern.search(text.strip())
            if match:
                modules.append({
                    'block': f"T{int(match.group(1)):02d}",
                    'string': int(match.group(2)),
                    'slot': int(match.group(3)),
                    'x': x,
                    'y': y,
                })

    if not modules:
        print("ERROR: No PV module labels found (pattern: T##-N##-S##)")
        sys.exit(1)

    print(f"Found {len(modules)} PV modules")

    # Build grid: cluster X (left→right = col 1,2,3...) and Y (top→bottom = row 1,2,3...)
    x_clusters = cluster_values([m['x'] for m in modules], threshold)
    y_clusters_asc = cluster_values([m['y'] for m in modules], threshold)
    y_clusters_desc = list(reversed(y_clusters_asc))  # top of drawing = row 1

    print(f"Grid detected: {len(y_clusters_desc)} rows x {len(x_clusters)} columns")

    with open(output_csv, 'w', newline='') as f:
        writer = csv.writer(f)
        writer.writerow(['transformer_block', 'string_number', 'module_slot', 'visual_row', 'visual_col'])

        errors = 0
        for m in sorted(modules, key=lambda x: (x['block'], x['string'], x['slot'])):
            col = find_cluster_index(m['x'], x_clusters, threshold)
            row = find_cluster_index(m['y'], y_clusters_desc, threshold)

            if col == -1 or row == -1:
                print(f"  WARN: Could not place {m['block']}-N{m['string']:02d}-S{m['slot']:02d} (x={m['x']:.1f}, y={m['y']:.1f})")
                errors += 1
                continue

            writer.writerow([m['block'], m['string'], m['slot'], row, col])

    print(f"Done! Exported to: {output_csv}")
    if errors:
        print(f"  {errors} modules could not be placed -- try increasing threshold (current: {threshold})")
    print(f"\nNext step:")
    print(f"  php artisan cmms:import-pv-layout {output_csv}")


if __name__ == '__main__':
    if len(sys.argv) < 3:
        print("Usage: python3 parse_pv_dxf.py <input.dxf> <output.csv> [threshold=50]")
        sys.exit(1)

    dxf_file = sys.argv[1]
    csv_file = sys.argv[2]
    thresh = int(sys.argv[3]) if len(sys.argv) > 3 else 50

    extract_pv_layout(dxf_file, csv_file, threshold=thresh)
