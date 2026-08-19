# -*- coding: utf-8 -*-
import sys
sys.stdout.reconfigure(encoding='utf-8')
with open('ultimate-flatsome-vibecode/ultimate-flatsome-vibecode.php', 'r', encoding='utf-8') as f:
    lines = f.readlines()

for i, l in enumerate(lines):
    if 'shortcode_atts(' in l:
        print(f"Line {i+1}: {l.strip()}")
        for j in range(i+1, min(i+15, len(lines))):
            print(f"  {lines[j].strip()}")
            if ');' in lines[j]:
                break
        print("---")
