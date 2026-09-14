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
    const patternType = root.querySelector('select[name="licencepress_general[default_licence_pattern_type]"]');
    const customPattern = root.querySelector('input[name="licencepress_general[default_custom_licence_pattern]"]');
    const customPatternRow = root.querySelector('#licencepress-default-custom-pattern-row');
    const patternFormatRow = root.querySelector('#licencepress-default-pattern-format-row');
    const patternSeparatorRow = root.querySelector('#licencepress-default-pattern-separator-row');
    const patternLetterCaseRow = root.querySelector('#licencepress-default-pattern-letter-case-row');
    const applyPatternState = () => {
      const type = patternType ? (patternType.value || 'standard') : 'standard';
      const isCustomPattern = type === 'custom';

      if (customPatternRow) {
        customPatternRow.hidden = !isCustomPattern;
        customPatternRow.style.display = isCustomPattern ? '' : 'none';
      }

      if (patternFormatRow) {
        patternFormatRow.hidden = isCustomPattern;
        patternFormatRow.style.display = isCustomPattern ? 'none' : '';
      }

      if (patternSeparatorRow) {
        patternSeparatorRow.hidden = isCustomPattern;
        patternSeparatorRow.style.display = isCustomPattern ? 'none' : '';
      }

      if (patternLetterCaseRow && customPattern) {
        const hasPatternToken = /[XA]/i.test(customPattern.value || '');
        const shouldShowLetterCase = isCustomPattern && hasPatternToken;
        patternLetterCaseRow.hidden = !shouldShowLetterCase;
        patternLetterCaseRow.style.display = shouldShowLetterCase ? '' : 'none';
      }
    };

    if (patternType) patternType.addEventListener('change', applyPatternState);
    if (customPattern) customPattern.addEventListener('input', applyPatternState);
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

  const bindBillingCountryControls = () => {
    const countryField = root.querySelector('select[name="licencepress_billing[country]"]');
    const currencyField = root.querySelector('select[name="licencepress_billing[currency]"]');
    const vatToggle = root.querySelector('input[name="licencepress_billing[charge_vat]"]');
    const ukCountyRow = root.querySelector('#licencepress-billing-uk-county-row');
    const usStateRow = root.querySelector('#licencepress-billing-us-state-row');
    const otherCountyRow = root.querySelector('#licencepress-billing-other-county-state-row');
    const customCurrencyCodeRow = root.querySelector('#licencepress-billing-custom-currency-code-row');
    const customCurrencySymbolRow = root.querySelector('#licencepress-billing-custom-currency-symbol-row');
    const vatFields = root.querySelectorAll('#licencepress-billing-vat-label-row, #licencepress-billing-vat-percent-row, #licencepress-billing-vat-number-row');

    const applyBillingCountryState = () => {
      const value = countryField ? (countryField.value || '') : '';
      const isUk = value === 'United Kingdom';
      const isUs = value === 'United States of America';

      if (ukCountyRow) {
        ukCountyRow.hidden = !isUk;
        ukCountyRow.style.display = isUk ? '' : 'none';
      }

      if (usStateRow) {
        usStateRow.hidden = !isUs;
        usStateRow.style.display = isUs ? '' : 'none';
      }

      if (otherCountyRow) {
        otherCountyRow.hidden = isUk || isUs;
        otherCountyRow.style.display = isUk || isUs ? 'none' : '';
      }
    };

    const applyCustomCurrencyState = () => {
      const value = currencyField ? (currencyField.value || '') : '';
      const isCustomCurrency = value === 'CUSTOM';

      if (customCurrencyCodeRow) {
        customCurrencyCodeRow.hidden = !isCustomCurrency;
        customCurrencyCodeRow.style.display = isCustomCurrency ? '' : 'none';
      }

      if (customCurrencySymbolRow) {
        customCurrencySymbolRow.hidden = !isCustomCurrency;
        customCurrencySymbolRow.style.display = isCustomCurrency ? '' : 'none';
      }
    };

    const applyVatState = () => {
      const checked = !!(vatToggle && vatToggle.checked);
      vatFields.forEach((row) => {
        if (!row) return;
        row.hidden = !checked;
        row.style.display = checked ? '' : 'none';
      });
    };

    if (countryField) {
      countryField.addEventListener('change', applyBillingCountryState);
      applyBillingCountryState();
    }

    if (currencyField) {
      currencyField.addEventListener('change', applyCustomCurrencyState);
      applyCustomCurrencyState();
    }

    if (vatToggle) {
      vatToggle.addEventListener('change', applyVatState);
      applyVatState();
    }
  };

  const syncImagePreview = (targetId, url) => {
    const previewContainer = document.querySelector(`[data-licencepress-image-preview="${targetId}"]`);
    const previewImage = previewContainer ? previewContainer.querySelector('img') : null;

    if (previewContainer) {
      previewContainer.style.display = url ? '' : 'none';
    }

    if (previewImage && url) {
      previewImage.src = url;
    }
  };

  const bindMediaPickerControls = () => {
    const mediaButtons = root.querySelectorAll('[data-licencepress-media-select]');

    mediaButtons.forEach((button) => {
      if (button.dataset.licencepressMediaBound === 'true') {
        return;
      }

      button.dataset.licencepressMediaBound = 'true';
      button.addEventListener('click', (event) => {
        event.preventDefault();

        const targetId = button.dataset.licencepressMediaSelect;
        if (!targetId || !window.wp || !window.wp.media) {
          return;
        }

        const mediaFrame = window.wp.media({
          title: button.dataset.licencepressMediaTitle || 'Select media',
          button: { text: 'Use this image' },
          multiple: false,
          library: { type: 'image' }
        });

        mediaFrame.on('select', () => {
          const attachment = mediaFrame.state().get('selection').first().toJSON();
          if (!attachment || !attachment.id) {
            return;
          }

          const targetField = document.getElementById(targetId);
          if (!targetField) {
            return;
          }

          targetField.value = attachment.id;

          const previewUrl = attachment.url || attachment.sizes?.medium?.url || attachment.sizes?.thumbnail?.url || attachment.icon;
          syncImagePreview(targetId, previewUrl);
        });

        mediaFrame.open();
      });
    });
  };

  const bindTemplatePreviewControls = () => {
    const previewModal = root.querySelector('#licencepress-template-preview-modal');
    const previewBody = root.querySelector('#licencepress-template-preview-body');
    const editModal = root.querySelector('#licencepress-template-edit-modal');
    const editorField = root.querySelector('#licencepress-template-editor');

    if (!previewModal || !previewBody || !editModal || !editorField) {
      return;
    }

    root.querySelectorAll('[data-licencepress-template-preview]').forEach((button) => {
      button.addEventListener('click', () => {
        const content = button.dataset.licencepressTemplateContent || '';
        previewBody.innerHTML = content;
      });
    });

    root.querySelectorAll('[data-licencepress-template-editor]').forEach((button) => {
      button.addEventListener('click', () => {
        const editorValue = button.dataset.licencepressTemplateValue || '';
        const editorInstance = window.tinymce?.get(editorField.id);

        if (editorInstance) {
          editorInstance.setContent(editorValue);
          return;
        }

        editorField.value = editorValue;
      });
    });
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
        bindBillingCountryControls();
        bindMediaPickerControls();
        bindTemplatePreviewControls();
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
  bindBillingCountryControls();
  bindMediaPickerControls();
  bindTemplatePreviewControls();
});