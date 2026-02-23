/**
 * Insolvenzo Form – Frontend Script
 *
 * JavaScript logic executed on the frontend for the Insolvenzo form.
 * Responsibilities:
 * - Handle client-side interactivity (steps, validation, toggles)
 * - Enhance the server-rendered HTML output
 * - Communicate with backend endpoints or Power Automate if needed
 *
 * Markup is rendered via PHP; this file only augments behavior.
 */

function initStepper(root) {
  const steps = Array.from(root.querySelectorAll('.insolvenzo-step'));
  if (!steps.length) return;

  const stepperSteps = Array.from(root.querySelectorAll('.insolvenzo-stepper-step'));
  const prevBtn = root.querySelector('[data-step-prev]');
  const nextBtn = root.querySelector('[data-step-next]');
  const submitBtn = root.querySelector('#submit-btn');

  let current = 0;

  function updateStepper(stepIndex) {
    stepperSteps.forEach((step, idx) => {
      const circle = step.querySelector('.insolvenzo-stepper-circle');
      if (idx < stepIndex) {
        step.classList.add('completed');
        step.classList.remove('active');
      } else if (idx === stepIndex) {
        step.classList.add('active');
        step.classList.remove('completed');
      } else {
        step.classList.remove('active', 'completed');
      }
    });
  }

  function show(i) {
    current = Math.max(0, Math.min(i, steps.length - 1));
    steps.forEach((s, idx) => {
      s.style.display = idx === current ? '' : 'none';
    });

    window.scrollTo({
      top: 0,
      behavior: 'smooth',
    });

    // Update horizontal stepper
    updateStepper(current);

    // Update button states
    if (prevBtn) {
      prevBtn.disabled = current === 0;
    }

    if (nextBtn && submitBtn) {
      if (current === steps.length - 1) {
        nextBtn.style.display = 'none';
        submitBtn.style.display = '';
      } else {
        nextBtn.style.display = '';
        submitBtn.style.display = 'none';
      }
    }

    root.dispatchEvent(new CustomEvent('insolvenzo:stepChange', { detail: { current } }));
  }

  // Wire Prev/Next buttons if present inside block
  if (prevBtn) {
    prevBtn.addEventListener('click', (e) => { 
      e.preventDefault(); 
      show(current - 1); 
    });
  }

  if (nextBtn) {
    nextBtn.addEventListener('click', (e) => {
      e.preventDefault();
      const nextIndex = Math.min(current + 1, steps.length - 1);
      const stepEl = steps[current];

      // Check if an element is inside a hidden ancestor
      function isInsideHiddenParent(el) {
        let node = el.parentElement;
        while (node && node !== stepEl) {
          if (node.style && node.style.display === 'none') return true;
          node = node.parentElement;
        }
        return false;
      }

      const required = Array.from(stepEl.querySelectorAll('[data-required]'));
      const seenRadioGroups = new Set();

      const invalid = required.filter((input) => {
        // Skip fields inside hidden containers
        if (isInsideHiddenParent(input)) return false;

        // Handle radio groups: check once per group name
        if (input.type === 'radio' && input.hasAttribute('data-required-radio')) {
          const groupName = input.getAttribute('data-required-radio');
          if (seenRadioGroups.has(groupName)) return false;
          seenRadioGroups.add(groupName);
          return !stepEl.querySelector(`input[name="${groupName}"]:checked`);
        }

        // Standard value check
        return !input.value || input.value.trim() === '';
      });

      if (invalid.length) {
        const firstInvalid = invalid[0];
        firstInvalid.focus();
        firstInvalid.style.borderColor = 'red';
        firstInvalid.style.outline = '2px solid red';
        setTimeout(() => {
          firstInvalid.style.borderColor = '';
          firstInvalid.style.outline = '';
        }, 2500);
        return;
      }

      show(nextIndex);
    });
  }

  // Make stepper circles clickable to navigate (optional enhancement)
  stepperSteps.forEach((step, idx) => {
    step.addEventListener('click', () => {
      // Only allow navigation to completed or current steps
      if (idx <= current) {
        show(idx);
      }
    });
  });

  // start
  show(0);
}

function initIssuerTypeToggle(root) {
  const step1 = root.querySelector('.insolvenzo-step[data-step-number="1"]');
  if (!step1) return;

  const issuerSelect = step1.querySelector('#issuer_type');
  const fieldsWithFirma = step1.querySelector('[data-issuer-fields="with-firma"]');
  const fieldsSb = step1.querySelector('[data-issuer-fields="schuldnerberatung"]');

  const hideAll = () => {
    if (fieldsWithFirma) fieldsWithFirma.style.display = 'none';
    if (fieldsSb) fieldsSb.style.display = 'none';
  };

  const update = () => {
    hideAll();
    if (!issuerSelect || !issuerSelect.value) return;

    if (issuerSelect.value === 'schuldnerberatung') {
      if (fieldsSb) fieldsSb.style.display = '';
    } else {
      if (fieldsWithFirma) fieldsWithFirma.style.display = '';
    }
  };

  // initial state
  hideAll();
  update();

  if (issuerSelect) {
    issuerSelect.addEventListener('change', update);
  }
}

function initStep3BasicCalculation(root) {
  const step3 = root.querySelector('.insolvenzo-step[data-step-number="3"]');
  if (!step3) return;

  const dependentsInput = step3.querySelector('#dependents_count');
  const considerPersonsEl = step3.querySelector('#considered_persons_count');
  const enhancementEl = step3.querySelector('#enhancement_amount');
  const perPersonInput = step3.querySelector('[name="unterhaltsperson_betrag"]');
  const erstePersonInput = step3.querySelector('[name="erste_person_betrag"]');

  const perPerson = perPersonInput && perPersonInput.value
    ? parseFloat(perPersonInput.value)
    : 326.04;

  const erstePerson = erstePersonInput && erstePersonInput.value
    ? parseFloat(erstePersonInput.value)
    : 585.23;

  function formatEUR(val) {
    return val.toLocaleString('de-DE', {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2
    });
  }

  const MAX_DEPENDENTS = 5;

  function recalc() {
    let dependents = Math.max(0, parseInt(dependentsInput.value || '0', 10));

    // Enforce maximum of 5
    if (dependents > MAX_DEPENDENTS) {
      dependents = MAX_DEPENDENTS;
      dependentsInput.value = MAX_DEPENDENTS;
    }

    let enhancement = 0;
    if (dependents >= 1) {
      enhancement = erstePerson + (dependents - 1) * perPerson;
    }

    considerPersonsEl.textContent = String(dependents);
    enhancementEl.innerHTML = `<strong>${formatEUR(enhancement)} €</strong>`;
  }

  if (dependentsInput) {
    dependentsInput.addEventListener('input', recalc);
    recalc();
  }
}

function initStep4Children(root) {
  const step4 = root.querySelector('.insolvenzo-step[data-step-number="4"]');
  if (!step4) return;

  // Toggle für Mehrbedarf "Sonstiges"
  const mehrbedarf = step4.querySelector('#mehrbedarf_type');
  const mehrbedarf_sonstige = step4.querySelector('#mehrbedarf_sonstige');
  if (mehrbedarf) {
    mehrbedarf.addEventListener('change', function() {
      if (mehrbedarf_sonstige) {
        mehrbedarf_sonstige.style.display = this.value === 'sonstig' ? 'block' : 'none';
      }
    });
  }

  // Toggle für einmalige Erfassung "Weitere regelmäßige Geldleistung für das Kind selbst"
  const kindSelbstArt = step4.querySelector('#kind_selbst_art');
  const kindSelbstSonstige = step4.querySelector('#kind_selbst_sonstige');

  if (kindSelbstArt) {
    const updateKindSelbstSonstige = () => {
      if (kindSelbstSonstige) {
        kindSelbstSonstige.style.display = kindSelbstArt.value === 'sonstige' ? 'block' : 'none';
      }
    };

    kindSelbstArt.addEventListener('change', updateKindSelbstSonstige);
    updateKindSelbstSonstige();
  }
}

function initStep5OneTime(root) {
  const step5 = root.querySelector('.insolvenzo-step[data-step-number="5"]');
  if (!step5) return;

  // 1. Sozialleistungen - Sonstiges Toggle
  const sozialleistung = step5.querySelector('#sozialleistung_type');
  const sozialleistung_sonstige = step5.querySelector('#sozialleistung_sonstige');
  if (sozialleistung) {
    sozialleistung.addEventListener('change', function() {
      if (sozialleistung_sonstige) {
        sozialleistung_sonstige.style.display = this.value === 'sonstige' ? 'block' : 'none';
      }
    });
  }

  // 1. Sozialleistungen - Nachweis Sonstiges Toggle
  const sozialleistung_nachweis_checks = step5.querySelectorAll('[name="sozialleistung_nachweis"]');
  const sozialleistung_nachweis_sonst = step5.querySelector('[name="sozialleistung_nachweis_sonst"]');
  sozialleistung_nachweis_checks.forEach(check => {
    check.addEventListener('change', function() {
      if (sozialleistung_nachweis_sonst) {
        const hasSonst = Array.from(sozialleistung_nachweis_checks).some(c => c.value === 'sonst' && c.checked);
        sozialleistung_nachweis_sonst.style.display = hasSonst ? 'block' : 'none';
      }
    });
  });

  // 2. Bundesrecht - Sonstiges Toggle
  const bundesrecht = step5.querySelector('#bundesrecht_type');
  const bundesrecht_sonstige = step5.querySelector('#bundesrecht_sonstige');
  if (bundesrecht) {
    bundesrecht.addEventListener('change', function() {
      if (bundesrecht_sonstige) {
        bundesrecht_sonstige.style.display = this.value === 'sonstige' ? 'block' : 'none';
      }
    });
  }

  // 2. Bundesrecht - Nachweis Sonstiges Toggle
  const bundesrecht_nachweis_checks = step5.querySelectorAll('[name="bundesrecht_nachweis"]');
  const bundesrecht_nachweis_sonst = step5.querySelector('[name="bundesrecht_nachweis_sonst"]');
  bundesrecht_nachweis_checks.forEach(check => {
    check.addEventListener('change', function() {
      if (bundesrecht_nachweis_sonst) {
        const hasSonst = Array.from(bundesrecht_nachweis_checks).some(c => c.value === 'sonst' && c.checked);
        bundesrecht_nachweis_sonst.style.display = hasSonst ? 'block' : 'none';
      }
    });
  });

  // 3. Nachzahlung Leistung - Sonstiges Toggle
  const nachzahlung_leistung = step5.querySelector('#nachzahlung_leistung_type');
  const nachzahlung_leistung_sonstige = step5.querySelector('#nachzahlung_leistung_sonstige');
  if (nachzahlung_leistung) {
    nachzahlung_leistung.addEventListener('change', function() {
      if (nachzahlung_leistung_sonstige) {
        nachzahlung_leistung_sonstige.style.display = this.value === 'sonstige' ? 'block' : 'none';
      }
    });
  }

  // 3. Nachzahlung Leistung - Nachweis Sonstiges Toggle
  const nachzahlung_leistung_nachweis_checks = step5.querySelectorAll('[name="nachzahlung_leistung_nachweis"]');
  const nachzahlung_leistung_nachweis_sonst = step5.querySelector('[name="nachzahlung_leistung_nachweis_sonst"]');
  nachzahlung_leistung_nachweis_checks.forEach(check => {
    check.addEventListener('change', function() {
      if (nachzahlung_leistung_nachweis_sonst) {
        const hasSonst = Array.from(nachzahlung_leistung_nachweis_checks).some(c => c.value === 'sonst' && c.checked);
        nachzahlung_leistung_nachweis_sonst.style.display = hasSonst ? 'block' : 'none';
      }
    });
  });

  // 4. Nachzahlung 500 - Sonstiges Toggle
  const nachzahlung_500 = step5.querySelector('#nachzahlung_500_type');
  const nachzahlung_500_sonstige = step5.querySelector('#nachzahlung_500_sonstige');
  if (nachzahlung_500) {
    nachzahlung_500.addEventListener('change', function() {
      if (nachzahlung_500_sonstige) {
        nachzahlung_500_sonstige.style.display = this.value === 'sonstige' ? 'block' : 'none';
      }
    });
  }

  // 5. Mutter und Kind - Toggle
  const mutter_kind_checkbox = step5.querySelector('[name="mutter_kind_stiftung"]');
  const mutter_kind_amount_wrapper = step5.querySelector('#mutter_kind_amount_wrapper');
  const mutter_kind_nachweis_wrapper = step5.querySelector('#mutter_kind_nachweis_wrapper');
  
  if (mutter_kind_checkbox) {
    mutter_kind_checkbox.addEventListener('change', function() {
      if (mutter_kind_amount_wrapper) {
        mutter_kind_amount_wrapper.style.display = this.checked ? 'block' : 'none';
      }
      if (mutter_kind_nachweis_wrapper) {
        mutter_kind_nachweis_wrapper.style.display = this.checked ? 'block' : 'none';
      }
    });
  }
}

function initInfoBoxCollapsibles(root) {
  const collapsibles = root.querySelectorAll('[data-insolvenzo-collapsible]');
  if (!collapsibles.length) return;

  collapsibles.forEach((box) => {
    const toggle = box.querySelector('.insolvenzo-info-box-toggle');
    const panel = box.querySelector('.insolvenzo-info-box-panel');
    if (!toggle || !panel) return;

    toggle.addEventListener('click', () => {
      const isExpanded = toggle.getAttribute('aria-expanded') === 'true';
      const nextState = !isExpanded;

      toggle.setAttribute('aria-expanded', nextState ? 'true' : 'false');
      panel.hidden = !nextState;
      box.classList.toggle('is-open', nextState);

      if (!nextState) {
        const video = panel.querySelector('video');
        if (video && !video.paused) {
          video.pause();
        }
      }
    });
  });
}

function addChild() {
  const container = document.getElementById('kindergeld_container');
  if (!container) return;

  const childCount = container.querySelectorAll('.insolvenzo-card-child').length;
  const newIndex = childCount;

  const newChild = document.createElement('div');
  newChild.className = 'insolvenzo-card insolvenzo-card-child';
  newChild.innerHTML = `
    <div class="insolvenzo-card-header">
      <h5>Kind ${childCount + 1}</h5>
      <button type="button" class="insolvenzo-btn-remove elementor-button" onclick="removeChildCard(this)">Entfernen</button>
    </div>
    <div class="insolvenzo-card-content">
      <div class="insolvenzo-form-row">
        <div class="insolvenzo-form-group" style="flex: 1;">
          <label>Geburtsmonat</label>
          <select name="kindergeld[${newIndex}][monat]">
            <option value="">-- Monat --</option>
            <option value="01">Januar</option>
            <option value="02">Februar</option>
            <option value="03">März</option>
            <option value="04">April</option>
            <option value="05">Mai</option>
            <option value="06">Juni</option>
            <option value="07">Juli</option>
            <option value="08">August</option>
            <option value="09">September</option>
            <option value="10">Oktober</option>
            <option value="11">November</option>
            <option value="12">Dezember</option>
          </select>
        </div>
        <div class="insolvenzo-form-group" style="flex: 1;">
          <label>Geburtsjahr</label>
          <input type="number" name="kindergeld[${newIndex}][jahr]" min="1950" max="2030" placeholder="YYYY" />
        </div>
      </div>
      
      <div class="insolvenzo-form-group">
        <label>Monatlicher Betrag (€)</label>
        <input type="number" name="kindergeld[${newIndex}][betrag]" step="0.01" placeholder="259,00 €" />
      </div>

      <div class="insolvenzo-form-group">
        <label>Nachweis liegt vor in Form von</label>
        <div class="insolvenzo-checkbox-group">
          <label><input type="checkbox" name="kindergeld[${newIndex}][nachweis]" value="bescheid" /> Kindergeldbescheid</label>
          <label><input type="checkbox" name="kindergeld[${newIndex}][nachweis]" value="lohnabr" /> Lohnabrechnung (bei Auszahlung über den Arbeitgeber)</label>
          <label><input type="checkbox" name="kindergeld[${newIndex}][nachweis]" value="konto" /> Kontoauszug</label>
        </div>
        <p class="insolvenzo-info-text"><small>Bei Nachweis durch Kontoauszug muss der Betrag mit dem eingetragenen Betrag übereinstimmen.</small></p>
      </div>
    </div>
  `;

  container.appendChild(newChild);
}

function removeChildCard(button) {
  const card = button.closest('.insolvenzo-card-child');
  if (card && window.confirm('Möchten Sie dieses Kind wirklich entfernen?')) {
    card.remove();
  }
}

function initStep2AccountToggle(root) {
  const step2 = root.querySelector('.insolvenzo-step[data-step-number="2"]');
  if (!step2) return;

  const select = step2.querySelector('#pkonto_kontoangaben');
  const kontoGrp = step2.querySelector('.pkonto-kontonummer');
  const ibanGrp = step2.querySelector('.pkonto-iban');
  const kontoInput = kontoGrp ? kontoGrp.querySelector('input') : null;
  const ibanInput = ibanGrp ? ibanGrp.querySelector('input') : null;

  function setRequired(el, required) {
    if (!el) return;
    if (required) {
      el.setAttribute('data-required', '');
      el.setAttribute('required', '');
    } else {
      el.removeAttribute('data-required');
      el.removeAttribute('required');
    }
  }

  function update() {
    if (!select) return;
    const v = select.value;
    if (v === 'kontonummer') {
      if (kontoGrp) kontoGrp.style.display = '';
      if (ibanGrp) ibanGrp.style.display = 'none';
      setRequired(kontoInput, true);
      setRequired(ibanInput, false);
    } else if (v === 'iban') {
      if (kontoGrp) kontoGrp.style.display = 'none';
      if (ibanGrp) ibanGrp.style.display = '';
      setRequired(kontoInput, false);
      setRequired(ibanInput, true);
    } else if (v === 'beide') {
      if (kontoGrp) kontoGrp.style.display = '';
      if (ibanGrp) ibanGrp.style.display = '';
      setRequired(kontoInput, true);
      setRequired(ibanInput, true);
    } else {
      // default: show both but none required
      if (kontoGrp) kontoGrp.style.display = '';
      if (ibanGrp) ibanGrp.style.display = '';
      setRequired(kontoInput, false);
      setRequired(ibanInput, false);
    }
  }

  if (select) {
    select.addEventListener('change', update);
    update();
  }
}

function setNestedValue(target, path, value) {
  let cursor = target;

  for (let i = 0; i < path.length; i += 1) {
    const key = path[i];
    const isLast = i === path.length - 1;

    if (isLast) {
      if (key === '') {
        if (!Array.isArray(cursor)) {
          return;
        }
        cursor.push(value);
      } else if (Object.prototype.hasOwnProperty.call(cursor, key)) {
        if (!Array.isArray(cursor[key])) {
          cursor[key] = [cursor[key]];
        }
        cursor[key].push(value);
      } else {
        cursor[key] = value;
      }
      return;
    }

    const nextKey = path[i + 1];

    if (key === '') {
      if (!Array.isArray(cursor)) {
        return;
      }

      const nextContainer = nextKey === '' || /^\d+$/.test(nextKey) ? [] : {};
      cursor.push(nextContainer);
      cursor = nextContainer;
      continue;
    }

    if (!Object.prototype.hasOwnProperty.call(cursor, key) || typeof cursor[key] !== 'object' || cursor[key] === null) {
      cursor[key] = (nextKey === '' || /^\d+$/.test(nextKey)) ? [] : {};
    }

    cursor = cursor[key];
  }
}

function parseFormKey(name) {
  const keys = [];
  const baseMatch = name.match(/^([^\[]+)/);

  if (!baseMatch) {
    return [name];
  }

  keys.push(baseMatch[1]);

  const bracketRegex = /\[([^\]]*)\]/g;
  let match;
  while ((match = bracketRegex.exec(name)) !== null) {
    keys.push(match[1]);
  }

  return keys;
}

function formDataToJson(formData) {
  const payload = {};

  formData.forEach((rawValue, rawName) => {
    if (rawName === '_wpnonce' || rawName === '_wp_http_referer' || rawName === '_wpnonce_rest') {
      return;
    }

    const keys = parseFormKey(rawName);
    const value = typeof rawValue === 'string' ? rawValue.trim() : rawValue;
    setNestedValue(payload, keys, value);
  });

  return payload;
}

function getCheckboxFieldPaths(form) {
  const paths = [];
  const seen = new Set();
  const checkboxes = form.querySelectorAll('input[type="checkbox"][name]');

  checkboxes.forEach((checkbox) => {
    const path = parseFormKey(checkbox.name);
    const id = JSON.stringify(path);

    if (!seen.has(id)) {
      seen.add(id);
      paths.push(path);
    }
  });

  return paths;
}

function getAccessorForKey(key) {
  return /^\d+$/.test(key) ? Number(key) : key;
}

function ensurePathIsArray(target, path) {
  let cursor = target;

  for (let i = 0; i < path.length; i += 1) {
    const key = path[i];
    const accessor = getAccessorForKey(key);
    const isLast = i === path.length - 1;
    const nextKey = path[i + 1];
    const nextIsNumeric = /^\d+$/.test(nextKey || '');

    if (isLast) {
      if (typeof cursor[accessor] === 'undefined') {
        cursor[accessor] = [];
      } else if (!Array.isArray(cursor[accessor])) {
        cursor[accessor] = [cursor[accessor]];
      }
      return;
    }

    if (typeof cursor[accessor] === 'undefined' || cursor[accessor] === null || typeof cursor[accessor] !== 'object') {
      cursor[accessor] = nextIsNumeric ? [] : {};
    }

    cursor = cursor[accessor];
  }
}

function showSubmitStatus(root, message, type = 'info') {
  let statusEl = root.querySelector('.insolvenzo-submit-status');
  if (!statusEl) {
    statusEl = document.createElement('div');
    statusEl.className = 'insolvenzo-submit-status';
    const nav = root.querySelector('.insolvenzo-form-nav');
    if (nav && nav.parentNode) {
      nav.parentNode.insertBefore(statusEl, nav.nextSibling);
    } else {
      root.appendChild(statusEl);
    }
  }

  statusEl.textContent = message;
  statusEl.style.marginTop = '12px';
  statusEl.style.padding = '10px 12px';
  statusEl.style.borderRadius = '6px';

  if (type === 'success') {
    statusEl.style.background = '#d1fae5';
    statusEl.style.color = '#065f46';
    statusEl.style.border = '1px solid #34d399';
  } else if (type === 'error') {
    statusEl.style.background = '#fee2e2';
    statusEl.style.color = '#991b1b';
    statusEl.style.border = '1px solid #f87171';
  } else {
    statusEl.style.background = '#eff6ff';
    statusEl.style.color = '#1e40af';
    statusEl.style.border = '1px solid #60a5fa';
  }
}

function initJsonSubmit(root) {
  const form = root.querySelector('form');
  if (!form) return;

  const submitBtn = form.querySelector('#submit-btn');

  form.addEventListener('submit', async (event) => {
    event.preventDefault();

    const nonceField = form.querySelector('[name="_wpnonce_rest"]');
    const restNonce = nonceField ? nonceField.value : '';

    if (!restNonce) {
      showSubmitStatus(root, 'Sicherheits-Token fehlt. Bitte Seite neu laden.', 'error');
      return;
    }

    const formData = new FormData(form);
    const payload = formDataToJson(formData);

    // `unterhaltsperson_betrag` gilt nur für weitere unterhaltspflichtige Personen
    // (ab der 2. Person). Bei 0 oder 1 Person keinen Betrag senden.
    const dependentsCount = parseInt(payload.dependents_count || '0', 10);
    if (Number.isNaN(dependentsCount) || dependentsCount < 2) {
      payload.unterhaltsperson_betrag = '';
    }

    const checkboxPaths = getCheckboxFieldPaths(form);
    checkboxPaths.forEach((path) => {
      ensurePathIsArray(payload, path);
    });

    if (!payload.contact_email || !String(payload.contact_email).includes('@')) {
      showSubmitStatus(root, 'Bitte eine gültige E-Mail-Adresse angeben.', 'error');
      const emailInput = form.querySelector('#contact_email');
      if (emailInput) emailInput.focus();
      return;
    }

    if (submitBtn) {
      submitBtn.disabled = true;
      submitBtn.dataset.originalText = submitBtn.textContent;
      submitBtn.textContent = 'Wird gesendet...';
    }

    showSubmitStatus(root, 'Formular wird gesendet ...', 'info');

    try {
      const response = await fetch('/wp-json/insolvenzo/v1/submit', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-WP-Nonce': restNonce,
        },
        body: JSON.stringify(payload),
      });

      let data = null;
      try {
        data = await response.json();
      } catch (jsonError) {
        data = null;
      }

      if (!response.ok) {
        const serverMessage = data && data.message ? data.message : 'Übermittlung fehlgeschlagen. Bitte erneut versuchen.';
        throw new Error(serverMessage);
      }

      showSubmitStatus(root, 'Vielen Dank! Das Formular wurde erfolgreich übermittelt.', 'success');
      form.reset();

      const prevBtn = root.querySelector('[data-step-prev]');
      if (prevBtn && prevBtn.disabled === false) {
        prevBtn.click();
        while (!prevBtn.disabled) {
          prevBtn.click();
        }
      }
    } catch (error) {
      showSubmitStatus(root, error.message || 'Fehler bei der Übermittlung.', 'error');
    } finally {
      if (submitBtn) {
        submitBtn.disabled = false;
        submitBtn.textContent = submitBtn.dataset.originalText || 'Abschicken';
      }
    }
  });
}

function initAll() {
  const blocks = document.querySelectorAll('.wp-block-insolvenzo-public-form, .insolvenzo-form');
  blocks.forEach((root) => {
    initInfoBoxCollapsibles(root);
    initStepper(root);
    initIssuerTypeToggle(root);
    initStep2AccountToggle(root);
    initStep3BasicCalculation(root);
    initStep4Children(root);
    initStep5OneTime(root);
    initJsonSubmit(root);
  });
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initAll);
} else {
  initAll();
}

// Make functions globally available for inline onclick handlers
window.addChild = addChild;
window.removeChildCard = removeChildCard;