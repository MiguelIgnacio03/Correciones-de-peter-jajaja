<?php
/**
 * Modelo para gestionar operaciones de préstamos en la base de datos
 * 
 * Maneja el CRUD de préstamos, devoluciones y consultas relacionadas
 */
class LoanModel {
    private $dbConnection;
    private $tableName = 'loans';

    /**
     * Constructor del modelo de préstamos
     * 
     * @param PDO $database Conexión a la base de datos
     */
    public function __construct($database) {
        $this->dbConnection = $database;
    }

    /**
     * Obtiene todos los préstamos con información de usuario y libro
     * 
     * @param int $page Página actual para paginación
     * @param int $perPage Elementos por página
     * @return array Lista de préstamos con información relacionada
     */
    public function getAllLoans($page = 1, $perPage = ITEMS_PER_PAGE) {
        $offset = ($page - 1) * $perPage;
        
        $query = "SELECT l.*, 
                         u.username, u.first_name, u.last_name,
                         b.title as book_title, b.isbn as book_isbn,
                         a.name as author_name
                 FROM " . $this->tableName . " l 
                 JOIN users u ON l.user_id = u.id 
                 JOIN books b ON l.book_id = b.id 
                 JOIN authors a ON b.author_id = a.id 
                 ORDER BY l.created_at DESC 
                 LIMIT :limit OFFSET :offset";
        
        $statement = $this->dbConnection->prepare($query);
        $statement->bindValue(':limit', (int)$perPage, PDO::PARAM_INT);
        $statement->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $statement->execute();
        
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene un préstamo por su ID con información completa
     * 
     * @param int $loanId ID del préstamo a buscar
     * @return array|null Datos del préstamo o null si no existe
     */
    public function getLoanById($loanId) {
        $query = "SELECT l.*, 
                         u.username, u.first_name, u.last_name, u.email,
                         b.title as book_title, b.isbn as book_isbn, b.author_id,
                         a.name as author_name
                 FROM " . $this->tableName . " l 
                 JOIN users u ON l.user_id = u.id 
                 JOIN books b ON l.book_id = b.id 
                 JOIN authors a ON b.author_id = a.id 
                 WHERE l.id = :id";
        
        $statement = $this->dbConnection->prepare($query);
        $statement->bindParam(':id', $loanId, PDO::PARAM_INT);
        $statement->execute();
        
        return $statement->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Crea un nuevo préstamo en el sistema
     * 
     * @param array $loanData Datos del préstamo a crear
     * @return bool True si se creó exitosamente, false en caso contrario
     */
    public function createLoan($loanData) {
        $query = "INSERT INTO " . $this->tableName . " 
                 (user_id, book_id, loan_date, due_date, status) 
                 VALUES (:user_id, :book_id, :loan_date, :due_date, :status)";
        
        $statement = $this->dbConnection->prepare($query);
        
        // Bind de parámetros
        $statement->bindParam(':user_id', $loanData['userId'], PDO::PARAM_INT);
        $statement->bindParam(':book_id', $loanData['bookId'], PDO::PARAM_INT);
        $statement->bindParam(':loan_date', $loanData['loanDate']);
        $statement->bindParam(':due_date', $loanData['dueDate']);
        $statement->bindParam(':status', $loanData['status']);
        
        return $statement->execute();
    }

    /**
     * Registra la devolución de un libro
     * 
     * @param int $loanId ID del préstamo a actualizar
     * @param string $returnDate Fecha de devolución
     * @return bool True si se actualizó exitosamente
     */
    public function returnBook($loanId, $returnDate) {
        $query = "UPDATE " . $this->tableName . " 
                 SET return_date = :return_date, status = :status 
                 WHERE id = :id";
        
        $statement = $this->dbConnection->prepare($query);
        $statement->bindParam(':return_date', $returnDate);
        $statement->bindParam(':status', $status);
        $statement->bindParam(':id', $loanId, PDO::PARAM_INT);
        
        $status = LOAN_RETURNED;
        
        return $statement->execute();
    }

    /**
     * Obtiene los préstamos activos de un usuario
     * 
     * @param int $userId ID del usuario
     * @return array Lista de préstamos activos del usuario
     */
    public function getActiveLoansByUser($userId) {
        $query = "SELECT l.*, b.title, b.isbn, a.name as author_name
                 FROM " . $this->tableName . " l 
                 JOIN books b ON l.book_id = b.id 
                 JOIN authors a ON b.author_id = a.id 
                 WHERE l.user_id = :user_id AND l.status = 'active' 
                 ORDER BY l.due_date ASC";
        
        $statement = $this->dbConnection->prepare($query);
        $statement->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $statement->execute();
        
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene los préstamos activos de un libro
     * 
     * @param int $bookId ID del libro
     * @return array Lista de préstamos activos del libro
     */
    public function getActiveLoansByBook($bookId) {
        $query = "SELECT l.*, u.username, u.first_name, u.last_name
                 FROM " . $this->tableName . " l 
                 JOIN users u ON l.user_id = u.id 
                 WHERE l.book_id = :book_id AND l.status = 'active' 
                 ORDER BY l.due_date ASC";
        
        $statement = $this->dbConnection->prepare($query);
        $statement->bindParam(':book_id', $bookId, PDO::PARAM_INT);
        $statement->execute();
        
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Verifica si un usuario tiene préstamos activos
     * 
     * @param int $userId ID del usuario
     * @return bool True si tiene préstamos activos
     */
    public function userHasActiveLoans($userId) {
        $query = "SELECT COUNT(*) as active_loans 
                 FROM " . $this->tableName . " 
                 WHERE user_id = :user_id AND status = 'active'";
        
        $statement = $this->dbConnection->prepare($query);
        $statement->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $statement->execute();
        
        $result = $statement->fetch(PDO::FETCH_ASSOC);
        return $result['active_loans'] > 0;
    }

    /**
     * Verifica si un libro está disponible para préstamo
     * 
     * @param int $bookId ID del libro
     * @return bool True si el libro está disponible
     */
    public function isBookAvailable($bookId) {
        $query = "SELECT available_copies FROM books WHERE id = :book_id";
        $statement = $this->dbConnection->prepare($query);
        $statement->bindParam(':book_id', $bookId, PDO::PARAM_INT);
        $statement->execute();
        
        $result = $statement->fetch(PDO::FETCH_ASSOC);
        return $result && $result['available_copies'] > 0;
    }

    /**
     * Obtiene el total de préstamos en el sistema
     * 
     * @return int Número total de préstamos
     */
    public function getTotalLoans() {
        $query = "SELECT COUNT(*) as total FROM " . $this->tableName;
        $statement = $this->dbConnection->prepare($query);
        $statement->execute();
        
        $result = $statement->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }

    /**
     * Obtiene estadísticas de préstamos
     * 
     * @return array Estadísticas de préstamos
     */
    public function getLoanStats() {
    $stats = [];
    
    // Total de préstamos activos
    $query = "SELECT COUNT(*) as count FROM " . $this->tableName . " WHERE status = 'active'";
    $statement = $this->dbConnection->prepare($query);
    $statement->execute();
    $stats['active_loans'] = $statement->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;
    
    // Préstamos vencidos
    $query = "SELECT COUNT(*) as count FROM " . $this->tableName . " 
             WHERE status = 'active' AND due_date < CURDATE()";
    $statement = $this->dbConnection->prepare($query);
    $statement->execute();
    $stats['overdue_loans'] = $statement->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;
    
    // Préstamos devueltos este mes
    $query = "SELECT COUNT(*) as count FROM " . $this->tableName . " 
             WHERE status = 'returned' AND MONTH(return_date) = MONTH(CURDATE()) 
             AND YEAR(return_date) = YEAR(CURDATE())";
    $statement = $this->dbConnection->prepare($query);
    $statement->execute();
    $stats['returned_this_month'] = $statement->fetch(PDO::FETCH_ASSOC)['count'] ?? 0;
    
    return $stats;
}

    /**
     * Actualiza el estado de préstamos vencidos
     * 
     * @return int Número de préstamos actualizados
     */
    public function updateOverdueLoans() {
        $query = "UPDATE " . $this->tableName . " 
                 SET status = 'overdue' 
                 WHERE status = 'active' AND due_date < CURDATE()";
        
        $statement = $this->dbConnection->prepare($query);
        $statement->execute();
        
        return $statement->rowCount();
    }

    /**
     * Busca préstamos por usuario, libro o estado
     * 
     * @param string $searchTerm Término de búsqueda
     * @return array Lista de préstamos que coinciden con la búsqueda
     */
    public function searchLoans($searchTerm) {
        $query = "SELECT l.*, 
                         u.username, u.first_name, u.last_name,
                         b.title as book_title, b.isbn as book_isbn,
                         a.name as author_name
                 FROM " . $this->tableName . " l 
                 JOIN users u ON l.user_id = u.id 
                 JOIN books b ON l.book_id = b.id 
                 JOIN authors a ON b.author_id = a.id 
                 WHERE u.username LIKE :search 
                    OR u.first_name LIKE :search 
                    OR u.last_name LIKE :search 
                    OR b.title LIKE :search 
                    OR a.name LIKE :search 
                 ORDER BY l.created_at DESC";
        
        $statement = $this->dbConnection->prepare($query);
        $searchTerm = '%' . $searchTerm . '%';
        $statement->bindParam(':search', $searchTerm);
        $statement->execute();
        
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene el historial de préstamos de un usuario
     * 
     * @param int $userId ID del usuario
     * @return array Historial de préstamos del usuario
     */
    public function getUserLoanHistory($userId) {
        $query = "SELECT l.*, b.title, b.isbn, a.name as author_name
                 FROM " . $this->tableName . " l 
                 JOIN books b ON l.book_id = b.id 
                 JOIN authors a ON b.author_id = a.id 
                 WHERE l.user_id = :user_id 
                 ORDER BY l.loan_date DESC";
        
        $statement = $this->dbConnection->prepare($query);
        $statement->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $statement->execute();
        
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>