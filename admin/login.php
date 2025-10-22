<?php
// admin/login.php
session_start();
if (!empty($_SESSION['user'])) { header("Location: ./index.php"); exit; }
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <title>Ingresar · Menú Digital</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body{min-height:100dvh;background:#0f172a;background:linear-gradient(135deg,#0f172a,#1f2937);}
    .card{border:0;border-radius:16px;box-shadow:0 10px 30px rgba(0,0,0,.25)}
    .brand{font-weight:800;color:#0ea5e9}
  </style>
</head>
<body class="d-flex align-items-center justify-content-center p-3">
  <div class="card p-4" style="max-width:420px;width:100%">
    <h1 class="h4 mb-1 text-center brand">Menú Digital · Admin</h1>
    <p class="text-center text-muted mb-4">Inicia sesión para continuar</p>
    <form id="formLogin" class="vstack gap-3">
      <div>
        <label class="form-label">Usuario</label>
        <input type="text" name="usuario" class="form-control form-control-lg" placeholder="admin" required>
      </div>
      <div>
        <label class="form-label">Clave</label>
        <input type="password" name="clave" class="form-control form-control-lg" placeholder="••••" required>
      </div>
      <button class="btn btn-primary btn-lg w-100">Ingresar</button>
    </form>
   
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/axios@1.7.7/dist/axios.min.js"></script>
  <script>
    const API_BASE = '../api';
    document.getElementById('formLogin').addEventListener('submit', async (e)=>{
      e.preventDefault();
      const fd = new FormData(e.currentTarget);
      try{
        const {data} = await axios.post(`${API_BASE}/auth/login.php`, fd);
        if(data.ok){
          location.href = './index.php';
        }else{
          alert(data.msg || 'Credenciales inválidas');
        }
      }catch(err){
        alert('Error de conexión');
      }
    });
  </script>
</body>
</html>
