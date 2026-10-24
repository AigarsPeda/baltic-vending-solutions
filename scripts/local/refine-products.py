#!/usr/bin/env python3
"""Build a reviewable product-page payload from a WP snapshot; does not publish it."""
import copy
import json
import re
import sys
from pathlib import Path

before = json.loads(Path(sys.argv[1]).read_text())
after = copy.deepcopy(before)
for key, page in after['pages'].items():
    lv = key.startswith('lv-')
    compact = key.endswith('-compact')
    content = page['content']

    def replace_once(old, new):
        global content
        if content.count(old) != 1:
            raise ValueError(f'{key}: expected one occurrence of {old!r}')
        content = content.replace(old, new, 1)

    # Keep the fuller touchscreen description in one row, preserving all facts.
    label = 'Skārienekrāns' if lv else 'Touchscreen'
    detail_label = 'Ekrāns un saskarne' if lv else 'Display and interface'
    duplicate = re.search(r'<tr><td>' + detail_label + r'</td><td>(.*?)</td></tr>', content)
    if not duplicate:
        raise ValueError(f'{key}: missing display details')
    screen = re.search(r'<tr><td>' + label + r'</td><td>.*?</td></tr>', content)
    if not screen:
        raise ValueError(f'{key}: missing touchscreen row')
    replace_once(screen[0], f'<tr><td>{label}</td><td>{duplicate[1]}</td></tr>')
    replace_once(duplicate[0], '')
    replace_once('Piedāvātā specifikācija' if lv else 'The proposed specification',
                 'Iekārtas specifikācija' if lv else 'Equipment specifications')
    replace_once(
        'Specifikācija atbilst mūsu iekārtu piedāvājumam. Galīgo konfigurāciju, maksājumu pakalpojuma sniedzēju un pakalpojumu apjomu apstiprinām piedāvājumā.' if lv else
        'Specifications follow our equipment proposal. Final configuration, payment provider and service scope are confirmed in your quote.',
        'Galīgo konfigurāciju, maksājumu pakalpojuma sniedzēju un iekļautos pakalpojumus precizējam piedāvājumā.' if lv else
        'We confirm the final configuration, payment provider and included services in your quote.')
    if compact:
        replace_once('Vision AI atpazīst pircēja paņemtos produktus.' if lv else 'Vision AI recognises the products customers take.',
                     'Vision AI kameras atpazīst pircēja paņemtos produktus.' if lv else 'Vision AI cameras recognise the products customers take.')
        replace_once('Krājumi reāllaikā un attālināta produktu un cenu vadība' if lv else 'Live inventory and remote product / price management',
                     'Krājumi reāllaikā; produktu katalogu un cenas mainiet attālināti Vendlive pārvaldības panelī' if lv else
                     'Live stock information; update the product catalogue and prices remotely in the Vendlive dashboard')
    elif lv:
        replace_once('Dubultstikls ar pret aizsvīšanas apstrādi', 'Dubultstikls ar aizsardzību pret aizsvīšanu')

    # Native Group classes scope presentation; block content stays in the editor.
    for classes in ['bvs-section bvs-hero', 'bvs-section bvs-specs', 'bvs-section bvs-contact bvs-surface']:
        replace_once('"className":"' + classes + '"', '"className":"' + classes + ' bvs-product-section"')
        replace_once('class="wp-block-group ' + classes + '"', 'class="wp-block-group ' + classes + ' bvs-product-section"')

    def update_form(match):
        attrs = json.loads(match[1])
        attrs['collapseOptional'] = True
        attrs['optionalLabel'] = 'Papildu informācija un dizains (nav obligāti)' if lv else 'More details and design (optional)'
        return '<!-- wp:bvs/quote-form ' + json.dumps(attrs, ensure_ascii=False, separators=(',', ':')) + ' /-->'

    content, count = re.subn(r'<!-- wp:bvs/quote-form (\{.*?\}) /-->', update_form, content)
    if count != 1:
        raise ValueError(f'{key}: expected one native quote form')
    page['content'] = content
Path(sys.argv[2]).write_text(json.dumps({'before': before, 'after': after}, ensure_ascii=False, indent=2))
