/* Public Local consent checks. NODE_PATH must point to an installed Playwright package. */
const fs = require('node:fs');
const assert = require('node:assert/strict');
const {chromium} = require('playwright');
const base = process.env.BVS_TEST_URL || 'http://baltic-vending-solutions.local';
if (!new URL(base).hostname.endsWith('.local')) throw new Error('Use a Local .local URL.');
(async () => {
  const browser = await chromium.launch({channel:'chrome',headless:true});
  const context = await browser.newContext();
  const page = await context.newPage();
  const errors = []; const tracking = [];
  context.on('page', tab => {
    tab.on('pageerror',error => errors.push(error.message));
    tab.on('request',request => { if (/google-analytics|googletagmanager/.test(request.url())) tracking.push(request.url()); });
  });
  page.on('pageerror',error => errors.push(error.message));
  page.on('request',request => { if (/google-analytics|googletagmanager/.test(request.url())) tracking.push(request.url()); });
  const choice = async () => {
    const cookie = (await context.cookies()).find(cookie => cookie.name==='bvs_consent');
    return cookie ? JSON.parse(decodeURIComponent(cookie.value)) : null;
  };
  try {
    fs.mkdirSync('output',{recursive:true});
    for (const width of [1440,375,320]) {
      await context.clearCookies();
      await page.setViewportSize({width,height:1000});
      await page.goto(base,{waitUntil:'networkidle'});
      await page.waitForSelector('#bvs-cookie-banner:not([hidden])');
      assert.equal(await page.locator('#bvs-cookie-title').textContent(),'Jūsu sīkdatņu izvēle');
      assert.equal(await page.evaluate(() => wp_has_consent('statistics')),false);
      assert.equal(await page.evaluate(() => wp_has_consent('marketing')),false);
      assert.equal(await page.locator('#bvs-cookie-banner a').getAttribute('href'),base+'/privatums/');
      assert(await page.locator('[data-cookie-close]').isHidden(),'First visit has only reject and allow actions');
      assert.equal(await page.locator('.bvs-cookie-copy p').first().evaluate(el => getComputedStyle(el).color),'rgb(255, 255, 255)');
      assert.equal(await page.locator('.bvs-cookie-copy a').evaluate(el => getComputedStyle(el).color),'rgb(255, 255, 255)');
      assert.deepEqual(await context.cookies(),[],'Public browsing must not persist cookies before confirmation');
      assert.deepEqual(await page.evaluate(() => ({local:{...localStorage},session:{...sessionStorage}})),{local:{},session:{}},'No choice is silently written to browser storage');
      assert(await page.evaluate(() => document.documentElement.scrollWidth<=innerWidth));
      if(width!==320) await page.locator('#bvs-cookie-banner').screenshot({path:`output/cookies-${width}.png`});
      await page.getByRole('button',{name:'Noraidīt analītiku',exact:true}).click();
      assert.equal((await choice()).analytics,false);
      assert(await page.locator('#bvs-cookie-banner').isHidden());
      await page.reload({waitUntil:'networkidle'});
      assert(await page.locator('#bvs-cookie-banner').isHidden(),'Rejection remembered');
    }
    await page.goto(base+'/en/',{waitUntil:'networkidle'});
    assert(await page.locator('#bvs-cookie-banner').isHidden(),'Preference shared across languages');
    await page.getByRole('button',{name:'Cookie settings',exact:true}).click();
    assert.equal(await page.locator('#bvs-cookie-title').textContent(),'Your cookie choice');
    assert.equal(await page.locator('#bvs-cookie-banner a').getAttribute('href'),base+'/en/privacy/');
    await page.keyboard.press('Escape');
    assert(await page.locator('#bvs-cookie-banner').isHidden());
    assert(await page.locator('.bvs-cookie-settings').evaluate(el => el===document.activeElement));
    await page.locator('.bvs-cookie-settings').click();
    await page.getByRole('button',{name:'Allow analytics',exact:true}).click();
    assert.equal((await choice()).analytics,true);
    assert.equal(await page.evaluate(() => wp_has_consent('statistics')),true);
    assert.equal(await page.evaluate(() => wp_has_consent('marketing')),false);
    await page.reload({waitUntil:'networkidle'});
    assert(await page.locator('#bvs-cookie-banner').isHidden(),'Acceptance remembered');
    const other = await context.newPage();
    await other.goto(base,{waitUntil:'networkidle'});
    await page.evaluate(() => { document.cookie='_ga=synthetic; Path=/'; document.cookie='_ga_BVSTEST=synthetic; Path=/'; });
    await page.locator('.bvs-cookie-settings').click();
    await page.getByRole('button',{name:'Reject analytics',exact:true}).click();
    assert.equal((await choice()).analytics,false);
    assert(!(await context.cookies()).some(cookie => /^_ga(?:_|$)/.test(cookie.name)),'Analytics cookies cleared on withdrawal');
    await other.waitForFunction(() => !wp_has_consent('statistics'));
    await other.close();
    for (const value of ['malformed',JSON.stringify({v:0,analytics:true,at:Date.now()}),JSON.stringify({v:1,analytics:true,at:Date.now()-181*86400000})]) {
      await context.addCookies([{name:'bvs_consent',value:encodeURIComponent(value),url:base}]);
      await page.reload({waitUntil:'networkidle'});
      assert(await page.locator('#bvs-cookie-banner').isVisible(),'Invalid/expired choice asks again');
      assert.equal(await page.evaluate(() => wp_has_consent('statistics')),false);
    }
    assert(await page.locator('[data-cookie-close]').isHidden());
    await page.locator('[data-cookie-choice="false"]').focus();
    await page.keyboard.press('Escape');
    assert(await page.locator('#bvs-cookie-banner').isVisible(),'First visit waits for an explicit choice');
    await page.getByRole('button',{name:'Reject analytics',exact:true}).click();
    const saved = await choice();
    await page.locator('.bvs-cookie-settings').click();
    await page.getByRole('button',{name:'Close without changing',exact:true}).click();
    assert(await page.locator('#bvs-cookie-banner').isHidden());
    assert.deepEqual(await choice(),saved,'Closing settings preserves the saved choice');
    assert.deepEqual(errors,[]);
    assert.deepEqual(tracking,[],'Local stays free of Google requests after all choices');
    console.log('Passed: translated banners, 3 widths, reject/accept persistence, reopen/Escape, withdrawal, cross-tab sync, malformed/expired choices, no implicit consent or Google requests.');
  } finally { await browser.close(); }
})().catch(error => {console.error(error);process.exitCode=1;});
