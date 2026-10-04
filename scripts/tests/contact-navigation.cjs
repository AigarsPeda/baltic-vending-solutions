/* Read-only Local contact navigation checks. NODE_PATH must include Playwright. */
const fs = require('node:fs');
const assert = require('node:assert/strict');
const { chromium } = require('playwright');
const base = process.env.BVS_TEST_URL || 'http://baltic-vending-solutions.local';
if (!new URL(base).hostname.endsWith('.local')) throw new Error('Use a Local .local URL.');

(async () => {
  const browser = await chromium.launch({ channel: 'chrome', headless: true });
  const page = await browser.newPage();
  const errors = [];
  page.on('pageerror', error => errors.push(error.message));
  fs.mkdirSync('output', { recursive: true });
  try {
    for (const width of [1440, 1280, 1181, 375, 320]) {
      await page.setViewportSize({ width, height: 900 });
      for (const route of ['/', '/en/', '/dizaina-redaktors/', '/en/design-editor/', '/kompaktais-ledusskapis/', '/en/smart-fridge/']) {
        await page.goto(base + route, { waitUntil: 'networkidle' });
        const contact = page.locator('.bvs-contact-menu a');
        const anchor = route.includes('editor') || route.includes('redaktors') ? '#design-quote' : '#quote';
        assert.equal(await contact.count(), 1);
        assert.equal(await contact.innerText(), route.startsWith('/en/') ? 'Contact' : 'Kontakti');
        assert.equal(await contact.getAttribute('href'), anchor, route);
        assert(await contact.evaluate(node => Boolean(node.closest('li').nextElementSibling?.classList.contains('lang-item'))), 'Contact sits before the language switch');
        if (width <= 1180) {
          await page.locator('.menu-toggle').click();
          await page.waitForFunction(() => document.getElementById('mobile-menu').classList.contains('is-open'));
        }
        assert(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth), `No overflow at ${width}px on ${route}`);
        await contact.click();
        await page.waitForFunction(() => !document.getElementById('mobile-menu').open);
        await page.waitForFunction(hash => {
          const top = document.querySelector(hash).getBoundingClientRect().top;
          return location.hash === hash && top >= 80 && top <= 110;
        }, anchor);
        assert.equal(new URL(page.url()).pathname, route, 'Contact stays on the current page');
        assert.equal(await page.evaluate(() => document.documentElement.classList.contains('bvs-menu-open')), false);
        assert(await page.locator(`${anchor} .bvs-quote-form`).count(), 'The target contains the quote form');
        if (route === '/' && width === 1440) await page.screenshot({ path: 'output/contact-desktop.png' });
        if (route === '/' && width === 375) await page.screenshot({ path: 'output/contact-mobile.png' });
      }
    }
    for (const route of ['/privatums/', '/en/privacy/']) {
      await page.goto(base + route, { waitUntil: 'networkidle' });
      assert.equal(await page.locator('.bvs-contact-menu a').getAttribute('href'), base + (route.startsWith('/en/') ? '/en/' : '/') + '#quote');
    }
    await page.setViewportSize({ width: 1440, height: 900 });
    await page.goto(base + '/', { waitUntil: 'networkidle' });
    await page.screenshot({ path: 'output/contact-header-desktop.png' });
    await page.setViewportSize({ width: 375, height: 812 });
    await page.locator('.menu-toggle').click();
    await page.waitForFunction(() => document.getElementById('mobile-menu').classList.contains('is-open'));
    await page.locator('#mobile-menu').evaluate(node => Promise.all(node.getAnimations().map(animation => animation.finished)));
    await page.screenshot({ path: 'output/contact-menu-mobile.png' });
    assert.deepEqual(errors, []);
    console.log('PASS: translated Contact menu, current-page form scrolling on home/product/editor pages, mobile drawer closing, desktop/mobile widths and translated fallback on pages without a form.');
  } finally {
    await browser.close();
  }
})().catch(error => { console.error(error); process.exitCode = 1; });
