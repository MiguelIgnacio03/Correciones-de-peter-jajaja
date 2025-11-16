<?php
/**
 * Punto de entrada principal del sistema de gestión de biblioteca
 * 
 * Este archivo maneja el enrutamiento de todas las solicitudes
 * y carga los controladores y vistas correspondientes
 */

// Incluir archivos de configuración
require_once '../config/constants.php';
require_once '../config/database.php';

// Incluir modelos
require_once '../app/models/UserModel.php';
require_once '../app/models/BookModel.php';
require_once '../app/models/AuthorModel.php';
require_once '../app/models/LoanModel.php';
require_once '../app/models/ReportModel.php';

// Incluir controladores
require_once '../app/controllers/AuthController.php';
require_once '../app/controllers/BookController.php';
require_once '../app/controllers/AuthorController.php';
require_once '../app/controllers/LoanController.php';
require_once '../app/controllers/AdminController.php';

// Iniciar sesión si no está activa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Crear conexión a la base de datos
$databaseConfig = new DatabaseConfig();
$dbConnection = $databaseConfig->connect();

// Verificar conexión a la base de datos
if (!$dbConnection) {
    die('Error al conectar con la base de datos. Por favor, verifica la configuración.');
}

// Obtener la acción desde el parámetro GET, por defecto 'login'
$action = $_GET['action'] ?? 'login';

// Crear instancia del controlador de autenticación para verificar sesión
$authController = new AuthController($dbConnection);

// Definir rutas que no requieren autenticación
$publicRoutes = ['login', 'processLogin', 'register', 'processRegister'];

// Verificar autenticación para rutas protegidas
if (!in_array($action, $publicRoutes) && !$authController->isLoggedIn()) {
    header('Location: index.php?action=login');
    exit;
}

// Enrutamiento de la aplicación
switch ($action) {
    // Rutas de autenticación
    case 'login':
        $authController->showLogin();
        break;
    case 'processLogin':
        $authController->processLogin();
        break;
    case 'register':
        $authController->showRegister();
        break;
    case 'processRegister':
        $authController->processRegister();
        break;
    case 'logout':
        $authController->logout();
        break;

    // Rutas del dashboard
    case 'dashboard':
        if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
            $adminController = new AdminController($dbConnection);
            $adminController->adminDashboard();
        } else {
            $bookController = new BookController($dbConnection);
            $bookController->dashboard();
        }
        break;

    // Rutas de libros
    case 'books':
        $bookController = new BookController($dbConnection);
        $bookController->index();
        break;
    case 'createBook':
        $bookController = new BookController($dbConnection);
        $bookController->create();
        break;
    case 'storeBook':
        $bookController = new BookController($dbConnection);
        $bookController->store();
        break;
    case 'editBook':
        $bookController = new BookController($dbConnection);
        $bookController->edit();
        break;
    case 'updateBook':
        $bookController = new BookController($dbConnection);
        $bookController->update();
        break;
    case 'deleteBook':
        $bookController = new BookController($dbConnection);
        $bookController->delete();
        break;

    // Rutas de autores
    case 'authors':
        $authorController = new AuthorController($dbConnection);
        $authorController->index();
        break;
    case 'createAuthor':
        $authorController = new AuthorController($dbConnection);
        $authorController->create();
        break;
    case 'storeAuthor':
        $authorController = new AuthorController($dbConnection);
        $authorController->store();
        break;
    case 'editAuthor':
        $authorController = new AuthorController($dbConnection);
        $authorController->edit();
        break;
    case 'updateAuthor':
        $authorController = new AuthorController($dbConnection);
        $authorController->update();
        break;
    case 'deleteAuthor':
        $authorController = new AuthorController($dbConnection);
        $authorController->delete();
        break;
    case 'showAuthor':
        $authorController = new AuthorController($dbConnection);
        $authorController->show();
        break;
    case 'getAuthorsApi':
        $authorController = new AuthorController($dbConnection);
        $authorController->getAuthorsApi();
        break;

    // Rutas de préstamos
    case 'loans':
        $loanController = new LoanController($dbConnection);
        $loanController->index();
        break;
    case 'createLoan':
        $loanController = new LoanController($dbConnection);
        $loanController->create();
        break;
    case 'storeLoan':
        $loanController = new LoanController($dbConnection);
        $loanController->store();
        break;
    case 'returnLoan':
        $loanController = new LoanController($dbConnection);
        $loanController->returnBook();
        break;
    case 'myLoans':
        $loanController = new LoanController($dbConnection);
        $loanController->myLoans();
        break;
    case 'getBookInfo':
        $loanController = new LoanController($dbConnection);
        $loanController->getBookInfo();
        break;
    case 'getUserInfo':
        $loanController = new LoanController($dbConnection);
        $loanController->getUserInfo();
        break;
    case 'generateReport':
        $loanController = new LoanController($dbConnection);
        $loanController->generateReport();
        break;

    // Rutas de administración
    case 'adminDashboard':
        $adminController = new AdminController($dbConnection);
        $adminController->adminDashboard();
        break;
    case 'manageUsers':
        $adminController = new AdminController($dbConnection);
        $adminController->manageUsers();
        break;
    case 'reports':
        $adminController = new AdminController($dbConnection);
        $adminController->showReports();
        break;
    case 'exportReport':
        $adminController = new AdminController($dbConnection);
        $adminController->exportReport();
        break;
    case 'updateUserRole':
        $adminController = new AdminController($dbConnection);
        $adminController->updateUserRole();
        break;
    case 'toggleUserStatus':
        $adminController = new AdminController($dbConnection);
        $adminController->toggleUserStatus();
        break;
    case 'alerts':
        $adminController = new AdminController($dbConnection);
        $adminController->showAlerts();
        break;

    // Ruta por defecto (404)
    default:
        http_response_code(404);
        echo '<h1>Página no encontrada</h1>';
        echo '<p>La página que buscas no existe.</p>';
        echo '<a href="index.php?action=dashboard">Volver al Dashboard</a>';
        break;
}

// Cerrar conexión a la base de datos
$databaseConfig->disconnect();
?>