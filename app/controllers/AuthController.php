<?php
/**
 * Controlador para manejar autenticación y registro de usuarios
 * 
 * Gestiona login, registro, logout y verificación de sesiones
 */
class AuthController {
    private $userModel;

    /**
     * Constructor del controlador de autenticación
     * 
     * @param PDO $database Conexión a la base de datos
     */
    public function __construct($database) {
        $this->userModel = new UserModel($database);
        
        // Iniciar sesión si no está activa
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Muestra el formulario de login
     */
    public function showLogin() {
        // Si ya está logueado, redirigir al dashboard
        if ($this->isLoggedIn()) {
            header('Location: index.php?action=dashboard');
            exit;
        }
        
        require_once '../app/views/auth/login.php';
    }

    /**
     * Procesa el formulario de login
     */
    public function processLogin() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['error'] = 'Método no permitido';
            header('Location: index.php?action=login');
            exit;
        }

        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        // Validaciones básicas
        if (empty($username) || empty($password)) {
            $_SESSION['error'] = 'Usuario y contraseña son requeridos';
            header('Location: index.php?action=login');
            exit;
        }

        // Verificar credenciales
        $user = $this->userModel->verifyCredentials($username, $password);
        
        if ($user) {
            // Establecer sesión
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['first_name'] = $user['first_name'];
            
            $_SESSION['success'] = '¡Bienvenido ' . $user['first_name'] . '!';
            header('Location: index.php?action=dashboard');
            exit;
        } else {
            $_SESSION['error'] = 'Credenciales inválidas';
            header('Location: index.php?action=login');
            exit;
        }
    }

    /**
     * Muestra el formulario de registro
     */
    public function showRegister() {
        if ($this->isLoggedIn()) {
            header('Location: index.php?action=dashboard');
            exit;
        }
        
        require_once '../app/views/auth/register.php';
    }

    /**
     * Procesa el formulario de registro
     */
    public function processRegister() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['error'] = 'Método no permitido';
            header('Location: index.php?action=register');
            exit;
        }

        // Recoger y limpiar datos
        $userData = [
            'username' => trim($_POST['username'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'password' => $_POST['password'] ?? '',
            'confirmPassword' => $_POST['confirm_password'] ?? '',
            'firstName' => trim($_POST['first_name'] ?? ''),
            'lastName' => trim($_POST['last_name'] ?? ''),
            'role' => 'user' // Rol por defecto
        ];

        // Validaciones
        $errors = $this->validateRegistration($userData);
        
        if (!empty($errors)) {
            $_SESSION['error'] = implode('<br>', $errors);
            $_SESSION['form_data'] = $userData;
            header('Location: index.php?action=register');
            exit;
        }

        // Verificar si usuario o email ya existen
        if ($this->userModel->findByUsername($userData['username'])) {
            $_SESSION['error'] = 'El nombre de usuario ya está en uso';
            $_SESSION['form_data'] = $userData;
            header('Location: index.php?action=register');
            exit;
        }

        if ($this->userModel->findByEmail($userData['email'])) {
            $_SESSION['error'] = 'El email ya está registrado';
            $_SESSION['form_data'] = $userData;
            header('Location: index.php?action=register');
            exit;
        }

        // Crear usuario
        $userData['password_hash'] = password_hash($userData['password'], PASSWORD_DEFAULT);
        
        if ($this->userModel->createUser($userData)) {
            $_SESSION['success'] = '¡Registro exitoso! Ahora puedes iniciar sesión';
            header('Location: index.php?action=login');
            exit;
        } else {
            $_SESSION['error'] = 'Error al crear el usuario. Intenta nuevamente.';
            header('Location: index.php?action=register');
            exit;
        }
    }

    /**
     * Cierra la sesión del usuario
     */
    public function logout() {
        // Limpiar todas las variables de sesión
        $_SESSION = array();
        
        // Destruir la sesión
        session_destroy();
        
        header('Location: index.php?action=login');
        exit;
    }

    /**
     * Verifica si hay un usuario logueado
     * 
     * @return bool True si hay sesión activa, false en caso contrario
     */
    public function isLoggedIn() {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }

    /**
     * Valida los datos del formulario de registro
     * 
     * @param array $userData Datos del usuario a validar
     * @return array Lista de errores de validación
     */
    private function validateRegistration($userData) {
        $errors = [];

        // Validar username
        if (strlen($userData['username']) < 3) {
            $errors[] = 'El usuario debe tener al menos 3 caracteres';
        }

        // Validar email
        if (!filter_var($userData['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'El email no tiene un formato válido';
        }

        // Validar contraseña
        if (strlen($userData['password']) < 6) {
            $errors[] = 'La contraseña debe tener al menos 6 caracteres';
        }

        if ($userData['password'] !== $userData['confirmPassword']) {
            $errors[] = 'Las contraseñas no coinciden';
        }

        // Validar nombre
        if (empty($userData['firstName']) || empty($userData['lastName'])) {
            $errors[] = 'Nombre y apellido son requeridos';
        }

        return $errors;
    }
}
?>