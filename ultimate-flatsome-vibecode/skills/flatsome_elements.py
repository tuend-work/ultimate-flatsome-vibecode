"""
flatsome_elements.py — Flatsome Native + vbc_section Builder
=============================================================
Kiến trúc 2-Pass:
  [vbc_section id="sec-hero" custom_css="selector {...} selector .title {...}"]
    [section bg_color="..." class="sec-hero"]
      [ux_banner ...][text_box]...[/text_box][/ux_banner]
    [/section]
  [/vbc_section]

Quy tắc CSS:
  - "selector" = #id wrapper (PHP thay runtime)
  - CSS của TẤT CẢ phần tử con đặt trong custom_css của vbc_section cha
  - KHÔNG dùng CSS inline hoặc thẻ <style> trong post_content
"""

import re


def esc(text: str) -> str:
    if not text:
        return ""
    return str(text).replace('"', "&quot;").replace('\n', ' ').strip()


def make_section_id(name: str) -> str:
    slug = re.sub(r'[^a-z0-9]+', '-', name.lower().strip())
    return f"sec-{slug.strip('-')}"


class FlatSection:
    """
    Đại diện 1 section. Output:
      [vbc_section id="{id}" custom_css="{scoped_css}"]
        [section bg_color="{bg}" padding="{padding}" class="{class}"]
          ... Flatsome elements ...
        [/section]
      [/vbc_section]
    CSS rules -> custom_css của [vbc_section]:
      selector { ... }
      selector .child { ... }
      selector:hover { ... }
      @media (...) { selector { ... } }
    """

    def __init__(self, section_id: str, section_class: str = "",
                 bg_color: str = "", padding: str = "60px"):
        self.section_id = section_id
        self.section_class = section_class or section_id
        self.bg_color = bg_color
        self.padding = padding
        self._inner = []
        self._css = {}

    def add(self, sc: str):
        if sc:
            self._inner.append(sc.strip())

    def add_css(self, suffix: str, props: str):
        if suffix in self._css:
            self._css[suffix] += " " + props.strip()
        else:
            self._css[suffix] = props.strip()

    def build(self) -> str:
        css_parts = []
        for suffix, props in self._css.items():
            if suffix.startswith("@media"):
                css_parts.append(f"{suffix} {{ selector {{ {props} }} }}")
            elif suffix == "":
                css_parts.append(f"selector {{ {props} }}")
            else:
                css_parts.append(f"selector{suffix} {{ {props} }}")
        css_str = " ".join(css_parts)

        inner = "\n".join(self._inner)

        sec_atts = []
        if self.bg_color:
            sec_atts.append(f'bg_color="{self.bg_color}"')
        if self.padding:
            sec_atts.append(f'padding="{self.padding}"')
        if self.section_class:
            sec_atts.append(f'class="{self.section_class}"')
        flatsome_sec = f'[section {" ".join(sec_atts)}]\n{inner}\n[/section]'

        vbc_atts = [f'id="{self.section_id}"']
        if css_str:
            safe = css_str.replace('"', "'")
            vbc_atts.append(f'custom_css="{safe}"')
        return f'[vbc_section {" ".join(vbc_atts)}]\n{flatsome_sec}\n[/vbc_section]'


# ── Flatsome Element Helpers ──────────────────────────────────────────────────────

def make_title(text, tag="h2", style="bold-center", sub_text="", color=""):
    atts = [f'text="{esc(text)}"', f'tag_name="{tag}"', f'style="{style}"']
    if sub_text: atts.append(f'sub_text="{esc(sub_text)}"')
    if color:    atts.append(f'color="{color}"')
    return f'[title {" ".join(atts)}]'


def make_divider(width="60px", color="#b20000", align="center", margin="10px"):
    return f'[divider width="{width}" color="{color}" align="{align}" margin="{margin}"]'


def make_button(text, link, color="primary", size="large", style="",
                icon="", target="_self", expand="", class_=""):
    atts = [f'text="{esc(text)}"', f'link="{link}"', f'color="{color}"']
    if size:              atts.append(f'size="{size}"')
    if style:             atts.append(f'style="{style}"')
    if icon:              atts.append(f'icon="{icon}"')
    if target != "_self": atts.append(f'target="{target}"')
    if expand:            atts.append(f'expand="{expand}"')
    if class_:            atts.append(f'class="{class_}"')
    return f'[button {" ".join(atts)}]'


def make_ux_banner(bg_url, height="500px", height_sm="300px",
                   bg_overlay="", bg_pos="center center", content="", class_=""):
    atts = [f'height="{height}"', f'height__sm="{height_sm}"']
    if bg_url:     atts.append(f'bg="{bg_url}"')
    if bg_overlay: atts.append(f'bg_overlay="{bg_overlay}"')
    if bg_pos:     atts.append(f'bg_pos="{bg_pos}"')
    if class_:     atts.append(f'class="{class_}"')
    return f'[ux_banner {" ".join(atts)}]{content or ""}[/ux_banner]'


def make_text_box(content, position_x="50", position_y="50",
                  width="60", text_color="light", text_align="center", padding=""):
    atts = [f'position_x="{position_x}"', f'position_y="{position_y}"',
            f'width="{width}"', f'text_color="{text_color}"', f'text_align="{text_align}"']
    if padding: atts.append(f'padding="{padding}"')
    return f'[text_box {" ".join(atts)}]{content}[/text_box]'


def make_row(content, gap="", v_align="", h_align="", width=""):
    atts = []
    if gap:     atts.append(f'gap="{gap}"')
    if v_align: atts.append(f'v_align="{v_align}"')
    if h_align: atts.append(f'h_align="{h_align}"')
    if width:   atts.append(f'width="{width}"')
    prefix = f'[row {" ".join(atts)}]' if atts else '[row]'
    return f'{prefix}\n{content}\n[/row]'


def make_col(content, span="4", span_sm="12", span_md=""):
    atts = [f'span="{span}"', f'span__sm="{span_sm}"']
    if span_md: atts.append(f'span__md="{span_md}"')
    return f'[col {" ".join(atts)}]\n{content}\n[/col]'


def make_ux_image_box(img_url, title="", content="", link="",
                      style="normal", image_hover="zoom",
                      text_pos="bottom", text_align="center", class_=""):
    atts = [f'img="{img_url}"', f'text_pos="{text_pos}"', f'text_align="{text_align}"']
    if style:       atts.append(f'style="{style}"')
    if image_hover: atts.append(f'image_hover="{image_hover}"')
    if link:        atts.append(f'link="{link}"')
    if class_:      atts.append(f'class="{class_}"')
    inner = (f'[title text="{esc(title)}" tag_name="h3"]' if title else '') + (content or '')
    return f'[ux_image_box {" ".join(atts)}]{inner}[/ux_image_box]'


def make_featured_box(img_url, title, content="",
                      pos="top", link="", img_width="60", class_=""):
    atts = [f'img="{img_url}"', f'title="{esc(title)}"',
            f'pos="{pos}"', f'img_width="{img_width}"']
    if link:   atts.append(f'link="{link}"')
    if class_: atts.append(f'class="{class_}"')
    return f'[featured_box {" ".join(atts)}]{content}[/featured_box]'


def make_testimonial(content, name, company="",
                     stars="5", image="", pos="left"):
    atts = [f'name="{esc(name)}"', f'stars="{stars}"', f'pos="{pos}"']
    if company: atts.append(f'company="{esc(company)}"')
    if image:   atts.append(f'image="{image}"')
    return f'[testimonial {" ".join(atts)}]{content}[/testimonial]'


def make_accordion(items, faq_schema=True, open_item="1"):
    schema = "true" if faq_schema else "false"
    items_str = "".join(
        f'[accordion-item title="{esc(i.get("title",""))}"]{i.get("content","") }[/accordion-item]\n'
        for i in items
    )
    return f'[accordion faq_schema="{schema}" open="{open_item}"]\n{items_str}[/accordion]'


def make_cf7_form(form_id, title=""):
    t = f' title="{esc(title)}"' if title else ''
    return f'[contact-form-7 id="{form_id}"{t}]'


def make_gap(height="30px"):
    return f'[gap height="{height}"]'


# ── Section Header ────────────────────────────────────────────────────────────────

def section_header(title, subtitle="", divider_color="#b20000"):
    parts = [make_title(title, tag="h2", style="bold-center", sub_text=subtitle),
             make_divider(width="60px", color=divider_color, align="center")]
    return "\n".join(parts)


def _span(cols):
    return {2: "6", 3: "4", 4: "3", 6: "2"}.get(cols, "4")


# ── Preset Builders ───────────────────────────────────────────────────────────────

def build_hero_section(section_id, bg_url, heading, subheading="",
                       cta_text="", cta_link="", bg_overlay="rgba(0,0,0,0.5)",
                       height="520px", height_sm="320px",
                       heading_color="#ffffff", heading_size="42px"):
    sec = FlatSection(section_id, bg_color="", padding="0")
    tb = f'[title text="{esc(heading)}" tag_name="h1" style="normal"]'
    if subheading: tb += f'\n<p>{esc(subheading)}</p>'
    if cta_text and cta_link:
        tb += f'\n{make_button(cta_text, cta_link, color="primary", size="xlarge")}'
    banner = make_ux_banner(
        bg_url=bg_url, height=height, height_sm=height_sm,
        bg_overlay=bg_overlay, bg_pos="center center",
        content=make_text_box(tb, "50", "50", "70", "light", "center")
    )
    sec.add(banner)
    sec.add_css("", "position: relative; overflow: hidden;")
    sec.add_css(" h1 .section-title-main",
                f"font-size: {heading_size}; color: {heading_color}; font-weight: 900; text-transform: uppercase; line-height: 1.2;")
    sec.add_css(" p", "color: rgba(255,255,255,0.9); font-size: 18px; margin: 16px 0 24px;")
    return sec


def build_cards_section(section_id, title, cards, cols=3,
                        bg_color="#f8fafc", padding="60px", divider_color="#b20000"):
    sec = FlatSection(section_id, section_class=section_id, bg_color=bg_color, padding=padding)
    sec.add(section_header(title, divider_color=divider_color))
    sec.add(make_gap("30px"))
    col_items = []
    for c in cards:
        ib = make_ux_image_box(
            img_url=c.get("img", ""), title=c.get("title", ""),
            content=f'<p>{esc(c.get("desc",""))}</p>' if c.get("desc") else "",
            link=c.get("link", ""), image_hover="zoom"
        )
        col_items.append(make_col(ib, span=_span(cols), span_sm="12", span_md="6"))
    sec.add(make_row("\n".join(col_items), gap="24px"))
    sec.add_css(" .section-title", "font-size: 28px; font-weight: 900; color: #0f172a;")
    sec.add_css(" .box", "border-radius: 12px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.06); transition: transform 0.3s ease, box-shadow 0.3s ease;")
    sec.add_css(" .box:hover", "transform: translateY(-4px); box-shadow: 0 12px 32px rgba(0,0,0,0.12);")
    sec.add_css(" .box-image img", "height: 220px; object-fit: cover; width: 100%;")
    sec.add_css(" .box-text", "padding: 20px; background: #ffffff;")
    sec.add_css(" .box-text h3", "font-size: 16px; font-weight: 700; color: #0f172a;")
    return sec


def build_features_section(section_id, title, features, cols=3,
                            bg_color="#ffffff", padding="60px", icon_color="#b20000"):
    sec = FlatSection(section_id, section_class=section_id, bg_color=bg_color, padding=padding)
    sec.add(section_header(title))
    sec.add(make_gap("30px"))
    col_items = []
    for f in features:
        fb = make_featured_box(
            img_url=f.get("img", ""), title=f.get("title", ""),
            content=f'<p>{esc(f.get("desc",""))}</p>' if f.get("desc") else "",
            pos="top", img_width="60"
        )
        col_items.append(make_col(fb, span=_span(cols), span_sm="12", span_md="6"))
    sec.add(make_row("\n".join(col_items), gap="24px"))
    sec.add_css(" .featured-box", "text-align: center; padding: 30px 20px;")
    sec.add_css(" .icon-box-img", f"color: {icon_color};")
    sec.add_css(" .icon-box-text h5", "font-size: 18px; font-weight: 700; color: #0f172a; text-transform: none;")
    sec.add_css(" .icon-box-text p", "font-size: 14px; color: #64748b; line-height: 1.7;")
    return sec


def build_testimonials_section(section_id, title, testimonials, cols=3,
                                bg_color="#0f172a", padding="70px"):
    sec = FlatSection(section_id, section_class=section_id, bg_color=bg_color, padding=padding)
    sec.add(section_header(title, divider_color="#b20000"))
    sec.add(make_gap("30px"))
    col_items = []
    for t in testimonials:
        tst = make_testimonial(
            content=t.get("content", ""), name=t.get("name", ""),
            company=t.get("company", ""), stars=str(t.get("stars", "5")),
            image=t.get("image", ""), pos="left"
        )
        col_items.append(make_col(tst, span=_span(cols), span_sm="12", span_md="6"))
    sec.add(make_row("\n".join(col_items), gap="24px"))
    sec.add_css(" .section-title", "color: #ffffff; font-size: 28px;")
    sec.add_css(" .is-divider", "background-color: #b20000;")
    sec.add_css(" .testimonial-box", "background: rgba(255,255,255,0.06); border-radius: 16px; padding: 28px;")
    sec.add_css(" .testimonial-text", "color: #cbd5e1; font-size: 14px; line-height: 1.8; font-style: italic;")
    sec.add_css(" .testimonial-name", "color: #ffffff; font-weight: 700;")
    sec.add_css(" .testimonial-company", "color: #94a3b8;")
    return sec


def build_faq_section(section_id, title, faq_items, bg_color="#f8fafc",
                      padding="60px", accent_color="#b20000"):
    sec = FlatSection(section_id, section_class=section_id, bg_color=bg_color, padding=padding)
    sec.add(section_header(title))
    sec.add(make_gap("30px"))
    items = [{"title": f["question"], "content": f["answer"]} for f in faq_items]
    sec.add(make_accordion(items, faq_schema=True, open_item="1"))
    sec.add_css(" .section-title", "font-size: 28px; color: #0f172a;")
    sec.add_css(" .accordion", "border-radius: 12px; overflow: hidden; box-shadow: 0 2px 12px rgba(0,0,0,0.06); background: #ffffff;")
    sec.add_css(" .accordion-title", f"font-size: 15px; font-weight: 700; color: #0f172a; padding: 18px 22px;")
    sec.add_css(" .accordion-title.active", f"color: {accent_color};")
    sec.add_css(" .accordion-inner", "font-size: 14px; color: #475569; line-height: 1.75; padding: 20px 22px;")
    return sec


def build_contact_section(section_id, title, cf7_form_id,
                          bg_color="#ffffff", padding="60px", accent_color="#b20000"):
    sec = FlatSection(section_id, section_class=section_id, bg_color=bg_color, padding=padding)
    sec.add(section_header(title))
    sec.add(make_gap("30px"))
    sec.add(f'[row][col span="12"]\n{make_cf7_form(cf7_form_id, title)}\n[/col][/row]')
    sec.add_css(" .section-title", "font-size: 28px; color: #0f172a;")
    sec.add_css(" .wpcf7-form", "max-width: 700px; margin: 0 auto;")
    sec.add_css(" .wpcf7-form input[type=text], .wpcf7-form input[type=email], .wpcf7-form input[type=tel], .wpcf7-form textarea",
                "width: 100%; padding: 14px 18px; border: 1.5px solid #e2e8f0; border-radius: 8px; font-size: 15px; margin-bottom: 16px;")
    sec.add_css(" .wpcf7-form input:focus, .wpcf7-form textarea:focus", f"border-color: {accent_color}; outline: none;")
    sec.add_css(" .wpcf7-form input[type=submit]",
                f"background: {accent_color}; color: #fff; border: none; border-radius: 8px; padding: 16px 40px; font-size: 16px; font-weight: 700; cursor: pointer; width: 100%;")
    return sec


# ── Compile ───────────────────────────────────────────────────────────────────────

def compile_page(sections: list) -> str:
    output = []
    for sec in sections:
        if isinstance(sec, FlatSection):
            output.append(sec.build())
        elif isinstance(sec, str):
            output.append(sec.strip())
    return "\n\n".join(output)


if __name__ == "__main__":
    hero = build_hero_section("sec-hero", "https://example.com/hero.jpg",
                               "XE KHACH BAC NAM", "Dat ve nhanh 24/7",
                               "Dat Ve Ngay", "tel:0968866855")
    cards = build_cards_section("sec-routes", "TUYEN PHO BIEN",
        [{"img": "x.jpg", "title": "HN-DN", "desc": "Hang ngay", "link": "#"}])
    print(compile_page([hero, cards])[:400], "...")
    print("OK")