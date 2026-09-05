document.addEventListener('DOMContentLoaded', () => {
  const root = document;
  const panel = root.querySelector('#licencepress-settings-panel');
  const config = window.licencepressSettingsTabs || {};
  if (!panel) return;

  const stateFromHash = () => {
    const hash = window.location.hash.replace(/^#/, '') || panel.dataset.currentTab || 'general';
    if (hash.indexOf('layout-') === 0) return { tab: 'layout', section: hash.replace('layout-', '') || 'general' };
    return { tab: hash, section: 'general' };
  };

  const setActive = (tab, section) => {
    root.querySelectorAll('[data-licencepress-settings-tab]').forEach((link) => {
      const active = link.dataset.licencepressSettingsTab === tab && (!link.dataset.licencepressSettingsSection || link.dataset.licencepressSettingsSection === section);
      link.classList.toggle('active', active);
      link.setAttribute('aria-selected', active ? 'true' : 'false');
      if (active) link.setAttribute('aria-current', 'page');
      else link.removeAttribute('aria-current');
    });
  };

  const bindForms = () => root.querySelectorAll('.licencepress-settings-form, .licencepress-import-form').forEach((form) => {
    form.addEventListener('submit', () => {
      const submit = form.querySelector('[type="submit"]');
      if (submit) submit.disabled = true;
    });
  });

  const bindLicencePatternControls = () => {
    const patternType = root.querySelector('select[name="licencepress_general[licence_pattern_type]"]');
    const customPattern = root.querySelector('input[name="licencepress_general[custom_pattern]"]');
    const letterCaseRow = root.querySelector('select[name="licencepress_general[pattern_letter_case]"]')?.closest('tr');
    const customRows = root.querySelectorAll('[data-licencepress-pattern-mode="custom"]');
    const standardRows = root.querySelectorAll('[data-licencepress-pattern-mode="standard"]');
    const applyPatternState = () => {
      const type = patternType ? (patternType.value || 'standard') : 'standard';
      standardRows.forEach((row) => {
        row.hidden = false;
        row.style.display = '';
      });
      customRows.forEach((row) => {
        const isCustom = 'custom' === type;
        row.hidden = !isCustom;
        row.style.display = isCustom ? '' : 'none';
      });

      if (letterCaseRow && customPattern) {
        const hasPatternToken = /[XA]/i.test(customPattern.value || '');
        const shouldShowLetterCase = 'custom' === type && hasPatternToken;
        letterCaseRow.hidden = !shouldShowLetterCase;
        letterCaseRow.style.display = shouldShowLetterCase ? '' : 'none';
      }
    };

    if (patternType) patternType.addEventListener('change', applyPatternState);
    if (customPattern) customPattern.addEventListener('input', applyPatternState);
    applyPatternState();
  };

  const activateLayoutTab = (button) => {
    const target = root.querySelector(button.dataset.bsTarget);
    if (!target) return;

    const current = root.querySelector('#licencepress-layout-tab .nav-link.active');
    const currentPane = root.querySelector('#licencepress-layout-tab-content .tab-pane.active');
    if (current === button && currentPane === target) return;

    root.querySelectorAll('#licencepress-layout-tab .nav-link').forEach((tab) => {
      const active = tab === button;
      tab.classList.toggle('active', active);
      tab.setAttribute('aria-selected', active ? 'true' : 'false');
    });

    if (currentPane) {
      currentPane.classList.remove('show');
      window.setTimeout(() => currentPane.classList.remove('active'), 150);
    }

    target.classList.add('active');
    requestAnimationFrame(() => target.classList.add('show'));
  };

  const loadTab = (tab, section, updateHash = true) => {
    const currentContent = panel.querySelector('.licencepress-settings-tab-content');
    if (currentContent) currentContent.classList.add('is-loading');
    panel.setAttribute('aria-busy', 'true');
    const body = new URLSearchParams({ action: 'licencepress_load_settings_tab', nonce: config.nonce, tab, layout_section: section });
    fetch(config.ajaxUrl, { method: 'POST', credentials: 'same-origin', headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' }, body })
      .then((response) => response.json())
      .then((response) => {
        if (!response.success || !response.data.html) throw new Error('Unable to load settings tab');
        panel.innerHTML = response.data.html;
        panel.dataset.currentTab = response.data.tab;
        panel.dataset.currentSection = response.data.layout_section;
        setActive(response.data.tab, response.data.layout_section);
        if (updateHash) window.history.pushState({}, '', `${window.location.pathname}${window.location.search}#${response.data.tab === 'layout' ? `layout-${response.data.layout_section}` : response.data.tab}`);
        bindForms();
        const nextContent = panel.querySelector('.licencepress-settings-tab-content');
        if (nextContent) requestAnimationFrame(() => nextContent.classList.remove('is-loading'));
      })
      .catch(() => { panel.classList.remove('is-loading'); })
      .finally(() => panel.removeAttribute('aria-busy'));
  };

  root.addEventListener('click', (event) => {
    const layoutButton = event.target.closest?.('[data-licencepress-layout-tab]');
    if (layoutButton) {
      event.preventDefault();
      activateLayoutTab(layoutButton);
      window.history.pushState({}, '', `${window.location.pathname}${window.location.search}#layout-${layoutButton.dataset.licencepressLayoutTab}`);
      panel.dataset.currentTab = 'layout';
      panel.dataset.currentSection = layoutButton.dataset.licencepressLayoutTab;
      return;
    }

    const link = event.target.closest?.('#licencepress-settings-panel [data-licencepress-settings-tab]');
    if (!link) return;
    event.preventDefault();
    event.stopPropagation();
    loadTab(link.dataset.licencepressSettingsTab, link.dataset.licencepressSettingsSection || 'general');
  }, true);
  const navigateFromHash = () => {
    const state = stateFromHash();
    if ('layout' === state.tab) {
      const button = root.querySelector(`[data-licencepress-layout-tab="${state.section}"]`);
      if (button) {
        activateLayoutTab(button);
        panel.dataset.currentTab = 'layout';
        panel.dataset.currentSection = state.section;
      }
      return;
    }
    loadTab(state.tab, state.section, false);
  };
  window.addEventListener('popstate', navigateFromHash);
  window.addEventListener('hashchange', navigateFromHash);

  const initial = stateFromHash();
  setActive(initial.tab, initial.section);
  if ('layout' === initial.tab) {
    const button = root.querySelector(`[data-licencepress-layout-tab="${initial.section}"]`);
    if (button) activateLayoutTab(button);
  }
  if (window.location.hash && 'layout' !== initial.tab && (initial.tab !== panel.dataset.currentTab || initial.section !== panel.dataset.currentSection)) loadTab(initial.tab, initial.section, false);
  bindForms();
  bindLicencePatternControls();
});