document.addEventListener('DOMContentLoaded', () => {
  const root = document;

  const onboardingModal = root.getElementById('licencepress-onboarding-modal');
  if (onboardingModal) {
    const modal = window.bootstrap?.Modal.getOrCreateInstance(onboardingModal);
    const steps = [...onboardingModal.querySelectorAll('[data-step]')];
    const nextButton = onboardingModal.querySelector('[data-role="next"]');
    const prevButton = onboardingModal.querySelector('[data-role="prev"]');
    const skipButton = onboardingModal.querySelector('[data-role="skip"]');
    const finishButton = onboardingModal.querySelector('[data-role="finish"]');

    let currentStep = 1;

    const updateStep = () => {
      steps.forEach((step) => {
        const stepNumber = Number(step.dataset.step || 1);
        step.classList.toggle('d-none', stepNumber !== currentStep);
      });

      prevButton.classList.toggle('d-none', currentStep === 1);
      nextButton.classList.toggle('d-none', currentStep === steps.length);
      finishButton.classList.toggle('d-none', currentStep !== steps.length);
    };

    const dismissOnboarding = () => {
      const config = window.licencepressOnboarding || {};
      const ajaxUrl = config.ajaxUrl || window.ajaxurl || '/wp-admin/admin-ajax.php';
      const formData = new URLSearchParams({
        action: 'licencepress_dismiss_onboarding',
        nonce: config.nonce || '',
      });

      fetch(ajaxUrl, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
        },
        credentials: 'same-origin',
        body: formData.toString(),
      }).catch(() => undefined).finally(() => {
        modal.hide();
      });
    };

    nextButton.addEventListener('click', () => {
      if (currentStep < steps.length) {
        currentStep += 1;
        updateStep();
        return;
      }

      dismissOnboarding();
    });

    prevButton.addEventListener('click', () => {
      currentStep = Math.max(1, currentStep - 1);
      updateStep();
    });

    skipButton.addEventListener('click', dismissOnboarding);
    finishButton.addEventListener('click', dismissOnboarding);
    onboardingModal.addEventListener('hidden.bs.modal', () => {
      onboardingModal.classList.remove('show');
      onboardingModal.setAttribute('aria-hidden', 'true');
    });

    updateStep();
    modal.show();
  }

  const toggleCollapse = (button) => {
    const target = root.querySelector(button.dataset.bsTarget);
    if (!target || target.classList.contains('collapsing')) return;

    const isOpen = target.classList.contains('show');
    button.setAttribute('aria-expanded', isOpen ? 'false' : 'true');
    button.classList.toggle('collapsed', isOpen);
    target.classList.add('collapsing');
    target.classList.remove('collapse', 'show');
    target.style.height = isOpen ? `${target.scrollHeight}px` : '0px';
    target.offsetHeight;
    target.style.height = isOpen ? '0px' : `${target.scrollHeight}px`;

    window.setTimeout(() => {
      target.classList.remove('collapsing');
      target.classList.add('collapse');
      if (!isOpen) target.classList.add('show');
      target.style.height = '';
    }, 350);
  };

  root.addEventListener('click', (event) => {
    const button = event.target.closest?.('[data-bs-toggle="collapse"]');
    if (!button) return;

    event.preventDefault();
    toggleCollapse(button);
  });

  root.querySelectorAll('[data-bs-toggle="tooltip"]').forEach((element) => {
    window.bootstrap?.Tooltip.getOrCreateInstance(element);
  });

  root.querySelectorAll('[data-permalink-field="permalink"]').forEach((field) => {
    const tokenButtons = field.parentElement.querySelectorAll('[data-permalink-token]');

    const refreshTokens = () => {
      tokenButtons.forEach((button) => {
        button.classList.toggle('d-none', field.value.includes(button.dataset.permalinkToken));
      });
    };

    tokenButtons.forEach((button) => {
      button.addEventListener('click', (event) => {
        event.preventDefault();
        const token = button.dataset.permalinkToken || '';
        if (!token || field.value.includes(token)) return;

        const start = field.selectionStart ?? field.value.length;
        const end = field.selectionEnd ?? start;
        const left = field.value.slice(0, start).replace(/\/+$/, '');
        const right = field.value.slice(end).replace(/^\/+/, '');
        const prefix = left ? `${left}/` : '';
        const suffix = right ? `/${right}` : '';
        field.value = `${prefix}${token}/${suffix}`.replace(/\/{2,}/g, '/');
        field.dispatchEvent(new Event('input', { bubbles: true }));
        field.focus();
      });
    });

    field.addEventListener('input', refreshTokens);
    refreshTokens();
  });
});