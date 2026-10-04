/* Public Local preview checks. NODE_PATH must point to an installed Playwright package. */
const fs = require('node:fs');
const assert = require('node:assert/strict');
const { chromium } = require('playwright');
const base = process.env.BVS_TEST_URL || 'http://baltic-vending-solutions.local';
if (!new URL(base).hostname.endsWith('.local')) throw new Error('Use a Local .local URL.');
(async () => {
  const browser = await chromium.launch({channel: 'chrome', headless: true});
  const page = await browser.newPage();
  const errors = []; const tracking = [];
  page.on('pageerror', error => errors.push(error.message));
  page.on('request', request => { if (/google-analytics|googletagmanager/.test(request.url())) tracking.push(request.url()); });
  try {
    for (const path of ['/', '/en/', '/kompaktais-ledusskapis/', '/viedais-ledusskapis/', '/en/compact-cooler/', '/en/smart-fridge/', '/privatums/', '/en/privacy/']) {
      const response = await page.goto(base + path, {waitUntil:'networkidle'});
      assert.equal(response.status(), 200, path);
      assert.equal(await page.locator('main h1').count(), 1, path);
      const labels = await page.locator('main .bvs-eyebrow').allTextContents();
      assert(labels.every(text => /^0[1-3]$/.test(text.trim())), 'Only useful process numbers remain');
      const alternate = page.locator('header .lang-item a');
      assert.equal(await alternate.count(), 1, 'Equivalent-page language switcher');
      const href = await alternate.getAttribute('href');
      assert(!href.endsWith('/en/') || path === '/', 'Equipment translations stay on the equipment page');
    }
    for (const width of [1440, 768, 375, 320]) {
      await page.setViewportSize({width,height:1000});
      await page.goto(base+'/en/', {waitUntil:'networkidle'});
      assert(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth), `No overflow at ${width}px`);
    }
    await page.getByRole('button',{name:'Menu',exact:true}).click();
    assert.equal(await page.locator('.menu-toggle').getAttribute('aria-expanded'),'true');
    await page.locator('header .site-navigation a').first().focus();
    await page.keyboard.press('Escape');
    assert.equal(await page.locator('.menu-toggle').getAttribute('aria-expanded'),'false');
    assert(await page.locator('.menu-toggle').evaluate(node => node === document.activeElement));
    await page.goto(base+'/en/?model=compact&mode=rent#quote',{waitUntil:'networkidle'});
    const form = page.locator('.bvs-quote-form');
    assert.equal(await form.locator('[name=model]').inputValue(), 'compact');
    assert.equal(await form.locator('[name=mode]').inputValue(), 'rent');
    await form.locator('[name=name]').fill('BVS browser verification');
    await form.locator('[name=email]').fill('test@example.invalid');
    await form.locator('[name=products]').fill('Synthetic packaged meals');
    await form.getByRole('button',{name:'Request a quote',exact:true}).click();
    await page.waitForSelector('.bvs-form-message[data-status=success]');
    assert.equal(await form.locator('.bvs-form-message').innerText(), 'Your enquiry has been saved.');
    assert.equal(await form.locator('[name=name]').inputValue(), '');
    assert.deepEqual(errors, []); assert.deepEqual(tracking, []);
    fs.mkdirSync('output',{recursive:true});
    await page.setViewportSize({width:1440,height:1000});
    await page.goto(base+'/en/',{waitUntil:'networkidle'});
    await page.screenshot({path:'output/home-desktop.png',fullPage:true});
    await page.setViewportSize({width:375,height:1000});
    await page.screenshot({path:'output/home-mobile.png',fullPage:true});
    console.log('Passed: eight routes, translated navigation, four viewport widths, menu keyboard behaviour, quote prefill and saving, no runtime errors or Analytics requests.');
  } finally { await browser.close(); }
})().catch(error => {console.error(error);process.exitCode=1;});
