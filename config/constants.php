<?php
/**
 * Constantes del sistema de gestión de biblioteca
 * 
 * Define valores constantes utilizados throughout la aplicación
 */

// Constantes de roles de usuario
define('ROLE_ADMIN', 'admin');
define('ROLE_LIBRARIAN', 'librarian');
define('ROLE_USER', 'user');

// Constantes de estado de préstamos
define('LOAN_ACTIVE', 'active');
define('LOAN_RETURNED', 'returned');
define('LOAN_OVERDUE', 'overdue');

// Configuración de la aplicación
define('APP_NAME', 'Sistema de Gestión de Biblioteca');
define('MAX_LOAN_DAYS', 14); // Días máximos para un préstamo
define('ITEMS_PER_PAGE', 10); // Elementos por página en listados

// Mensajes de respuesta comunes
define('MSG_SUCCESS', 'success');
define('MSG_ERROR', 'error');
?>