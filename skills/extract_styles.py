# -*- coding: utf-8 -*-
import sys
import re

if sys.platform == 'win32':
    try:
        sys.stdout.reconfigure(encoding='utf-8')
        sys.stderr.reconfigure(encoding='utf-8')
    except AttributeError:
        pass

with open('hoanglong_raw.html', 'r', encoding='utf-8') as f:
    html = f.read()

# Find custom CSS or style tags
styles = re.findall(r'<style\b[^>]*>([\s\S]*?)</style>', html, re.IGNORECASE)
print(f"Total <style> blocks: {len(styles)}")

all_custom_css = "\n".join(styles)

# Filter out standard reset styles, look for .banner-bus, .booking-form, .sec-dv, etc.
important_classes = ['banner-bus', 'btn-call', 'btn-zalo', 'booking-form', 'sec-dv', 'sec-why', 'sec-qt', 'sec-kh', 'two-col', 'box-dv', 'why-item', 'qt-item']
found_css = []
for block in styles:
    for cls in important_classes:
        if cls in block:
            found_css.append(block)
            break

print(f"Found {len(found_css)} relevant custom CSS blocks")
with open('hoanglong_custom.css', 'w', encoding='utf-8') as f:
    f.write("\n\n/* --- EXTRACTED CSS --- */\n\n".join(found_css))

print("Saved to hoanglong_custom.css")
