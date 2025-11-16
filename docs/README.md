📚 SISTEMA DE GESTIÓN DE BIBLIOTECA - Documentación
markdown
# 📚 Sistema de Gestión de Biblioteca

Sistema web desarrollado en PHP para la gestión integral de una biblioteca, incluyendo administración de libros, autores, préstamos y usuarios.

## 🚀 Características Principales

- **Autenticación de usuarios** con tres roles: Administrador, Bibliotecario y Usuario
- **CRUD completo** para libros y autores
- **Gestión de préstamos** con control de fechas y disponibilidad
- **Panel de administración** con reportes y estadísticas
- **Interfaz responsive** y moderna
- **Arquitectura MVC** bien definida
- **Base de datos normalizada** (3FN)

## 🛠️ Tecnologías Utilizadas

- **Backend:** PHP 7.4+
- **Frontend:** HTML5, CSS3, JavaScript
- **Base de Datos:** MySQL 5.7+
- **Arquitectura:** MVC (Modelo-Vista-Controlador)
- **Seguridad:** Password hashing, Prepared Statements

## 📋 Requisitos del Sistema

- Servidor web (Apache/Nginx)
- PHP 7.4 o superior
- MySQL 5.7 o superior
- Extensiones PHP: PDO, MySQL

## 🗂️ Estructura del Proyecto
library_management/
├── app/ # Lógica de aplicación (MVC)
│ ├── controllers/ # Controladores
│ ├── models/ # Modelos de datos
│ └── views/ # Vistas
├── config/ # Configuración
├── public/ # Punto de entrada público
│ └── assets/ # Recursos estáticos
├── database/ # Esquema de base de datos
└── docs/ # Documentación

text

## 🔧 Instalación

1. **Clonar o descargar** el proyecto en el directorio web
2. **Configurar la base de datos:**
   ```sql
   CREATE DATABASE library_management;
   USE library_management;
   SOURCE database/schema.sql;
Configurar conexión en config/database.php

Acceder al sistema via: http://localhost/tu-proyecto/public/

👤 Usuarios por Defecto
Administrador
Usuario: admin

Contraseña: password

Rol: Administrador completo

Bibliotecario
Usuario: bibliotecario

Contraseña: password

Rol: Gestión de libros y préstamos

📊 Módulos del Sistema
1. Autenticación y Usuarios
Registro e inicio de sesión

Gestión de perfiles y roles

Control de acceso por permisos

2. Gestión de Libros
CRUD completo de libros

Búsqueda por título, autor o ISBN

Control de inventario y copias

3. Gestión de Autores
CRUD completo de autores

Relación libros-autores

Búsqueda y filtros

4. Gestión de Préstamos
Registro de nuevos préstamos

Devolución de libros

Control de vencimientos

Historial por usuario

5. Administración y Reportes
Dashboard con estadísticas

Reportes de préstamos

Gestión de usuarios

Exportación de datos

🗃️ Base de Datos
Normalización (3FN)
Primera Forma Normal (1FN)
Eliminación de grupos repetitivos

Atributos atómicos en todas las tablas

Segunda Forma Normal (2FN)
Dependencia completa de claves primarias

Eliminación de dependencias parciales

Tercera Forma Normal (3FN)
Eliminación de dependencias transitivas

Optimización de relaciones

Esquema Principal
sql
-- Usuarios del sistema
users (id, username, email, password_hash, first_name, last_name, role, is_active, created_at)

-- Autores de libros  
authors (id, name, nationality, birth_date, biography, created_at)

-- Catálogo de libros
books (id, title, isbn, author_id, publication_year, genre, publisher, total_copies, available_copies, description)

-- Registro de préstamos
loans (id, user_id, book_id, loan_date, due_date, return_date, status, created_at)

🔐 Seguridad
Hash de contraseñas con password_hash()

Prepared statements para prevenir SQL injection

Validación de entrada en frontend y backend

Control de sesiones seguro

XSS prevention con htmlspecialchars()

Verificación de roles para cada acción

🎨 Características de Usabilidad
Interfaz responsive para todos los dispositivos

Mensajes de feedback para el usuario

Navegación intuitiva y contextual

Búsquedas y filtros avanzados

Confirmaciones para acciones destructivas

Paginación para grandes volúmenes de datos

📈 Reportes y Estadísticas
Dashboard con métricas clave

Reportes de préstamos por período

Estadísticas de uso por usuario

Libros más populares

Alertas de stock bajo y vencimientos

Exportación a CSV

🚀 Funcionalidades por Rol
Administrador
Gestión completa del sistema

Administración de usuarios

Todos los reportes y estadísticas

Configuración del sistema

Bibliotecario
Gestión de libros y autores

Registro y devolución de préstamos

Consulta de reportes básicos

Usuario
Consulta de catálogo de libros

Visualización de sus préstamos

Historial personal

🔄 Flujo de la Aplicación
text
Usuario → public/index.php → Router → Controlador → Modelo → Vista → Usuario
📝 Documentación del Código
Comentarios en español en todo el código

Métodos documentados con parámetros y retornos

Estructura MVC claramente definida

Estándares de codificación PHP FIG

🐛 Solución de Problemas
Error de estilos no cargados
Verificar rutas en app/views/layout/header.php

Error de conexión a base de datos
Verificar configuración en config/database.php

Error de métodos no definidos
Verificar que todos los modelos tengan los métodos requeridos

📞 Soporte
Para issues y preguntas, revisar la documentación o contactar al equipo de desarrollo.

📄 Licencia
Este proyecto es para fines educativos y de demostración.

👥 Créditos
Sistema desarrollado como proyecto académico para la gestión de bibliotecas.

text

## 📁 ESTRUCTURA DE ARCHIVOS ADICIONALES

Además del README.md, te recomiendo crear estos archivos de documentación:

### 📄 docs/INSTALACION.md
```markdown
# Guía de Instalación

## Requisitos Previos
- Servidor web (XAMPP, WAMP, o similar)
- PHP 7.4+
- MySQL 5.7+

## Pasos de Instalación

1. **Descargar el proyecto**
2. **Configurar la base de datos**
3. **Configurar conexión a BD**
4. **Probar el sistema**