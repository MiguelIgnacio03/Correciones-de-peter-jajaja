<?php
/**
 * Controlador para manejar operaciones relacionadas con libros
 * 
 * Gestiona el CRUD de libros, búsquedas y visualizaciones
 */
    class BookController {
    
    private $bookModel;
    private $authorModel;
    private $dbConnection;

    /**
     * Constructor del controlador de libros
     * 
     * @param PDO $database Conexión a la base de datos
     */
    public function __construct($database) {
        $this->dbConnection = $database;
        $this->bookModel = new BookModel($database);
        $this->authorModel = new AuthorModel($database);
    }

    /**
     * Muestra el dashboard principal con estadísticas
     */
    public function dashboard() {
        $totalBooks = $this->bookModel->getTotalBooks();
        $recentBooks = $this->bookModel->getAllBooks(1, 5);
        
        // Obtener estadísticas de préstamos para el dashboard
        $loanModel = new LoanModel($this->dbConnection);
        $loanStats = $loanModel->getLoanStats();
        
        // Obtener total de usuarios (solo para admin, pero necesitamos la variable)
        $userModel = new UserModel($this->dbConnection);
        $totalUsers = $userModel->getTotalUsers();
        
        require_once '../app/views/books/dashboard.php';
    }

    /**
     * Muestra la lista de todos los libros
     */
    public function index() {
        $page = $_GET['page'] ?? 1;
        $search = $_GET['search'] ?? '';
        
        if (!empty($search)) {
            $books = $this->bookModel->searchBooks($search);
            $totalBooks = count($books);
        } else {
            $books = $this->bookModel->getAllBooks($page);
            $totalBooks = $this->bookModel->getTotalBooks();
        }
        
        $totalPages = ceil($totalBooks / ITEMS_PER_PAGE);
        
        require_once '../app/views/books/index.php';
    }

    /**
     * Muestra el formulario para crear un nuevo libro
     */
    public function create() {
        $authors = $this->authorModel->getAllAuthors();
        
        require_once '../app/views/books/create.php';
    }

    /**
     * Procesa la creación de un nuevo libro
     */
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['error'] = 'Método no permitido';
            header('Location: index.php?action=books');
            exit;
        }

        // Recoger y limpiar datos
        $bookData = [
            'title' => trim($_POST['title'] ?? ''),
            'isbn' => trim($_POST['isbn'] ?? ''),
            'authorId' => $_POST['author_id'] ?? '',
            'publicationYear' => $_POST['publication_year'] ?? '',
            'genre' => trim($_POST['genre'] ?? ''),
            'publisher' => trim($_POST['publisher'] ?? ''),
            'totalCopies' => $_POST['total_copies'] ?? 1,
            'availableCopies' => $_POST['available_copies'] ?? $_POST['total_copies'] ?? 1,
            'description' => trim($_POST['description'] ?? '')
        ];

        // Validaciones
        $errors = $this->validateBook($bookData);
        
        if (!empty($errors)) {
            $_SESSION['error'] = implode('<br>', $errors);
            $_SESSION['form_data'] = $bookData;
            header('Location: index.php?action=createBook');
            exit;
        }

        // Crear libro
        if ($this->bookModel->createBook($bookData)) {
            $_SESSION['success'] = 'Libro creado exitosamente';
            header('Location: index.php?action=books');
            exit;
        } else {
            $_SESSION['error'] = 'Error al crear el libro';
            $_SESSION['form_data'] = $bookData;
            header('Location: index.php?action=createBook');
            exit;
        }
    }

    /**
     * Muestra el formulario para editar un libro
     */
    public function edit() {
        $bookId = $_GET['id'] ?? 0;
        
        if (!$bookId) {
            $_SESSION['error'] = 'ID de libro no válido';
            header('Location: index.php?action=books');
            exit;
        }

        $book = $this->bookModel->getBookById($bookId);
        
        if (!$book) {
            $_SESSION['error'] = 'Libro no encontrado';
            header('Location: index.php?action=books');
            exit;
        }

        $authors = $this->authorModel->getAllAuthors();
        
        require_once '../app/views/books/edit.php';
    }

    /**
     * Procesa la actualización de un libro
     */
    public function update() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['error'] = 'Método no permitido';
            header('Location: index.php?action=books');
            exit;
        }

        $bookId = $_POST['id'] ?? 0;
        
        if (!$bookId) {
            $_SESSION['error'] = 'ID de libro no válido';
            header('Location: index.php?action=books');
            exit;
        }

        // Recoger y limpiar datos
        $bookData = [
            'title' => trim($_POST['title'] ?? ''),
            'isbn' => trim($_POST['isbn'] ?? ''),
            'authorId' => $_POST['author_id'] ?? '',
            'publicationYear' => $_POST['publication_year'] ?? '',
            'genre' => trim($_POST['genre'] ?? ''),
            'publisher' => trim($_POST['publisher'] ?? ''),
            'totalCopies' => $_POST['total_copies'] ?? 1,
            'availableCopies' => $_POST['available_copies'] ?? 1,
            'description' => trim($_POST['description'] ?? '')
        ];

        // Validaciones
        $errors = $this->validateBook($bookData);
        
        if (!empty($errors)) {
            $_SESSION['error'] = implode('<br>', $errors);
            $_SESSION['form_data'] = $bookData;
            header('Location: index.php?action=editBook&id=' . $bookId);
            exit;
        }

        // Actualizar libro
        if ($this->bookModel->updateBook($bookId, $bookData)) {
            $_SESSION['success'] = 'Libro actualizado exitosamente';
            header('Location: index.php?action=books');
            exit;
        } else {
            $_SESSION['error'] = 'Error al actualizar el libro';
            $_SESSION['form_data'] = $bookData;
            header('Location: index.php?action=editBook&id=' . $bookId);
            exit;
        }
    }

    /**
     * Elimina un libro
     */
    public function delete() {
        $bookId = $_GET['id'] ?? 0;
        
        if (!$bookId) {
            $_SESSION['error'] = 'ID de libro no válido';
            header('Location: index.php?action=books');
            exit;
        }

        if ($this->bookModel->deleteBook($bookId)) {
            $_SESSION['success'] = 'Libro eliminado exitosamente';
        } else {
            $_SESSION['error'] = 'No se puede eliminar el libro. Puede que tenga préstamos activos.';
        }
        
        header('Location: index.php?action=books');
        exit;
    }

    /**
     * Valida los datos de un libro
     * 
     * @param array $bookData Datos del libro a validar
     * @return array Lista de errores de validación
     */
    private function validateBook($bookData) {
        $errors = [];

        // Validar título
        if (empty($bookData['title'])) {
            $errors[] = 'El título es requerido';
        } elseif (strlen($bookData['title']) < 2) {
            $errors[] = 'El título debe tener al menos 2 caracteres';
        }

        // Validar ISBN
        if (empty($bookData['isbn'])) {
            $errors[] = 'El ISBN es requerido';
        }

        // Validar autor
        if (empty($bookData['authorId'])) {
            $errors[] = 'El autor es requerido';
        }

        // Validar año de publicación
        if (!empty($bookData['publicationYear'])) {
            $currentYear = date('Y');
            if ($bookData['publicationYear'] < 1000 || $bookData['publicationYear'] > $currentYear) {
                $errors[] = 'El año de publicación no es válido';
            }
        }

        // Validar copias
        if ($bookData['totalCopies'] < 1) {
            $errors[] = 'Debe haber al menos 1 copia';
        }

        if ($bookData['availableCopies'] < 0 || $bookData['availableCopies'] > $bookData['totalCopies']) {
            $errors[] = 'El número de copias disponibles no es válido';
        }

        return $errors;
    }
}
?>