import Selectpicker from '../../../node_modules/@crestapps/bootstrap-select/dist/js/bootstrap-select.min.js';

const root = document;

const initializeField = (field, options = {}) => {
  const modal = field.closest?.('.modal');
  if (modal && !modal.classList.contains('show')) {
    if (!field.__licencepressModalSelectHandler) {
      field.__licencepressModalSelectHandler = () => {
        field.__licencepressModalSelectHandler = null;
        initializeField(field, options);
      };
      modal.addEventListener('shown.bs.modal', field.__licencepressModalSelectHandler, { once: true });
    }
    return null;
  }

  const instance = Selectpicker.getOrCreateInstance(field, options);

  if (instance.button && !instance.__licencepressClickHandler) {
    instance.__licencepressClickHandler = (event) => {
      event.preventDefault();
      event.stopPropagation();
      instance.toggle(event);
    };
    instance.button.addEventListener('click', instance.__licencepressClickHandler);
  }

  return instance;
};

window.licencepressBootstrapSelect = {
  initialize: initializeField,
};

const initialize = (scope = root) => {
  if (!scope.querySelectorAll) return;

  scope.querySelectorAll('.selectpicker').forEach((field) => {
    initializeField(field);
  });
};

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', () => initialize(), { once: true });
} else {
  initialize();
}

