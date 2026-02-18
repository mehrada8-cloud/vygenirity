const root = document.documentElement;
const themeToggle = document.querySelector('[data-theme-toggle]');
const floatingMenu = document.querySelector('[data-floating-menu]');
const floatingTrigger = document.querySelector('[data-floating-trigger]');
const floatingActions = document.querySelector('.floating-menu__actions');

const storedTheme = localStorage.getItem('vygen-theme');
if (storedTheme) {
  root.setAttribute('data-theme', storedTheme);
}

themeToggle?.addEventListener('click', () => {
  const current = root.getAttribute('data-theme') || 'dark';
  const next = current === 'light' ? 'dark' : 'light';
  root.setAttribute('data-theme', next);
  localStorage.setItem('vygen-theme', next);
});

const closeFloatingMenu = () => {
  if (!floatingMenu) {
    return;
  }
  floatingMenu.dataset.open = 'false';
  floatingTrigger?.setAttribute('aria-expanded', 'false');
};

const toggleFloatingMenu = () => {
  if (!floatingMenu) {
    return;
  }
  const isOpen = floatingMenu.dataset.open === 'true';
  floatingMenu.dataset.open = isOpen ? 'false' : 'true';
  floatingTrigger?.setAttribute('aria-expanded', isOpen ? 'false' : 'true');
};

floatingTrigger?.addEventListener('click', (event) => {
  event.stopPropagation();
  toggleFloatingMenu();
});

floatingActions?.addEventListener('click', (event) => {
  if (event.target.closest('[data-theme-toggle]') || event.target.closest('[data-report-open]')) {
    closeFloatingMenu();
  }
});

document.addEventListener('click', (event) => {
  if (!floatingMenu || floatingMenu.contains(event.target)) {
    return;
  }
  closeFloatingMenu();
});

document.addEventListener('keydown', (event) => {
  if (event.key === 'Escape') {
    closeFloatingMenu();
  }
});

async function pollWithEtag(url, onData) {
  let etag = null;
  async function fetchData() {
    const headers = {};
    if (etag) {
      headers['If-None-Match'] = etag;
    }
    const response = await fetch(url, { headers });
    if (response.status === 304) {
      return;
    }
    etag = response.headers.get('ETag');
    const json = await response.json();
    onData(json);
  }

  await fetchData();
  setInterval(fetchData, 10000);
}

const categoryContainer = document.getElementById('categories');
if (categoryContainer) {
  const endpoint = categoryContainer.dataset.endpoint;
  pollWithEtag(endpoint, (payload) => {
    const data = payload.data || [];
    if (!data.length) {
      categoryContainer.innerHTML = '<div class="empty-state"><h3>دسته‌بندی ثبت نشده</h3><p>ادمین هنوز دسته‌بندی اضافه نکرده است.</p></div>';
      return;
    }
    categoryContainer.innerHTML = data.map((category) => `
      <a class="card" href="/signals/${category.slug}">
        <div>
          <span class="tag">ویژه</span>
          <h3>${category.name}</h3>
          <p class="subtle">مشاهده سیگنال‌های مرتبط با این دسته‌بندی</p>
        </div>
        <span class="arrow">→</span>
      </a>
    `).join('');
  });
}

const signalContainer = document.getElementById('signals');
if (signalContainer) {
    const persianFormatter = new Intl.DateTimeFormat('fa-IR-u-ca-persian', {
    day: 'numeric',
    month: 'long',
    year: 'numeric',
    weekday: 'long',
  });
  const gregorianFormatter = new Intl.DateTimeFormat('en-US', {
    day: 'numeric',
    month: 'long',
    year: 'numeric',
    weekday: 'long',
  });
  const timeFormatter = new Intl.DateTimeFormat('fa-IR', {
    hour: '2-digit',
    minute: '2-digit',
  });
  const reportModal = document.querySelector('[data-report-modal]');
  const reportTrigger = document.querySelector('[data-report-open]');
  const reportSubtitle = reportModal?.querySelector('[data-report-subtitle]');
  const reportTotal = reportModal?.querySelector('[data-report-total]');
  const reportCount = reportModal?.querySelector('[data-report-count]');
  const reportClosed = reportModal?.querySelector('[data-report-closed]');
  const reportSuccess = reportModal?.querySelector('[data-report-success]');
  const reportFailed = reportModal?.querySelector('[data-report-failed]');
  const reportCreated = reportModal?.querySelector('[data-report-created]');
  const reportClosedAt = reportModal?.querySelector('[data-report-closed-at]');
  const reportTarget = reportModal?.querySelector('[data-report-target]');
  const reportGuarantee = reportModal?.querySelector('[data-report-guarantee]');
  const reportChart = reportModal?.querySelector('[data-report-chart]');
  const reportScrollBody = reportModal?.querySelector('[data-report-scroll-body]');
  const reportScrollButton = reportModal?.querySelector('[data-report-scroll]');
  const lineProgress = document.querySelector('[data-line-progress]');
  const lineProgressTitle = lineProgress?.querySelector('[data-line-progress-title]');
  const lineProgressValue = lineProgress?.querySelector('[data-line-progress-value]');
  const lineProgressTarget = lineProgress?.querySelector('[data-line-progress-target]');
  const lineProgressStop = lineProgress?.querySelector('[data-line-progress-stop]');
  const lineProgressCaption = lineProgress?.querySelector('[data-line-progress-caption]');
  const lineProgressMarker = lineProgress?.querySelector('[data-line-progress-marker]');
  const setLineProgressFocus = (isFocused) => {
    if (!lineProgress) {
      return;
    }
    lineProgress.dataset.focused = isFocused ? 'true' : 'false';
  };

  lineProgress?.addEventListener('pointerdown', () => setLineProgressFocus(true));
  lineProgress?.addEventListener('focusin', () => setLineProgressFocus(true));

  document.addEventListener('pointerdown', (event) => {
    if (!lineProgress || lineProgress.contains(event.target)) {
      return;
    }
    setLineProgressFocus(false);
  });

  let reportPayload = { data: [], meta: {} };
  let hasShownPersianNumbers = false;

  const closeReport = () => {
    if (!reportModal) {
      return;
    }
    reportModal.setAttribute('aria-hidden', 'true');
    document.body.classList.remove('modal-open');
  };

  const openReport = () => {
    if (!reportModal) {
      return;
    }
    reportModal.setAttribute('aria-hidden', 'false');
    document.body.classList.add('modal-open');
    renderReport(reportPayload);
    if (reportScrollBody) {
      reportScrollBody.scrollTop = 0;
      requestAnimationFrame(() => updateReportScrollButton());
    }
  };

  const getDateParts = (date, formatter) => formatter.formatToParts(date).reduce((acc, part) => {
    if (part.type !== 'literal') {
      acc[part.type] = part.value;
    }
    return acc;
  }, {});

  const buildCalendarFace = (parts, typeClass) => `
    <div class="calendar-face ${typeClass}">
      <span class="calendar-month">${parts.month}</span>
      <span class="calendar-day">${parts.day}</span>
      <span class="calendar-weekday">${parts.weekday}</span>
      <span class="calendar-year">${parts.year}</span>
    </div>
  `;

  const buildDateCard = (date, title) => {
    if (!date) {
      return `
        <div class="date-card date-card--inactive">
          <span class="date-card__title">${title}</span>
          <p class="date-card__empty">در حال اجرا</p>
        </div>
      `;
    }

    const persianParts = getDateParts(date, persianFormatter);
    const gregorianParts = getDateParts(date, gregorianFormatter);

    return `
      <div class="date-card">
        <span class="date-card__title">${title}</span>
        <div class="date-card__calendars">
          ${buildCalendarFace(persianParts, 'calendar-face--persian')}
          ${buildCalendarFace(gregorianParts, 'calendar-face--gregorian')}
        </div>
        <span class="date-card__time">ساعت ${timeFormatter.format(date)}</span>
      </div>
    `;
  };
  const formatPercent = (value) => {
    if (value === null || value === undefined || Number.isNaN(value)) {
      return null;
    }
    const numeric = Number(value);
    const sign = numeric > 0 ? '+' : numeric < 0 ? '−' : '';
    return `${sign}${Math.abs(numeric).toFixed(2)}%`;
  };

  const persianDigitMap = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
  const toPersianDigits = (value) => String(value).replace(/\d/g, (digit) => persianDigitMap[Number(digit)]);
  const toPersianNumberText = (value) => toPersianDigits(String(value).replace(/\./g, '٫'));
  const animateNumbersOnce = () => {
    const elements = Array.from(document.querySelectorAll('[data-animate-number="true"]'));
    if (!elements.length) {
      return;
    }
    elements.forEach((element) => {
      if (!element.dataset.originalValue) {
        element.dataset.originalValue = element.textContent || '';
      }
      element.textContent = toPersianDigits(element.dataset.originalValue);
    });
    window.setTimeout(() => {
      elements.forEach((element) => {
        if (!element.dataset.originalValue) {
          return;
        }
        element.textContent = element.dataset.originalValue;
          element.classList.add('is-latin');
          element.addEventListener(
              'animationend',
              () => {
                  element.classList.remove('is-latin');
              },
              { once: true },
          );
      });
    }, 3000);
  };


  const formatSignedPercent = (value) => formatPercent(value) ?? '—';
  const formatSignedPercentFa = (value) => {
    const formatted = formatSignedPercent(value);
    return formatted === '—' ? formatted : toPersianNumberText(formatted);
  };

  const clamp = (value, min, max) => Math.min(Math.max(value, min), max);

  const renderLineProgress = (category = {}) => {
    if (!lineProgress) {
      return;
    }
    const target = category.target_percent !== null && category.target_percent !== undefined
      ? Number(category.target_percent)
      : null;
    const guarantee = category.guarantee_percent !== null && category.guarantee_percent !== undefined
      ? Math.abs(Number(category.guarantee_percent))
      : null;
    const cumulative = Number(category.cumulative_pnl_percent) || 0;
    lineProgress.dataset.state = cumulative > 0 ? 'positive' : cumulative < 0 ? 'negative' : 'neutral';
    if (lineProgressTitle) {
      lineProgressTitle.textContent = category.name ? `پیشروی ${category.name}` : 'پیشروی لاین';
    }
    if (lineProgressValue) {
      lineProgressValue.textContent = formatSignedPercentFa(cumulative);
    }
    if (lineProgressTarget) {
      lineProgressTarget.textContent = `تارگت: ${target === null ? '—' : `${toPersianNumberText(target.toFixed(2))}%`}`;
    }
    if (lineProgressStop) {
      lineProgressStop.textContent = `حد ضمانت: ${guarantee === null ? '—' : `−${toPersianNumberText(guarantee.toFixed(2))}%`}`;
    }

    if (lineProgressCaption) {
      if (target === null) {
        lineProgressCaption.textContent = 'تارگت لاین هنوز توسط ادمین تعیین نشده است.';
      } else {
        lineProgressCaption.textContent = '';
      }
    }

    if (lineProgressMarker) {
      const min = guarantee === null ? -Math.max(target || 10, 10) : -guarantee;
      const max = target === null ? Math.max(guarantee || 10, 10) : target;
      const span = max - min || 1;
      const position = ((clamp(cumulative, min, max) - min) / span) * 100;
      lineProgressMarker.style.setProperty('--marker-position', `${position}%`);
    }
  };

  const setReportTotalState = (value) => {
    if (!reportTotal) {
      return;
    }
    const numeric = Number(value) || 0;
    reportTotal.classList.toggle('badge--positive', numeric > 0);
    reportTotal.classList.toggle('badge--negative', numeric < 0);
  };

  const clampPercent = (value) => Math.min(Math.abs(Number(value) || 0), 100);
  const buildReportChart = (points, total) => {
    if (!reportChart) {
      return;
    }
    if (!points.length) {
      reportChart.innerHTML = '<div class="empty-state"><h3>اطلاعات کافی نیست</h3><p>برای نمایش نمودار باید حداقل یک سیگنال بسته شده وجود داشته باشد.</p></div>';
      return;
    }

    const width = 520;
    const height = 220;
    const padding = 24;
    const values = points.map((point) => point.value);
    const minValue = Math.min(0, ...values);
    const maxValue = Math.max(0, ...values);
    const range = maxValue - minValue || 1;
    const stepX = (width - padding * 2) / Math.max(points.length - 1, 1);

    const coords = points.map((point, index) => {
      const x = padding + index * stepX;
      const y = padding + ((maxValue - point.value) / range) * (height - padding * 2);
      return { x, y };
    });

    const linePath = coords.map((coord, index) => `${index === 0 ? 'M' : 'L'}${coord.x},${coord.y}`).join(' ');
    const areaPath = `${linePath} L${coords[coords.length - 1].x},${height - padding} L${coords[0].x},${height - padding} Z`;
    const dots = coords.map((coord) => `<circle class="report-chart__dot" cx="${coord.x}" cy="${coord.y}" r="4"></circle>`).join('');

    reportChart.innerHTML = `
      <svg viewBox="0 0 ${width} ${height}" aria-label="نمودار PNL تجمعی">
        <path class="report-chart__area" d="${areaPath}"></path>
        <path class="report-chart__line" d="${linePath}"></path>
        ${dots}
      </svg>
    `;

    if (reportTotal) {
      reportTotal.textContent = toPersianNumberText(formatPercent(total) ?? '0%');
      setReportTotalState(total);
    }
  };

  const updateReportScrollButton = () => {
    if (!reportScrollBody || !reportScrollButton) {
      return;
    }
    const { scrollTop, scrollHeight, clientHeight } = reportScrollBody;
    const isScrollable = scrollHeight - clientHeight > 8;
    const atBottom = scrollTop + clientHeight >= scrollHeight - 8;
    const shouldShow = isScrollable && !atBottom;
    reportScrollButton.dataset.visible = shouldShow ? 'true' : 'false';
    reportScrollButton.setAttribute('aria-hidden', shouldShow ? 'false' : 'true');
  };

  const renderReport = (payload) => {
    reportPayload = payload || { data: [], meta: {} };
    const data = reportPayload.data || [];
    const category = reportPayload.meta?.category || {};
    const closedSignals = data.filter((signal) => signal.status === 'closed');
    const successSignals = closedSignals.filter((signal) => Number(signal.pnl_percent) > 0);
    const failedSignals = closedSignals.filter((signal) => Number(signal.pnl_percent) < 0);

    if (reportSubtitle) {
      reportSubtitle.textContent = category.name ? `عملکرد لاین ${category.name}` : 'عملکرد کلی این دسته‌بندی';
    }
    if (reportCount) {
      reportCount.textContent = toPersianDigits(String(data.length));
    }
    if (reportClosed) {
      reportClosed.textContent = toPersianDigits(String(closedSignals.length));
    }
    if (reportSuccess) {
      reportSuccess.textContent = toPersianDigits(String(successSignals.length));
    }
    if (reportFailed) {
      reportFailed.textContent = toPersianDigits(String(failedSignals.length));
    }
    if (reportCreated) {
      reportCreated.textContent = category.created_at_display || '—';
    }
    if (reportClosedAt) {
      reportClosedAt.textContent = category.closed_at_display || '—';
    }
    if (reportTarget) {
      reportTarget.textContent = formatSignedPercentFa(category.target_percent);
    }
    if (reportGuarantee) {
      const guaranteeValue = category.guarantee_percent !== null && category.guarantee_percent !== undefined
        ? -Math.abs(Number(category.guarantee_percent))
        : null;
      reportGuarantee.textContent = formatSignedPercentFa(guaranteeValue);
    }
    const pnlTimeline = closedSignals
      .filter((signal) => signal.pnl_percent !== null && signal.pnl_percent !== undefined)
      .sort((a, b) => new Date(a.end_at || a.updated_at || a.start_at) - new Date(b.end_at || b.updated_at || b.start_at));

    let cumulative = 0;
    const points = pnlTimeline.map((signal) => {
      const value = Number(signal.pnl_percent) || 0;
      cumulative += value;
      return { value: cumulative };
    });

    if (reportTotal) {
      reportTotal.textContent = toPersianNumberText(formatPercent(cumulative) ?? '0%');
      setReportTotalState(cumulative);
    }
    buildReportChart(points, cumulative);
    renderLineProgress(category);
    updateReportScrollButton();
  };
  const getOpenPanelIds = () => new Set(
    Array.from(signalContainer.querySelectorAll('.date-panel[data-open="true"]'))
      .map((panel) => panel.id)
      .filter(Boolean),
  );

  const restoreOpenPanels = (openPanelIds) => {
    openPanelIds.forEach((panelId) => {
      const panel = document.getElementById(panelId);
      if (!panel) {
        return;
      }
      panel.dataset.open = 'true';
      panel.setAttribute('aria-hidden', 'false');
      const trigger = panel.parentElement?.querySelector('[data-date-trigger]');
      trigger?.setAttribute('aria-expanded', 'true');
    });
  };
  const endpoint = signalContainer.dataset.endpoint;
  pollWithEtag(endpoint, (payload) => {
    const openPanelIds = getOpenPanelIds();
    const data = payload.data || [];
    if (!data.length) {
      signalContainer.innerHTML = '<div class="empty-state"><h3>سیگنالی موجود نیست</h3><p>سیگنال‌های این دسته‌بندی به‌زودی ثبت می‌شوند.</p></div>';
      renderReport(payload);
      return;
    }

    signalContainer.innerHTML = data.map((signal, index) => {
      const statusClass = signal.status === 'open' ? 'status-open' : 'status-closed';
      const closeLabels = {
        target: 'بسته با تارگت',
        stop: 'بسته با استاپ',
        manual: 'بسته شده | پیش از برخورد به تارگت یا استاپ',
      };
      const closeText = signal.status === 'closed'
        ? (closeLabels[signal.close_reason] || 'بسته شده')
        : 'فعال';
      const startDate = signal.start_at ? new Date(signal.start_at) : null;
      const endDate = signal.end_at ? new Date(signal.end_at) : null;
      const panelId = `date-panel-${signal.id ?? index}`;
      const targetPercent = signal.target_percent !== null ? Number(signal.target_percent) : null;
      const pnlPercent = signal.pnl_percent !== null ? Number(signal.pnl_percent) : null;
      const targetPercentText = formatPercent(targetPercent) ?? '—';
      const pnlPercentText = formatPercent(pnlPercent) ?? '—';
      const manualExitPrice = signal.close_reason === 'manual' && signal.exit_price !== null
        ? signal.exit_price
        : null;
      const pnlState = pnlPercent === null ? 'waiting' : pnlPercent >= 0 ? 'positive' : 'negative';
      const pnlCaption = pnlPercent === null ? 'در انتظار بسته شدن سیگنال' : 'محاسبه شده پس از بستن سیگنال';
      
      return `
        <article class="signal-card ${statusClass}">
          <header>
            <div class="signal-heading">
              <div class="signal-title-row">
                <h3>${signal.title}</h3>
                <p class="subtle mono">${signal.pair}</p>
              </div>
            </div>
              <span class="badge signal-badge signal-badge--${signal.position_type}">${signal.position_type.toUpperCase()} • ${signal.market_type.toUpperCase()}</span>
          </header>
          <div class="signal-grid">
            <div>
              <span class="label">ورود</span>
              <strong class="animated-number" data-animate-number="true" dir="ltr">${signal.entry_price}</strong>
            </div>
            <div>
              <span class="label">تارگت</span>
              <div class="signal-price-row">
                <strong class="animated-number" data-animate-number="true" dir="ltr">${signal.target_price}</strong>
                ${manualExitPrice === null ? '' : `<span class="signal-manual-exit">خروج دستی: <strong class="animated-number" data-animate-number="true" dir="ltr">${manualExitPrice}</strong></span>`}
              </div>
            </div>
            <div>
              <span class="label">استاپ</span>
              <strong class="animated-number" data-animate-number="true" dir="ltr">${signal.stop_price}</strong>
            </div>
          </div>
          <div class="signal-metrics">
            <div class="metric-card metric-card--target" data-collapsible="true" data-open="false">
              <div class="metric-header">
                <div class="metric-title">
                  <span class="metric-label">درصد تارگت</span>
                  <span class="metric-value animated-number" data-animate-number="true" dir="ltr">${targetPercentText}</span>
                </div>
                <button class="metric-toggle" type="button" data-metric-toggle aria-expanded="false" aria-label="باز و بسته کردن جزئیات">
                  <span class="metric-toggle__icon" aria-hidden="true">▾</span>
                </button>
              </div>
              <div class="metric-details">
                <p class="metric-note">پیش‌بینی شده بر اساس اختلاف ورود تا تارگت</p>
              </div>
            </div>
            <div class="metric-card metric-card--pnl metric-card--${pnlState}" data-collapsible="true" data-open="false">
              <div class="metric-header">
                <div class="metric-title">
                  <span class="metric-label">PNL نهایی</span>
                  <span class="metric-value animated-number" data-animate-number="true" dir="ltr">${pnlPercentText}</span>
                </div>
                <button class="metric-toggle" type="button" data-metric-toggle aria-expanded="false" aria-label="باز و بسته کردن جزئیات">
                  <span class="metric-toggle__icon" aria-hidden="true">▾</span>
                </button>
              </div>
              <div class="metric-details">
                <p class="metric-note">${pnlCaption}</p>
              </div>
            </div>
          </div>
          <div class="signal-footer">
            <div>
              <span class="label">زمان‌بندی</span>
              <div class="date-trigger-wrap">
                <button class="date-trigger" type="button" data-date-trigger aria-expanded="false" aria-controls="${panelId}">
                  <span class="date-trigger__icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" focusable="false">
                      <path d="M7 2a1 1 0 0 1 1 1v1h8V3a1 1 0 1 1 2 0v1h1.5A2.5 2.5 0 0 1 22 6.5v12A2.5 2.5 0 0 1 19.5 21h-15A2.5 2.5 0 0 1 2 18.5v-12A2.5 2.5 0 0 1 4.5 4H6V3a1 1 0 0 1 1-1Zm12.5 8.5h-15v8a.5.5 0 0 0 .5.5h14a.5.5 0 0 0 .5-.5v-8Zm-15-2h15V6.5a.5.5 0 0 0-.5-.5H18v1a1 1 0 1 1-2 0V6H8v1a1 1 0 1 1-2 0V6H4.5a.5.5 0 0 0-.5.5v2Z"/>
                    </svg>
                  </span>
                  <span class="date-trigger__text">مشاهده تاریخ‌ها</span>
                </button>
                <div class="date-panel" id="${panelId}" data-open="false" aria-hidden="true" role="dialog">
                  <div class="date-panel__header">
                    <span>تقویم سیگنال</span>
                    <button type="button" class="date-panel__close" data-date-close aria-label="بستن">×</button>
                  </div>
                  <div class="date-panel__content">
                    ${buildDateCard(startDate, 'شروع سیگنال')}
                    ${buildDateCard(endDate, 'پایان سیگنال')}
                  </div>
                </div>
              </div>
            </div>
            <div>
              <span class="label">وضعیت</span>
              <span class="status">${closeText}</span>
            </div>
          </div>
        </article>
      `;
    }).join('');
    restoreOpenPanels(openPanelIds);
    renderReport(payload);
    if (!hasShownPersianNumbers) {
      animateNumbersOnce();
      hasShownPersianNumbers = true;
    }
  });

  reportTrigger?.addEventListener('click', () => {
    openReport();
  });

  reportModal?.addEventListener('click', (event) => {
    if (event.target.closest('[data-report-close]')) {
      closeReport();
    }
  });

  reportScrollButton?.addEventListener('click', () => {
    if (!reportScrollBody) {
      return;
    }
    reportScrollBody.scrollTo({ top: reportScrollBody.scrollHeight, behavior: 'smooth' });
  });

  reportScrollBody?.addEventListener('scroll', () => {
    updateReportScrollButton();
  });
  
  const closeDatePanels = () => {
    signalContainer.querySelectorAll('.date-panel[data-open="true"]').forEach((panel) => {
      panel.dataset.open = 'false';
      panel.classList.remove('date-panel--flip');
      panel.setAttribute('aria-hidden', 'true');
      const trigger = panel.parentElement?.querySelector('[data-date-trigger]');
      trigger?.setAttribute('aria-expanded', 'false');
    });
  };

  const updateDatePanelPosition = (panel) => {
    if (!panel || panel.dataset.open !== 'true') {
      return;
    }
    panel.classList.remove('date-panel--flip');
    const rect = panel.getBoundingClientRect();
    if (rect.top < 16) {
      panel.classList.add('date-panel--flip');
    }
  };

  signalContainer.addEventListener('click', (event) => {
    const closeButton = event.target.closest('[data-date-close]');
    if (closeButton) {
      const panel = closeButton.closest('.date-panel');
      if (panel) {
        panel.dataset.open = 'false';
        panel.setAttribute('aria-hidden', 'true');
        panel.parentElement?.querySelector('[data-date-trigger]')?.setAttribute('aria-expanded', 'false');
      }
      return;
    }

    const metricToggle = event.target.closest('[data-metric-toggle]');
    if (metricToggle) {
      const metricCard = metricToggle.closest('.metric-card[data-collapsible="true"]');
      if (metricCard) {
        const isOpen = metricCard.dataset.open === 'true';
        metricCard.dataset.open = isOpen ? 'false' : 'true';
        metricToggle.setAttribute('aria-expanded', isOpen ? 'false' : 'true');
      }
      return;
    }

    const trigger = event.target.closest('[data-date-trigger]');
    if (!trigger) {
      return;
    }

    event.stopPropagation();
    const panelId = trigger.getAttribute('aria-controls');
    if (!panelId) {
      return;
    }

    const panel = document.getElementById(panelId);
    if (!panel) {
      return;
    }

    const isOpen = panel.dataset.open === 'true';
    closeDatePanels();
    panel.dataset.open = isOpen ? 'false' : 'true';
    panel.setAttribute('aria-hidden', isOpen ? 'true' : 'false');
    trigger.setAttribute('aria-expanded', isOpen ? 'false' : 'true');
    if (!isOpen) {
      requestAnimationFrame(() => updateDatePanelPosition(panel));
    }
  });

  document.addEventListener('click', (event) => {
    if (!signalContainer.contains(event.target)) {
      closeDatePanels();
    }
  });

  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
      closeDatePanels();
      closeReport();
    }
  });

  window.addEventListener('resize', () => {
    signalContainer.querySelectorAll('.date-panel[data-open="true"]').forEach((panel) => {
      updateDatePanelPosition(panel);
    });
  });
}
