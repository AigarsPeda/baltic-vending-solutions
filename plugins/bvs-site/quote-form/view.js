(() => {
  document.querySelectorAll('.bvs-quote-form').forEach(form => {
    const button = form.querySelector('.bvs-submit');
    const message = form.querySelector('.bvs-form-message');
    const label = button.textContent;
    const design = form.elements.design;
    const remove = form.querySelector('.bvs-remove-design');
    const editorOption = form.querySelector('.bvs-editor-design-option');
    const useEditor = form.querySelector('.bvs-use-editor-design');
    const designHint = form.querySelector('.bvs-design-attachment > .bvs-form-note');
    let editorFile = false;
    const updateDesign = () => {
      const available = !!form.bvsEditorDesign?.hasDraft();
      editorOption.hidden = !available;
      const automatic = available && useEditor.checked;
      design.hidden = automatic;designHint.hidden = automatic;
      remove.hidden = !automatic && !design.files.length;
    };
    form.addEventListener('bvs:design-changed', updateDesign);
    useEditor?.addEventListener('change', () => {
      if (useEditor.checked || editorFile) {design.value='';editorFile=false;design.setCustomValidity('');}
      updateDesign();
    });
    design?.addEventListener('change', event => {
      editorFile = !!event.detail?.editor;
      if (!editorFile) useEditor.checked = false;
      const file = design.files[0];
      updateDesign();
      design.setCustomValidity('');
      if (!file) return;
      if (!['image/png','image/jpeg','image/webp'].includes(file.type) || file.size>5*1024*1024) {
        design.setCustomValidity(design.dataset.error); design.reportValidity();
      }
    });
    remove?.addEventListener('click', () => {design.value='';editorFile=false;useEditor.checked=false;design.setCustomValidity('');updateDesign();design.focus();});
    form.addEventListener('reset', () => {editorFile=false;if(design)design.setCustomValidity('');setTimeout(updateDesign,0);});
    const query = new URLSearchParams(location.search);
    ['model','mode'].forEach(key => { const field=form.elements[key]; if ([...field.options].some(option => option.value === query.get(key))) field.value=query.get(key); });
    form.addEventListener('submit', async event => {
      event.preventDefault();
      if (button.disabled) return;
      button.disabled = true; button.textContent = button.dataset.sending;
      message.hidden = true;
      try {
        const data = new FormData(form);
        if (useEditor.checked && form.bvsEditorDesign?.hasDraft()) {
          data.set('design', await form.bvsEditorDesign.file());
          data.set('model', 'fridge');
        }
        const response = await fetch(form.dataset.endpoint, {method:'POST',body:data,credentials:'same-origin'});
        const result = await response.json();
        if (!result.data?.message) throw new Error('Unexpected response');
        message.textContent = result.data.message;
        message.dataset.status = result.success ? 'success' : 'error';
        if (result.success) form.reset();
      } catch (_) {
        message.textContent = message.dataset.fallback;
        message.dataset.status = 'error';
      } finally {
        message.hidden = false; button.disabled = false; button.textContent = label;
      }
    });
  });
})();
