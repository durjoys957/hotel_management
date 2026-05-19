/* LuxStay Hotel — Main JS */

// Flash auto-dismiss
document.querySelectorAll('.flash').forEach(el => {
  setTimeout(() => el.style.display = 'none', 5000);
});

// Modal helpers
function openModal(id) {
  const el = document.getElementById(id);
  if (el) el.classList.add('open');
}
function closeModal(id) {
  const el = document.getElementById(id);
  if (el) el.classList.remove('open');
}
document.querySelectorAll('.modal-overlay').forEach(overlay => {
  overlay.addEventListener('click', e => {
    if (e.target === overlay) overlay.classList.remove('open');
  });
});

// Stars rating widget
document.querySelectorAll('.star-input').forEach(container => {
  const stars = container.querySelectorAll('label');
  stars.forEach((star, i) => {
    star.addEventListener('mouseover', () => {
      stars.forEach((s, j) => s.classList.toggle('active', j <= i));
    });
    star.addEventListener('mouseout', () => {
      const checked = container.querySelector('input:checked');
      const checkedIdx = checked ? parseInt(checked.value) - 1 : -1;
      stars.forEach((s, j) => s.classList.toggle('active', j <= checkedIdx));
    });
  });
});

// Confirm dialogs
document.querySelectorAll('[data-confirm]').forEach(el => {
  el.addEventListener('click', e => {
    if (!confirm(el.dataset.confirm)) e.preventDefault();
  });
});

// Generic AJAX helper
function ajax(url, data, callback) {
  const xhr = new XMLHttpRequest();
  xhr.open('POST', url, true);
  xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
  xhr.onload = function () {
    if (xhr.status === 200) {
      try {
        callback(null, JSON.parse(xhr.responseText));
      } catch (e) {
        callback('Invalid JSON: ' + xhr.responseText, null);
      }
    } else {
      callback('HTTP Error: ' + xhr.status, null);
    }
  };
  xhr.onerror = () => callback('Network error', null);
  if (data) {
    const params = Object.entries(data).map(([k, v]) => encodeURIComponent(k) + '=' + encodeURIComponent(v)).join('&');
    xhr.send(params);
  } else {
    xhr.send();
  }
}

function ajaxGet(url, callback) {
  const xhr = new XMLHttpRequest();
  xhr.open('GET', url, true);
  xhr.onload = function () {
    if (xhr.status === 200) {
      try { callback(null, JSON.parse(xhr.responseText)); }
      catch (e) { callback('Parse error', null); }
    } else {
      callback('HTTP ' + xhr.status, null);
    }
  };
  xhr.onerror = () => callback('Network error', null);
  xhr.send();
}
