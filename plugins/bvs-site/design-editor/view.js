/* One browser-local draft, shared by the translated editor pages. No draft upload requests. */
(() => {
  document.querySelectorAll('.bvs-design-editor').forEach(root => {
    const labels = JSON.parse(root.dataset.labels);
    const find = selector => root.querySelector(selector);
    const all = selector => [...root.querySelectorAll(selector)];
    const canvas = find('canvas'), ctx = canvas.getContext('2d');
    const viewer = find('model-viewer'), status = find('.bvs-design-status');
    const draftStatus = find('.bvs-design-save-status');
    const quoteForm = document.querySelector('#design-quote .bvs-quote-form');
    let hasDraft = false;
    const controls = Object.fromEntries(all('[data-control]').map(el=>[el.dataset.control,el]));
    const blank = panel => ({colour:'#00675f', artwork:null, logo:null, fit:'cover', size:22, x:panel==='front'?17:50, y:panel==='front'?6:35, rotation:0, strokes:[]});
    let panels = Object.fromEntries(['front','left','right'].map(name=>[name,blank(name)]));
    let selected = 'front', tool = 'move', dragging = null, frame = 0, ready = false;
    let modelDrawing=false, flatZoom=100, flatPanning=false, panning=null;
    const drawingTools=['raw','smooth','line','rectangle','ellipse','text'];
    const history = [], future = [], textures = {};
    const imageBlobs = new WeakMap();
    let database = null, saveTimer = 0, revision = 0;
    const draftMessage = (key,state) => {draftStatus.textContent=labels[key];draftStatus.dataset.state=state;};
    function draftTransaction(mode,action) {
      return new Promise((resolve,reject)=>{
        if(!database){reject(new Error('Browser storage unavailable'));return;}
        const transaction=database.transaction('drafts',mode),request=action(transaction.objectStore('drafts'));
        transaction.oncomplete=()=>resolve(request.result);
        transaction.onerror=transaction.onabort=()=>reject(transaction.error||new Error('Draft storage failed'));
        if(mode==='readwrite')transaction.commit?.();
      });
    }
    function saveDraft() {
      clearTimeout(saveTimer);if(!hasDraft)return;
      const savedRevision=revision;
      const savedPanels=Object.fromEntries(Object.entries(panels).map(([name,p])=>[name,{...p,artwork:p.artwork?imageBlobs.get(p.artwork):null,logo:p.logo?imageBlobs.get(p.logo):null}]));
      const draft={version:1,updatedAt:Date.now(),panels:savedPanels,selected,tool,brushColour:controls.brushColour.value,brushSize:Number(controls.brushSize.value),drawingTool:controls.drawingTool.value,text:controls.text.value,textSize:Number(controls.textSize.value),shapeFill:controls.shapeFill.checked,flatZoom};
      draftTransaction('readwrite',store=>store.put(draft,'smart-fridge')).then(()=>{
        if(revision===savedRevision)draftMessage('draftSavedMessage','saved');
      }).catch(()=>{if(revision===savedRevision)draftMessage('draftErrorMessage','error');});
    }
    function queueSave() {
      if(!hasDraft)return;revision++;clearTimeout(saveTimer);
      draftMessage('draftSavingMessage','saving');saveTimer=setTimeout(saveDraft,250);
    }
    function updateTool() {
      setFlatPanning(false);
      root.dataset.tool=tool;
      const button=find('button[data-tool="draw"]');button.setAttribute('aria-pressed',String(tool==='draw'));
      const kind=controls.drawingTool.value;
      button.textContent=labels[kind==='erase'?(tool==='draw'?'stopEraseLabel':'eraseActionLabel'):(tool==='draw'?'stopDrawLabel':'drawLabel')];
      find('[data-preview-mode="draw"]').textContent=labels[kind==='erase'?'eraseModelLabel':'drawModelLabel'];
      find('.bvs-design-text-controls').hidden=kind!=='text';
      find('.bvs-design-fill').hidden=!['rectangle','ellipse'].includes(kind);
      find('[data-drawing-hint]').hidden=kind==='text'||kind==='erase';
      find('[data-eraser-hint]').hidden=kind!=='erase';
      find('.bvs-design-brush').hidden=kind==='erase';controls.brushSize.closest('label').hidden=kind==='erase';
      if(tool!=='draw')setModelDrawing(false);
      updateEraserButton();
    }
    function setModelDrawing(active) {
      modelDrawing=active&&ready;root.dataset.modelDrawing=String(modelDrawing);
      viewer.cameraControls=!modelDrawing;viewer.setAttribute('touch-action',modelDrawing?'none':'pan-y');
      all('[data-preview-mode]').forEach(button=>button.setAttribute('aria-pressed',String((button.dataset.previewMode==='draw')===modelDrawing)));
      updateEraserButton();
    }
    async function restoreDraft(draft) {
      if(draft.version!==1)throw new Error('Unknown draft version');
      const restored={};const colour=value=>typeof value==='string'&&/^#[\da-f]{6}$/i.test(value);
      const bounded=(value,min,max)=>Number.isFinite(value)&&value>=min&&value<=max;
      for(const name of ['front','left','right']) {
        const p=draft.panels?.[name];
        if(!p||!colour(p.colour)||!['cover','contain'].includes(p.fit)||!bounded(p.size,5,100)||!bounded(p.x,0,100)||!bounded(p.y,0,100)||!bounded(p.rotation,-180,180)||!Array.isArray(p.strokes)||p.strokes.length>1000||p.strokes.reduce((n,s)=>n+(Array.isArray(s.points)?s.points.length:Infinity),0)>25000)throw new Error('Invalid draft panel');
        const strokes=p.strokes.map(s=>{
          const kind=s.kind||'raw';
          if(!drawingTools.includes(kind)||!colour(s.colour)||!bounded(s.size,2,60)||!s.points.length||s.points.length>5000||!s.points.every(point=>bounded(point.x,0,1)&&bounded(point.y,0,1)))throw new Error('Invalid draft drawing');
          if(kind==='text'&&(typeof s.text!=='string'||s.text.length>100||!bounded(s.textSize,12,120)))throw new Error('Invalid draft text');
          return {kind,colour:s.colour,size:s.size,fill:s.fill===true,...(kind==='text'?{text:s.text,textSize:s.textSize}:{}),points:s.points.map(point=>({x:point.x,y:point.y}))};
        });
        restored[name]={...blank(name),colour:p.colour,fit:p.fit,size:p.size,x:p.x,y:p.y,rotation:p.rotation,strokes};
        for(const kind of ['artwork','logo'])if(p[kind]){
          const blob=p[kind];if(!(blob instanceof Blob)||blob.type!=='image/png'||blob.size>5*1024*1024)throw new Error('Invalid saved image');
          const image=await createImageBitmap(blob);
          try{if(image.width>1024||image.height>1024)throw new Error('Saved image dimensions');
            const asset=document.createElement('canvas');asset.width=image.width;asset.height=image.height;asset.getContext('2d').drawImage(image,0,0);imageBlobs.set(asset,blob);restored[name][kind]=asset;
            restored[name][kind+'Name']=typeof p[kind+'Name']==='string'?p[kind+'Name'].slice(0,255):'';
          }finally{image.close();}
        }
      }
      panels=restored;selected=['front','left','right'].includes(draft.selected)?draft.selected:'front';tool=draft.tool==='draw'?'draw':'move';
      if(colour(draft.brushColour))controls.brushColour.value=draft.brushColour;
      if(bounded(draft.brushSize,2,60)){controls.brushSize.value=draft.brushSize;controls.brushSize.nextElementSibling.value=draft.brushSize;}
      if(drawingTools.includes(draft.drawingTool)||draft.drawingTool==='erase')controls.drawingTool.value=draft.drawingTool;
      if(typeof draft.text==='string')controls.text.value=draft.text.slice(0,100);
      if(bounded(draft.textSize,12,120)){controls.textSize.value=draft.textSize;controls.textSize.nextElementSibling.value=draft.textSize;}
      controls.shapeFill.checked=draft.shapeFill===true;
      if(bounded(draft.flatZoom,100,400)){flatZoom=draft.flatZoom;updateFlatZoom();}
      hasDraft=true;quoteForm?.dispatchEvent(new Event('bvs:design-changed'));
      viewer.cameraOrbit=`${mobile.matches?0:{front:0,left:-75,right:75}[selected]}deg 90deg 4.74m`;
      draftMessage('draftRestoredMessage','saved');
    }
    async function loadDraft() {
      try {
        database=await new Promise((resolve,reject)=>{
          const request=indexedDB.open('bvs-design-editor',1);
          request.onupgradeneeded=()=>request.result.createObjectStore('drafts');
          request.onsuccess=()=>resolve(request.result);request.onerror=request.onblocked=()=>reject(request.error||new Error('Browser storage blocked'));
        });
        database.onversionchange=()=>{database.close();database=null;draftMessage('draftErrorMessage','error');};
        const draft=await draftTransaction('readonly',store=>store.get('smart-fridge'));
        if(draft)await restoreDraft(draft);else draftMessage('draftEmptyMessage','empty');
      }catch(_){draftMessage('draftErrorMessage','error');}
      updateTool();
      find('.bvs-design-drawing').open=tool==='draw';refresh();find('.bvs-design-workbench').hidden=false;
    }
    addEventListener('pagehide',saveDraft);
    document.addEventListener('visibilitychange',()=>{if(document.visibilityState==='hidden')saveDraft();});
    const mobile = matchMedia('(max-width: 680px)');
    const resetCameraView=()=>{viewer.cameraOrbit=`${mobile.matches?0:25}deg 90deg 4.74m`;viewer.cameraTarget='0m .965m 0m';};
    if(mobile.matches)resetCameraView();
    const flat = find('.bvs-design-flat'), tools = find('.bvs-design-tools'), previews = find('.bvs-design-previews');
    const stageHeading = find('.bvs-design-stage-heading'), machine = find('.bvs-design-machine');
    const previewTools=find('.bvs-design-preview-tools'),historyControls=find('.bvs-design-history');
    let mobileView='model';
    const mobilePreview=document.createElement('div');mobilePreview.className='bvs-design-mobile-preview';mobilePreview.id=root.id+'mobile-preview';
    const mobileHeader=document.createElement('div');mobileHeader.className='bvs-design-mobile-heading';
    const mobileTitle=document.createElement('h3');mobileHeader.append(mobileTitle);
    const switchPreview=find('[data-action="preview-switch"]'),resetView=find('[data-action="view"]');
    switchPreview.setAttribute('aria-controls',mobilePreview.id);
    function eraserActive() {
      return tool==='draw'&&controls.drawingTool.value==='erase'&&(modelDrawing||mobile.matches&&mobileView==='flat'||!ready);
    }
    function updateEraserButton() {find('[data-action=eraser]').setAttribute('aria-pressed',String(eraserActive()));}
    function updateMobileView() {
      root.dataset.mobileView=mobileView;
      if(mobile.matches){
        machine.hidden=mobileView==='flat';flat.hidden=mobileView==='model';
        mobileTitle.textContent=labels[mobileView==='model'?'modelViewLabel':'flatViewLabel'];
        switchPreview.textContent=labels[mobileView==='model'?'showFlatViewLabel':'showModelViewLabel'];
        resetView.hidden=mobileView==='flat';
      }else{machine.hidden=false;flat.hidden=false;resetView.hidden=false;}
      switchPreview.disabled=mobile.matches&&mobileView==='flat'&&root.dataset.model==='error';
      switchPreview.title=switchPreview.disabled?labels.fallbackMessage:'';
      updateEraserButton();
    }
    const adapt = () => {
      if(mobile.matches) {
        tools.prepend(mobilePreview);mobilePreview.append(mobileHeader,machine,flat);
        mobileHeader.append(historyControls,switchPreview);machine.querySelector('.bvs-design-model').append(resetView);switchPreview.hidden=false;
      } else {
        previews.prepend(machine);previews.append(flat);find('.bvs-design-stage').prepend(stageHeading);
        previewTools.prepend(resetView);previewTools.append(historyControls);stageHeading.append(switchPreview);switchPreview.hidden=true;mobilePreview.remove();
      }
      updateMobileView();
    };
    mobile.addEventListener('change',adapt);adapt();
    const sizes = {front:[592,1024],left:[400,1024],right:[400,1024]};
    const cutouts = new Path2D(find('.bvs-design-mask path').getAttribute('d'));
    const say = (key,error=false) => {status.textContent=labels[key];status.dataset.error=String(error);};
    const snapshot = () => Object.fromEntries(Object.entries(panels).map(([name,p])=>[name,{...p,strokes:p.strokes.map(s=>({...s,points:s.points.map(point=>({...point}))}))}]));
    const markDraft = () => {hasDraft=true;quoteForm?.dispatchEvent(new Event('bvs:design-changed'));queueSave();};
    const remember = () => {markDraft();history.push(snapshot());if(history.length>20)history.shift();future.length=0;};
    function paintDrawing(c,s,w,h) {
      const points=s.points.map(p=>({x:p.x*w,y:p.y*h})),a=points[0],b=points.at(-1),kind=s.kind||'raw';
      c.strokeStyle=s.colour;c.fillStyle=s.colour;c.lineWidth=s.size*w/592;c.lineCap='round';c.lineJoin='round';
      if(kind==='text'){c.font=`600 ${s.textSize*w/592}px Plex, sans-serif`;c.textBaseline='top';c.fillText(s.text,a.x,a.y);return;}
      c.beginPath();
      if(kind==='rectangle')c.rect(Math.min(a.x,b.x),Math.min(a.y,b.y),Math.abs(b.x-a.x),Math.abs(b.y-a.y));
      else if(kind==='ellipse')c.ellipse((a.x+b.x)/2,(a.y+b.y)/2,Math.abs(b.x-a.x)/2,Math.abs(b.y-a.y)/2,0,0,Math.PI*2);
      else if(kind==='smooth'&&points.length>2){
        c.moveTo(a.x,a.y);
        for(let i=1;i<points.length-1;i++){const p=points[i],next=points[i+1];c.quadraticCurveTo(p.x,p.y,(p.x+next.x)/2,(p.y+next.y)/2);}
        c.lineTo(b.x,b.y);
      }else{
        points.forEach((point,i)=>{if(!i)c.moveTo(point.x,point.y);else c.lineTo(point.x,point.y);});
        if(points.length===1)c.lineTo(a.x+.01,a.y+.01);
      }
      if(s.fill&&['rectangle','ellipse'].includes(kind))c.fill();else c.stroke();
    }
    // Simplify in actual panel pixels so smoothing strength is consistent on all panels.
    function smoothStroke(s,panel) {
      if(s.kind!=='smooth'||s.points.length<3)return;
      const [w,h]=sizes[panel],pixels=s.points.map(p=>({x:p.x*w,y:p.y*h}));
      const distance=(p,a,b)=>{
        const dx=b.x-a.x,dy=b.y-a.y,t=Math.max(0,Math.min(1,((p.x-a.x)*dx+(p.y-a.y)*dy)/(dx*dx+dy*dy||1)));
        return Math.hypot(p.x-a.x-t*dx,p.y-a.y-t*dy);
      };
      const first=pixels[0],last=pixels.at(-1);
      if(Math.hypot(last.x-first.x,last.y-first.y)>30&&pixels.every(p=>distance(p,first,last)<8+s.size/2)){
        s.points=[s.points[0],s.points.at(-1)];return;
      }
      // Iterative Ramer–Douglas–Peucker avoids recursion on long strokes.
      const kept=new Set([0,pixels.length-1]),stack=[[0,pixels.length-1]];
      while(stack.length){const [start,end]=stack.pop();let max=2,index=0;
        for(let i=start+1;i<end;i++){const d=distance(pixels[i],pixels[start],pixels[end]);if(d>max){max=d;index=i;}}
        if(index){kept.add(index);stack.push([start,index],[index,end]);}
      }
      s.points=[...kept].sort((a,b)=>a-b).map(i=>s.points[i]);
    }
    function paint(target,panel,flip=false) {
      const p = panels[panel], c = target.getContext('2d'), w=target.width, h=target.height;
      c.clearRect(0,0,w,h);c.save();
      // CanvasTexture uses bottom-up UVs; flat edits use top-down screen coordinates.
      if(flip){c.translate(0,h);c.scale(1,-1);}
      c.fillStyle=p.colour;c.fillRect(0,0,w,h);
      if (p.artwork) {
        const scale = (p.fit==='cover'?Math.max:Math.min)(w/p.artwork.width,h/p.artwork.height);
        const iw=p.artwork.width*scale,ih=p.artwork.height*scale;
        c.drawImage(p.artwork,(w-iw)/2,(h-ih)/2,iw,ih);
      }
      for (const stroke of p.strokes)paintDrawing(c,stroke,w,h);
      paintLogo(c,p,w,h);
      // Flat templates have transparent hardware cut-outs. The 3D model already has separate hardware geometry.
      if(panel==='front'&&!flip){c.globalCompositeOperation='destination-out';c.scale(w/592,h/1024);c.fill(cutouts);}
      c.restore();
    }
    function paintLogo(c,p,w,h) {
      if(!p.logo)return;
      const lw=w*p.size/100,lh=lw*p.logo.height/p.logo.width;
      c.save();c.translate(w*p.x/100,h*p.y/100);c.rotate(p.rotation*Math.PI/180);c.drawImage(p.logo,-lw/2,-lh/2,lw,lh);c.restore();
    }
    const eraseCanvas=document.createElement('canvas');eraseCanvas.width=eraseCanvas.height=11;
    const eraseContext=eraseCanvas.getContext('2d',{willReadFrequently:true});
    function eraseAt(panel,pos) {
      const p=panels[panel],[w,h]=sizes[panel];
      // Test actual painted pixels in a small patch, including rotated logos and smoothed paths.
      const hits=draw=>{
        eraseContext.clearRect(0,0,11,11);eraseContext.save();eraseContext.translate(5-pos.x*w,5-pos.y*h);draw();eraseContext.restore();
        const pixels=eraseContext.getImageData(0,0,11,11).data;
        return pixels.some((value,index)=>index%4===3&&value>0);
      };
      if(p.logo&&hits(()=>paintLogo(eraseContext,p,w,h))){remember();p.logo=null;refresh();saveDraft();return;}
      for(let i=p.strokes.length-1;i>=0;i--)if(hits(()=>paintDrawing(eraseContext,p.strokes[i],w,h))){
        remember();p.strokes.splice(i,1);refresh();saveDraft();return;
      }
    }
    function render() {
      paint(canvas,selected);
      for(const [panel,texture] of Object.entries(textures)){paint(texture.source.element,panel,true);texture.source.update();}
      find('[data-action="undo"]').disabled=!history.length;
      find('[data-action="redo"]').disabled=!future.length;
    }
    const schedule = () => {markDraft();cancelAnimationFrame(frame);frame=requestAnimationFrame(render);};
    function refresh() {
      const p=panels[selected];[canvas.width,canvas.height]=sizes[selected];
      root.dataset.panel=selected;
      all('[data-panel]').forEach(button=>button.setAttribute('aria-pressed',String(button.dataset.panel===selected)));
      controls.colour.value=p.colour;controls.hex.value=p.colour;controls.fit.value=p.fit;
      for(const key of ['size','x','y','rotation']){controls[key].value=p[key];controls[key].nextElementSibling.value=p[key];}
      find('.bvs-design-logo-controls').hidden=!p.logo;
      find('.bvs-design-fit').hidden=!p.artwork;
      for(const kind of ['artwork','logo']) {find(`[data-action="remove-${kind}"]`).hidden=!p[kind];const input=find(`[data-upload="${kind}"]`);input.value='';const name=find(`[data-file-label="${kind}"]`);name.textContent=p[kind+'Name']||'';name.hidden=!p[kind];}
      render();
    }
    all('[data-panel]').forEach(button=>button.addEventListener('click',()=>{
      if(dragging)return;
      selected=button.dataset.panel;dragging=null;refresh();
      viewer.cameraOrbit=`${{front:0,left:-75,right:75}[selected]}deg 90deg ${ready?viewer.getCameraOrbit().radius:4.74}m`;
      viewer.cameraTarget='0m .965m 0m';
      queueSave();
      saveDraft();
    }));
    for(const key of ['colour','hex','fit','size','x','y','rotation']) {
      const input=controls[key];
      // One history entry per continuous slider / colour drag.
      input.addEventListener('pointerdown',()=>{if(key!=='hex'&&key!=='fit')remember();});
      input.addEventListener('keydown',event=>{if(event.key.startsWith('Arrow'))remember();});
      input.addEventListener(key==='fit'||key==='hex'?'change':'input',()=>{
        if(key==='hex'&&!/^#[\da-f]{6}$/i.test(input.value)){input.value=panels[selected].colour;return;}
        if(key==='hex'||key==='fit')remember();
        const property=key==='hex'?'colour':key;
        panels[selected][property]=['size','x','y','rotation'].includes(key)?Number(input.value):input.value;
        if(property==='colour'){controls.colour.value=input.value;controls.hex.value=input.value;}
        if(input.nextElementSibling?.tagName==='OUTPUT')input.nextElementSibling.value=input.value;
        schedule();
      });
      input.addEventListener('change',saveDraft);
      if(['size','x','y','rotation'].includes(key))input.addEventListener('dblclick',event=>{
        event.preventDefault();const value=blank(selected)[key];
        if(panels[selected][key]===value)return;
        remember();panels[selected][key]=value;input.value=value;input.nextElementSibling.value=value;
        schedule();saveDraft();
      });
    }
    all('[data-upload]').forEach(input=>input.addEventListener('change',async()=>{
      const file=input.files[0], panel=selected, kind=input.dataset.upload;if(!file)return;
      if(!['image/png','image/jpeg','image/webp'].includes(file.type)||file.size>5*1024*1024){input.value='';say('fileError',true);return;}
      const url=URL.createObjectURL(file);
      try {
        const image=new Image();image.src=url;await image.decode();
        if(image.width>4096||image.height>4096||image.width*image.height>16777216)throw new Error('Image dimensions');
        // The editor makes a visual mockup, so keep working images bounded to 1024px.
        const normalized=document.createElement('canvas'), ratio=Math.min(1,1024/Math.max(image.width,image.height));
        normalized.width=Math.max(1,Math.round(image.width*ratio));normalized.height=Math.max(1,Math.round(image.height*ratio));
        normalized.getContext('2d').drawImage(image,0,0,normalized.width,normalized.height);
        const blob=await new Promise(resolve=>normalized.toBlob(resolve,'image/png'));if(!blob)throw new Error('Image conversion failed');imageBlobs.set(normalized,blob);
        remember();panels[panel][kind]=normalized;panels[panel][kind+'Name']=file.name;refresh();saveDraft();say(ready?'readyMessage':'fallbackMessage');
      } catch(_){input.value='';say('fileError',true);} finally {URL.revokeObjectURL(url);}
    }));
    all('button[data-tool]').forEach(button=>button.addEventListener('click',()=>{
      tool=tool==='draw'?'move':'draw';updateTool();
      queueSave();
      saveDraft();
    }));
    controls.drawingTool.addEventListener('change',()=>{tool='draw';updateTool();queueSave();saveDraft();});
    for(const key of ['text','textSize','shapeFill']){
      controls[key].addEventListener('input',()=>{
        if(key==='textSize')controls[key].nextElementSibling.value=controls[key].value;
        queueSave();
      });controls[key].addEventListener('change',saveDraft);
    }
    all('[data-preview-mode]').forEach(button=>button.addEventListener('click',()=>{
      if(button.dataset.previewMode==='draw'){tool='draw';updateTool();find('.bvs-design-drawing').open=true;}
      setModelDrawing(button.dataset.previewMode==='draw');queueSave();saveDraft();
    }));
    function updateFlatZoom() {
      find('.bvs-design-canvas-wrap').style.setProperty('--bvs-flat-zoom',flatZoom/100);
      find('[data-flat-zoom]').value=`${flatZoom}%`;
      find('[data-zoom="flat-out"]').disabled=flatZoom<=100;find('[data-zoom="flat-in"]').disabled=flatZoom>=400;
      find('[data-action="pan-panel"]').hidden=flatZoom<=100;
      if(flatZoom<=100)setFlatPanning(false);
    }
    function setFlatPanning(active) {
      flatPanning=active;root.dataset.flatPanning=String(active);
      const button=find('[data-action="pan-panel"]');button.setAttribute('aria-pressed',String(active));button.textContent=labels[active?'stopPanPanelLabel':'panPanelLabel'];
    }
    updateFlatZoom();
    all('[data-zoom]').forEach(button=>button.addEventListener('click',()=>{
      const [surface,direction]=button.dataset.zoom.split('-'),step=direction==='in'?25:-25;
      if(surface==='flat'){flatZoom=Math.max(100,Math.min(400,flatZoom+step));updateFlatZoom();queueSave();saveDraft();}
      else if(ready){const orbit=viewer.getCameraOrbit(),zoom=Math.max(75,Math.min(300,Math.round(474/orbit.radius)+step));viewer.cameraOrbit=`${orbit.theta}rad ${orbit.phi}rad ${474/zoom}m`;}
    }));
    viewer.addEventListener('camera-change',()=>{
      const zoom=Math.round(474/viewer.getCameraOrbit().radius);find('[data-model-zoom]').value=`${zoom}%`;
      find('[data-zoom="model-out"]').disabled=zoom<=75;find('[data-zoom="model-in"]').disabled=zoom>=300;
    });
    controls.brushSize.addEventListener('input',()=>{controls.brushSize.nextElementSibling.value=controls.brushSize.value;queueSave();});
    controls.brushColour.addEventListener('input',queueSave);
    controls.brushSize.addEventListener('change',saveDraft);controls.brushColour.addEventListener('change',saveDraft);
    function flatPoint(event) {
      const box=canvas.getBoundingClientRect();
      const pos={x:(event.clientX-box.left)/box.width,y:(event.clientY-box.top)/box.height};
      return editablePoint(selected,pos)?{panel:selected,pos}:null;
    }
    function editablePoint(panel,pos) {
      if(!pos||pos.x<0||pos.x>1||pos.y<0||pos.y>1)return false;
      return panel!=='front'||!ctx.isPointInPath(cutouts,pos.x*592,pos.y*1024);
    }
    function modelPoint(event) {
      if(!ready)return null;
      const material=viewer.materialFromPoint(event.clientX,event.clientY);
      const panel=['front','left','right'].find(name=>material?.name===`BVS wrap ${name}`);
      if(!panel)return null;
      const hit=viewer.positionAndNormalFromPoint(event.clientX,event.clientY);
      // The glTF's top-down UVs match the flat template; texture painting handles flipY separately.
      const pos=hit?.uv?{x:hit.uv.u,y:hit.uv.v}:null;
      return editablePoint(panel,pos)?{panel,pos}:null;
    }
    function beginGesture(event,surface) {
      if(event.button!==0||dragging||panning||surface===viewer&&!modelDrawing)return;
      if(surface===canvas&&flatPanning){
        const viewport=find('.bvs-design-canvas-wrap');
        panning={id:event.pointerId,x:event.clientX,y:event.clientY,left:viewport.scrollLeft,top:viewport.scrollTop};
        event.preventDefault();canvas.setPointerCapture(event.pointerId);return;
      }
      const hit=(surface===viewer?modelPoint:flatPoint)(event);if(!hit)return;
      const {panel,pos}=hit,p=panels[panel],kind=controls.drawingTool.value;
      if(tool==='draw'&&kind==='erase'){
        event.preventDefault();if(surface===viewer)event.stopPropagation();
        if(selected!==panel){selected=panel;refresh();queueSave();}
        eraseAt(panel,pos);return;
      }
      if(tool==='move'&&!p.logo)return;
      if(tool==='draw'&&kind==='text'&&!controls.text.value.trim()){controls.text.focus();return;}
      if(tool==='draw'&&(p.strokes.length>=1000||p.strokes.reduce((n,s)=>n+s.points.length,0)>=20000)){say('drawingLimitMessage',true);return;}
      event.preventDefault();
      if(surface===viewer)event.stopPropagation();
      if(selected!==panel){selected=panel;refresh();}
      remember();surface.setPointerCapture(event.pointerId);
      dragging={id:event.pointerId,panel,surface,kind:tool==='draw'?kind:'move',start:pos,first:p.strokes.length,stroke:null};
      if(tool==='draw'){
        const stroke={kind,colour:controls.brushColour.value,size:Number(controls.brushSize.value),fill:controls.shapeFill.checked,points:[pos]};
        if(kind==='text'){stroke.text=controls.text.value.trim();stroke.textSize=Number(controls.textSize.value);}
        if(['line','rectangle','ellipse'].includes(kind))stroke.points.push(pos);
        p.strokes.push(stroke);dragging.stroke=stroke;
      }else{dragging.dx=p.x-pos.x*100;dragging.dy=p.y-pos.y*100;}
      schedule();
    }
    function moveGesture(event,surface) {
      if(surface===canvas&&panning?.id===event.pointerId){
        event.preventDefault();const viewport=find('.bvs-design-canvas-wrap');
        viewport.scrollLeft=panning.left+panning.x-event.clientX;viewport.scrollTop=panning.top+panning.y-event.clientY;return;
      }
      if(!dragging||dragging.id!==event.pointerId||dragging.surface!==surface)return;
      event.preventDefault();if(surface===viewer)event.stopPropagation();
      const p=panels[dragging.panel],hit=(surface===viewer?modelPoint:flatPoint)(event);
      if(!hit||hit.panel!==dragging.panel){
        if(['raw','smooth'].includes(dragging.kind))dragging.stroke=null;
        return;
      }
      const pos=hit.pos,kind=dragging.kind;
      if(kind==='move'){
        p.x=Math.max(0,Math.min(100,Math.round(pos.x*100+dragging.dx)));p.y=Math.max(0,Math.min(100,Math.round(pos.y*100+dragging.dy)));
        for(const key of ['x','y']){controls[key].value=p[key];controls[key].nextElementSibling.value=p[key];}
      }else if(['raw','smooth'].includes(kind)){
        if(p.strokes.reduce((n,s)=>n+s.points.length,0)>=25000)return;
        if(!dragging.stroke){if(p.strokes.length>=1000)return;dragging.stroke={kind,colour:controls.brushColour.value,size:Number(controls.brushSize.value),points:[pos]};p.strokes.push(dragging.stroke);}
        else if(dragging.stroke.points.length<5000)dragging.stroke.points.push(pos);
      }else if(['line','rectangle','ellipse'].includes(kind)){
        const end={...pos};
        if(event.shiftKey&&kind!=='line'){
          const [w,h]=sizes[dragging.panel],start=dragging.start,side=Math.min(Math.abs(end.x-start.x)*w,Math.abs(end.y-start.y)*h);
          end.x=start.x+Math.sign(end.x-start.x)*side/w;end.y=start.y+Math.sign(end.y-start.y)*side/h;
        }
        dragging.stroke.points[1]=end;
      }
      schedule();
    }
    function endGesture(event,cancel=false) {
      if(panning?.id===event.pointerId){panning=null;if(canvas.hasPointerCapture(event.pointerId))canvas.releasePointerCapture(event.pointerId);return;}
      if(!dragging||event.pointerId!==dragging.id)return;
      const gesture=dragging;dragging=null;
      if(cancel){panels=history.pop();refresh();}
      else {for(const stroke of panels[gesture.panel].strokes.slice(gesture.first))smoothStroke(stroke,gesture.panel);render();}
      if(gesture.surface.hasPointerCapture(event.pointerId))gesture.surface.releasePointerCapture(event.pointerId);
      queueSave();saveDraft();
    }
    for(const surface of [canvas,viewer]){
      surface.addEventListener('pointerdown',event=>beginGesture(event,surface),true);
      surface.addEventListener('pointermove',event=>{
        if(dragging?.surface===viewer){event.preventDefault();event.stopPropagation();}
        // Preserve every reported sample in exact mode, including high-frequency pen input.
        const samples=dragging?.kind==='raw'?event.getCoalescedEvents?.():null;
        for(const sample of samples?.length?samples:[event])moveGesture(sample,surface);
      },true);
      surface.addEventListener('pointerup',event=>endGesture(event),true);
      surface.addEventListener('pointercancel',event=>endGesture(event,true),true);
      surface.addEventListener('lostpointercapture',event=>endGesture(event),true);
    }
    canvas.addEventListener('keydown',event=>{
      if(flatPanning&&event.key.startsWith('Arrow')){
        event.preventDefault();const viewport=find('.bvs-design-canvas-wrap');
        viewport.scrollBy({left:event.key==='ArrowLeft'?-30:event.key==='ArrowRight'?30:0,top:event.key==='ArrowUp'?-30:event.key==='ArrowDown'?30:0});return;
      }
      if(tool!=='move'||!panels[selected].logo||!['ArrowLeft','ArrowRight','ArrowUp','ArrowDown'].includes(event.key))return;
      event.preventDefault();remember();const p=panels[selected];const key=event.key.includes('Left')||event.key.includes('Right')?'x':'y';
      p[key]=Math.max(0,Math.min(100,p[key]+(event.key==='ArrowLeft'||event.key==='ArrowUp'?-1:1)*(event.shiftKey?5:1)));refresh();saveDraft();
    });
    root.addEventListener('keydown',event=>{
      if(dragging||event.target.matches('input,select,textarea')||!(event.ctrlKey||event.metaKey))return;
      const key=event.key.toLowerCase();
      if(key==='z'||key==='y'){event.preventDefault();find(`[data-action="${key==='y'||event.shiftKey?'redo':'undo'}"]`).click();}
    });
    async function designFile() {
      render();await new Promise(resolve=>requestAnimationFrame(resolve));
      const out=document.createElement('canvas');out.width=1880;out.height=1100;const c=out.getContext('2d');
      c.fillStyle='#f3f6f6';c.fillRect(0,0,out.width,out.height);c.fillStyle='#16282d';c.font='600 30px sans-serif';c.fillText(labels.downloadTitle,40,52);
      let start=40;
      if(ready){
        const hiddenMachine=machine.hidden;
        if(hiddenMachine){machine.hidden=false;flat.hidden=true;await new Promise(resolve=>requestAnimationFrame(()=>requestAnimationFrame(resolve)));}
        try{const blob=await viewer.toBlob({mimeType:'image/png',idealAspect:true});const image=await createImageBitmap(blob);const scale=Math.min(460/image.width,800/image.height);c.drawImage(image,20,90,image.width*scale,image.height*scale);image.close();start=520;}catch(_){start=40;}
        finally{if(hiddenMachine)updateMobileView();}
      }
      for(const panel of ['front','left','right']) {
        const flat=document.createElement('canvas');[flat.width,flat.height]=sizes[panel];paint(flat,panel);
        const w=panel==='front'?462.5:312.5;
        c.font='600 22px sans-serif';c.fillText(labels[panel+'Label'],start,104);c.drawImage(flat,start,130,w,800);start+=w+32;
      }
      c.fillStyle='#506166';c.font='20px sans-serif';c.fillText(labels.exportNote,40,1000);
      c.font='16px sans-serif';c.fillText('Baltic Vending Solutions',40,1040);
      const blob=await new Promise(resolve=>out.toBlob(resolve,'image/png'));
      if(!blob)throw new Error('PNG export failed');
      return new File([blob],'bvs-smart-fridge-design.png',{type:'image/png'});
    }
    let exporting=false;
    if(quoteForm)quoteForm.bvsEditorDesign={hasDraft:()=>hasDraft,file:designFile};
    all('[data-action]').forEach(button=>button.addEventListener('click',async()=>{
      const action=button.dataset.action,p=panels[selected];
      if(action==='preview-switch'){if(exporting||dragging||panning)return;mobileView=mobileView==='model'?'flat':'model';setModelDrawing(false);updateMobileView();return;}
      if(action==='pan-panel'){if(!dragging&&!panning)setFlatPanning(!flatPanning);return;}
      if(action==='view'){resetCameraView();return;}
      if(dragging)return;
      if(action==='eraser'){
        if(panning)return;
        const active=eraserActive();tool=active?'move':'draw';
        if(!active)controls.drawingTool.value='erase';
        updateTool();setModelDrawing(!active&&(!mobile.matches||mobileView==='model'));queueSave();saveDraft();return;
      }
      if(action==='undo'){if(history.length){future.push(snapshot());panels=history.pop();refresh();queueSave();saveDraft();}return;}
      if(action==='redo'){if(future.length){history.push(snapshot());panels=future.pop();refresh();queueSave();saveDraft();}return;}
      if(action==='reset'){
        if(!confirm(labels.resetConfirm))return;clearTimeout(saveTimer);revision++;hasDraft=false;history.length=0;future.length=0;
        tool='move';controls.drawingTool.value='raw';controls.text.value='';controls.shapeFill.checked=false;flatZoom=100;updateTool();updateFlatZoom();
        panels=Object.fromEntries(['front','left','right'].map(name=>[name,blank(name)]));refresh();quoteForm?.dispatchEvent(new Event('bvs:design-changed'));
        if(quoteForm){quoteForm.querySelector('.bvs-use-editor-design').checked=true;quoteForm.elements.design.value='';quoteForm.elements.design.dispatchEvent(new CustomEvent('change',{bubbles:true,detail:{editor:true}}));}
        try{await draftTransaction('readwrite',store=>store.delete('smart-fridge'));draftMessage('draftClearedMessage','empty');}catch(_){draftMessage('draftErrorMessage','error');}
        return;
      }
      if(action==='colour-all'){remember();Object.values(panels).forEach(panel=>panel.colour=p.colour);render();saveDraft();return;}
      if(action.startsWith('remove-')){remember();p[action.slice(7)]=null;refresh();saveDraft();return;}
      if(action==='clear'){remember();p.strokes=[];render();saveDraft();return;}
      if(!['download','attach'].includes(action)||exporting)return;
      exporting=true;const exportButtons=all('[data-action="attach"],[data-action="download"]');exportButtons.forEach(el=>el.disabled=true);
      try {
        const file=await designFile();
        if(action==='download'){const url=URL.createObjectURL(file),link=document.createElement('a');link.href=url;link.download=file.name;link.click();setTimeout(()=>URL.revokeObjectURL(url),30000);say('downloadMessage');}
        else {
          const form=quoteForm,input=form?.elements.design;
          if(!input)throw new Error('Missing quote attachment field');
          form.querySelector('.bvs-use-editor-design').checked=true;markDraft();
          const transfer=new DataTransfer();transfer.items.add(file);input.files=transfer.files;input.dispatchEvent(new CustomEvent('change',{bubbles:true,detail:{editor:true}}));
          form.elements.model.value='fridge';say('attachedMessage');
          form.scrollIntoView({behavior:matchMedia('(prefers-reduced-motion: reduce)').matches?'instant':'smooth',block:'start'});
          form.elements.name.focus({preventScroll:true});
        }
      }catch(_){say('exportError',true);}finally{exporting=false;exportButtons.forEach(el=>el.disabled=false);}
    }));
    viewer.addEventListener('load',()=>{
      try {
        for(const panel of ['front','left','right']) {
          const material=viewer.model.materials.find(m=>m.name===`BVS wrap ${panel}`);
          if(!material)throw new Error('Wrap material missing');
          const texture=viewer.createCanvasTexture();[texture.source.element.width,texture.source.element.height]=sizes[panel];
          material.pbrMetallicRoughness.setBaseColorFactor('#ffffff');material.pbrMetallicRoughness.baseColorTexture.setTexture(texture);textures[panel]=texture;
        }
        ready=true;root.dataset.model='ready';updateMobileView();find('[data-preview-mode="draw"]').disabled=false;render();say('readyMessage');
      }catch(_){root.dataset.model='error';mobileView='flat';updateMobileView();say('fallbackMessage',true);}
    });
    viewer.addEventListener('error',()=>{ready=false;setModelDrawing(false);find('[data-preview-mode="draw"]').disabled=true;root.dataset.model='error';mobileView='flat';updateMobileView();say('fallbackMessage',true);});
    setTimeout(()=>{if(!ready){root.dataset.model='error';mobileView='flat';updateMobileView();say('fallbackMessage',true);}},20000);
    loadDraft();
  });
})();
