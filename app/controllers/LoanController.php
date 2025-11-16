<?php
/**
 * Controlador para manejar operaciones relacionadas con préstamos
 * 
 * Gestiona préstamos, devoluciones y consultas de libros
 */
class LoanController {
    private $loanModel;
    private $bookModel;
    private $userModel;
    private $dbConnection;

    /**
     * Constructor del controlador de préstamos
     * 
     * @param PDO $database Conexión a la base de datos
     */
    public function __construct($database) {
        $this->dbConnection = $database;
        $this->loanModel = new LoanModel($database);
        $this->bookModel = new BookModel($database);
        $this->userModel = new UserModel($database);
    }

    /**
     * Muestra la lista de todos los préstamos
     */
    public function index() {
        $page = $_GET['page'] ?? 1;
        $search = $_GET['search'] ?? '';
        $status = $_GET['status'] ?? '';
        
        // Actualizar préstamos vencidos
        $this->loanModel->updateOverdueLoans();
        
        if (!empty($search)) {
            $loans = $this->loanModel->searchLoans($search);
            $totalLoans = count($loans);
        } else {
            $loans = $this->loanModel->getAllLoans($page);
            $totalLoans = $this->loanModel->getTotalLoans();
        }
        
        // Filtrar por estado si se especifica
        if (!empty($status) && $status !== 'all') {
            $loans = array_filter($loans, function($loan) use ($status) {
                return $loan['status'] === $status;
            });
            $totalLoans = count($loans);
        }
        
        $totalPages = ceil($totalLoans / ITEMS_PER_PAGE);
        $loanStats = $this->loanModel->getLoanStats();
        
        require_once '../app/views/loans/index.php';
    }

    /**
     * Muestra el formulario para crear un nuevo préstamo
     */
    public function create() {
        $users = $this->userModel->getAllUsers();
        $books = $this->bookModel->getAllBooks(1, 1000); // Obtener todos los libros
        
        // Filtrar solo libros disponibles
        $availableBooks = array_filter($books, function($book) {
            return $book['available_copies'] > 0;
        });
        
        require_once '../app/views/loans/create.php';
    }

    /**
     * Procesa la creación de un nuevo préstamo
     */
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['error'] = 'Método no permitido';
            header('Location: index.php?action=loans');
            exit;
        }

        // Recoger y limpiar datos
        $loanData = [
            'userId' => $_POST['user_id'] ?? '',
            'bookId' => $_POST['book_id'] ?? '',
            'loanDate' => $_POST['loan_date'] ?? date('Y-m-d'),
            'dueDate' => $_POST['due_date'] ?? '',
            'status' => LOAN_ACTIVE
        ];

        // Validaciones
        $errors = $this->validateLoan($loanData);
        
        if (!empty($errors)) {
            $_SESSION['error'] = implode('<br>', $errors);
            $_SESSION['form_data'] = $loanData;
            header('Location: index.php?action=createLoan');
            exit;
        }

        // Verificar disponibilidad del libro
        if (!$this->loanModel->isBookAvailable($loanData['bookId'])) {
            $_SESSION['error'] = 'El libro seleccionado no está disponible';
            $_SESSION['form_data'] = $loanData;
            header('Location: index.php?action=createLoan');
            exit;
        }

        // Crear préstamo
        if ($this->loanModel->createLoan($loanData)) {
            // Actualizar copias disponibles del libro
            $updateSuccess = $this->bookModel->updateAvailableCopies($loanData['bookId'], -1);
            
            if ($updateSuccess) {
                $_SESSION['success'] = 'Préstamo registrado exitosamente';
                header('Location: index.php?action=loans');
                exit;
            } else {
                // Revertir el préstamo si no se pudo actualizar el stock
                $this->revertLoan($loanData);
                $_SESSION['error'] = 'Error al actualizar el stock del libro';
                header('Location: index.php?action=createLoan');
                exit;
            }
        } else {
            $_SESSION['error'] = 'Error al registrar el préstamo';
            $_SESSION['form_data'] = $loanData;
            header('Location: index.php?action=createLoan');
            exit;
        }
    }

    /**
     * Revierte un préstamo en caso de error
     * 
     * @param array $loanData Datos del préstamo a revertir
     */
    private function revertLoan($loanData) {
        // Buscar el último préstamo creado para este libro y usuario
        $query = "SELECT id FROM loans 
                 WHERE user_id = :user_id AND book_id = :book_id 
                 ORDER BY created_at DESC LIMIT 1";
        
        $statement = $this->loanModel->dbConnection->prepare($query);
        $statement->bindParam(':user_id', $loanData['userId'], PDO::PARAM_INT);
        $statement->bindParam(':book_id', $loanData['bookId'], PDO::PARAM_INT);
        $statement->execute();
        
        $lastLoan = $statement->fetch(PDO::FETCH_ASSOC);
        
        if ($lastLoan) {
            // Eliminar el préstamo
            $deleteQuery = "DELETE FROM loans WHERE id = :id";
            $deleteStatement = $this->loanModel->dbConnection->prepare($deleteQuery);
            $deleteStatement->bindParam(':id', $lastLoan['id'], PDO::PARAM_INT);
            $deleteStatement->execute();
        }
    }

    /**
     * Procesa la devolución de un libro
     */
    public function returnBook() {
        $loanId = $_GET['id'] ?? 0;
        
        if (!$loanId) {
            $_SESSION['error'] = 'ID de préstamo no válido';
            header('Location: index.php?action=loans');
            exit;
        }

        $loan = $this->loanModel->getLoanById($loanId);
        
        if (!$loan) {
            $_SESSION['error'] = 'Préstamo no encontrado';
            header('Location: index.php?action=loans');
            exit;
        }

        if ($loan['status'] !== LOAN_ACTIVE && $loan['status'] !== LOAN_OVERDUE) {
            $_SESSION['error'] = 'Este préstamo ya fue devuelto';
            header('Location: index.php?action=loans');
            exit;
        }

        $returnDate = date('Y-m-d');
        
        if ($this->loanModel->returnBook($loanId, $returnDate)) {
            // Actualizar copias disponibles del libro
            $this->bookModel->updateAvailableCopies($loan['book_id'], 1);
            
            $_SESSION['success'] = 'Devolución registrada exitosamente';
        } else {
            $_SESSION['error'] = 'Error al registrar la devolución';
        }
        
        header('Location: index.php?action=loans');
        exit;
    }

    /**
     * Muestra los préstamos activos del usuario actual
     */
    public function myLoans() {
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['error'] = 'Debes iniciar sesión para ver tus préstamos';
            header('Location: index.php?action=login');
            exit;
        }

        $userId = $_SESSION['user_id'];
        $activeLoans = $this->loanModel->getActiveLoansByUser($userId);
        $loanHistory = $this->loanModel->getUserLoanHistory($userId);
        
        require_once '../app/views/loans/my_loans.php';
    }

    /**
     * Obtiene información de un libro para AJAX
     */
    public function getBookInfo() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            exit;
        }

        $bookId = $_POST['book_id'] ?? 0;
        
        if (!$bookId) {
            http_response_code(400);
            echo json_encode(['error' => 'ID de libro no válido']);
            exit;
        }

        $book = $this->bookModel->getBookById($bookId);
        
        if (!$book) {
            http_response_code(404);
            echo json_encode(['error' => 'Libro no encontrado']);
            exit;
        }

        header('Content-Type: application/json');
        echo json_encode([
            'title' => $book['title'],
            'author' => $book['author_name'],
            'isbn' => $book['isbn'],
            'available_copies' => $book['available_copies']
        ]);
        exit;
    }

    /**
     * Obtiene información de un usuario para AJAX
     */
    public function getUserInfo() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            exit;
        }

        $userId = $_POST['user_id'] ?? 0;
        
        if (!$userId) {
            http_response_code(400);
            echo json_encode(['error' => 'ID de usuario no válido']);
            exit;
        }

        $user = $this->userModel->findById($userId);
        
        if (!$user) {
            http_response_code(404);
            echo json_encode(['error' => 'Usuario no encontrado']);
            exit;
        }

        $activeLoans = $this->loanModel->getActiveLoansByUser($user['id']);

        header('Content-Type: application/json');
        echo json_encode([
            'username' => $user['username'],
            'firstName' => $user['first_name'],
            'lastName' => $user['last_name'],
            'email' => $user['email'],
            'activeLoans' => count($activeLoans)
        ]);
        exit;
    }

    /**
     * Genera reporte de préstamos
     */
    public function generateReport() {
        // Verificar permisos básicos (cualquier usuario logueado puede ver reportes)
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['error'] = 'Debes iniciar sesión para ver reportes';
            header('Location: index.php?action=login');
            exit;
        }

        $startDate = $_GET['start_date'] ?? date('Y-m-01'); // Inicio del mes actual
        $endDate = $_GET['end_date'] ?? date('Y-m-t'); // Fin del mes actual
        $reportType = $_GET['type'] ?? 'general';
        
        // Obtener todos los préstamos
        $allLoans = $this->loanModel->getAllLoans(1, 1000);
        
        // Filtrar por fecha si es necesario
        $filteredLoans = array_filter($allLoans, function($loan) use ($startDate, $endDate) {
            $loanDate = $loan['loan_date'];
            return $loanDate >= $startDate && $loanDate <= $endDate;
        });
        
        // Reindexar el array
        $filteredLoans = array_values($filteredLoans);
        
        require_once '../app/views/loans/report.php';
    }

    /**
     * Valida los datos de un préstamo
     * 
     * @param array $loanData Datos del préstamo a validar
     * @return array Lista de errores de validación
     */
    private function validateLoan($loanData) {
        $errors = [];

        // Validar usuario
        if (empty($loanData['userId'])) {
            $errors[] = 'El usuario es requerido';
        }

        // Validar libro
        if (empty($loanData['bookId'])) {
            $errors[] = 'El libro es requerido';
        }

        // Validar fecha de préstamo
        if (empty($loanData['loanDate'])) {
            $errors[] = 'La fecha de préstamo es requerida';
        } else {
            $loanDate = DateTime::createFromFormat('Y-m-d', $loanData['loanDate']);
            if (!$loanDate) {
                $errors[] = 'La fecha de préstamo no es válida';
            }
        }

        // Validar fecha de devolución
        if (empty($loanData['dueDate'])) {
            $errors[] = 'La fecha de devolución es requerida';
        } else {
            $dueDate = DateTime::createFromFormat('Y-m-d', $loanData['dueDate']);
            if (!$dueDate) {
                $errors[] = 'La fecha de devolución no es válida';
            } elseif ($loanDate && $dueDate <= $loanDate) {
                $errors[] = 'La fecha de devolución debe ser posterior a la fecha de préstamo';
            }
        }

        return $errors;
    }
}
?>