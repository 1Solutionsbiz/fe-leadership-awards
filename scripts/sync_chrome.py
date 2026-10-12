#!/usr/bin/env python3
"""Stamp the shared header / menu / footer (partials/*.html) into every page.

Edit partials/ (markup) or site-chrome.css / site-chrome.js (style, behaviour), then run:
    python3 scripts/sync_chrome.py
Each page holds the partial between <!-- chrome:NAME:start --> / <!-- chrome:NAME:end --> markers.
"""
import os, re, sys

ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
PAGES = ["index", "about", "criteria", "how-judging-works"]

# the home page's header starts transparent over the hero; inner pages are permanently in the solid state
NAV_ATTRS = {
    "index": 'class="site-nav" id="siteNav"',
    "*": 'class="site-nav scrolled" id="siteNav" data-static style="--nav-progress:1"',
}

# first-run fallback: the un-marked blocks as they were hand-written in each page
LEGACY = {
    "header": r'<header class="site-nav".*?</header>',
    "menu": r'<div class="menu-overlay".*?<div class="menu-overlay-foot">.*?</div>\s*</div>',
    "footer": r'(?:<div class="footer-spacer" id="footerSpacer"></div>\s*)?<footer id="siteFooter">.*?</footer>',
}

def partial(name, page):
    text = open(os.path.join(ROOT, "partials", name + ".html"), encoding="utf-8").read().strip()
    return text.replace("{{NAV_ATTRS}}", NAV_ATTRS.get(page, NAV_ATTRS["*"]))

def stamp(html, name, page):
    block = f"<!-- chrome:{name}:start -->\n{partial(name, page)}\n<!-- chrome:{name}:end -->"
    marked = re.compile(rf"<!-- chrome:{name}:start -->.*?<!-- chrome:{name}:end -->", re.S)
    if marked.search(html):
        pat = marked
    else:
        pat = re.compile(LEGACY[name], re.S)
    found = pat.findall(html)
    assert len(found) == 1, f"{page}: expected exactly 1 '{name}' block, found {len(found)}"
    return pat.sub(lambda _m: block, html, count=1)

def ensure_assets(html, page):
    if '/site-chrome.css' not in html:
        assert html.count("</style>") == 1, f"{page}: expected one </style>"
        html = html.replace("</style>", '</style>\n<link rel="stylesheet" href="/site-chrome.css">', 1)
    if '/site-chrome.js' not in html:
        assert html.count("<script>") == 1, f"{page}: expected one inline <script>"
        html = html.replace("<script>", '<script src="/site-chrome.js"></script>\n<script>', 1)
    return html

for page in PAGES:
    path = os.path.join(ROOT, page + ".html")
    html = open(path, encoding="utf-8").read()
    for name in ("header", "menu", "footer"):
        html = stamp(html, name, page)
    html = ensure_assets(html, page)
    open(path, "w", encoding="utf-8").write(html)
    print("synced", page)
