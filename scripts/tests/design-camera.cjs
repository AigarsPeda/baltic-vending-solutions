/* Local camera restoration regression; isolated browser drafts and no submissions. */
const assert = require('node:assert/strict');
const { chromium } = require('playwright');
const base = 'http://baltic-vending-solutions.local';
const orbit = page => page.locator('model-viewer').evaluate(viewer => {
  const { theta, phi, radius } = viewer.getCameraOrbit();
  return { theta, phi, radius };
});
const sameOrbit = (actual, expected) => {
  for (const key of ['theta', 'phi', 'radius']) assert(Math.abs(actual[key] - expected[key]) < .015, `Restored ${key}: ${actual[key]} instead of ${expected[key]}`);
};
const open = async (page, route = '/en/design-editor/') => {
  await page.goto(base + route, { waitUntil: 'networkidle' });
  await page.waitForSelector('.bvs-design-editor[data-model=ready] .bvs-design-workbench:not([hidden])');
  await page.evaluate(() => document.querySelector('.bvs-cookie-banner')?.remove());
};
const saved = page => page.waitForSelector('.bvs-design-save-status[data-state=saved]');
(async () => {
  const browser = await chromium.launch({ channel: 'chrome', headless: true });
  try {
    for (const width of [1440, 375]) {
      const context = await browser.newContext({ viewport: { width, height: 1000 } });
      const page = await context.newPage();
      const errors = [];
      page.on('pageerror', error => errors.push(error.message));
      await page.addInitScript(() => {
        window.cameraFrames = [];
        const sample = () => {
          const root = document.querySelector('.bvs-design-editor[data-model=ready]');
          if (root && !root.querySelector('.bvs-design-workbench').hidden && window.cameraFrames.length < 30) {
            const { theta, phi, radius } = root.querySelector('model-viewer').getCameraOrbit();
            window.cameraFrames.push({ theta, phi, radius });
          }
          requestAnimationFrame(sample);
        };
        requestAnimationFrame(sample);
      });
      await open(page);
      await page.locator('button[data-panel=left]').click();
      await page.locator('[data-control=hex]').fill('#bb4433');
      await page.locator('[data-control=hex]').dispatchEvent('change');
      await page.locator('[data-action=view]').click();
      await page.waitForFunction(theta => Math.abs(document.querySelector('model-viewer').getCameraOrbit().theta - theta) < .005, width === 375 ? 0 : 25 * Math.PI / 180);
      const before = await orbit(page);
      await page.waitForSelector('.bvs-design-save-status[data-state=saved]');
      await page.goto(base + '/', { waitUntil: 'networkidle' });
      await open(page);
      sameOrbit(await orbit(page), before);
      assert.equal(await page.locator('button[data-panel=left]').getAttribute('aria-pressed'), 'true', 'Panel selection stays independent of camera view');
      assert.equal(await page.locator('[data-control=hex]').inputValue(), '#bb4433');
      // An arbitrary rotated and zoomed view survives reload and a language change.
      await page.locator('model-viewer').evaluate(viewer => { viewer.cameraOrbit = '43deg 80deg 3.792m'; });
      await page.waitForFunction(() => Math.abs(document.querySelector('model-viewer').getCameraOrbit().radius - 3.792) < .005);
      await saved(page);
      const rotated = await orbit(page);
      await open(page, '/dizaina-redaktors/');
      sameOrbit(await orbit(page), rotated);
      await page.waitForFunction(() => window.cameraFrames.length >= 30);
      for (const frame of await page.evaluate(() => window.cameraFrames)) sameOrbit(frame, rotated);
      assert.equal(await page.locator('[data-model-zoom]').innerText(), '125%');
      // Older drafts omit camera data and keep the normal opening view.
      await page.evaluate(() => new Promise(resolve => {
        const request = indexedDB.open('bvs-design-editor', 1);
        request.onsuccess = () => {
          const db = request.result, transaction = db.transaction('drafts', 'readwrite'), store = transaction.objectStore('drafts');
          const draft = store.get('smart-fridge');
          draft.onsuccess = () => { delete draft.result.camera; store.put(draft.result, 'smart-fridge'); };
          transaction.oncomplete = () => { db.close(); resolve(); };
        };
      }));
      // Avoid pagehide writing a new camera into the intentionally old-format record.
      const legacy = await context.newPage();
      await open(legacy);
      sameOrbit(await orbit(legacy), { theta: width === 375 ? 0 : 25 * Math.PI / 180, phi: Math.PI / 2, radius: 4.74 });
      assert.equal(await legacy.locator('[data-control=hex]').inputValue(), '#bb4433');
      await context.close();
      assert.deepEqual(errors, []);
    }
    console.log('PASS: desktop/mobile camera angle and zoom survive home navigation and language changes without visible camera jumps; panel selection stays independent and old drafts keep the normal opening view.');
  } finally { await browser.close(); }
})().catch(error => { console.error(error); process.exitCode = 1; });
