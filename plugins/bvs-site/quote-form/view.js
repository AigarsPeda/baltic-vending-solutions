(() => {
  document.querySelectorAll('.bvs-quote-form').forEach(form => {
    const button = form.querySelector('.bvs-submit');
    const message = form.querySelector('.bvs-form-message');
    const label = button.textContent;
    const query = new URLSearchParams(location.search);
    ['model','mode'].forEach(key => { const field=form.elements[key]; if ([...field.options].some(option => option.value === query.get(key))) field.value=query.get(key); });
    form.addEventListener('submit', async event => {
      event.preventDefault();
      if (button.disabled) return;
      button.disabled = true; button.textContent = button.dataset.sending;
      message.hidden = true;
      try {
        const response = await fetch(form.dataset.endpoint, {method:'POST',body:new FormData(form),credentials:'same-origin'});
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
