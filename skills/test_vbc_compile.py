import sys
import os
if sys.platform == 'win32':
    try:
        sys.stdout.reconfigure(encoding='utf-8')
    except AttributeError:
        pass
sys.path.insert(0, os.path.dirname(__file__))
from html_to_vbc import compile_html_to_vbc

sample_html = """
<header class="main-header">
  <div class="container">
    <img src="https://example.com/logo.png" alt="Logo" class="logo-img" />
    <a href="tel:0968866855" class="hotline-btn">Hotline: 0968866855</a>
  </div>
</header>
<section class="hero-section">
  <div class="container">
    <h1 class="hero-title">XE KHÁCH BẮC NAM</h1>
    <p class="hero-desc">Dịch vụ uy tín chất lượng</p>
    [contact-form-7 id="492" title="Form Đặt Vé"]
  </div>
</section>
"""

vbc_output = compile_html_to_vbc(sample_html)
print("--- VBC COMPILED OUTPUT ---")
print(vbc_output)
