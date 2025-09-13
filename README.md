# Create a Newsletter with the Block Editor

Un plugin de WordPress que crea newsletters en estilo Substack utilizando el editor de bloques de WordPress.

## Características

- Custom Post Type para newsletters
- Panel de configuración en el editor de Gutenberg
- Envío automático de newsletters al publicar
- Sistema de suscriptores con roles personalizados
- Enlaces de cancelación de suscripción seguros
- Dashboard para suscriptores
- Soporte para múltiples roles de destinatarios
- Estilo inspirado en Substack

## Instalación para Desarrollo

### Prerrequisitos

- Node.js (versión 14 o superior)
- npm o yarn
- WordPress instalado y configurado

### Configuración del entorno de desarrollo

1. Clona o descarga el plugin en tu directorio de plugins de WordPress:
```bash
cd wp-content/plugins/
```

2. Navega al directorio del plugin:
```bash
cd create-a-newsletter-with-the-block-editor
```

3. Instala las dependencias:
```bash
npm install
```

### Scripts disponibles

- **Desarrollo**: `npm run start` - Inicia el modo de desarrollo con recarga automática
- **Construcción**: `npm run build` - Construye los archivos para producción
- **Linting JS**: `npm run lint:js` - Verifica el código JavaScript
- **Linting CSS**: `npm run lint:css` - Verifica los estilos CSS
- **Formateo**: `npm run format` - Formatea el código automáticamente

### Estructura del proyecto

```
create-a-newsletter-with-the-block-editor/
├── build/                  # Archivos compilados (generados)
├── src/                    # Código fuente
│   ├── components/
│   │   └── NewsletterMetaFields.js
│   └── index.js
├── languages/              # Archivos de traducción
├── create-a-newsletter-with-the-block-editor.php  # Archivo principal del plugin
├── package.json
├── webpack.config.js
└── README.md
```

## Desarrollo

### Modo de desarrollo

Para trabajar en el plugin, ejecuta:

```bash
npm run start
```

Esto iniciará webpack en modo watch, recompilando automáticamente los archivos cuando hagas cambios.

### Construcción para producción

Cuando estés listo para desplegar, construye los archivos optimizados:

```bash
npm run build
```

### Añadir nuevos componentes

1. Crea tus componentes en `src/components/`
2. Impórtalos en `src/index.js` o en otros componentes
3. Los archivos se compilarán automáticamente

### Localización

El plugin está preparado para traducción. Las cadenas de texto utilizan la función `__()` de WordPress:

```javascript
__('Texto a traducir', 'create-a-newsletter-with-the-block-editor')
```

## API del Plugin

### Meta Fields registrados

- `canwbe_intro_message`: Mensaje de introducción del newsletter
- `canwbe_unsubscribe_message`: Mensaje del enlace de cancelación de suscripción
- `canwbe_recipient_roles`: Array de roles que recibirán el newsletter

### Constantes del plugin

- `CANWBE_VERSION`: Versión actual del plugin
- `CANWBE_PLUGIN_URL`: URL del directorio del plugin
- `CANWBE_PLUGIN_PATH`: Ruta del directorio del plugin

### Hooks personalizados

El plugin proporciona varios hooks para extender su funcionalidad. Consulta el archivo PHP principal para más detalles.

## Configuración de GitHub desde PHPStorm

### 1. Inicializar Git en el proyecto

En PHPStorm:
1. Ve a **VCS** → **Create Git Repository**
2. Selecciona la carpeta raíz del plugin
3. Click en **OK**

### 2. Configurar el repositorio remoto

1. Ve a **VCS** → **Git** → **Remotes**
2. Click en **+** para añadir un nuevo remote
3. Nombre: `origin`
4. URL: `https://github.com/TU_USUARIO/create-a-newsletter-with-the-block-editor.git`

### 3. Commit inicial y push

En la terminal de PHPStorm o usando el GUI:

```bash
# Añadir todos los archivos
git add .

# Commit inicial
git commit -m "Initial commit: Create a Newsletter with Block Editor v1.3"

# Push al repositorio remoto
git push -u origin main
```

### 4. Configurar .gitignore adicional

Asegúrate de que tu `.gitignore` esté actualizado para PHPStorm:

```gitignore
# PHPStorm
.idea/
*.iml

# Node modules
node_modules/

# Build files
build/

# WordPress
wp-config.php
wp-content/uploads/

# OS files
.DS_Store
Thumbs.db

# Logs
*.log
```

## Contribuir

1. Haz fork del proyecto
2. Crea una rama para tu característica (`git checkout -b feature/nueva-caracteristica`)
3. Realiza tus cambios
4. Ejecuta los linters: `npm run lint:js && npm run lint:css`
5. Haz commit de tus cambios (`git commit -am 'Añade nueva característica'`)
6. Push a la rama (`git push origin feature/nueva-caracteristica`)
7. Crea un Pull Request

## Changelog

### Versión 1.3
- Actualizado el nombre del plugin y text domain
- Refactorizado código con nuevos prefijos (canwbe_)
- Añadido soporte para constantes del plugin
- Mejorada la carga de traducciones
- Actualizada estructura para desarrollo moderno

### Versión 1.2
- Versión inicial con funcionalidad completa de newsletter

## Licencia

GPL v3 o posterior

## Autor

Flavia Bernárdez Rodríguez  
Web: [https://flabernardez.com](https://flabernardez.com)
