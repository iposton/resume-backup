'use strict';
document.documentElement.classList.add('js');
const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
const tabs = [...document.querySelectorAll('.mdl-layout__tab')];
const panels = [...document.querySelectorAll('.mdl-layout__tab-panel')];
const tabBar = document.querySelector('.mdl-layout__tab-bar');
tabBar.setAttribute('role', 'tablist');

function activateTab(id) {
  tabs.forEach((tab) => {
    const active = tab.hash === `#${id}`;
    tab.classList.toggle('is-active', active);
    tab.setAttribute('aria-selected', String(active));
    tab.tabIndex = active ? 0 : -1;
  });
  panels.forEach((panel) => {
    const active = panel.id === id;
    panel.classList.toggle('is-active', active);
    panel.hidden = !active;
  });
}
tabs.forEach((tab, index) => {
  tab.id = `profile-tab-${index}`;
  tab.setAttribute('role', 'tab');
  tab.setAttribute('aria-controls', tab.hash.slice(1));
  const panel = document.querySelector(tab.hash);
  panel.setAttribute('role', 'tabpanel');
  panel.setAttribute('aria-labelledby', tab.id);
  tab.addEventListener('keydown', (event) => {
    let next;
    if (event.key === 'ArrowRight') next = (index + 1) % tabs.length;
    if (event.key === 'ArrowLeft') next = (index + tabs.length - 1) % tabs.length;
    if (event.key === 'Home') next = 0;
    if (event.key === 'End') next = tabs.length - 1;
    if (next !== undefined) { event.preventDefault(); tabs[next].focus(); tabs[next].click(); }
  });
});
let scrollFrame;
function scrollToTarget(target, animate) {
  cancelAnimationFrame(scrollFrame);
  const start = window.scrollY;
  const end = Math.max(0, Math.min(
    target.getBoundingClientRect().top + start - 24,
    document.documentElement.scrollHeight - window.innerHeight
  ));
  if (!animate || reducedMotion.matches) { window.scrollTo(0, end); return; }
  const started = performance.now();
  function step(now) {
    const progress = Math.min((now - started) / 650, 1);
    const eased = progress < .5 ? 4 * progress ** 3 : 1 - (-2 * progress + 2) ** 3 / 2;
    window.scrollTo(0, start + (end - start) * eased);
    if (progress < 1) scrollFrame = requestAnimationFrame(step);
  }
  scrollFrame = requestAnimationFrame(step);
}
function syncHash(animate = false) {
  const id = location.hash.slice(1);
  const target = document.getElementById(id);
  const panel = target?.closest('.mdl-layout__tab-panel');
  const panelId = panel?.id || (new URLSearchParams(location.search).has('success') ? 'scroll-tab-contact' : 'scroll-tab-about');
  activateTab(panelId);
  const activeHash = panelId === 'scroll-tab-resume' && (!target || target === panel) ? '#about' : target ? `#${id}` : `#${panelId}`;
  document.querySelectorAll('a.smooth').forEach((link) => {
    const active = link.hash === activeHash;
    link.classList.toggle('active', active);
    if (active) link.setAttribute('aria-current', 'location');
    else link.removeAttribute('aria-current');
  });
  // Wait for the newly revealed panel to lay out before measuring its target.
  requestAnimationFrame(() => scrollToTarget(target || document.getElementById(panelId), animate));
}
document.querySelectorAll('a.smooth, .mdl-layout__tab').forEach((link) => {
  link.addEventListener('click', (event) => {
    if (event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
    event.preventDefault();
    // Prevent the browser's immediate anchor jump, including repeat clicks.
    if (location.hash !== link.hash) history.pushState(null, '', link.hash);
    syncHash(link.matches('a.smooth'));
  });
});
window.addEventListener('popstate', () => syncHash());
window.addEventListener('hashchange', () => syncHash());
for (const eventName of ['wheel', 'touchstart', 'keydown']) {
  window.addEventListener(eventName, () => cancelAnimationFrame(scrollFrame), { passive: true });
}
syncHash();
document.querySelectorAll('[id^="accordion"]').forEach((accordion, index) => {
  const heading = accordion.querySelector('h3');
  const panel = heading.nextElementSibling;
  const button = document.createElement('button');
  button.type = 'button';
  button.className = 'accordion-trigger';
  button.id = `accordion-trigger-${index}`;
  button.textContent = heading.textContent;
  button.setAttribute('aria-expanded', 'false');
  panel.id = `accordion-panel-${index}`;
  panel.classList.add('accordion-panel');
  panel.setAttribute('role', 'region');
  panel.setAttribute('aria-labelledby', button.id);
  panel.hidden = true;
  button.setAttribute('aria-controls', panel.id);
  heading.classList.add('accordion-heading');
  heading.replaceChildren(button);
  let animation;
  button.addEventListener('click', () => {
    animation?.cancel();
    const open = button.getAttribute('aria-expanded') !== 'true';
    button.setAttribute('aria-expanded', String(open));
    panel.hidden = false;
    if (reducedMotion.matches) { panel.hidden = !open; return; }
    const height = panel.scrollHeight;
    animation = panel.animate([{ height: `${open ? 0 : height}px`, opacity: open ? 0 : 1 }, { height: `${open ? height : 0}px`, opacity: open ? 1 : 0 }], { duration: 240, easing: 'ease-out' });
    animation.onfinish = () => { panel.hidden = !open; };
  });
});
const form = document.querySelector('form');
form.addEventListener('submit', () => {
  const button = form.querySelector('button[type="submit"]');
  button.disabled = true;
  button.querySelector('span').textContent = button.dataset.sendingLabel;
});
window.addEventListener('pageshow', () => {
  const button = form.querySelector('button[type="submit"]');
  button.disabled = false;
  button.querySelector('span').textContent = button.dataset.submitLabel;
});
const notice = document.querySelector('.form-messages');
if (notice) {
  notice.setAttribute('role', 'status');
  setTimeout(() => { notice.style.opacity = '0'; setTimeout(() => { notice.hidden = true; }, 500); }, 8000);
}
