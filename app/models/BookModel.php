<?php
/**
 * Modelo para gestionar operaciones de libros en la base de datos
 * 
 * Maneja el CRUD de libros, búsquedas y operaciones relacionadas
 */
class BookModel {
    private $dbConnection;
    private $tableName = 'books';

    /**
     * Constructor del modelo de libros
     * 
     * @param PDO $database Conexión a la base de datos
     */
    public function __construct($database) {
        $this->dbConnection = $database;
    }

    /**
     * Obtiene todos los libros con información del autor
     * 
     * @param int $page Página actual para paginación
     * @param int $perPage Elementos por página
     * @return array Lista de libros con información del autor
     */
    public function getAllBooks($page = 1, $perPage = ITEMS_PER_PAGE) {
        $offset = ($page - 1) * $perPage;
        
        $query = "SELECT b.*, a.name as author_name 
                 FROM " . $this->tableName . " b 
                 LEFT JOIN authors a ON b.author_id = a.id 
                 ORDER BY b.created_at DESC 
                 LIMIT :limit OFFSET :offset";
        
        $statement = $this->dbConnection->prepare($query);
        $statement->bindValue(':limit', (int)$perPage, PDO::PARAM_INT);
        $statement->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $statement->execute();
        
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene un libro por su ID con información del autor
     * 
     * @param int $bookId ID del libro a buscar
     * @return array|null Datos del libro o null si no existe
     */
    public function getBookById($bookId) {
        $query = "SELECT b.*, a.name as author_name 
                 FROM " . $this->tableName . " b 
                 LEFT JOIN authors a ON b.author_id = a.id 
                 WHERE b.id = :id";
        
        $statement = $this->dbConnection->prepare($query);
        $statement->bindParam(':id', $bookId, PDO::PARAM_INT);
        $statement->execute();
        
        return $statement->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Crea un nuevo libro en el sistema
     * 
     * @param array $bookData Datos del libro a crear
     * @return bool True si se creó exitosamente, false en caso contrario
     */
    public function createBook($bookData) {
        $query = "INSERT INTO " . $this->tableName . " 
                 (title, isbn, author_id, publication_year, genre, publisher, total_copies, available_copies, description) 
                 VALUES (:title, :isbn, :author_id, :publication_year, :genre, :publisher, :total_copies, :available_copies, :description)";
        
        $statement = $this->dbConnection->prepare($query);
        
        // Bind de parámetros
        $statement->bindParam(':title', $bookData['title']);
        $statement->bindParam(':isbn', $bookData['isbn']);
        $statement->bindParam(':author_id', $bookData['authorId'], PDO::PARAM_INT);
        $statement->bindParam(':publication_year', $bookData['publicationYear'], PDO::PARAM_INT);
        $statement->bindParam(':genre', $bookData['genre']);
        $statement->bindParam(':publisher', $bookData['publisher']);
        $statement->bindParam(':total_copies', $bookData['totalCopies'], PDO::PARAM_INT);
        $statement->bindParam(':available_copies', $bookData['availableCopies'], PDO::PARAM_INT);
        $statement->bindParam(':description', $bookData['description']);
        
        return $statement->execute();
    }

    /**
     * Actualiza un libro existente
     * 
     * @param int $bookId ID del libro a actualizar
     * @param array $bookData Nuevos datos del libro
     * @return bool True si se actualizó exitosamente, false en caso contrario
     */
    public function updateBook($bookId, $bookData) {
        $query = "UPDATE " . $this->tableName . " 
                 SET title = :title, isbn = :isbn, author_id = :author_id, 
                     publication_year = :publication_year, genre = :genre, 
                     publisher = :publisher, total_copies = :total_copies, 
                     available_copies = :available_copies, description = :description 
                 WHERE id = :id";
        
        $statement = $this->dbConnection->prepare($query);
        
        $statement->bindParam(':id', $bookId, PDO::PARAM_INT);
        $statement->bindParam(':title', $bookData['title']);
        $statement->bindParam(':isbn', $bookData['isbn']);
        $statement->bindParam(':author_id', $bookData['authorId'], PDO::PARAM_INT);
        $statement->bindParam(':publication_year', $bookData['publicationYear'], PDO::PARAM_INT);
        $statement->bindParam(':genre', $bookData['genre']);
        $statement->bindParam(':publisher', $bookData['publisher']);
        $statement->bindParam(':total_copies', $bookData['totalCopies'], PDO::PARAM_INT);
        $statement->bindParam(':available_copies', $bookData['availableCopies'], PDO::PARAM_INT);
        $statement->bindParam(':description', $bookData['description']);
        
        return $statement->execute();
    }

    /**
     * Elimina un libro del sistema
     * 
     * @param int $bookId ID del libro a eliminar
     * @return bool True si se eliminó exitosamente, false en caso contrario
     */
    public function deleteBook($bookId) {
        // Verificar si el libro tiene préstamos activos
        if ($this->hasActiveLoans($bookId)) {
            return false;
        }
        
        $query = "DELETE FROM " . $this->tableName . " WHERE id = :id";
        $statement = $this->dbConnection->prepare($query);
        $statement->bindParam(':id', $bookId, PDO::PARAM_INT);
        
        return $statement->execute();
    }

    /**
     * Busca libros por título, autor o ISBN
     * 
     * @param string $searchTerm Término de búsqueda
     * @return array Lista de libros que coinciden con la búsqueda
     */
    public function searchBooks($searchTerm) {
        $query = "SELECT b.*, a.name as author_name 
                 FROM " . $this->tableName . " b 
                 LEFT JOIN authors a ON b.author_id = a.id 
                 WHERE b.title LIKE :search 
                    OR a.name LIKE :search 
                    OR b.isbn LIKE :search 
                 ORDER BY b.title";
        
        $statement = $this->dbConnection->prepare($query);
        $searchTerm = '%' . $searchTerm . '%';
        $statement->bindParam(':search', $searchTerm);
        $statement->execute();
        
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene el total de libros en el sistema
     * 
     * @return int Número total de libros
     */
    public function getTotalBooks() {
        $query = "SELECT COUNT(*) as total FROM " . $this->tableName;
        $statement = $this->dbConnection->prepare($query);
        $statement->execute();
        
        $result = $statement->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }

    /**
     * Verifica si un libro tiene préstamos activos
     * 
     * @param int $bookId ID del libro a verificar
     * @return bool True si tiene préstamos activos, false en caso contrario
     */
    private function hasActiveLoans($bookId) {
        $query = "SELECT COUNT(*) as active_loans FROM loans WHERE book_id = :book_id AND status = 'active'";
        $statement = $this->dbConnection->prepare($query);
        $statement->bindParam(':book_id', $bookId, PDO::PARAM_INT);
        $statement->execute();
        
        $result = $statement->fetch(PDO::FETCH_ASSOC);
        return $result['active_loans'] > 0;
    }

    /**
     * Actualiza el número de copias disponibles de un libro
     * 
     * @param int $bookId ID del libro
     * @param int $change Cambio en el número de copias (positivo o negativo)
     * @return bool True si se actualizó exitosamente
     */
    public function updateAvailableCopies($bookId, $change) {
        // Consulta optimizada que evita el problema de parámetros duplicados
        $query = "UPDATE " . $this->tableName . " 
                 SET available_copies = GREATEST(0, available_copies + :change) 
                 WHERE id = :id";
        
        $statement = $this->dbConnection->prepare($query);
        $statement->bindParam(':change', $change, PDO::PARAM_INT);
        $statement->bindParam(':id', $bookId, PDO::PARAM_INT);
        
        return $statement->execute();
    }

    /**
     * Obtiene libros por autor
     * 
     * @param int $authorId ID del autor
     * @return array Lista de libros del autor
     */
    public function getBooksByAuthor($authorId) {
        $query = "SELECT * FROM " . $this->tableName . " 
                 WHERE author_id = :author_id 
                 ORDER BY title";
        
        $statement = $this->dbConnection->prepare($query);
        $statement->bindParam(':author_id', $authorId, PDO::PARAM_INT);
        $statement->execute();
        
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene libros disponibles para préstamo
     * 
     * @return array Lista de libros con copias disponibles
     */
    public function getAvailableBooks() {
        $query = "SELECT b.*, a.name as author_name 
                 FROM " . $this->tableName . " b 
                 LEFT JOIN authors a ON b.author_id = a.id 
                 WHERE b.available_copies > 0 
                 ORDER BY b.title";
        
        $statement = $this->dbConnection->prepare($query);
        $statement->execute();
        
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene libros recientemente agregados
     * 
     * @param int $limit Límite de libros a retornar
     * @return array Lista de libros recientes
     */
    public function getRecentBooks($limit = 5) {
        $query = "SELECT b.*, a.name as author_name 
                 FROM " . $this->tableName . " b 
                 LEFT JOIN authors a ON b.author_id = a.id 
                 ORDER BY b.created_at DESC 
                 LIMIT :limit";
        
        $statement = $this->dbConnection->prepare($query);
        $statement->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $statement->execute();
        
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene estadísticas básicas de libros
     * 
     * @return array Estadísticas de libros
     */
    public function getBookStats() {
        $stats = [];
        
        // Total de libros
        $query = "SELECT COUNT(*) as total FROM " . $this->tableName;
        $statement = $this->dbConnection->prepare($query);
        $statement->execute();
        $stats['total_books'] = $statement->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Libros disponibles
        $query = "SELECT COUNT(*) as available FROM " . $this->tableName . " WHERE available_copies > 0";
        $statement = $this->dbConnection->prepare($query);
        $statement->execute();
        $stats['available_books'] = $statement->fetch(PDO::FETCH_ASSOC)['available'];
        
        // Libros sin stock
        $query = "SELECT COUNT(*) as out_of_stock FROM " . $this->tableName . " WHERE available_copies = 0";
        $statement = $this->dbConnection->prepare($query);
        $statement->execute();
        $stats['out_of_stock_books'] = $statement->fetch(PDO::FETCH_ASSOC)['out_of_stock'];
        
        return $stats;
    }
}
?>