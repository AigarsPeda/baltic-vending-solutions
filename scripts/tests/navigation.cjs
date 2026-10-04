/* Public Local drawer regression checks. NODE_PATH must include Playwright. */
const fs = require('node:fs');
const assert = require('node:assert/strict');
const { chromium } = require('playwright');
const base = process.env.BVS_TEST_URL || 'http://baltic-vending-solutions.local';
if (!new URL(base).hostname.endsWith('.local')) throw new Error('Use a Local .local URL.');

(async () => {
  const browser = await chromium.launch({ channel: 'chrome', headless: true });
  const page = await browser.newPage({ viewport: { width: 375, height: 812 } });
  const errors = [];
  page.on('pageerror', error => errors.push(error.message));
  fs.mkdirSync('output', { recursive: true });
  const drawer = page.locator('#mobile-menu');
  const toggle = page.locator('.menu-toggle');
  const closed = () => page.waitForFunction(() => !document.getElementById('mobile-menu').open);
  const settled = () => drawer.evaluate(node => Promise.all(node.getAnimations().map(animation => animation.finished)));
  try {
    for (const route of ['/dizaina-redaktors/', '/en/design-editor/']) {
      await page.goto(base + route, { waitUntil: 'networkidle' });
      for (const width of [320, 375, 768, 1180]) {
        await page.setViewportSize({ width, height: 812 });
        const mainTop = await page.locator('main').evaluate(node => node.getBoundingClientRect().top);
        await toggle.click();
        await page.waitForFunction(() => document.getElementById('mobile-menu').classList.contains('is-open'));
        await settled();
        const firstLink = drawer.locator('.site-navigation ul a').first();
        assert.equal(await firstLink.innerText(), route.startsWith('/en/') ? 'Home' : 'Sākums');
        assert.equal(await firstLink.getAttribute('href'), base + (route.startsWith('/en/') ? '/en/' : '/'));
        assert.equal(await toggle.getAttribute('aria-expanded'), 'true');
        assert(await drawer.evaluate(node => node.matches(':modal')), 'Drawer occupies the top layer');
        const box = await drawer.boundingBox();
        assert.equal(Math.round(box.y), 0);
        assert.equal(Math.round(box.height), 812);
        assert.equal(Math.round(box.x + box.width), width);
        if (width <= 375) assert.equal(Math.round(box.x), 0, 'Drawer covers the phone viewport');
        assert.equal(await page.locator('main').evaluate(node => node.getBoundingClientRect().top), mainTop, 'Opening does not shift page content');
        assert(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth), 'No horizontal overflow');
        assert.equal(await page.evaluate(() => getComputedStyle(document.documentElement).overflow), 'hidden');
        await page.mouse.wheel(0, 400);
        assert.equal(await page.evaluate(() => window.scrollY), 0, 'Background scrolling is locked');
        await page.locator('.drawer-close').focus();
        await page.keyboard.press('Shift+Tab');
        assert(await drawer.evaluate(node => node.contains(document.activeElement)), 'Focus stays in the drawer');
        if (route === '/dizaina-redaktors/' && width === 375) await page.screenshot({ path: 'output/mobile-menu-open.png' });
        await page.keyboard.press('Escape');
        assert(await drawer.evaluate(node => node.open), 'Exit animation runs before hiding the dialog');
        await closed();
        assert.equal(await toggle.getAttribute('aria-expanded'), 'false');
        assert(await toggle.evaluate(node => node === document.activeElement), 'Escape restores focus');
      }
    }
    await page.setViewportSize({ width: 768, height: 812 });
    await toggle.click();
    await settled();
    await page.mouse.click(20, 200);
    await closed();
    await toggle.click();
    await page.locator('.drawer-close').click();
    await closed();
    await toggle.click();
    await page.setViewportSize({ width: 1440, height: 900 });
    await closed();
    assert(await page.locator('header .site-navigation').isVisible(), 'Desktop navigation returns to the header');
    assert.equal(await page.locator('header .bvs-mobile-home').isVisible(), false, 'Homepage entry is mobile only');
    assert.equal(await toggle.isVisible(), false);
    assert.equal(await page.evaluate(() => document.documentElement.classList.contains('bvs-menu-open')), false);
    await page.screenshot({ path: 'output/mobile-menu-desktop.png' });
    await page.setViewportSize({ width: 375, height: 812 });
    await toggle.click();
    await settled();
    await drawer.locator('a[href*="#"]').first().click();
    await closed();
    assert(new URL(page.url()).hash, 'Anchor navigation works after closing');
    await page.goto(base + '/en/', { waitUntil: 'networkidle' });
    await page.emulateMedia({ reducedMotion: 'reduce' });
    await toggle.click();
    assert.equal(await drawer.evaluate(node => getComputedStyle(node).transitionDuration), '0s');
    await page.keyboard.press('Escape');
    await closed();
    const noJS = await browser.newPage({ javaScriptEnabled: false, viewport: { width: 375, height: 812 } });
    await noJS.goto(base + '/en/', { waitUntil: 'networkidle' });
    assert(await noJS.locator('header .site-navigation').isVisible(), 'No-JavaScript navigation stays available');
    await noJS.close();
    assert.deepEqual(errors, []);
    console.log('PASS: LV/EN drawer coverage, entrance/exit, scroll lock, focus trapping/return, Escape, close button, backdrop, links, responsive reset, desktop, reduced motion and no-JavaScript fallback.');
  } finally {
    await browser.close();
  }
})().catch(error => { console.error(error); process.exitCode = 1; });
