/* Compare the running Local site with the configured IP preview. */
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const { chromium } = require('playwright');
const { PNG } = require('pngjs');
const local = process.env.BVS_LOCAL_URL || 'http://baltic-vending-solutions.local';
const remote = process.env.BVS_REMOTE_URL || 'http://167.71.44.25';
const output = path.resolve('output/deployment-parity');
const routes = ['/', '/en/', '/kompaktais-ledusskapis/', '/viedais-ledusskapis/',
  '/en/compact-cooler/', '/en/smart-fridge/', '/privatums/', '/en/privacy/',
  '/dizaina-redaktors/', '/en/design-editor/'];

function difference(a, b) {
  assert.equal(a.width, b.width, 'Screenshot width');
  assert.equal(a.height, b.height, 'Screenshot height');
  let changed = 0;
  let exact = 0;
  for (let i = 0; i < a.data.length; i += 4) {
    let delta = 0;
    for (let channel = 0; channel < 3; channel++) delta = Math.max(delta, Math.abs(a.data[i + channel] - b.data[i + channel]));
    if (delta) exact++;
    if (delta > 8) changed++;
  }
  return { changedPixels: changed, exactDifferentPixels: exact, fraction: changed / (a.width * a.height) };
}

async function capture(browser, origin, route, width, label) {
  const context = await browser.newContext({ viewport: { width, height: 1000 }, reducedMotion: 'reduce' });
  const page = await context.newPage();
  const errors = [];
  const failures = [];
  page.on('pageerror', error => errors.push(error.message));
  page.on('response', response => { if (response.status() >= 400) failures.push(`${response.status()} ${response.url()}`); });
  try {
    const response = await page.goto(origin + route, { waitUntil: 'networkidle' });
    assert.equal(response.status(), 200, origin + route);
    await page.evaluate(() => document.fonts.ready);
    if (route.includes('redaktors') || route.includes('design-editor')) {
      await page.waitForFunction(() => document.querySelector('model-viewer')?.loaded && document.querySelector('model-viewer')?.model);
      assert.equal(await page.locator('.bvs-design-workbench').isVisible(), true);
    }
    // Trigger lazy media before full-page capture.
    await page.evaluate(async () => {
      for (let y = 0; y < document.documentElement.scrollHeight; y += 750) {
        scrollTo(0, y);
        await new Promise(resolve => setTimeout(resolve, 30));
      }
      await Promise.all([...document.images].map(img => img.decode().catch(() => {})));
      scrollTo(0, 0);
    });
    await page.waitForTimeout(300);
    const snapshot = await page.evaluate(origin => ({
      text: document.body.innerText,
      links: [...document.querySelectorAll('a[href]')].map(a => a.href.replace(origin, 'ORIGIN')),
      images: [...document.images].map(img => ({ src: img.src.replace(origin, 'ORIGIN'), srcset: img.srcset.split(origin).join('ORIGIN'), alt: img.alt, width: img.getAttribute('width'), height: img.getAttribute('height'), loaded: img.complete && img.naturalWidth > 0 })),
      overflow: document.documentElement.scrollWidth > innerWidth,
      headings: document.querySelectorAll('main h1').length,
    }), origin);
    assert.equal(snapshot.overflow, false, `${route} at ${width}px`);
    assert.equal(snapshot.headings, 1);
    assert(snapshot.images.every(img => img.loaded), 'All images loaded');
    assert.deepEqual(errors, [], 'Browser runtime errors');
    assert.deepEqual(failures, [], 'Failed HTTP requests');
    const filename = path.join(output, `${label}-${route.replace(/\//g, '_')}-${width}.png`);
    const screenshot = await page.screenshot({ path: filename, fullPage: true, animations: 'disabled' });
    return { snapshot, screenshot: PNG.sync.read(screenshot) };
  } finally { await context.close(); }
}

async function verifyInteractions(browser) {
  const context = await browser.newContext({ viewport: { width: 375, height: 1000 }, acceptDownloads: true });
  const page = await context.newPage();
  const errors = [];
  page.on('pageerror', error => errors.push(error.message));
  try {
    await page.goto(remote + '/en/', { waitUntil: 'networkidle' });
    await page.getByRole('button', { name: 'Menu', exact: true }).click();
    assert.equal(await page.locator('.menu-toggle').getAttribute('aria-expanded'), 'true');
    await page.keyboard.press('Escape');
    await page.waitForFunction(() => !document.getElementById('mobile-menu').open);
    assert.equal(await page.locator('.menu-toggle').getAttribute('aria-expanded'), 'false');
    const reject = page.locator('[data-cookie-choice="false"]');
    if (await reject.count()) await reject.click();
    await page.goto(remote + '/en/design-editor/', { waitUntil: 'networkidle' });
    await page.waitForFunction(() => document.querySelector('model-viewer')?.loaded && document.querySelector('model-viewer')?.model);
    await page.evaluate(() => document.querySelector('.bvs-cookie-banner')?.remove());
    const model = page.locator('model-viewer');
    const before = PNG.sync.read(await model.screenshot());
    await model.evaluate(viewer => { viewer.cameraOrbit = '70deg 80deg 4.74m'; viewer.jumpCameraToGoal(); });
    await page.waitForTimeout(500);
    const after = PNG.sync.read(await model.screenshot());
    assert(difference(before, after).fraction > 0.01, 'Actual rendered 3D model responds to camera changes');
    await page.locator('[data-control=hex]').fill('#ed5b24');
    await page.locator('[data-control=hex]').dispatchEvent('change');
    await page.waitForSelector('.bvs-design-save-status[data-state=saved]');
    const texturePixel = await model.evaluate(viewer => Array.from(viewer.model.materials.find(m => m.name === 'BVS wrap front').pbrMetallicRoughness.baseColorTexture.texture.source.element.getContext('2d').getImageData(0, 0, 1, 1).data));
    assert.deepEqual(texturePixel, [237, 91, 36, 255]);
    await page.reload({ waitUntil: 'networkidle' });
    await page.waitForSelector('.bvs-design-save-status[data-state=saved]');
    assert.equal(await page.locator('[data-control=hex]').inputValue(), '#ed5b24', 'Draft survives reload on HTTP IP origin');
    const downloadPromise = page.waitForEvent('download');
    await page.locator('[data-action=download]').click();
    const download = await downloadPromise;
    assert.equal(download.suggestedFilename(), 'bvs-smart-fridge-design.png');
    await download.saveAs(path.join(output, 'live-editor-export.png'));
    if (process.env.BVS_VERIFY_FORMS === '1') {
      const form = page.locator('.bvs-quote-form');
      await form.locator('[name=name]').fill('BVS deployment verification');
      await form.locator('[name=email]').fill('test@example.invalid');
      await form.locator('[name=products]').fill('Synthetic deployment check');
      await form.locator('button[type=submit]').click();
      await page.waitForSelector('.bvs-form-message[data-status=success]');
      assert.equal(await form.locator('[name=name]').inputValue(), '');
    }
    assert.deepEqual(errors, []);
    console.log('PASS: mobile navigation, rendered 3D camera and texture, persistent HTTP draft, PNG export' + (process.env.BVS_VERIFY_FORMS === '1' ? ', quote submission with design' : ''));
  } finally { await context.close(); }
}

(async () => {
  fs.mkdirSync(output, { recursive: true });
  const browser = await chromium.launch({ channel: 'chrome', headless: true });
  const report = [];
  try {
    for (const route of routes) {
      const widths = ['/', '/en/', '/dizaina-redaktors/', '/en/design-editor/'].includes(route) ? [1440, 768, 375, 320] : [1440, 375];
      for (const width of widths) {
        const baseline = await capture(browser, local, route, width, 'local');
        const deployed = await capture(browser, remote, route, width, 'live');
        assert.deepEqual(deployed.snapshot, baseline.snapshot, `Content, links and media match: ${route} ${width}`);
        const diff = difference(baseline.screenshot, deployed.screenshot);
        report.push({ route, width, ...diff });
        console.log(JSON.stringify(report.at(-1)));
        assert(diff.fraction < 0.001, `Visual difference exceeds 0.1%: ${route} ${width}`);
      }
    }
    await verifyInteractions(browser);
    console.log(`PASS: ${report.length} local/live screenshot pairs, matching content and assets, no overflow or browser errors`);
  } finally {
    fs.writeFileSync(path.join(output, 'report.json'), JSON.stringify(report, null, 2) + '\n');
    await browser.close();
  }
})().catch(error => { console.error(error); process.exitCode = 1; });
