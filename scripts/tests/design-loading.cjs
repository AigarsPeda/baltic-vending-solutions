/* Read-only editor loading regression using isolated browser drafts. No submissions. */
const assert = require('node:assert/strict');
const { chromium } = require('playwright');
const base = process.env.BVS_TEST_URL || 'http://baltic-vending-solutions.local';
const rgb = hex => hex.slice(1).match(/../g).map(value => parseInt(value, 16));
const ready = page => page.waitForSelector('.bvs-design-editor[data-model=ready] .bvs-design-workbench:not([hidden])');

(async () => {
  const browser = await chromium.launch({ channel: 'chrome', headless: true });
  try {
    for (const width of [1440, 375]) {
      const context = await browser.newContext({ viewport: { width, height: 1000 } });
      const page = await context.newPage();
      const errors = [], posters = [];
      page.on('pageerror', error => errors.push(error.message));
      page.on('request', request => { if (request.url().includes('smart-fridge-neutral.png')) posters.push(request.url()); });
      await page.addInitScript(() => {
        window.loadingFrames = [];
        const sample = () => {
          const root = document.querySelector('.bvs-design-editor');
          const viewer = root?.querySelector('model-viewer');
          if (viewer && !root.querySelector('.bvs-design-workbench').hidden && getComputedStyle(viewer).visibility === 'visible' && window.loadingFrames.length < 15) {
            window.loadingFrames.push({
              state: root.dataset.model,
              panels: ['front', 'left', 'right'].map(panel => {
                const texture = viewer.model?.materials.find(material => material.name === `BVS wrap ${panel}`)?.pbrMetallicRoughness.baseColorTexture.texture;
                const canvas = texture?.source.element;
                return canvas?.getContext ? [...canvas.getContext('2d').getImageData(0, 0, 1, 1).data].slice(0, 3) : null;
              }),
            });
          }
          requestAnimationFrame(sample);
        };
        requestAnimationFrame(sample);
      });
      let release;
      const modelGate = new Promise(resolve => { release = resolve; });
      await page.route('**/smart-fridge-design.glb?*', async route => { await modelGate; await route.continue(); });
      await page.goto(base + '/en/design-editor/', { waitUntil: 'domcontentloaded' });
      await page.waitForSelector('.bvs-design-workbench:not([hidden])');
      await page.evaluate(() => document.querySelector('.bvs-cookie-banner')?.remove());
      assert.equal(await page.locator('model-viewer').evaluate(node => getComputedStyle(node).visibility), 'hidden');
      assert(await page.locator('.bvs-design-loading').isVisible());
      assert.equal(await page.locator('model-viewer').getAttribute('poster'), null);
      const defaultColour = await page.locator('[data-control=hex]').inputValue();
      await page.locator('.bvs-design-model').screenshot({ path: `output/design-loading-${width}.png` });
      release();
      await ready(page);
      await page.waitForFunction(() => window.loadingFrames.length === 15);
      for (const frame of await page.evaluate(() => window.loadingFrames)) {
        assert.equal(frame.state, 'ready');
        assert.deepEqual(frame.panels, [rgb(defaultColour), rgb(defaultColour), rgb(defaultColour)]);
      }
      assert.equal(await page.locator('.bvs-design-loading').isVisible(), false);
      await page.unroute('**/smart-fridge-design.glb?*');

      // Restore three different colours and an image whose decoding is deliberately slow.
      const colours = ['#2457e6', '#ad3377', '#ef8822'];
      for (const [index, panel] of ['front', 'left', 'right'].entries()) {
        await page.locator(`button[data-panel=${panel}]`).click();
        await page.locator('[data-control=hex]').fill(colours[index]);
        await page.locator('[data-control=hex]').dispatchEvent('change');
      }
      await page.locator('button[data-panel=front]').click();
      const transparent = await page.evaluate(() => {
        const canvas = document.createElement('canvas'); canvas.width = canvas.height = 1;
        return canvas.toDataURL('image/png').split(',')[1];
      });
      await page.locator('[data-upload=artwork]').setInputFiles({ name: 'loading-draft.png', mimeType: 'image/png', buffer: Buffer.from(transparent, 'base64') });
      await page.waitForSelector('.bvs-design-save-status[data-state=saved]');
      await page.goto(base + '/', { waitUntil: 'networkidle' });
      await page.addInitScript(() => {
        const decode = window.createImageBitmap.bind(window);
        window.createImageBitmap = async (...args) => {
          if (args[0] instanceof Blob && args[0].type === 'image/png' && args[0].size < 1000) await new Promise(resolve => setTimeout(resolve, 700));
          return decode(...args);
        };
      });
      await page.goto(base + '/dizaina-redaktors/', { waitUntil: 'networkidle' });
      await ready(page);
      await page.waitForFunction(() => window.loadingFrames.length === 15);
      for (const frame of await page.evaluate(() => window.loadingFrames)) {
        assert.equal(frame.state, 'ready');
        assert.deepEqual(frame.panels, colours.map(rgb), 'Every visible frame uses the restored design');
      }
      assert.deepEqual(posters, [], 'The old green image is never requested');
      assert.deepEqual(errors, []);
      await context.close();
    }
    console.log(`PASS ${base}: neutral loading state, no green poster, correctly painted first visible frames for fresh and slowly restored drafts in LV/EN on desktop/mobile.`);
  } finally { await browser.close(); }
})().catch(error => { console.error(error); process.exitCode = 1; });
