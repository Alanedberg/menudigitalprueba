/* admin/admin.js */
const fmt = v => new Intl.NumberFormat("es-DO",{style:"currency",currency:"DOP"}).format(v||0);

// si el backend devuelve solo el nombre de archivo, arma la ruta completa
const resolveImg = (p) => {
  if (!p) return "";
  // ya es absoluta (http, /, data:)
  if (/^(https?:)?\/\//.test(p) || p.startsWith("/") || p.startsWith("data:")) return p;
  // evita dobles slash
  const base = (typeof UPLOADS_BASE !== 'undefined' ? UPLOADS_BASE : "../uploads").replace(/\/+$/,"");
  const file = p.replace(/^\/+/,"");
  return `${base}/${file}`;
};

let dtCats, dtPlatos;
let modalCat, modalPlato;

document.addEventListener('DOMContentLoaded', init);

function init(){
  modalCat   = new bootstrap.Modal('#modalCat');
  modalPlato = new bootstrap.Modal('#modalPlato');

  // Tablas
  dtCats = $('#tblCats').DataTable({
    ajax: { url: `${API_BASE}/categories/obtener.php`, dataSrc: 'data' },
    pageLength: 10,
    columns: [
      { data: 'nombre' },
      { data: 'estado', render: v => v==1 ? '<span class="badge badge-on">Activo</span>' : '<span class="badge badge-off">Inactivo</span>' },
      { data: 'orden' },
      { data: null, orderable:false, searchable:false,
        render: row => `
          <div class="btn-group btn-group-sm">
            <button class="btn btn-outline-primary" onclick='editCat(${JSON.stringify(row).replace(/'/g,"&#39;")})'><i class="bi bi-pencil-square"></i></button>
            <button class="btn btn-outline-danger" onclick='delCat(${row.id})'><i class="bi bi-trash3"></i></button>
          </div>`
      }
    ],
    language: { url: 'https://cdn.datatables.net/plug-ins/1.13.8/i18n/es-ES.json' }
  });

  dtPlatos = $('#tblPlatos').DataTable({
    ajax: { url: `${API_BASE}/platos/obtener.php`, dataSrc: 'data' },
    pageLength: 10,
    columns: [
      { data: 'imagen', orderable:false, render: img => {
          const src = resolveImg(img);
          return src ? `<img class="img-thumb" src="${src}" alt="">` : `<span class="text-muted">—</span>`;
        }
      },
      { data: 'nombre' },
      { data: 'categoria_nombre', defaultContent: '-' },
      { data: 'precio', render: v => fmt(v) },
      { data: 'destacado', render: v => v==1 ? '<i class="bi bi-star-fill text-warning"></i>' : '' },
      { data: 'activo', render: v => v==1 ? '<span class="badge badge-on">Sí</span>' : '<span class="badge badge-off">No</span>' },
      { data: 'orden' },
      { data: null, orderable:false, searchable:false,
        render: row => `
          <div class="btn-group btn-group-sm">
            <button class="btn btn-outline-primary" onclick='editPlato(${JSON.stringify(row).replace(/'/g,"&#39;")})'><i class="bi bi-pencil-square"></i></button>
            <button class="btn btn-outline-danger" onclick='delPlato(${row.id})'><i class="bi bi-trash3"></i></button>
          </div>`
      }
    ],
    language: { url: 'https://cdn.datatables.net/plug-ins/1.13.8/i18n/es-ES.json' }
  });

  // Botones
  document.getElementById('btnNuevaCat').addEventListener('click', () => {
    document.getElementById('formCat').reset();
    document.querySelector('#formCat [name="id"]').value = '';
    modalCat.show();
  });

  document.getElementById('btnNuevoPlato').addEventListener('click', async () => {
    await cargarCategoriasSelect();
    document.getElementById('formPlato').reset();
    document.querySelector('#formPlato [name="id"]').value = '';
    document.getElementById('platoPreview').classList.add('d-none');
    modalPlato.show();
  });

  // Forms
  document.getElementById('formCat').addEventListener('submit', guardarCategoria);
  document.getElementById('formPlato').addEventListener('submit', guardarPlato);
  document.getElementById('formSettings').addEventListener('submit', guardarSettings);

  // Previews
  const logoInput = document.querySelector('#formSettings [name="logo"]');
  logoInput.addEventListener('change', e=>{
    const f = e.target.files[0];
    const img = document.getElementById('logoPreview');
    if(f){ img.src = URL.createObjectURL(f); img.classList.remove('d-none'); }
  });
  const imgPlato = document.querySelector('#formPlato [name="imagen"]');
  imgPlato.addEventListener('change', e=>{
    const f = e.target.files[0];
    const img = document.getElementById('platoPreview');
    if(f){ img.src = URL.createObjectURL(f); img.classList.remove('d-none'); }
  });

  // Cargar settings iniciales
  cargarSettings();
}

// ===== Settings =====
async function cargarSettings(){
  try{
    const res = await fetch(`${API_BASE}/settings/obtener.php`);
    const data = await res.json();
    if(data && data.data){
      const s = data.data;
      const f = document.getElementById('formSettings');
      f.nombre_negocio.value = s.nombre_negocio || '';
      f.whatsapp.value = s.whatsapp || '';
      if (s.logo){
        const img = document.getElementById('logoPreview');
        img.src = resolveImg(s.logo);
        img.classList.remove('d-none');
      }
    }
  }catch(e){ console.warn(e); }
}

async function guardarSettings(e){
  e.preventDefault();
  const fd = new FormData(e.currentTarget);
  try{
    const res = await fetch(`${API_BASE}/settings/guardar.php`, { method:'POST', body:fd });
    const data = await res.json();
    if(data.ok){
      Swal.fire({icon:'success', title:'Listo', text:'Configuración guardada', timer:1600, showConfirmButton:false});
      cargarSettings();
    }else{
      Swal.fire('Ups', data.msg || 'No se guardó', 'error');
    }
  }catch(err){
    Swal.fire('Error', 'Fallo de red', 'error');
  }
}

// ===== Categorías =====
function editCat(row){
  const f = document.getElementById('formCat');
  f.id.value = row.id;
  f.nombre.value = row.nombre;
  f.estado.value = row.estado;
  f.orden.value = row.orden ?? 0;
  modalCat.show();
}

async function delCat(id){
  const ok = await Swal.fire({
    icon:'warning', title:'¿Eliminar categoría?', showCancelButton:true,
    confirmButtonText:'Sí, eliminar', cancelButtonText:'Cancelar'
  }).then(r=>r.isConfirmed);
  if(!ok) return;
  const fd = new FormData(); fd.append('id', id);
  const res = await fetch(`${API_BASE}/categories/eliminar.php`, { method:'POST', body:fd });
  const data = await res.json();
  if(data.ok){ dtCats.ajax.reload(null,false); dtPlatos.ajax.reload(null,false); }
  else Swal.fire('Error', data.msg || 'No se pudo eliminar', 'error');
}

async function guardarCategoria(e){
  e.preventDefault();
  const fd = new FormData(e.currentTarget);
  const res = await fetch(`${API_BASE}/categories/guardar.php`, { method:'POST', body:fd });
  const data = await res.json();
  if(data.ok){
    modalCat.hide();
    dtCats.ajax.reload(null,false);
  }else{
    Swal.fire('Error', data.msg || 'No se guardó', 'error');
  }
}

// ===== Platos =====
async function cargarCategoriasSelect(){
  const sel = document.querySelector('#formPlato [name="categoria_id"]');
  sel.innerHTML = '<option value="">Cargando...</option>';
  const res = await fetch(`${API_BASE}/categories/obtener.php`);
  const data = await res.json();
  const cats = data.data || [];
  sel.innerHTML = '<option value="" disabled selected>-- Seleccionar --</option>' +
    cats.filter(c=>c.estado==1).map(c=>`<option value="${c.id}">${c.nombre}</option>`).join('');
}

function editPlato(row){
  const f = document.getElementById('formPlato');
  cargarCategoriasSelect().then(()=>{
    f.id.value = row.id;
    f.nombre.value = row.nombre;
    f.precio.value = row.precio;
    f.categoria_id.value = row.categoria_id;
    f.descripcion.value = row.descripcion || '';
    f.destacado.value = row.destacado;
    f.activo.value = row.activo;
    f.orden.value = row.orden ?? 0;
    const prev = document.getElementById('platoPreview');
    if(row.imagen){ prev.src = resolveImg(row.imagen); prev.classList.remove('d-none'); } else { prev.classList.add('d-none'); }
    modalPlato.show();
  });
}

async function delPlato(id){
  const ok = await Swal.fire({
    icon:'warning', title:'¿Eliminar plato?', showCancelButton:true,
    confirmButtonText:'Sí, eliminar', cancelButtonText:'Cancelar'
  }).then(r=>r.isConfirmed);
  if(!ok) return;
  const fd = new FormData(); fd.append('id', id);
  const res = await fetch(`${API_BASE}/platos/eliminar.php`, { method:'POST', body:fd });
  const data = await res.json();
  if(data.ok){ dtPlatos.ajax.reload(null,false); }
  else Swal.fire('Error', data.msg || 'No se pudo eliminar', 'error');
}

async function guardarPlato(e){
  e.preventDefault();
  const fd = new FormData(e.currentTarget);
  const res = await fetch(`${API_BASE}/platos/guardar.php`, { method:'POST', body:fd });
  const data = await res.json();
  if(data.ok){
    modalPlato.hide();
    dtPlatos.ajax.reload(null,false);
  }else{
    Swal.fire('Error', data.msg || 'No se guardó', 'error');
  }
}
