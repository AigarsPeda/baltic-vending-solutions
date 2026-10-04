/* Public privacy-page checks against Local; no enquiries or editorial writes. */
const assert=require('node:assert/strict');
const fs=require('node:fs');
const {chromium}=require('playwright');
const base=process.env.BVS_TEST_URL || 'http://baltic-vending-solutions.local';
if(!new URL(base).hostname.endsWith('.local'))throw new Error('Use a Local .local URL.');
(async()=>{const browser=await chromium.launch({channel:'chrome',headless:true});try{
 const context=await browser.newContext();const page=await context.newPage();const errors=[];const tracking=[];
 page.on('pageerror',error=>errors.push(error.message));page.on('request',request=>{if(/google-analytics|googletagmanager/.test(request.url()))tracking.push(request.url());});
 fs.mkdirSync('output',{recursive:true});
 for(const [lang,path,title,switchPath] of [['lv','/privatums/','Privātums un sīkdatnes','/en/privacy/'],['en','/en/privacy/','Privacy & cookies','/privatums/']])for(const width of [1440,768,375,320]){
  await context.clearCookies();await page.setViewportSize({width,height:1000});const response=await page.goto(base+path,{waitUntil:'networkidle'});assert.equal(response.status(),200);
  assert.equal(await page.locator('.bvs-policy h1').textContent(),title);assert.equal(await page.locator('.bvs-policy h2').count(),8);
  assert.equal(await page.locator('.bvs-policy ul li').count(),4);
  assert.equal(await page.locator('.site-navigation .lang-item a').getAttribute('href'),base+switchPath);
  assert(await page.evaluate(()=>document.documentElement.scrollWidth<=innerWidth),'No page overflow');
  assert.deepEqual(await context.cookies(),[],'Privacy page creates no cookies before confirmation');
  await page.locator('[data-cookie-choice="false"]').click();
  if(width===1440 || width===375)await page.locator('.bvs-policy').screenshot({path:`output/privacy-${lang}-${width}.png`});
  const control=page.locator('.bvs-cookie-open a');await control.click();assert(await page.locator('#bvs-cookie-banner').isVisible());
  assert.equal(await page.evaluate(()=>wp_has_consent('statistics')),false);
  await page.keyboard.press('Escape');assert(await page.locator('#bvs-cookie-banner').isHidden());assert(await control.evaluate(el=>el===document.activeElement),'Cookie focus returns to policy control');
  console.log('Passed privacy:',lang,width);
 }
 const nojs=await browser.newContext({javaScriptEnabled:false});const fallback=await nojs.newPage();await fallback.goto(base+'/en/privacy/');assert.equal(await fallback.locator('.bvs-policy h2').count(),8);assert.equal(await fallback.locator('.bvs-cookie-open a').getAttribute('href'),'#cookie-choices');await nojs.close();
 assert.deepEqual(errors,[]);assert.deepEqual(tracking,[],'No Google tracking in Local');
 console.log('Passed privacy pages, equivalent translations, 4 widths, native policy links, cookie-control focus and no-JavaScript reading.');
}finally{await browser.close();}})().catch(error=>{console.error(error);process.exitCode=1;});
