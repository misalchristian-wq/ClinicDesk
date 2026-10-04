// Shared pagination for ClinicDesk tables, including rows rendered later by Vue.
(() => {
  const sizes = [10, 15, 20];
  const states = new WeakMap();
  const style = document.createElement('style');
  style.textContent = `
    .clinic-page-hidden { display: none !important; }
    .clinic-table-pager { display: flex; align-items: center; justify-content: space-between;
      flex-wrap: wrap; gap: 12px; padding: 14px 2px 4px; color: #6b7d87;
      font: 600 13px Arial, sans-serif; }
    .clinic-table-pager[hidden] { display: none !important; }
    .clinic-table-pager__size, .clinic-table-pager__nav { display: flex; align-items: center; gap: 9px; }
    .clinic-table-pager select { border: 1px solid #d9eef0; border-radius: 10px;
      background: #fff; color: #16323f; padding: 7px 28px 7px 10px; font-weight: 700; }
    .clinic-table-pager button { border: 1px solid #d9eef0; border-radius: 10px;
      background: #fff; color: #0f766e; padding: 7px 12px; font-weight: 800; }
    .clinic-table-pager button:hover:not(:disabled) { background: #f0fdfa; border-color: #14b8a6; }
    .clinic-table-pager button:disabled { color: #9aaeb4; opacity: .65; cursor: not-allowed; }
    .clinic-table-pager__range { min-width: 118px; text-align: center; }
    @media print { .clinic-page-hidden { display: table-row !important; }
      .clinic-table-pager { display: none !important; } }
  `;
  document.head.appendChild(style);

  function getRows(table) {
    const body = table.tBodies[0];
    return body ? Array.from(body.children).filter(node => node.tagName === 'TR') : [];
  }

  function render(table, state) {
    const rows = getRows(table);
    const count = rows.length;
    const signature = count + ':' + (rows[0]?.textContent || '') + ':' + (rows[count - 1]?.textContent || '');
    if (state.signature !== signature) {
      state.page = 1;
      state.signature = signature;
    }
    const pages = Math.max(1, Math.ceil(count / state.size));
    state.page = Math.min(state.page, pages);
    const first = (state.page - 1) * state.size;
    rows.forEach((row, index) => row.classList.toggle('clinic-page-hidden', index < first || index >= first + state.size));
    state.pager.hidden = count <= 10;
    state.range.textContent = count ? `${first + 1}–${Math.min(first + state.size, count)} of ${count} items` : '0 items';
    state.previous.disabled = state.page === 1;
    state.next.disabled = state.page === pages;
  }

  function attach(table) {
    if (states.has(table) || table.dataset.pagination === 'off' || !table.tBodies[0]) return;
    const pager = document.createElement('div');
    pager.className = 'clinic-table-pager';
    pager.setAttribute('role', 'navigation');
    pager.setAttribute('aria-label', 'Table pagination');
    const sizeGroup = document.createElement('label');
    sizeGroup.className = 'clinic-table-pager__size';
    sizeGroup.append(document.createTextNode('Items per page:'));
    const select = document.createElement('select');
    select.setAttribute('aria-label', 'Items per page');
    sizes.forEach(size => select.add(new Option(String(size), String(size))));
    sizeGroup.append(select);
    const nav = document.createElement('div');
    nav.className = 'clinic-table-pager__nav';
    const previous = document.createElement('button');
    previous.type = 'button';
    previous.textContent = 'Previous';
    const range = document.createElement('span');
    range.className = 'clinic-table-pager__range';
    range.setAttribute('aria-live', 'polite');
    const next = document.createElement('button');
    next.type = 'button';
    next.textContent = 'Next';
    nav.append(previous, range, next);
    pager.append(sizeGroup, nav);
    const anchor = table.parentElement?.classList.contains('table-responsive') ? table.parentElement : table;
    anchor.insertAdjacentElement('afterend', pager);

    const state = { page: 1, size: 10, signature: '', pager, range, previous, next };
    states.set(table, state);
    select.addEventListener('change', () => {
      state.size = Number(select.value);
      state.page = 1;
      render(table, state);
    });
    previous.addEventListener('click', () => { state.page -= 1; render(table, state); });
    next.addEventListener('click', () => { state.page += 1; render(table, state); });
    new MutationObserver(() => render(table, state)).observe(table.tBodies[0],
      { childList: true, characterData: true, subtree: true });
    render(table, state);
  }

  function scan(root = document) {
    if (root.matches?.('table')) attach(root);
    root.querySelectorAll?.('table').forEach(attach);
  }

  function ready() {
    scan();
    new MutationObserver(mutations => {
      for (const mutation of mutations) {
        for (const node of mutation.addedNodes) {
          if (node.nodeType === Node.ELEMENT_NODE && (node.matches('table') || node.querySelector('table'))) {
            scan(node);
          }
        }
      }
    }).observe(document.body, { childList: true, subtree: true });
  }

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', ready);
  else ready();
})();
