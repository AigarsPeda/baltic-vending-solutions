(() => {
  const banner = document.getElementById('bvs-cookie-banner');
  if (!banner) return;
  const openers = [...document.querySelectorAll('.bvs-cookie-settings'), ...document.querySelectorAll('.bvs-cookie-open a')];
  const cookieName = 'bvs_consent';
  const version = Number(banner.dataset.version);
  const lifetime = Number(banner.dataset.lifetime);
  let opener;
  const readChoice = () => {
    try {
      const cookie = document.cookie.split('; ').find(value => value.startsWith(cookieName + '='));
      const choice = JSON.parse(decodeURIComponent(cookie?.slice(cookieName.length + 1) || 'null'));
      return choice && choice.v === version && typeof choice.analytics === 'boolean'
        && Number.isFinite(choice.at) && choice.at <= Date.now()
        && Date.now() - choice.at < lifetime * 1000 ? choice : null;
    } catch { return null; }
  };
  let choice = readChoice();
  const clearAnalyticsCookies = () => {
    const hostname = location.hostname.split('.');
    document.cookie.split('; ').forEach(cookie => {
      const name = cookie.split('=')[0];
      if (!/^_ga(?:_|$)/.test(name)) return;
      document.cookie = `${name}=; Max-Age=0; Path=/`;
      for (let i=0; i<hostname.length-1; i++) document.cookie = `${name}=; Max-Age=0; Path=/; Domain=${hostname.slice(i).join('.')}`;
    });
  };
  const applyChoice = () => {
    window.wp_consent_type = 'optin';
    if (choice && typeof window.wp_set_consent === 'function') {
      for (const [category, value] of Object.entries({functional:'allow', preferences:'allow', statistics:choice?.analytics ? 'allow':'deny', 'statistics-anonymous':'deny', marketing:'deny'})) window.wp_set_consent(category, value);
    } else if (!choice) {
      // The API setter persists cookies. Before confirmation use opt-in's default denial,
      // removing stale API preferences instead of writing a new default choice.
      const prefix = window.consent_api?.cookie_prefix || 'wp_consent';
      document.cookie.split('; ').forEach(cookie => {
        const name = cookie.split('=')[0];
        if (name.startsWith(prefix + '_') || name === 'pll_language') document.cookie = `${name}=; Max-Age=0; Path=/`;
      });
      document.dispatchEvent(new CustomEvent('wp_listen_for_consent_change', {detail:{statistics:'deny','statistics-anonymous':'deny',marketing:'deny'}}));
    }
    document.dispatchEvent(new CustomEvent('wp_consent_type_defined'));
    if (!choice?.analytics) clearAnalyticsCookies();
  };
  const updateSpace = () => {
    document.documentElement.style.setProperty('--bvs-cookie-space', banner.hidden ? '0px' : `${banner.getBoundingClientRect().height}px`);
  };
  const show = () => {
    banner.querySelector('[data-cookie-close]').hidden = !choice;
    banner.hidden = false;
    document.documentElement.classList.add('bvs-cookie-visible');
    openers.forEach(button => button.setAttribute('aria-expanded','true'));
    updateSpace();
  };
  const hide = () => {
    if (banner.contains(document.activeElement)) (opener || openers[0])?.focus({preventScroll:true});
    banner.hidden = true;
    document.documentElement.classList.remove('bvs-cookie-visible');
    openers.forEach(button => button.setAttribute('aria-expanded','false'));
    updateSpace();
  };
  openers.forEach(button => {
    button.hidden = false;
    button.setAttribute('aria-controls', banner.id);
    button.setAttribute('aria-expanded', 'false');
    button.addEventListener('click', event => {
      event.preventDefault();
      opener = button;
      show();
      banner.querySelector('[data-cookie-choice]')?.focus({preventScroll:true});
    });
  });
  banner.querySelectorAll('[data-cookie-choice]').forEach(button => button.addEventListener('click', () => {
    const next = {v:version, analytics:button.dataset.cookieChoice === 'true', at:Date.now()};
    document.cookie = `${cookieName}=${encodeURIComponent(JSON.stringify(next))}; Max-Age=${lifetime}; Path=/; SameSite=Lax${location.protocol==='https:'?'; Secure':''}`;
    const saved = readChoice();
    if (!saved || saved.at !== next.at) {
      banner.querySelector('.bvs-cookie-error').hidden = false;
      return;
    }
    choice = saved;
    applyChoice();
    hide();
    try {
      localStorage.setItem('bvs_consent_changed',String(choice.at));
      localStorage.removeItem('bvs_consent_changed');
    } catch { /* The preference cookie works without localStorage. */ }
    // Site Kit tags are server-gated. Reload after a production choice to start or unload them.
    if (banner.dataset.reload === 'true') location.reload();
  }));
  banner.querySelector('[data-cookie-close]').addEventListener('click',() => { if (choice) hide(); });
  banner.addEventListener('keydown',event => { if (event.key === 'Escape' && choice) hide(); });
  const syncChoice = () => {
    const next = readChoice();
    const changed = choice?.analytics !== next?.analytics;
    choice = next;
    applyChoice();
    if (changed && banner.dataset.reload === 'true') { location.reload(); return; }
    if (choice) hide(); else show();
  };
  window.addEventListener('storage',event => { if (event.key === 'bvs_consent_changed') syncChoice(); });
  document.addEventListener('visibilitychange',() => { if (!document.hidden) syncChoice(); });
  if ('ResizeObserver' in window) new ResizeObserver(updateSpace).observe(banner);
  else window.addEventListener('resize',updateSpace);
  applyChoice();
  if (!choice) show();
})();
