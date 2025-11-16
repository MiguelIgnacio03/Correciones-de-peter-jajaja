<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo defined('APP_NAME') ? APP_NAME : 'Sistema de Biblioteca'; ?></title>
    <link rel="stylesheet" href="../public/assets/css/style.css">
</head>
<body>
    <?php 
    // Verificar si la sesión está iniciada y el usuario está logueado
    $isLoggedIn = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    ?>
    
    <?php if ($isLoggedIn): ?>
        <!-- Header para usuarios logueados -->
        <header class="main-header">
            <div class="container">
                <div class="header-content">
                    <h1 class="logo">📚 <?php echo defined('APP_NAME') ? APP_NAME : 'Sistema de Biblioteca'; ?></h1>
                    <nav class="main-nav">
                        <span>Hola, <?php echo htmlspecialchars($_SESSION['first_name'] ?? 'Usuario'); ?></span>
                        <a href="index.php?action=dashboard">Dashboard</a>
                        <a href="index.php?action=books">Libros</a>
                        <a href="index.php?action=authors">Autores</a>
                        <a href="index.php?action=loans">Préstamos</a>
                        <?php if (($_SESSION['role'] ?? '') === 'admin'): ?>
                            <div class="nav-dropdown">
                                <a href="#" class="nav-dropdown-toggle">Administración ▾</a>
                                <div class="nav-dropdown-menu">
                                    <a href="index.php?action=manageUsers">Gestionar Usuarios</a>
                                    <a href="index.php?action=reports">Reportes</a>
                                    <a href="index.php?action=alerts">Alertas</a>
                                </div>
                            </div>
                        <?php endif; ?>
                        <a href="index.php?action=logout" class="logout-btn">Cerrar Sesión</a>
                    </nav>
                </div>
            </div>
        </header>
    <?php else: ?>
        <!-- Header simple para páginas de auth -->
        <header class="simple-header">
            <div class="container">
                <h1>📚 <?php echo defined('APP_NAME') ? APP_NAME : 'Sistema de Biblioteca'; ?></h1>
            </div>
        </header>
    <?php endif; ?>

    <main class="main-content">
        <div class="container">