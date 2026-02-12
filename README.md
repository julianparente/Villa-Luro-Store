# Villa Luro Store

Plataforma web para catálogo y venta de perfumes, desarrollada en PHP (PDO), MySQL, JavaScript, y Tailwind CSS.

## Estructura del proyecto

- `mysql/` — Scripts SQL para la base de datos
- `config/` — Configuración de base de datos y sesión
- `css/` — Hojas de estilo
- `img/` — Imágenes estáticas
- `js/` — Scripts de JavaScript
- `vendor/` — Dependencias de Composer (PHPMailer)
- `views/` — Archivos de vista (HTML/PHP), incluyendo `partials/` para componentes reutilizables.

## Instalación

1. Clona el repositorio o copia los archivos en tu servidor.
2. Crea la base de datos MySQL y ejecuta el script SQL en `mysql/`.
3. Configura los datos de conexión en `config/db.php`.
4. Asegúrate de que la carpeta `uploads/` tenga permisos de escritura.
5. Accede a `/admin/panel.php` para gestionar marcas y perfumes.

## Tecnologías

- PHP (PDO)
- MySQL
- JavaScript (Vanilla, minificado)
- Tailwind CSS

## Recomendaciones para producción

- Usa los archivos JS minificados (`*.min.js`) para mejor rendimiento.
- Configura HTTPS y sesiones seguras.
- Valida y sanitiza todos los datos de entrada.
- Mantén actualizada la base de datos y realiza backups periódicos.

## Seguridad

- Las contraseñas de usuarios se almacenan con hash seguro (password_hash).
- Nunca compartas ni subas archivos de configuración con credenciales reales (`db.php`, `.env`).
- Cambia la contraseña del usuario admin tras la instalación.
- Usa HTTPS y configura sesiones seguras en producción.
- Mantén el software y dependencias actualizadas.

## Accesibilidad y UX

- Formularios accesibles con etiquetas, aria-labels y mensajes claros.
- Animación de mensajes de éxito/error.
- Navegación optimizada y diseño responsive.

## Contacto y soporte

Para dudas o soporte, contacta al desarrollador o consulta la documentación interna.

## Preguntas frecuentes (FAQ)

**¿Cómo restablezco la contraseña de un usuario?**

Actualmente no hay función de recuperación automática. El administrador puede actualizar la contraseña directamente en la base de datos.

**¿Qué hago si no puedo iniciar sesión?**

Verifica que el email y la contraseña sean correctos. Si el problema persiste, revisa la configuración de la base de datos y los permisos de usuario.

**¿Cómo puedo hacer un backup de la base de datos?**

Utiliza herramientas como `mysqldump` o el panel de control de tu hosting para exportar la base de datos.

**¿Cómo cambio el logo o imágenes?**

Reemplaza los archivos en la carpeta `img/`.

## Contribución

Si deseas contribuir a este proyecto:

1. Haz un fork del repositorio.
2. Crea una rama para tu mejora o corrección.
3. Realiza tus cambios y agrega comentarios claros.
4. Envía un pull request con una descripción detallada.

Se agradecen sugerencias, mejoras de accesibilidad, optimización y nuevas funcionalidades.
