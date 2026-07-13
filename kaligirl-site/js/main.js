// Kaligirl Financial Services — minimal interactions
// Kept intentionally light; extend in Claude Code as needed.

document.addEventListener('DOMContentLoaded', () => {
  // Mark the current page in the nav
  const path = location.pathname.split('/').pop() || 'index.html';
  document.querySelectorAll('.nav-links a').forEach((a) => {
    if (a.getAttribute('href') === path) a.setAttribute('aria-current', 'page');
  });

  // Guard unfinished placeholder links so nothing silently 404s pre-launch
  document.querySelectorAll('[data-placeholder]').forEach((el) => {
    el.addEventListener('click', (e) => {
      e.preventDefault();
      const kind = el.getAttribute('data-placeholder');
      const msg = kind === 'moxo'
        ? 'Client portal link goes here — connect your Moxo booking/portal URL.'
        : 'This link is a placeholder pending compliance-approved content.';
      alert(msg);
    });
  });

  // Login form isn't wired to an auth backend yet — surface that instead
  // of silently doing nothing on submit.
  const loginForm = document.getElementById('login-form');
  if (loginForm) {
    loginForm.addEventListener('submit', (e) => {
      e.preventDefault();
      alert('Client sign-in isn’t connected yet — this will route to the Moxo client portal login.');
    });
  }
});
