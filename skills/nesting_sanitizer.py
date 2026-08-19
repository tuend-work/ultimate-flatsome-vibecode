# -*- coding: utf-8 -*-
"""
Resolve all nested shortcodes using stack tokenizer
"""
import re

VBC_CONTAINERS = [
    'vbc_div', 'vbc_box', 'vbc_block', 'vbc_container', 'vbc_span', 'vbc_p', 'vbc_a',
    'vbc_card', 'vbc_post', 'vbc_h1', 'vbc_h2', 'vbc_h3', 'vbc_h4', 'vbc_h5', 'vbc_h6',
    'vbc_li', 'vbc_ul', 'vbc_ol', 'vbc_table', 'vbc_tr', 'vbc_td', 'vbc_th',
    'vbc_b', 'vbc_strong', 'vbc_em', 'vbc_u', 'vbc_testimonial', 'vbc_accordion',
    'vbc_accordion_item', 'vbc_slider', 'vbc_slide', 'vbc_fullpage'
]

def sanitize_nesting(content):
    fixed = content
    for tag in VBC_CONTAINERS:
        # Reset existing suffixes
        norm_pattern = re.compile(rf'\[(/?){tag}_inner(?:_\d+)?(\s[^\]]*)?\]', re.IGNORECASE)
        fixed = norm_pattern.sub(r'[\1' + tag + r'\2]', fixed)

        # Tokenize
        token_pattern = re.compile(rf'\[(/?){tag}(\s[^\]]*)?\]', re.IGNORECASE)
        tokens = []
        for m in token_pattern.finditer(fixed):
            tokens.append({
                'full': m.group(0),
                'is_close': m.group(1) == '/',
                'attrs': m.group(2) or '',
                'start': m.start(),
                'end': m.end()
            })

        if not tokens:
            continue

        stack = []
        replacements = []

        for t in tokens:
            if not t['is_close']:
                depth = len(stack) + 1
                if depth > 1:
                    suffix = '_inner' if depth == 2 else f'_inner_{depth - 2}'
                    target_tag = f"{tag}{suffix}"
                    new_open = f"[{target_tag}{t['attrs']}]"
                    replacements.append({
                        'start': t['start'],
                        'end': t['end'],
                        'new_text': new_open
                    })
                    stack.append(target_tag)
                else:
                    stack.append(tag)
            else:
                if stack:
                    expected = stack.pop()
                    if expected != tag:
                        replacements.append({
                            'start': t['start'],
                            'end': t['end'],
                            'new_text': f"[/{expected}]"
                        })

        replacements.sort(key=lambda x: x['start'], reverse=True)
        for r in replacements:
            fixed = fixed[:r['start']] + r['new_text'] + fixed[r['end']:]

    return fixed
