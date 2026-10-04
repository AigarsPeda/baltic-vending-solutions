const assert=require('node:assert/strict');const {chromium}=require('playwright');
const base='http://baltic-vending-solutions.local';
(async()=>{
 const browser=await chromium.launch({channel:'chrome',headless:true});
 try {
 const context=await browser.newContext({viewport:{width:1440,height:1100}});const page=await context.newPage();const errors=[];let posts=0,downloads=0;
 page.on('pageerror',error=>errors.push(error.message));page.on('download',()=>downloads++);page.on('request',request=>{if(request.method()==='POST')posts++;});
 await page.goto(base+'/en/design-editor/',{waitUntil:'networkidle'});await page.waitForFunction(()=>document.querySelector('.bvs-design-editor').dataset.model==='ready');await page.evaluate(()=>document.querySelector('.bvs-cookie-banner')?.remove());
 const form=page.locator('#design-quote .bvs-quote-form');const option=form.locator('.bvs-use-editor-design');
 assert.equal(await form.locator('.bvs-editor-design-option').isVisible(),false,'Untouched editor does not attach a default design');
 await page.locator('[data-control=hex]').fill('#ed5b24');await page.locator('[data-control=hex]').dispatchEvent('change');
 assert.equal(await option.isChecked(),true);assert.equal(await form.locator('[name=design]').isVisible(),false);assert.equal(posts,0);
 await option.uncheck();assert.equal(await form.locator('[name=design]').isVisible(),true);
 const pixelFile=await page.evaluate(()=>{const canvas=document.createElement('canvas');canvas.width=8;canvas.height=8;return canvas.toDataURL().split(',')[1];});
 await form.locator('[name=design]').setInputFiles({name:'manual.png',mimeType:'image/png',buffer:Buffer.from(pixelFile,'base64')});assert.equal(await option.isChecked(),false);
 await option.check();assert.equal(await form.locator('[name=design]').evaluate(input=>input.files.length),0);
 // The quote is filled first, then the visitor makes another edit. Submission must use that latest state.
 await form.locator('[name=name]').fill('BVS automatic design verification');await form.locator('[name=email]').fill('test@example.invalid');await form.locator('[name=products]').fill('Synthetic automatic design test');
 await page.locator('[data-control=hex]').fill('#245bed');await page.locator('[data-control=hex]').dispatchEvent('change');await page.locator('[data-action=colour-all]').click();
 let attached;
 await page.route('**/admin-ajax.php',async route=>{
   const request=route.request(),body=request.postDataBuffer(),boundary=request.headers()['content-type'].split('boundary=')[1];
   const parts=body.toString('latin1').split('--'+boundary);const part=parts.find(value=>value.includes('name="design"; filename="bvs-smart-fridge-design.png"'));assert.ok(part,'Automatic submission includes generated PNG');
   attached=Buffer.from(part.slice(part.indexOf('\r\n\r\n')+4,-2),'latin1');await route.continue();
 });
 await form.locator('[type=submit]').click();await page.waitForSelector('.bvs-form-message[data-status=success]');
 const colour=await page.evaluate(async bytes=>{const image=await createImageBitmap(new Blob([new Uint8Array(bytes)],{type:'image/png'}));const canvas=document.createElement('canvas');canvas.width=image.width;canvas.height=image.height;canvas.getContext('2d').drawImage(image,0,0);return [...canvas.getContext('2d').getImageData(1400,200,1,1).data];},[...attached]);
 assert.deepEqual(colour,[36,91,237,255],'Latest blue design, not the earlier orange edit, was submitted');assert.equal(downloads,0);assert.equal(posts,1);assert.deepEqual(await context.cookies(),[]);assert.equal(await page.evaluate(()=>localStorage.length+sessionStorage.length),0);assert.deepEqual(errors,[]);
 await page.unroute('**/admin-ajax.php');
 // Removing the editor design opts out; a rejected submission must keep that choice.
 await form.locator('.bvs-remove-design').click();assert.equal(await option.isChecked(),false);assert.equal(await form.locator('[name=design]').isVisible(),true);
 await form.locator('[name=name]').fill('BVS excluded design verification');await form.locator('[name=email]').fill('test@example.invalid');await form.locator('[name=products]').fill('Synthetic excluded design');
 await page.route('**/admin-ajax.php',async route=>{assert.ok(!route.request().postDataBuffer().toString('latin1').includes('bvs-smart-fridge-design.png'));await route.fulfill({status:400,contentType:'application/json',body:JSON.stringify({success:false,data:{message:'Synthetic rejection'}})});});
 await form.locator('[type=submit]').click();await page.waitForSelector('.bvs-form-message[data-status=error]');assert.equal(await option.isChecked(),false);assert.equal(await form.locator('[name=name]').inputValue(),'BVS excluded design verification');
 await page.setViewportSize({width:375,height:900});await page.waitForFunction(()=>document.documentElement.scrollWidth<=innerWidth);assert.equal(await page.evaluate(()=>document.documentElement.scrollWidth>innerWidth),false);await option.check();await form.locator('.bvs-design-attachment').screenshot({path:'output/design-auto-form-en-375.png'});
 const lv=await context.newPage();await lv.goto(base+'/dizaina-redaktors/',{waitUntil:'networkidle'});await lv.locator('[data-control=hex]').fill('#ed5b24');await lv.locator('[data-control=hex]').dispatchEvent('change');assert.match(await lv.locator('.bvs-editor-design-option').innerText(),/automātiski/);await lv.locator('.bvs-design-attachment').screenshot({path:'output/design-auto-form-lv-1440.png'});
 console.log('PASS: latest editor design attached on direct form submission, zero downloads/manual uploads, untouched draft excluded, manual override and remove preserved, translated copy and mobile reflow, no cookies or local/session storage.');
 } finally {await browser.close();}
})().catch(error=>{console.error(error);process.exit(1);});
