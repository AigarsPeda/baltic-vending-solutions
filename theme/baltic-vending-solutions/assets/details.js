(() => {
  if (!Element.prototype.animate) return;
  const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');

  document.querySelectorAll('main .wp-block-details').forEach(item => {
    const summary = item.querySelector(':scope > summary');
    if (!summary) return;

    // Runtime decoration keeps the saved Details block fully editable in WordPress.
    const icon = document.createElement('span');
    icon.className = 'bvs-details-icon';
    icon.setAttribute('aria-hidden', 'true');
    const lines = document.createElement('span');
    lines.className = 'bvs-details-icon-lines';
    const horizontal = document.createElement('span');
    horizontal.className = 'bvs-details-icon-horizontal';
    const vertical = document.createElement('span');
    vertical.className = 'bvs-details-icon-vertical';
    lines.append(horizontal, vertical);
    icon.append(lines);
    summary.append(icon);
    summary.classList.add('bvs-details-enhanced');

    const iconOptions = {duration: 340, fill: 'both', easing: 'linear'};
    const iconAnimations = [
      lines.animate([
        {transform: 'rotate(0deg)', offset: 0, easing: 'ease-in-out'},
        {transform: 'rotate(45deg)', offset: .5, easing: 'ease-in-out'},
        {transform: 'rotate(90deg)', offset: 1},
      ], iconOptions),
      // The original vertical stroke becomes the dash; the horizontal stroke fades.
      horizontal.animate([
        {opacity: 1, offset: 0},
        {opacity: .5, offset: .5},
        {opacity: 0, offset: 1},
      ], iconOptions),
    ];
    const settleIcon = () => iconAnimations.forEach(animation => {
      animation.pause();
      animation.currentTime = item.open ? 340 : 0;
    });
    settleIcon();

    // Wrap only the rendered answer. Saved content remains native WordPress blocks.
    const answer = document.createElement('div');
    answer.className = 'bvs-details-answer';
    while (summary.nextSibling) answer.append(summary.nextSibling);
    item.append(answer);

    let targetOpen = item.open;
    let heightAnimation;
    let answerAnimation;
    item.addEventListener('toggle', () => {
      if (!heightAnimation) {
        targetOpen = item.open;
        answer.inert = !item.open;
        settleIcon();
      }
    });
    const finish = () => {
      item.open = targetOpen;
      heightAnimation?.cancel();
      answerAnimation?.cancel();
      heightAnimation = answerAnimation = undefined;
      item.style.overflow = '';
      item.style.willChange = '';
      item.classList.remove('is-closing');
      answer.inert = !targetOpen;
      settleIcon();
    };

    summary.addEventListener('click', event => {
      if (reducedMotion.matches) return;
      event.preventDefault();
      // Read the visible frame before cancelling, so a second click reverses smoothly.
      const startHeight = item.getBoundingClientRect().height;
      const startOpacity = item.open ? getComputedStyle(answer).opacity : '0';
      const startTransform = item.open ? getComputedStyle(answer).transform : 'translateY(-8px)';
      targetOpen = !(heightAnimation ? targetOpen : item.open);
      heightAnimation?.cancel();
      answerAnimation?.cancel();
      item.open = true;
      item.classList.toggle('is-closing', !targetOpen);
      answer.inert = !targetOpen;
      iconAnimations.forEach(animation => {
        // Playing the same timeline backwards also preserves an interrupted frame.
        animation.playbackRate = targetOpen ? 1 : -340 / 220;
        animation.play();
      });

      const style = getComputedStyle(item);
      const closedHeight = summary.getBoundingClientRect().height
        + parseFloat(style.paddingTop) + parseFloat(style.paddingBottom)
        + parseFloat(style.borderTopWidth) + parseFloat(style.borderBottomWidth);
      const endHeight = targetOpen ? item.getBoundingClientRect().height : closedHeight;
      item.style.overflow = 'hidden';
      item.style.willChange = 'height';
      const options = {
        duration: targetOpen ? 340 : 220,
        easing: targetOpen ? 'cubic-bezier(.16,1,.3,1)' : 'cubic-bezier(.4,0,1,1)',
        fill: 'both',
      };
      answerAnimation = answer.animate([
        {opacity: startOpacity, transform: startTransform},
        {opacity: targetOpen ? 1 : 0, transform: targetOpen ? 'translateY(0)' : 'translateY(-8px)'},
      ], options);
      const animation = item.animate({height: [`${startHeight}px`, `${endHeight}px`]}, options);
      heightAnimation = animation;
      animation.onfinish = () => { if (heightAnimation === animation) finish(); };
    });

    reducedMotion.addEventListener('change', () => { if (heightAnimation) finish(); });
  });
})();
