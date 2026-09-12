document.addEventListener('DOMContentLoaded', () => {
  const root = document;
  const panel = root.querySelector('#licencepress-settings-panel');
  const config = window.licencepressSettingsTabs || {};

  const initializeBootstrapSelects = (scope = document) => {
    if (window.licencepressBootstrapSelect?.initialize) {
      window.licencepressBootstrapSelect.initialize(scope);
      return;
    }

    if (window.Selectpicker?.getOrCreateInstance) {
      scope.querySelectorAll('.selectpicker').forEach((field) => {
        if (field && !field.dataset.licencepressSelectpickerInitialized) {
          field.dataset.licencepressSelectpickerInitialized = 'true';
          window.Selectpicker.getOrCreateInstance(field);
        }
      });
    }
  };

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

  const showSaveAlert = (form, type, message) => {
    const existing = form.parentElement?.querySelector('[data-licencepress-save-alert]');
    if (existing) existing.remove();

    const alert = document.createElement('div');
    alert.className = `alert alert-${type === 'success' ? 'success' : 'danger'} d-flex align-items-center fade show`;
    alert.setAttribute('role', 'alert');
    alert.setAttribute('data-licencepress-save-alert', 'true');
    alert.innerHTML = `<i class="flex-shrink-0 me-2 fa-solid fa-${type === 'success' ? 'check-circle' : 'times-circle'}" aria-hidden="true"></i><span>${message}</span>`;
    form.parentElement?.insertBefore(alert, form);
  };

  const bindForms = () => root.querySelectorAll('.licencepress-settings-form, .licencepress-import-form').forEach((form) => {
    form.addEventListener('submit', (event) => {
      event.preventDefault();

      const requiredFields = form.querySelectorAll('[data-licencepress-required="true"]');
      const invalidFields = Array.from(requiredFields).filter((field) => field.value.trim() === '');

      if (invalidFields.length > 0) {
        showSaveAlert(form, 'error', 'Settings could not be saved.');
        return;
      }

      const regexFields = form.querySelectorAll('[data-licencepress-validate="true"][pattern]');
      const invalidRegexFields = Array.from(regexFields).filter((field) => {
        const pattern = field.getAttribute('pattern');
        if (!pattern) return false;
        try {
          return !new RegExp(pattern).test(field.value.trim());
        } catch (error) {
          return false;
        }
      });

      if (invalidRegexFields.length > 0) {
        showSaveAlert(form, 'error', 'Settings could not be saved.');
        return;
      }

      const submit = form.querySelector('[type="submit"]');
      if (submit) submit.disabled = true;
      showSaveAlert(form, 'success', 'Settings saved successfully.');
      setTimeout(() => { if (submit) submit.disabled = false; }, 600);
    });
  });

  const bindFieldValidation = () => {
    root.querySelectorAll('[data-licencepress-validate="true"]').forEach((field) => {
      const feedback = field.nextElementSibling;

      if (!feedback || !feedback.classList?.contains('invalid-feedback')) {
        return;
      }

      const validate = () => {
        const value = field.value.trim();
        if (value.length === 0) {
          field.classList.remove('is-invalid', 'is-valid');
          feedback.style.display = 'none';
          return;
        }

        const pattern = field.getAttribute('pattern');
        const valid = !pattern || (() => {
          try {
            return new RegExp(pattern).test(value);
          } catch (error) {
            return true;
          }
        })();

        field.classList.toggle('is-invalid', !valid);
        field.classList.toggle('is-valid', valid);
        feedback.style.display = valid ? 'none' : 'block';
      };

      field.addEventListener('blur', () => {
        if (field.value.trim().length === 0) {
          field.classList.remove('is-invalid', 'is-valid');
          if (feedback) feedback.style.display = 'none';
          return;
        }

        field.dataset.licencepressTouched = 'true';
        validate();
      });
    });
  };

  const bindLicencePatternControls = () => {
    const patternType = root.querySelector('select[name="licencepress_general[licence_pattern_type]"]');
    const defaultPatternType = root.querySelector('select[name="licencepress_general[default_licence_pattern_type]"]');
    const customPattern = root.querySelector('input[name="licencepress_general[custom_pattern]"]');
    const defaultCustomPatternRow = root.querySelector('#licencepress-default-custom-pattern-row');
    const defaultCustomPatternInput = root.querySelector('input[name="licencepress_general[default_custom_pattern]"]');
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

      if (defaultPatternType && defaultCustomPatternRow) {
        const defaultCustom = defaultPatternType.value === 'custom';
        defaultCustomPatternRow.hidden = !defaultCustom;
        defaultCustomPatternRow.style.display = defaultCustom ? '' : 'none';
      }

      if (letterCaseRow && customPattern) {
        const hasPatternToken = /[XA]/i.test(customPattern.value || '');
        const shouldShowLetterCase = 'custom' === type && hasPatternToken;
        letterCaseRow.hidden = !shouldShowLetterCase;
        letterCaseRow.style.display = shouldShowLetterCase ? '' : 'none';
      }

      if (defaultCustomPatternInput && defaultCustomPatternRow) {
        const hasDefaultPatternToken = /[XA]/i.test(defaultCustomPatternInput.value || '');
        const shouldShowDefaultLetterCase = defaultPatternType && defaultPatternType.value === 'custom' && hasDefaultPatternToken;
        const defaultLetterCaseRow = root.querySelector('select[name="licencepress_general[pattern_letter_case]"]')?.closest('tr');
        if (defaultLetterCaseRow) {
          defaultLetterCaseRow.hidden = !shouldShowDefaultLetterCase;
          defaultLetterCaseRow.style.display = shouldShowDefaultLetterCase ? '' : 'none';
        }
      }
    };

    if (patternType) patternType.addEventListener('change', applyPatternState);
    if (defaultPatternType) defaultPatternType.addEventListener('change', applyPatternState);
    if (customPattern) customPattern.addEventListener('input', applyPatternState);
    if (defaultCustomPatternInput) defaultCustomPatternInput.addEventListener('input', applyPatternState);
    applyPatternState();
  };

  const bindRenewalPolicyControls = () => {
    const renewalMode = root.querySelector('select[name="licencepress_general[default_renewal_policy_mode]"]');
    const customRenewalRow = root.querySelector('#licencepress-custom-renewal-row');
    if (!renewalMode || !customRenewalRow) return;

    const applyRenewalPolicyState = () => {
      const isCustom = renewalMode.value === 'custom';
      customRenewalRow.hidden = !isCustom;
      customRenewalRow.style.display = isCustom ? '' : 'none';
    };

    renewalMode.addEventListener('change', applyRenewalPolicyState);
    applyRenewalPolicyState();
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
        initializeBootstrapSelects(panel);
        if (updateHash) window.history.pushState({}, '', `${window.location.pathname}${window.location.search}#${response.data.tab === 'layout' ? `layout-${response.data.layout_section}` : response.data.tab}`);
        bindForms();
        bindFieldValidation();
        bindLicencePatternControls();
        bindRenewalPolicyControls();
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
  initializeBootstrapSelects(panel);
  bindForms();
  bindFieldValidation();
  bindLicencePatternControls();
  bindRenewalPolicyControls();
});