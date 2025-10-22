<?php require __DIR__.'/_auth.php'; ?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <title>Admin · Menú Digital</title>
  <base href="/menudigitalprueba/">
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <!-- DataTables -->
  <link href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css" rel="stylesheet">
  <!-- SweetAlert2 -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>
    :root{ --card-radius:16px; }
    body{ background:#f5f7fb; }
    .card{ border:0; border-radius:var(--card-radius); box-shadow:0 10px 30px rgba(0,0,0,.05); }
    .img-thumb{ width:72px; height:72px; object-fit:cover; border-radius:10px; background:#e5e7eb; }
    .badge-on{ background:#16a34a!important; }
    .badge-off{ background:#6b7280!important; }
    .table thead th{ white-space:nowrap; }
    .app-header {
      background: linear-gradient(100deg,#0ea5e9 0%,#2563eb 100%);
      color:#fff; border-radius:20px; padding:16px 20px; box-shadow:0 8px 22px rgba(37,99,235,.25);
    }
    .app-header h1{ font-size:1.25rem; margin:0; }
    .app-header small{ opacity:.95 }
    .btn-icon{ display:inline-flex; align-items:center; gap:.45rem }
    @media (max-width: 992px){
      .card .card-header{ gap:.5rem; flex-wrap:wrap }
      .btn-sm{ padding:.45rem .7rem; }
      .app-header{ border-radius:12px; }
    }
  </style>
</head>
<body>
<div class="container-fluid p-3 p-sm-4">
  <div class="app-header d-flex align-items-center justify-content-between mb-4">
    <div class="d-flex align-items-center gap-3">
      <i class="bi bi-layout-text-sidebar-reverse fs-3"></i>
      <div>
        <h1>Panel administrador</h1>
        <small>Organiza categorías y platos de tu menú</small>
      </div>
    </div>
    <div class="d-flex gap-2">
      <a class="btn btn-light btn-sm btn-icon" href="./logout.php"><i class="bi bi-box-arrow-right"></i> Salir</a>
      <a class="btn btn-dark btn-sm btn-icon" target="_blank" href="../public/"><i class="bi bi-eye"></i> Ver menú público</a>
    </div>
  </div>

  <!-- Configuración -->
  <div class="card mb-4">
    <div class="card-header d-flex align-items-center gap-2">
      <i class="bi bi-gear"></i><span class="fw-semibold">Configuración del negocio</span>
    </div>
    <div class="card-body">
      <form id="formSettings" class="row g-3" enctype="multipart/form-data">
        <div class="col-12 col-md-4">
          <label class="form-label">Nombre del negocio</label>
          <input type="text" name="nombre_negocio" class="form-control" required>
        </div>
        <div class="col-12 col-md-4">
          <label class="form-label">WhatsApp (solo dígitos)</label>
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-whatsapp"></i></span>
            <input type="text" name="whatsapp" class="form-control" required placeholder="18099150634">
          </div>
        </div>
        <div class="col-12 col-md-4">
          <label class="form-label">Logo (opcional)</label>
          <input type="file" name="logo" class="form-control" accept="image/*">
        </div>
        <div class="col-12 d-flex align-items-center gap-3">
          <img id="logoPreview" class="img-thumb d-none" alt="logo">
          <button class="btn btn-primary btn-icon"><i class="bi bi-save2"></i> Guardar</button>
        </div>
      </form>
    </div>
  </div>

  <div class="row g-3">
    <!-- Categorías -->
    <div class="col-12 col-lg-4">
      <div class="card h-100">
        <div class="card-header d-flex justify-content-between align-items-center">
          <div class="d-flex align-items-center gap-2">
            <i class="bi bi-tags"></i><span class="fw-semibold">Categorías</span>
          </div>
          <button class="btn btn-sm btn-success btn-icon" id="btnNuevaCat"><i class="bi bi-plus-circle"></i> Nueva</button>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table id="tblCats" class="table table-sm table-striped align-middle w-100">
              <thead><tr><th>Nombre</th><th>Estado</th><th>Orden</th><th></th></tr></thead>
              <tbody></tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- Platos -->
    <div class="col-12 col-lg-8">
      <div class="card h-100">
        <div class="card-header d-flex justify-content-between align-items-center">
          <div class="d-flex align-items-center gap-2">
            <i class="bi bi-basket2"></i><span class="fw-semibold">Platos</span>
          </div>
          <button class="btn btn-sm btn-success btn-icon" id="btnNuevoPlato"><i class="bi bi-plus-circle"></i> Nuevo</button>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table id="tblPlatos" class="table table-sm table-striped align-middle w-100">
              <thead>
                <tr>
                  <th>Img</th><th>Nombre</th><th>Categoría</th><th>Precio</th>
                  <th>Dest</th><th>Activo</th><th>Orden</th><th></th>
                </tr>
              </thead>
              <tbody></tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal Categoría -->
<div class="modal fade" id="modalCat" tabindex="-1">
  <div class="modal-dialog">
    <form id="formCat" class="modal-content">
      <div class="modal-header"><h5 class="modal-title"><i class="bi bi-tag"></i> Categoría</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <input type="hidden" name="id">
        <div class="mb-2">
          <label class="form-label">Nombre</label>
          <input type="text" name="nombre" class="form-control" required>
        </div>
        <div class="row g-2">
          <div class="col-6">
            <label class="form-label">Estado</label>
            <select name="estado" class="form-select">
              <option value="1">Activo</option>
              <option value="0">Inactivo</option>
            </select>
          </div>
          <div class="col-6">
            <label class="form-label">Orden</label>
            <input type="number" name="orden" class="form-control" value="0">
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-primary btn-icon"><i class="bi bi-save2"></i> Guardar</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Plato -->
<div class="modal fade" id="modalPlato" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <form id="formPlato" class="modal-content" enctype="multipart/form-data">
      <div class="modal-header"><h5 class="modal-title"><i class="bi bi-basket"></i> Plato</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <input type="hidden" name="id">
        <div class="row g-3">
          <div class="col-12 col-md-6">
            <label class="form-label">Nombre</label>
            <input type="text" name="nombre" class="form-control" required>
          </div>
          <div class="col-6 col-md-3">
            <label class="form-label">Precio</label>
            <input type="number" name="precio" step="0.01" min="0" class="form-control" required>
          </div>
          <div class="col-6 col-md-3">
            <label class="form-label">Categoría</label>
            <select name="categoria_id" class="form-select" required></select>
          </div>
          <div class="col-12">
            <label class="form-label">Descripción (opcional)</label>
            <textarea name="descripcion" class="form-control" rows="2"></textarea>
          </div>
          <div class="col-12 col-md-4">
            <label class="form-label">Imagen (opcional)</label>
            <input type="file" name="imagen" class="form-control" accept="image/*">
          </div>
          <div class="col-6 col-md-2">
            <label class="form-label d-block">Destacado</label>
            <select name="destacado" class="form-select">
              <option value="0">No</option>
              <option value="1">Sí</option>
            </select>
          </div>
          <div class="col-6 col-md-2">
            <label class="form-label d-block">Activo</label>
            <select name="activo" class="form-select">
              <option value="1">Sí</option>
              <option value="0">No</option>
            </select>
          </div>
          <div class="col-6 col-md-2">
            <label class="form-label">Orden</label>
            <input type="number" name="orden" class="form-control" value="0">
          </div>
          <div class="col-6 col-md-2 d-flex align-items-center">
            <img id="platoPreview" class="img-thumb d-none" alt="preview">
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-primary btn-icon"><i class="bi bi-save2"></i> Guardar</button>
      </div>
    </form>
  </div>
</div>

<!-- JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
<script>
  const API_BASE = '../api';
  const UPLOADS_BASE = '../uploads'; // ← ajusta si tu carpeta de imágenes es otra
</script>
<script src="admin/admin.js"></script>
</body>
</html>
