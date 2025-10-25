/* Menú público: carrito con dirección, mesa y notas (API WhatsApp) — versión compatible ES5 */
var DATA = { platos: [], categorias: [], settings: null, uploadsBase: 'uploads' };
var CART = {}; // { id: { item, qty } }
var $ = function (s, ctx) { return (ctx || document).querySelector(s); };
var $$ = function (s, ctx) { return Array.prototype.slice.call((ctx || document).querySelectorAll(s)); };
var fmt = function (v) { return new Intl.NumberFormat("es-DO", { style: "currency", currency: "DOP" }).format(v || 0); };

var offcart = null;

document.addEventListener('DOMContentLoaded', init);

function val(obj, path, def) {
  try {
    var parts = path.split('.');
    var cur = obj;
    for (var i = 0; i < parts.length; i++) {
      if (cur == null) return def;
      cur = cur[parts[i]];
    }
    return (cur == null ? def : cur);
  } catch (e) { return def; }
}

function initFuse(list) {
  if (typeof Fuse !== 'function') return null;
  return new Fuse(list, { keys: ['nombre', 'descripcion', 'categoria_nombre'], threshold: 0.35 });
}

async function init() {
  offcart = new bootstrap.Offcanvas('#offCart');

  /* ✅ Detección automática de ruta base para API */
  var basePath = window.location.pathname.split('/').filter(Boolean);
  if (basePath.includes('public')) basePath.pop(); // quita “public” si existe
  var API = '/' + basePath.join('/') + '/api';
  console.log('📡 API base detectada:', API);

  try {
    // Carga datos desde la API
    var p = await fetch(API + '/platos/obtener.php').then(r => r.json());
    var c = await fetch(API + '/categories/obtener.php').then(r => r.json());
    var s = await fetch(API + '/settings/obtener.php').then(r => r.json());
  } catch (err) {
    console.error('❌ Error al conectar con la API:', err);
    return;
  }

  DATA.uploadsBase = p && p.uploadsBase ? p.uploadsBase : 'uploads';
  DATA.categorias = (c && c.data ? c.data : []).filter(x => String(x.estado) === '1');

  var catMap = {};
  DATA.categorias.forEach(k => { catMap[String(k.id)] = k.nombre; });

  DATA.platos = (p && p.data ? p.data : [])
    .filter(x => String(x.activo) === '1')
    .map(x => {
      x.categoria_nombre = catMap[String(x.categoria_id)] || 'Sin categoría';
      return x;
    });

  DATA.settings = s ? s.data : null;

  renderHeader();
  renderPills();
  renderGrid(DATA.platos);

  // 🔍 Búsqueda
  var fuse = initFuse(DATA.platos);
  $('#txtSearch').addEventListener('input', function (e) {
    var q = (e.target.value || '').trim();
    if (!q || !fuse) { renderGrid(DATA.platos); return; }
    var res = fuse.search(q).map(x => x.item);
    renderGrid(res);
  });

  // 🛒 Botones fijos
  $('#btnCart').addEventListener('click', function () { offcart.show(); });
  $('#btnSendWA').addEventListener('click', sendWA);
  $('#btnClear').addEventListener('click', function () { CART = {}; updateCartUI(); });

  $('#grid').addEventListener('click', onGridClick);
  $('#cartList').addEventListener('click', onCartClick);

  updateCartUI();
}

/* ===== Render ===== */
function renderHeader() {
  var title = val(DATA, 'settings.nombre_negocio', 'Menú');
  $('#bizName').textContent = title;
  var img = $('#bizLogo');
  var logo = val(DATA, 'settings.logo', null);
  if (logo) {
    img.src = '../' + DATA.uploadsBase + '/' + logo;
    img.classList.remove('d-none');
  } else {
    img.classList.add('d-none');
  }
}

function renderPills() {
  var box = $('#catPills');
  box.innerHTML = '';

  function makePill(text, onClick) {
    var pill = document.createElement('span');
    pill.className = 'pill';
    pill.textContent = text;
    pill.onclick = function () {
      $$('.pill').forEach(p => p.classList.remove('active'));
      pill.classList.add('active');
      onClick();
    };
    box.appendChild(pill);
    return pill;
  }

  var all = makePill('Todos', function () { renderGrid(DATA.platos); });
  all.classList.add('active');

  DATA.categorias.forEach(function (cat) {
    makePill(cat.nombre, function () {
      var filtered = DATA.platos.filter(p => String(p.categoria_id) === String(cat.id));
      renderGrid(filtered);
    });
  });
}

function renderGrid(items) {
  var grid = $('#grid');
  grid.innerHTML = '';
  if (!items.length) {
    grid.innerHTML = '<div class="col-12 text-center text-secondary">No hay productos</div>';
    return;
  }
  var fallback = 'https://via.placeholder.com/800x600?text=Sin+imagen';

  items.forEach(function (p) {
    var col = document.createElement('div');
    col.className = 'col-12 col-sm-6';
    var img = p.imagen ? ('../' + DATA.uploadsBase + '/' + p.imagen) : fallback;
    col.innerHTML =
      '<div class="card-item h-100">' +
      '<div class="img-wrap">' +
      '<img class="img-cover" loading="lazy" src="' + img + '" alt="' + escapeHtml(p.nombre) + '">' +
      '<div class="img-gradient"></div>' +
      '<div class="cat-chip">' + escapeHtml(p.categoria_nombre) + '</div>' +
      '<div class="img-info">' +
      '<div class="img-title">' + escapeHtml(p.nombre) + (p.destacado ? ' ⭐' : '') + '</div>' +
      '<div class="img-price">' + fmt(p.precio) + '</div>' +
      '</div>' +
      '</div>' +
      '<div class="card-body">' +
      '<div class="card-desc">' + escapeHtml(p.descripcion || '') + '</div>' +
      '<button class="btn btn-accent mt-3 w-100" data-add="' + p.id + '">' +
      '<i class="bi bi-cart-plus me-1"></i>Agregar' +
      '</button>' +
      '</div>' +
      '</div>';
    grid.appendChild(col);
  });
}

/* ===== Eventos ===== */
function onGridClick(ev) {
  var btn = ev.target.closest ? ev.target.closest('button[data-add]') : null;
  if (!btn) return;
  var id = String(btn.getAttribute('data-add'));
  var item = DATA.platos.find(x => String(x.id) === id);
  if (!item) return;
  CART[id] = CART[id] || { item: item, qty: 0 };
  CART[id].qty += 1;
  updateCartUI();
  pingBadge();
}

function onCartClick(ev) {
  var decBtn = ev.target.closest ? ev.target.closest('button[data-dec]') : null;
  var incBtn = ev.target.closest ? ev.target.closest('button[data-inc]') : null;
  var delBtn = ev.target.closest ? ev.target.closest('button[data-del]') : null;
  var id = (decBtn && decBtn.getAttribute('data-dec')) ||
    (incBtn && incBtn.getAttribute('data-inc')) ||
    (delBtn && delBtn.getAttribute('data-del'));
  if (!id) return;

  if (decBtn) {
    CART[id].qty -= 1;
    if (CART[id].qty <= 0) delete CART[id];
  }
  if (incBtn) {
    CART[id].qty += 1;
  }
  if (delBtn) {
    delete CART[id];
  }
  updateCartUI();
}

/* ===== Carrito ===== */
function updateCartUI() {
  var list = $('#cartList'); list.innerHTML = '';
  var total = 0, qty = 0;
  var fallback = 'https://via.placeholder.com/300x200?text=IMG';

  Object.keys(CART).forEach(function (k) {
    var item = CART[k].item;
    var q = CART[k].qty;
    total += (item.precio * q); qty += q;

    var thumb = item.imagen ? ('../' + DATA.uploadsBase + '/' + item.imagen) : fallback;

    var row = document.createElement('div');
    row.className = 'cart-item';
    row.innerHTML =
      '<img class="cart-thumb" src="' + thumb + '" alt="">' +
      '<div class="cart-info">' +
      '<div class="cart-header">' +
      '<div>' +
      '<div class="cart-name">' + escapeHtml(item.nombre) + '</div>' +
      '<div class="cart-cat">' + escapeHtml(item.categoria_nombre || '') + '</div>' +
      '</div>' +
      '<div class="cart-price">' + fmt(item.precio * q) + '</div>' +
      '</div>' +
      '<div class="cart-unit">' + fmt(item.precio) + ' c/u</div>' +
      '<div class="cart-footer">' +
      '<div class="qty-group">' +
      '<button class="btn-qty" title="Quitar" data-dec="' + item.id + '"><i class="bi bi-dash-lg"></i></button>' +
      '<span class="fw-bold">' + q + '</span>' +
      '<button class="btn-qty" title="Agregar" data-inc="' + item.id + '"><i class="bi bi-plus-lg"></i></button>' +
      '<button class="btn-qty delete" title="Eliminar" data-del="' + item.id + '"><i class="bi bi-trash3"></i></button>' +
      '</div>' +
      '</div>' +
      '</div>';

    list.appendChild(row);
  });

  $('#cartTotal').textContent = fmt(total);
  $('#cartItems').textContent = qty;

  var badge = $('#badgeQty');
  if (qty > 0) { badge.textContent = qty; badge.classList.remove('d-none'); }
  else { badge.classList.add('d-none'); }
}

/* ===== WhatsApp (API) ===== */
function sendWA() {
  if (!Object.keys(CART).length) return;

  var phone = (val(DATA, 'settings.whatsapp', '') || '').replace(/\D+/g, '');
  var nombre = ($('#nombreCliente').value || '').trim();
  var direccion = ($('#direccionCliente').value || '').trim();
  var mesa = ($('#mesaCliente').value || '').trim();
  var notas = ($('#notasCliente').value || '').trim();

  var lines = [];
  lines.push('*' + (val(DATA, 'settings.nombre_negocio', 'Pedido')) + '*');
  if (nombre) lines.push('Cliente: ' + nombre);
  if (direccion) lines.push('Dirección: ' + direccion);
  if (mesa) lines.push('Mesa: ' + mesa);
  if (notas) lines.push('Nota: ' + notas);
  lines.push('');
  lines.push('*Detalle del pedido:*');

  var total = 0;
  Object.keys(CART).forEach(function (k) {
    var it = CART[k].item, q = CART[k].qty;
    var lineTotal = it.precio * q;
    total += lineTotal;
    lines.push('• ' + it.nombre + ' x' + q + ' - ' + fmt(lineTotal));
  });

  lines.push('');
  lines.push('*Total:* ' + fmt(total));

  var message = encodeURIComponent(lines.join('\n'));
  var url = 'https://api.whatsapp.com/send?phone=' + phone + '&text=' + message;
  window.open(url, '_blank');
}

/* ===== Utils ===== */
function escapeHtml(s) {
  s = (s == null ? '' : String(s));
  return s.replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');
}

function pingBadge() {
  var badge = $('#badgeQty');
  if (badge.classList.contains('d-none')) return;
  if (badge.animate) {
    badge.animate([{ transform: 'scale(1)' }, { transform: 'scale(1.25)' }, { transform: 'scale(1)' }], {
      duration: 250, easing: 'ease-out'
    });
  }
}
