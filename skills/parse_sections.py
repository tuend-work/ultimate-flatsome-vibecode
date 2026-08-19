# -*- coding: utf-8 -*-
import sys
import re
import json

if sys.platform == 'win32':
    try:
        sys.stdout.reconfigure(encoding='utf-8')
        sys.stderr.reconfigure(encoding='utf-8')
    except AttributeError:
        pass

with open('hoanglong_main_content.html', 'r', encoding='utf-8') as f:
    html = f.read()

# Let's find all section tags
sections = re.findall(r'<section\b[^>]*>[\s\S]*?</section>', html, re.IGNORECASE)
print(f"Total <section> tags found: {len(sections)}")

for i, sec in enumerate(sections):
    # Extract id and class
    id_match = re.search(r'id=["\']([^"\']+)["\']', sec)
    class_match = re.search(r'class=["\']([^"\']+)["\']', sec)
    sec_id = id_match.group(1) if id_match else 'no-id'
    sec_class = class_match.group(1) if class_match else 'no-class'
    
    # Extract headings
    h = re.findall(r'<h[1-6][^>]*>(.*?)</h[1-6]>', sec, re.DOTALL | re.IGNORECASE)
    clean_h = [re.sub(r'\s+', ' ', re.sub(r'<[^>]+>', ' ', x)).strip() for x in h]
    
    # Extract image sources
    imgs = re.findall(r'(?:src|data-src|data-lazy-src)=["\']([^"\'\s>]+)["\']', sec, re.IGNORECASE)
    
    # Extract background image
    bgs = re.findall(r'url\(["\']?([^"\'\)]+\.(?:png|jpg|jpeg|webp|svg|gif)[^"\'\)]*)["\']?\)', sec, re.IGNORECASE)
    
    print(f"\n--- SECTION {i+1} [ID: {sec_id}] [CLASS: {sec_class[:60]}...] ---")
    print(f"  Headings: {clean_h}")
    print(f"  Images count: {len(imgs)} | BGs: {bgs}")
    if imgs:
        print(f"  Sample Images: {imgs[:3]}")

# Also check footer
footer_match = re.search(r'<footer\b[^>]*>([\s\S]*?)</footer>', html, re.IGNORECASE)
if footer_match:
    print("\n--- FOOTER FOUND ---")
    footer_html = footer_match.group(1)
    f_h = re.findall(r'<h[1-6][^>]*>(.*?)</h[1-6]>', footer_html, re.DOTALL | re.IGNORECASE)
    print("  Footer headings:", [re.sub(r'\s+', ' ', re.sub(r'<[^>]+>', ' ', x)).strip() for x in f_h])
else:
    # Check if footer is outside main
    with open('hoanglong_raw.html', 'r', encoding='utf-8') as f:
        full_html = f.read()
    footer_match = re.search(r'<footer\b[^>]*>([\s\S]*?)</footer>', full_html, re.IGNORECASE)
    if footer_match:
        print("\n--- FOOTER FOUND IN FULL HTML ---")
        footer_html = footer_match.group(1)
        f_h = re.findall(r'<h[1-6][^>]*>(.*?)</h[1-6]>', footer_html, re.DOTALL | re.IGNORECASE)
        print("  Footer headings:", [re.sub(r'\s+', ' ', re.sub(r'<[^>]+>', ' ', x)).strip() for x in f_h])
