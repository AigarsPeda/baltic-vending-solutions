(() => {
  const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
  if (reducedMotion.matches || !('IntersectionObserver' in window)) return;

  // Values and suffixes come from editable native paragraphs, never from theme data.
  const counters = new Map();
  document.querySelectorAll('main .bvs-counter').forEach(element => {
    const text = element.textContent;
    const parts = text.match(/^(\s*[+−-]?)(\d+)([^\d]*)$/);
    if (!parts || !Number.isSafeInteger(Number(parts[2])) || Number(parts[2]) <= 0) return;
    counters.set(element, {text, parts, value: Number(parts[2]), nodes: [...element.childNodes], started: false});
  });
  if (!counters.size) return;

  const finish = (element, counter) => {
    window.cancelAnimationFrame(counter.frame);
    element.replaceChildren(...counter.nodes);
    element.dataset.bvsCounterState = 'done';
  };
  const observer = new IntersectionObserver(entries => {
    entries.forEach(entry => {
      if (!entry.isIntersecting) return;
      const element = entry.target;
      const counter = counters.get(element);
      if (counter.started) return;
      counter.started = true;
      observer.unobserve(element);

      const visual = document.createElement('span');
      visual.setAttribute('aria-hidden', 'true');
      const accessible = document.createElement('span');
      accessible.className = 'bvs-counter-accessible';
      accessible.textContent = counter.text;
      element.replaceChildren(visual, accessible);
      element.dataset.bvsCounterState = 'running';
      const start = performance.now();
      const tick = timestamp => {
        const progress = Math.min((timestamp - start) / 1000, 1);
        if (progress >= 1 || reducedMotion.matches || document.hidden || !element.isConnected) {
          finish(element, counter);
          return;
        }
        const value = Math.floor(counter.value * (1 - Math.pow(1 - progress, 3)));
        visual.textContent = counter.parts[1] + value + counter.parts[3];
        counter.frame = window.requestAnimationFrame(tick);
      };
      tick(start);
    });
  }, {threshold: .5, rootMargin: '0px 0px -40px 0px'});
  counters.forEach((counter, element) => observer.observe(element));

  reducedMotion.addEventListener('change', () => {
    if (!reducedMotion.matches) return;
    observer.disconnect();
    counters.forEach((counter, element) => { if (counter.started) finish(element, counter); });
  });
  document.addEventListener('visibilitychange', () => {
    if (document.hidden) counters.forEach((counter, element) => { if (counter.started) finish(element, counter); });
  });
})();
