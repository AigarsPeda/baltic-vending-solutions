(() => {
  const button = document.querySelector('.menu-toggle');
  const nav = document.getElementById('site-navigation');
  const drawer = document.getElementById('mobile-menu');
  if (!button || !nav || !drawer) return;
  const header = nav.parentElement;
  const mobile = window.matchMedia('(max-width: 1180px)');
  const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
  let closeTimer;
  let openFrame;
  button.hidden = false;
  document.documentElement.classList.add('bvs-js');

  const finishClose = () => {
    clearTimeout(closeTimer);
    drawer.close();
    document.documentElement.classList.remove('bvs-menu-open');
  };
  const close = (immediate = false) => {
    cancelAnimationFrame(openFrame);
    button.setAttribute('aria-expanded', 'false');
    drawer.classList.remove('is-open');
    if (immediate || reducedMotion.matches) finishClose();
    else closeTimer = setTimeout(finishClose, 220);
  };
  const open = () => {
    clearTimeout(closeTimer);
    drawer.showModal();
    document.documentElement.classList.add('bvs-menu-open');
    button.setAttribute('aria-expanded', 'true');
    // Commit the off-screen position before beginning the entrance transition.
    drawer.getBoundingClientRect();
    openFrame = requestAnimationFrame(() => drawer.classList.add('is-open'));
  };
  const updateLayout = () => {
    close(true);
    if (mobile.matches) drawer.append(nav);
    else header.append(nav);
  };
  updateLayout();
  mobile.addEventListener('change', updateLayout);
  button.addEventListener('click', open);
  drawer.querySelector('.drawer-close').addEventListener('click', () => close());
  nav.addEventListener('click', event => { if (event.target.closest('a')) close(); });
  drawer.addEventListener('cancel', event => {
    event.preventDefault();
    close();
  });
  drawer.addEventListener('keydown', event => {
    if (event.key !== 'Tab') return;
    const controls = Array.from(drawer.querySelectorAll('a[href], button:not([disabled]), [tabindex="0"]'))
      .filter(node => node.getClientRects().length);
    const first = controls[0];
    const last = controls[controls.length - 1];
    if (event.shiftKey && document.activeElement === first) {
      event.preventDefault();
      last.focus();
    } else if (!event.shiftKey && document.activeElement === last) {
      event.preventDefault();
      first.focus();
    }
  });
  drawer.addEventListener('click', event => {
    if (event.target !== drawer) return;
    const bounds = drawer.getBoundingClientRect();
    if (event.clientX < bounds.left || event.clientX > bounds.right ||
        event.clientY < bounds.top || event.clientY > bounds.bottom) close();
  });
})();
