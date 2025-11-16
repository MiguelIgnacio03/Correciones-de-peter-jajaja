<?php
/**
 * Controlador para manejar operaciones relacionadas con autores
 * 
 * Gestiona el CRUD de autores y operaciones relacionadas
 */
class AuthorController {
    private $authorModel;
    private $bookModel;

    /**
     * Constructor del controlador de autores
     * 
     * @param PDO $database Conexión a la base de datos
     */
    public function __construct($database) {
        $this->authorModel = new AuthorModel($database);
        $this->bookModel = new BookModel($database);
    }

    /**
     * Muestra la lista de todos los autores
     */
    public function index() {
        $page = $_GET['page'] ?? 1;
        $search = $_GET['search'] ?? '';
        
        if (!empty($search)) {
            $authors = $this->authorModel->searchAuthors($search);
            $totalAuthors = count($authors);
        } else {
            $authors = $this->authorModel->getAllAuthors($page);
            $totalAuthors = $this->authorModel->getTotalAuthors();
        }
        
        $totalPages = ceil($totalAuthors / ITEMS_PER_PAGE);
        
        require_once '../app/views/authors/index.php';
    }

    /**
     * Muestra el formulario para crear un nuevo autor
     */
    public function create() {
        require_once '../app/views/authors/create.php';
    }

    /**
     * Procesa la creación de un nuevo autor
     */
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['error'] = 'Método no permitido';
            header('Location: index.php?action=authors');
            exit;
        }

        // Recoger y limpiar datos
        $authorData = [
            'name' => trim($_POST['name'] ?? ''),
            'nationality' => trim($_POST['nationality'] ?? ''),
            'birthDate' => $_POST['birth_date'] ?? '',
            'biography' => trim($_POST['biography'] ?? '')
        ];

        // Validaciones
        $errors = $this->validateAuthor($authorData);
        
        if (!empty($errors)) {
            $_SESSION['error'] = implode('<br>', $errors);
            $_SESSION['form_data'] = $authorData;
            header('Location: index.php?action=createAuthor');
            exit;
        }

        // Crear autor
        if ($this->authorModel->createAuthor($authorData)) {
            $_SESSION['success'] = 'Autor creado exitosamente';
            header('Location: index.php?action=authors');
            exit;
        } else {
            $_SESSION['error'] = 'Error al crear el autor';
            $_SESSION['form_data'] = $authorData;
            header('Location: index.php?action=createAuthor');
            exit;
        }
    }

    /**
     * Muestra el formulario para editar un autor
     */
    public function edit() {
        $authorId = $_GET['id'] ?? 0;
        
        if (!$authorId) {
            $_SESSION['error'] = 'ID de autor no válido';
            header('Location: index.php?action=authors');
            exit;
        }

        $author = $this->authorModel->getAuthorById($authorId);
        
        if (!$author) {
            $_SESSION['error'] = 'Autor no encontrado';
            header('Location: index.php?action=authors');
            exit;
        }

        // Obtener libros del autor
        $books = $this->bookModel->getBooksByAuthor($authorId);
        
        require_once '../app/views/authors/edit.php';
    }

    /**
     * Procesa la actualización de un autor
     */
    public function update() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['error'] = 'Método no permitido';
            header('Location: index.php?action=authors');
            exit;
        }

        $authorId = $_POST['id'] ?? 0;
        
        if (!$authorId) {
            $_SESSION['error'] = 'ID de autor no válido';
            header('Location: index.php?action=authors');
            exit;
        }

        // Recoger y limpiar datos
        $authorData = [
            'name' => trim($_POST['name'] ?? ''),
            'nationality' => trim($_POST['nationality'] ?? ''),
            'birthDate' => $_POST['birth_date'] ?? '',
            'biography' => trim($_POST['biography'] ?? '')
        ];

        // Validaciones
        $errors = $this->validateAuthor($authorData);
        
        if (!empty($errors)) {
            $_SESSION['error'] = implode('<br>', $errors);
            $_SESSION['form_data'] = $authorData;
            header('Location: index.php?action=editAuthor&id=' . $authorId);
            exit;
        }

        // Actualizar autor
        if ($this->authorModel->updateAuthor($authorId, $authorData)) {
            $_SESSION['success'] = 'Autor actualizado exitosamente';
            header('Location: index.php?action=authors');
            exit;
        } else {
            $_SESSION['error'] = 'Error al actualizar el autor';
            $_SESSION['form_data'] = $authorData;
            header('Location: index.php?action=editAuthor&id=' . $authorId);
            exit;
        }
    }

    /**
     * Elimina un autor
     */
    public function delete() {
        $authorId = $_GET['id'] ?? 0;
        
        if (!$authorId) {
            $_SESSION['error'] = 'ID de autor no válido';
            header('Location: index.php?action=authors');
            exit;
        }

        if ($this->authorModel->deleteAuthor($authorId)) {
            $_SESSION['success'] = 'Autor eliminado exitosamente';
        } else {
            $_SESSION['error'] = 'No se puede eliminar el autor. Tiene libros asociados.';
        }
        
        header('Location: index.php?action=authors');
        exit;
    }

    /**
     * Muestra el perfil de un autor con sus libros
     */
    public function show() {
        $authorId = $_GET['id'] ?? 0;
        
        if (!$authorId) {
            $_SESSION['error'] = 'ID de autor no válido';
            header('Location: index.php?action=authors');
            exit;
        }

        $author = $this->authorModel->getAuthorById($authorId);
        
        if (!$author) {
            $_SESSION['error'] = 'Autor no encontrado';
            header('Location: index.php?action=authors');
            exit;
        }

        // Obtener libros del autor
        $books = $this->bookModel->getBooksByAuthor($authorId);
        
        require_once '../app/views/authors/show.php';
    }

    /**
     * Valida los datos de un autor
     * 
     * @param array $authorData Datos del autor a validar
     * @return array Lista de errores de validación
     */
    private function validateAuthor($authorData) {
        $errors = [];

        // Validar nombre
        if (empty($authorData['name'])) {
            $errors[] = 'El nombre es requerido';
        } elseif (strlen($authorData['name']) < 2) {
            $errors[] = 'El nombre debe tener al menos 2 caracteres';
        }

        // Validar nacionalidad
        if (empty($authorData['nationality'])) {
            $errors[] = 'La nacionalidad es requerida';
        }

        // Validar fecha de nacimiento
        if (!empty($authorData['birthDate'])) {
            $birthDate = DateTime::createFromFormat('Y-m-d', $authorData['birthDate']);
            $currentDate = new DateTime();
            
            if (!$birthDate || $birthDate > $currentDate) {
                $errors[] = 'La fecha de nacimiento no es válida';
            }
        }

        // Validar biografía
        if (!empty($authorData['biography']) && strlen($authorData['biography']) < 10) {
            $errors[] = 'La biografía debe tener al menos 10 caracteres';
        }

        return $errors;
    }

    /**
     * Obtiene autores para API (usado en formularios de libros)
     */
    public function getAuthorsApi() {
        $authors = $this->authorModel->getAuthorsForDropdown();
        header('Content-Type: application/json');
        echo json_encode($authors);
        exit;
    }
}
?>