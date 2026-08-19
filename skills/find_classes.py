# -*- coding: utf-8 -*-
import re

with open('ultimate-flatsome-vibecode/ultimate-flatsome-vibecode.php', 'r', encoding='utf-8') as f:
    content = f.read()

matches = re.finditer(r'function\s+(vbc_[a-zA-Z0-9_]+_shortcode|vbc_shortcode_renderer|vbc_[a-zA-Z0-9_]+_renderer)', content)
for m in matches:
    print(m.group(0), "at pos", m.start())
