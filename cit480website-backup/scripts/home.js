// /scripts/home.js
const API = '/api';

const esc = (s) => String(s ?? '').replace(/[&<>"']/g, c => (
  { '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;', "'":'&#39;' }[c]
));

function cardTd(v) {
  return `
    <td>
      <a href="venue.html?id=${encodeURIComponent(v.id)}">
        <div class="card" style="width: 300px;">
          <img src="https://placehold.co/300x160" class="card-img-top" alt="${esc(v.name)}">
          <div class="card-body">
            <h5>${esc(v.name)}</h5>
            <div class="text-muted small">
              ${esc([v.city, v.state].filter(Boolean).join(', '))}
              ${v.avg_rating != null ? ` • ⭐ ${v.avg_rating} (${v.review_count ?? 0})` : ''}
            </div>
          </div>
        </div>
      </a>
    </td>
  `;
}

async function getJSON(url) {
  const r = await fetch(url, { credentials: 'include' });
  if (!r.ok) throw new Error(`HTTP ${r.status}`);
  return r.json();
}

async function loadHomeRows() {
  try {
    const recent = await getJSON(`${API}/venues_recent.php`);
    const row = document.getElementById('recently-reviewed-row');
    if (row) row.innerHTML = recent.map(cardTd).join('');
  } catch (e) { console.error('recently-reviewed error', e); }

  try {
    const popular = await getJSON(`${API}/venues_popular.php`);
    const row = document.getElementById('popular-venues-row');
    if (row) row.innerHTML = popular.map(cardTd).join('');
  } catch (e) { console.error('popular-venues error', e); }
}

function wireSearch() {
  const form = document.querySelector('#search-container form');
  const input = document.getElementById('input-field');
  if (!form || !input) return;

  // If you already have a server /search route, remove preventDefault below.
  form.addEventListener('submit', (e) => {
    e.preventDefault();
    const q = input.value.trim();
    if (!q) return;
    // Navigate to a client results page that reads ?q= and calls /api/venues.php?q=...
    window.location.href = `venues.html?q=${encodeURIComponent(q)}`;
  });
}

window.addEventListener('DOMContentLoaded', () => {
  wireSearch();
  loadHomeRows();
});
