<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
  <title>K con Triángulos</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  <style>
    .triangulo {
      position: absolute;
      width: 100%;
      height: 100%;
    }
    .t1 {
      background: #FDC42D; /* naranja */
      clip-path: polygon(0% 50%, 0% 0%, 150% 0%);
    }
    .t2 {
      background: #FDE59D; /* amarillo */
      clip-path: polygon(0% 0%, 100% 100%, 0% 100%);
    }
    .bg {
      background: #FAF6EA; /* verde */
    }
  </style>
</head>
<body class="relative min-h-screen bg-gray-100 flex items-center justify-center">

  <!-- Fondo con 3 triángulos -->
  <div class="triangulo bg"></div>
  <div class="triangulo t2"></div>
  <div class="triangulo t1"></div>

  <!-- Contenido -->
  <div class="relative z-10 text-center">
    {{ $slot }}
  </div>

</body>
</html>
