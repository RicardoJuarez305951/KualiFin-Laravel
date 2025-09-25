# Auditoría de vistas mobile (legado)

## Resumen
- Se identificaron 77 plantillas en `resources/views/mobile_legacy` correspondientes a las vistas móviles heredadas para los perfiles de ejecutivo, supervisor y promotor, además de componentes auxiliares como modales y pantallas de cartera.
- Se ejecutó `rg "mobile_legacy" -g"*.php" -n` sobre el repositorio y no se encontraron referencias en rutas, controladores ni includes que apunten a estas vistas; el comando no devolvió coincidencias, confirmando que las plantillas estaban sin uso.

## Acciones tomadas
- Se eliminó por completo el directorio `resources/views/mobile_legacy` para evitar confusiones y reducir deuda técnica.
- Se mantuvieron las vistas activas ubicadas en `resources/views/mobile`, que reutilizan el mismo layout móvil.

## Próximos pasos sugeridos
- Si en el futuro se necesitara consultar el diseño anterior, recuperar las plantillas desde el historial de Git.
- Centralizar cualquier vista móvil nueva en `resources/views/mobile` para mantener una única fuente de verdad.
