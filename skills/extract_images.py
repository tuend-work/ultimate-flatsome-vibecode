import urllib.request, re, sys
sys.stdout.reconfigure(encoding='utf-8')

req = urllib.request.Request(
    'https://nihaoma-mandarin.com/vi/trang-chu/',
    headers={'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'}
)
with urllib.request.urlopen(req, timeout=20) as r:
    html = r.read().decode('utf-8', errors='ignore')

pattern = r'https://nihaoma-mandarin\.com/wp-content/uploads/[^\s"\'<>]+'
imgs = re.findall(pattern, html)
unique = sorted(set(imgs))
for u in unique:
    print(u)
