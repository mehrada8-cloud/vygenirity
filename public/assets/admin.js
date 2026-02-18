const categoryForm = document.querySelector('[data-category-form]');
if (categoryForm) {
  const idInput = categoryForm.querySelector('input[name="id"]');
  const nameInput = categoryForm.querySelector('input[name="name"]');
  const slugInput = categoryForm.querySelector('input[name="slug"]');
  const targetInput = categoryForm.querySelector('input[name="target_percent"]');
  const guaranteeInput = categoryForm.querySelector('input[name="guarantee_percent"]');
  const title = categoryForm.closest('.admin-card')?.querySelector('[data-category-form-title]');
  const resetButton = categoryForm.querySelector('[data-category-reset]');

  const resetEditMode = () => {
    idInput.value = '';
    nameInput.value = '';
    slugInput.value = '';
    if (targetInput) {
      targetInput.value = '';
    }
    if (guaranteeInput) {
      guaranteeInput.value = '';
    }
    if (title) {
      title.textContent = 'ایجاد / ویرایش دسته‌بندی';
    }
    resetButton?.setAttribute('hidden', 'hidden');
  };

  resetButton?.addEventListener('click', resetEditMode);

  document.querySelectorAll('[data-edit-category]').forEach((button) => {
    button.addEventListener('click', () => {
      idInput.value = button.dataset.id;
      nameInput.value = button.dataset.name;
      slugInput.value = button.dataset.slug;
      if (targetInput) {
        targetInput.value = button.dataset.targetPercent || '';
      }
      if (guaranteeInput) {
        guaranteeInput.value = button.dataset.guaranteePercent || '';
      }
      if (title) {
        title.textContent = `ویرایش دسته‌بندی: ${button.dataset.name}`;
      }
      resetButton?.removeAttribute('hidden');
      nameInput.focus();
      nameInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
    });
  });
}

const symbolsNote = document.querySelector('[data-available-symbols]');
if (symbolsNote) {
  const symbols = JSON.parse(symbolsNote.dataset.availableSymbols || '[]');
  const warning = symbolsNote.querySelector('[data-symbol-warning]');
  const pairInput = document.querySelector('input[name="pair"]');
  const monitoringCheckbox = document.querySelector('input[name="monitoring_enabled"]');

  const updateWarning = () => {
    if (!monitoringCheckbox?.checked || !pairInput) {
      warning.textContent = '';
      return;
    }
    const pair = pairInput.value.trim().toUpperCase();
    if (pair && !symbols.includes(pair)) {
      warning.textContent = 'این نماد در فایل قیمت وجود ندارد؛ سیگنال ثبت می‌شود اما بروزرسانی خودکار ندارد.';
    } else {
      warning.textContent = '';
    }
  };

  pairInput?.addEventListener('input', updateWarning);
  monitoringCheckbox?.addEventListener('change', updateWarning);
  updateWarning();
}

document.querySelectorAll('[data-close-form]').forEach((form) => {
  const reasonSelect = form.querySelector('[data-close-reason]');
  const exitInput = form.querySelector('[data-exit-price]');
  if (!reasonSelect || !exitInput) {
    return;
  }

  const updateExitState = () => {
    const isManual = reasonSelect.value === 'manual';
    exitInput.required = isManual;
    exitInput.disabled = !isManual;
    exitInput.classList.toggle('is-hidden', !isManual);
    if (!isManual) {
      exitInput.value = '';
    }
  };

  reasonSelect.addEventListener('change', updateExitState);
  updateExitState();
});

const tableConfigs = [
  {
    key: 'categories',
    searchInput: document.querySelector('[data-table-search="categories"]'),
    filterInput: document.querySelector('[data-table-filter="categories"]'),
    rows: Array.from(document.querySelectorAll('[data-row="categories"]')),
    emptyState: document.querySelector('[data-table-empty="categories"]'),
  },
  {
    key: 'signals',
    searchInput: document.querySelector('[data-table-search="signals"]'),
    filterInput: document.querySelector('[data-table-filter="signals"]'),
    rows: Array.from(document.querySelectorAll('[data-row="signals"]')),
    emptyState: document.querySelector('[data-table-empty="signals"]'),
  },
];

const applyTableFilter = (config) => {
  if (!config.rows.length) {
    return;
  }

  const query = config.searchInput?.value.trim().toLowerCase() || '';
  const status = config.filterInput?.value || 'all';
  let visibleCount = 0;

  config.rows.forEach((row) => {
    const searchable = row.dataset.searchable || '';
    const rowStatus = row.dataset.status || '';
    const matchesSearch = !query || searchable.includes(query);
    const matchesStatus = status === 'all' || rowStatus === status;
    const isVisible = matchesSearch && matchesStatus;
    row.hidden = !isVisible;
    if (isVisible) {
      visibleCount += 1;
    }
  });

  if (config.emptyState) {
    config.emptyState.hidden = visibleCount > 0;
  }
};

tableConfigs.forEach((config) => {
  if (!config.searchInput && !config.filterInput) {
    return;
  }

  config.searchInput?.addEventListener('input', () => applyTableFilter(config));
  config.filterInput?.addEventListener('change', () => applyTableFilter(config));
  applyTableFilter(config);
});

const firstSearchInput = tableConfigs.find((item) => item.searchInput)?.searchInput;
document.addEventListener('keydown', (event) => {
  if (event.key === '/' && !['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement?.tagName || '')) {
    if (firstSearchInput) {
      event.preventDefault();
      firstSearchInput.focus();
    }
  }
});

document.querySelectorAll('form[action*="/delete"], form[action*="/close"]').forEach((form) => {
  form.addEventListener('submit', (event) => {
    const submitter = event.submitter;
    if (!submitter || submitter.dataset.confirmed === 'true') {
      return;
    }

    const text = submitter.textContent?.trim() || 'انجام عملیات';
    if (!window.confirm(`آیا از «${text}» مطمئن هستید؟`)) {
      event.preventDefault();
      return;
    }
    submitter.dataset.confirmed = 'true';
  });
});
