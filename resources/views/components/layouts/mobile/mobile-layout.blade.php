<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
  <title>K con Triángulos</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  <style>
    :root{
      @role('promotor')
      --color-primary: #FDC42D;
      --color-secondary: #FDE59D;
      --color-background: #FAF6EA;
      @endrole

      @role('supervisor')
      --color-primary: #1187C4;
      --color-secondary: #A6D9F6;
      --color-background: #FAF6EA;
      @endrole
    }
    .triangulo {
      position: absolute;
      width: 100%;
      height: 100%;
    }
    .t1 {
      background: var(--color-primary); /* naranja */
      clip-path: polygon(0% 50%, 0% 0%, 150% 0%);
    }
    .t2 {
      background: var(--color-secondary); /* amarillo */
      clip-path: polygon(0% 0%, 150% 100%, 0% 100%);
    }
    .bg {
      background: var(--color-background); /* verde */
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
