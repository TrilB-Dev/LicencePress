document.addEventListener('DOMContentLoaded', () => {
  const root = document;

  const resetScope = root.querySelector('#licencepress-reset-scope');
  const resetPlugins = root.querySelector('#licencepress-reset-plugins');

  if (resetScope && resetPlugins) {
    const applyResetScopeState = () => {
      const isPluginsScope = resetScope.value === 'plugins';
      resetPlugins.hidden = !isPluginsScope;
      resetPlugins.querySelectorAll('input[type="checkbox"]').forEach((checkbox) => {
        checkbox.disabled = !isPluginsScope;
      });
    };

    resetScope.addEventListener('change', applyResetScopeState);
    applyResetScopeState();
  }

  root.querySelectorAll('.licencepress-editor-form').forEach((form) => {
    form.addEventListener('submit', () => {
      const submit = form.querySelector('[type="submit"]');
      if (submit) submit.disabled = true;
    });
  });
});