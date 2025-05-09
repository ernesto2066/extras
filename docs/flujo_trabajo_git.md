# Flujo de Trabajo con Git - POSITIVOS

## Introducción

Este documento técnico describe el flujo de trabajo estándar con Git para el desarrollo colaborativo del proyecto POSITIVOS. Estas directrices aseguran una colaboración efectiva entre desarrolladores, minimizando conflictos y manteniendo la integridad del código.

## Estructura de Ramas

El proyecto sigue una adaptación de GitFlow con las siguientes ramas principales:

### Ramas Permanentes

- **`main`**: Contiene el código en producción. Siempre debe estar en un estado estable y listo para desplegar.
- **`develop`**: Rama de integración donde se combinan todas las características completadas. Sirve como base para el desarrollo continuo.

### Ramas Temporales

- **`feature/nombre-funcionalidad`**: Para desarrollar nuevas funcionalidades
- **`bugfix/descripcion-error`**: Para corregir errores en desarrollo
- **`hotfix/descripcion-problema`**: Para correcciones urgentes en producción
- **`release/version`**: Para preparar releases hacia producción

## Representación Visual del Flujo de Ramas

```
                      hotfix/descripcion
                          ↗       ↘
                         /         \
┌──────────────────────┐     ┌─────────────┐
│                      │     │             │
│        main          │ ←── │   release   │
│   (código estable)   │     │   branch    │
│                      │     │             │
└──────────────────────┘     └─────────────┘
           ↑                        ↑
           │                        │
┌──────────────────────┐     ┌─────────────┐
│                      │     │             │
│       develop        │ ←── │   feature   │
│  (rama integración)  │     │   branch    │
│                      │     │             │
└──────────────────────┘     └─────────────┘
           ↑                        ↑
           │                        │
           └───────────────────────┘
                bugfix/descripcion
```

### Ciclo de Vida de las Ramas

1. Las ramas `feature/*` y `bugfix/*` parten siempre de `develop`
2. Las ramas `hotfix/*` parten de `main` para corregir problemas en producción
3. Las ramas `release/*` se crean desde `develop` cuando se prepara una versión
4. Los cambios siempre se integran a `develop` primero
5. De `develop` pasan a `release/*` y finalmente a `main`
6. Los `hotfix/*` se integran tanto en `main` como en `develop`

## Convenciones de Nomenclatura

### Formato General

```
tipo/descripcion-breve
```

### Ejemplos para POSITIVOS

- `feature/dashboard-aprobacion`
- `feature/notificaciones-email`
- `bugfix/validacion-actividades`
- `hotfix/error-login-panel`
- `release/1.2.0`

## Flujo de Trabajo Detallado

### 1. Desarrollo de Nuevas Funcionalidades

```bash
# 1. Asegurarse de tener la última versión de develop
git checkout develop
git pull origin develop

# 2. Crear nueva rama de característica
git checkout -b feature/nombre-caracteristica

# 3. Desarrollo y commits frecuentes
git add .
git commit -m "feat: descripción del cambio"

# 4. Mantener la rama actualizada con develop
git pull origin develop

# 5. Resolver conflictos si existen
# ...resolución de conflictos...

# 6. Enviar cambios al repositorio remoto
git push -u origin feature/nombre-caracteristica
```

### 2. Pull Requests

1. Ir a GitHub y crear un Pull Request
2. Base: `develop` ← Compare: `feature/nombre-caracteristica`
3. Describir los cambios siguiendo la plantilla
4. Asignar revisores (al menos uno)
5. Revisar y corregir comentarios del equipo
6. Realizar merge cuando esté aprobado

### 3. Corrección de Errores

```bash
# Para errores en desarrollo
git checkout develop
git checkout -b bugfix/descripcion-error

# Para errores críticos en producción
git checkout main
git checkout -b hotfix/descripcion-problema
```

### 4. Preparación de Releases

```bash
# 1. Crear rama de release desde develop
git checkout develop
git checkout -b release/x.y.z

# 2. Realizar ajustes menores, correcciones de última hora
# ...trabajo y commits...

# 3. Finalizar release
git checkout main
git merge release/x.y.z
git tag -a v.x.y.z -m "Versión x.y.z"
git push origin main --tags

# 4. Integrar cambios en develop
git checkout develop
git merge release/x.y.z
git push origin develop

# 5. Eliminar rama de release
git branch -d release/x.y.z
```

## Mensajes de Commit

Seguimos la convención de [Conventional Commits](https://www.conventionalcommits.org/):

```
tipo(alcance): mensaje corto

Descripción más detallada si es necesaria
```

### Tipos de Commit

- `feat`: Nueva funcionalidad
- `fix`: Corrección de error
- `docs`: Cambios en documentación
- `style`: Cambios de formato, espaciado, etc (no afectan código)
- `refactor`: Refactorización de código
- `test`: Adición o corrección de tests
- `chore`: Cambios en el proceso de build o herramientas auxiliares

### Ejemplos para POSITIVOS

```
feat(actividades): implementa filtro por torres
fix(aprobaciones): corrige validación en formulario de revisión
docs(readme): actualiza instrucciones de instalación
refactor(panel-admin): optimiza consultas en dashboard
```

## Prácticas Recomendadas

1. **Commits Frecuentes**: Realizar commits pequeños y frecuentes con mensajes descriptivos
2. **Pull Antes de Push**: Siempre hacer pull antes de push para evitar conflictos
3. **Revisión de Código**: Toda característica debe ser revisada por al menos un desarrollador
4. **No Forzar Push**: Evitar `git push --force` en ramas compartidas
5. **Ramas Limpias**: Eliminar ramas locales y remotas después de completar el trabajo
6. **Resolución de Conflictos**: Resolver conflictos localmente antes de hacer push

## Herramientas Recomendadas

- **GitHub Desktop**: Interfaz gráfica para operaciones Git básicas
- **Git Extensions**: Cliente Git más avanzado para Windows
- **SourceTree**: Cliente Git visual multiplataforma
- **GitKraken**: Interfaz gráfica potente para Git

## Referencias

- [Conventional Commits](https://www.conventionalcommits.org/)
- [GitFlow Workflow](https://www.atlassian.com/git/tutorials/comparing-workflows/gitflow-workflow)
- [GitHub Flow](https://guides.github.com/introduction/flow/)
