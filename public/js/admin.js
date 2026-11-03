(() => {
  'use strict';

  const norm = (text) => text.toLocaleLowerCase('id-ID').trim();

  document.querySelectorAll('[data-combo]').forEach((combo) => {
    const input = combo.querySelector('[data-combo-input]');
    const hidden = combo.querySelector('[data-combo-value]');
    const menu = combo.querySelector('[data-combo-menu]');
    const empty = combo.querySelector('[data-combo-empty]');
    const clear = combo.querySelector('[data-combo-clear]');
    if (!input || !hidden || !menu) return;

    const options = Array.from(menu.querySelectorAll('.combo-option'));
    let committedValue = hidden.value;
    let committedLabel = committedValue ? input.value : '';
    let activeIndex = -1;

    const visibleOptions = () => options.filter((option) => !option.hidden);

    const markSelected = () => {
      options.forEach((option) => {
        const selected = option.dataset.value !== '' && option.dataset.value === committedValue;
        option.classList.toggle('is-selected', selected);
        option.setAttribute('aria-selected', selected ? 'true' : 'false');
      });
    };

    const setActive = (index) => {
      const list = visibleOptions();
      list.forEach((option) => option.classList.remove('is-active'));
      if (!list.length) {
        activeIndex = -1;
        input.removeAttribute('aria-activedescendant');
        return;
      }
      activeIndex = (index + list.length) % list.length;
      const option = list[activeIndex];
      option.classList.add('is-active');
      option.setAttribute('id', option.id || `combo-opt-${Math.random().toString(36).slice(2)}`);
      input.setAttribute('aria-activedescendant', option.id);
      option.scrollIntoView({ block: 'nearest' });
    };

    const open = () => {
      menu.hidden = false;
      input.setAttribute('aria-expanded', 'true');
      options.forEach((option) => { option.hidden = false; });
      if (empty) empty.hidden = true;
      markSelected();
      setActive(Math.max(0, visibleOptions().indexOf(options.find((option) => option.dataset.value === committedValue))));
    };

    const close = () => {
      menu.hidden = true;
      input.setAttribute('aria-expanded', 'false');
      input.removeAttribute('aria-activedescendant');
      activeIndex = -1;
      options.forEach((option) => option.classList.remove('is-active'));
    };

    const commit = (value, label) => {
      committedValue = value;
      committedLabel = value === '' ? '' : label;
      hidden.value = value;
      input.value = committedLabel;
      if (clear) clear.hidden = committedLabel === '';
      markSelected();
      close();
    };

    const restore = () => {
      input.value = committedLabel;
      hidden.value = committedValue;
    };

    const filter = () => {
      const query = norm(input.value);
      let matches = 0;
      options.forEach((option) => {
        if (option.dataset.value === '') {
          option.hidden = query !== '';
          return;
        }
        const match = query === '' || norm(option.dataset.label || '').includes(query);
        option.hidden = !match;
        if (match) matches++;
      });
      if (empty) empty.hidden = matches > 0;
      activeIndex = -1;
      options.forEach((option) => option.classList.remove('is-active'));
    };

    input.addEventListener('focus', open);
    input.addEventListener('click', open);

    input.addEventListener('input', () => {
      hidden.value = '';
      if (clear) clear.hidden = true;
      open();
      filter();
    });

    input.addEventListener('keydown', (event) => {
      if (event.key === 'ArrowDown') {
        event.preventDefault();
        if (menu.hidden) open();
        setActive(activeIndex + 1);
      } else if (event.key === 'ArrowUp') {
        event.preventDefault();
        if (menu.hidden) open();
        setActive(activeIndex - 1);
      } else if (event.key === 'Enter') {
        if (!menu.hidden) {
          event.preventDefault();
          const option = visibleOptions()[activeIndex] || (input.value.trim() !== '' ? visibleOptions()[0] : null);
          if (option) commit(option.dataset.value, option.dataset.label);
        }
      } else if (event.key === 'Escape') {
        event.preventDefault();
        restore();
        close();
      } else if (event.key === 'Tab') {
        restore();
        close();
      }
    });

    options.forEach((option) => {
      option.addEventListener('mousedown', (event) => event.preventDefault());
      option.addEventListener('click', () => commit(option.dataset.value, option.dataset.label));
    });

    if (clear) {
      clear.addEventListener('click', () => {
        commit('', '');
        input.focus();
        open();
      });
    }

    document.addEventListener('click', (event) => {
      if (!combo.contains(event.target)) {
        restore();
        close();
      }
    });
  });
})();

(() => {
  'use strict';

  const form = document.getElementById('restoreUploadForm');
  if (!form) return;

  const file = form.querySelector('[data-restore-file-input]');
  const confirmation = form.querySelector('input[name="confirmation"]');
  const submit = form.querySelector('[data-restore-submit]');

  const sync = () => {
    submit.disabled = !file.files?.length || confirmation.value.trim().toUpperCase() !== 'PULIHKAN';
  };

  file.addEventListener('change', sync);
  confirmation.addEventListener('input', sync);
  form.addEventListener('submit', () => { submit.disabled = true; });

  sync();
})();
