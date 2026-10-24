document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('[data-dialog-open]').forEach((button) => {
    button.addEventListener('click', () => {
      const dialog = document.getElementById(button.dataset.dialogOpen);
      if (dialog instanceof HTMLDialogElement) {
        dialog.showModal();
      }
    });
  });

  document.querySelectorAll('.event-banner, .admin-event-thumb').forEach((image) => {
    const replaceWithFallback = () => {
      if (image.classList.contains('event-banner')) {
        const fallback = document.createElement('div');
        fallback.className = 'event-banner event-banner-empty';
        fallback.setAttribute('aria-hidden', 'true');
        const label = document.createElement('span');
        label.textContent = 'Campus Events';
        fallback.append(label);
        image.replaceWith(fallback);
        return;
      }

      const fallback = document.createElement('span');
      fallback.className = 'admin-event-thumb admin-event-thumb-empty';
      fallback.setAttribute('aria-hidden', 'true');
      fallback.textContent = 'CE';
      image.replaceWith(fallback);
    };

    if (image.complete && image.naturalWidth === 0) {
      replaceWithFallback();
    } else {
      image.addEventListener('error', replaceWithFallback, { once: true });
    }
  });

  document.querySelectorAll('form[data-validate]').forEach((form) => {
    const matchingInputs = form.querySelectorAll('[data-match]');

    const validateMatches = () => {
      matchingInputs.forEach((input) => {
        const original = form.elements.namedItem(input.dataset.match);
        input.setCustomValidity(original && input.value !== original.value ? 'Passwords do not match.' : '');
      });
    };

    matchingInputs.forEach((input) => input.addEventListener('input', validateMatches));
    form.addEventListener('submit', (event) => {
      validateMatches();
      if (!form.reportValidity()) {
        event.preventDefault();
        return;
      }

      const submitButton = form.querySelector('button[type="submit"]');
      if (submitButton && !form.dataset.submitting) {
        form.dataset.submitting = 'true';
        submitButton.disabled = true;
        submitButton.setAttribute('aria-busy', 'true');
      }
    });
  });
});
