<?php /* Menú público con carrito mejorado + dirección/mesa/notas */ ?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <title>Menú</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link href="./styles.css" rel="stylesheet">
</head>
<body>
<div class="container py-4">
  <div id="header" class="d-flex align-items-center gap-3 mb-4">
    <img id="bizLogo" class="biz-logo d-none" alt="logo">
    <div class="flex-grow-1">
      <h1 id="bizName" class="biz-title mb-0">Menú</h1>
      <small class="biz-subtitle">Menu digital, haz tu pedido por WhatsApp</small>
    </div>
  </div>

  <div class="row g-2 mb-3">
    <div class="col-12">
      <div class="input-icon">
        <i class="bi bi-search"></i>
        <input id="txtSearch" class="form-control form-control-lg input-search" placeholder="Buscar producto...">
      </div>
    </div>
    <div class="col-12">
      <div id="catPills" class="d-flex flex-wrap gap-2"></div>
    </div>
  </div>

  <div id="grid" class="row g-3"></div>
</div>

<!-- Botón carrito flotante -->
<div class="cart-fab">
  <button id="btnCart" class="btn btn-accent position-relative">
    <i class="bi bi-cart3 me-1"></i> Carrito
    <span id="badgeQty" class="badge badge-dot d-none">0</span>
  </button>
</div>

<!-- Offcanvas Carrito -->
<div class="offcanvas offcanvas-end offcart" tabindex="-1" id="offCart">
  <div class="offcanvas-header border-bottom border-surface">
    <h5 class="offcanvas-title"><i class="bi bi-cart-check me-2"></i>Tu pedido</h5>
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
  </div>

  <div class="offcanvas-body">
    <!-- Datos del cliente -->
    <div class="form-section">
      <div class="form-section-title"><i class="bi bi-person"></i> Datos del cliente</div>
      <div class="row g-2">
        <div class="col-12">
          <label class="form-label small text-secondary">Nombre (opcional)</label>
          <input id="nombreCliente" class="form-control" placeholder="Tu nombre...">
        </div>
        <div class="col-12">
          <label class="form-label small text-secondary">Dirección (opcional)</label>
          <input id="direccionCliente" class="form-control" placeholder="Ej.: Av. Duarte #123, 2do piso">
        </div>
        <div class="col-12">
          <label class="form-label small text-secondary">Mesa (opcional)</label>
          <input id="mesaCliente" class="form-control" placeholder="Ej.: Mesa 5">
        </div>
        <div class="col-12">
          <label class="form-label small text-secondary">Notas / Indicaciones</label>
          <textarea id="notasCliente" class="form-control" rows="2"
            placeholder="Ej.: sin cebolla, poca mayonesa, bien cocido, poco hielo"></textarea>
        </div>
      </div>
    </div>

    <!-- Lista del carrito -->
    <div id="cartList" class="vstack gap-2"></div>
  </div>

  <!-- Footer fijo con totales -->
  <div class="offcart-footer border-top border-surface">
    <div class="d-flex justify-content-between align-items-center mb-2">
      <span class="fw-semibold">Artículos</span>
      <span id="cartItems" class="text-weak">0</span>
    </div>
    <div class="d-flex justify-content-between align-items-center mb-3">
      <span class="fw-bold fs-5">Total</span>
      <span id="cartTotal" class="fs-5 fw-extrabold text-total">RD$ 0.00</span>
    </div>
    <div class="d-grid gap-2">
      <button id="btnSendWA" class="btn btn-wa">
        <i class="bi bi-whatsapp me-2"></i>Enviar por WhatsApp
      </button>
      <button id="btnClear" class="btn btn-outline-light">
        <i class="bi bi-trash3 me-1"></i> Vaciar carrito
      </button>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
  (function(){
    const parts = location.pathname.split('/').filter(Boolean);
    const appBase = '/' + (parts[0] || '');
    window.API_BASE = appBase + '/api';
  })();
</script>
<script src="https://cdn.jsdelivr.net/npm/fuse.js@7.0.0"></script>
<script src="./menu.js"></script>
</body>
</html>
