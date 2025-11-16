<?php
/**
 * Modelo para gestionar operaciones de autores en la base de datos
 * 
 * Maneja el CRUD de autores y operaciones relacionadas con libros
 */
class AuthorModel {
    private $dbConnection;
    private $tableName = 'authors';

    /**
     * Constructor del modelo de autores
     * 
     * @param PDO $database Conexión a la base de datos
     */
    public function __construct($database) {
        $this->dbConnection = $database;
    }

    /**
     * Obtiene todos los autores del sistema
     * 
     * @param int $page Página actual para paginación
     * @param int $perPage Elementos por página
     * @return array Lista de autores
     */
    public function getAllAuthors($page = 1, $perPage = ITEMS_PER_PAGE) {
        $offset = ($page - 1) * $perPage;
        
        $query = "SELECT * FROM " . $this->tableName . " 
                 ORDER BY name ASC 
                 LIMIT :limit OFFSET :offset";
        
        $statement = $this->dbConnection->prepare($query);
        $statement->bindValue(':limit', (int)$perPage, PDO::PARAM_INT);
        $statement->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $statement->execute();
        
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene un autor por su ID
     * 
     * @param int $authorId ID del autor a buscar
     * @return array|null Datos del autor o null si no existe
     */
    public function getAuthorById($authorId) {
        $query = "SELECT * FROM " . $this->tableName . " WHERE id = :id";
        $statement = $this->dbConnection->prepare($query);
        $statement->bindParam(':id', $authorId, PDO::PARAM_INT);
        $statement->execute();
        
        return $statement->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Crea un nuevo autor en el sistema
     * 
     * @param array $authorData Datos del autor a crear
     * @return bool True si se creó exitosamente, false en caso contrario
     */
    public function createAuthor($authorData) {
        $query = "INSERT INTO " . $this->tableName . " 
                 (name, nationality, birth_date, biography) 
                 VALUES (:name, :nationality, :birth_date, :biography)";
        
        $statement = $this->dbConnection->prepare($query);
        
        // Bind de parámetros
        $statement->bindParam(':name', $authorData['name']);
        $statement->bindParam(':nationality', $authorData['nationality']);
        $statement->bindParam(':birth_date', $authorData['birthDate']);
        $statement->bindParam(':biography', $authorData['biography']);
        
        return $statement->execute();
    }

    /**
     * Actualiza un autor existente
     * 
     * @param int $authorId ID del autor a actualizar
     * @param array $authorData Nuevos datos del autor
     * @return bool True si se actualizó exitosamente, false en caso contrario
     */
    public function updateAuthor($authorId, $authorData) {
        $query = "UPDATE " . $this->tableName . " 
                 SET name = :name, nationality = :nationality, 
                     birth_date = :birth_date, biography = :biography 
                 WHERE id = :id";
        
        $statement = $this->dbConnection->prepare($query);
        
        $statement->bindParam(':id', $authorId, PDO::PARAM_INT);
        $statement->bindParam(':name', $authorData['name']);
        $statement->bindParam(':nationality', $authorData['nationality']);
        $statement->bindParam(':birth_date', $authorData['birthDate']);
        $statement->bindParam(':biography', $authorData['biography']);
        
        return $statement->execute();
    }

    /**
     * Elimina un autor del sistema
     * 
     * @param int $authorId ID del autor a eliminar
     * @return bool True si se eliminó exitosamente, false en caso contrario
     */
    public function deleteAuthor($authorId) {
        // Verificar si el autor tiene libros asociados
        if ($this->hasBooks($authorId)) {
            return false;
        }
        
        $query = "DELETE FROM " . $this->tableName . " WHERE id = :id";
        $statement = $this->dbConnection->prepare($query);
        $statement->bindParam(':id', $authorId, PDO::PARAM_INT);
        
        return $statement->execute();
    }

    /**
     * Busca autores por nombre o nacionalidad
     * 
     * @param string $searchTerm Término de búsqueda
     * @return array Lista de autores que coinciden con la búsqueda
     */
    public function searchAuthors($searchTerm) {
        $query = "SELECT * FROM " . $this->tableName . " 
                 WHERE name LIKE :search 
                    OR nationality LIKE :search 
                 ORDER BY name";
        
        $statement = $this->dbConnection->prepare($query);
        $searchTerm = '%' . $searchTerm . '%';
        $statement->bindParam(':search', $searchTerm);
        $statement->execute();
        
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene el total de autores en el sistema
     * 
     * @return int Número total de autores
     */
    public function getTotalAuthors() {
        $query = "SELECT COUNT(*) as total FROM " . $this->tableName;
        $statement = $this->dbConnection->prepare($query);
        $statement->execute();
        
        $result = $statement->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }

    /**
     * Verifica si un autor tiene libros asociados
     * 
     * @param int $authorId ID del autor a verificar
     * @return bool True si tiene libros, false en caso contrario
     */
    private function hasBooks($authorId) {
        $query = "SELECT COUNT(*) as book_count FROM books WHERE author_id = :author_id";
        $statement = $this->dbConnection->prepare($query);
        $statement->bindParam(':author_id', $authorId, PDO::PARAM_INT);
        $statement->execute();
        
        $result = $statement->fetch(PDO::FETCH_ASSOC);
        return $result['book_count'] > 0;
    }

    /**
     * Obtiene autores populares (con más libros)
     * 
     * @param int $limit Límite de autores a retornar
     * @return array Lista de autores populares
     */
    public function getPopularAuthors($limit = 5) {
        $query = "SELECT a.*, COUNT(b.id) as book_count 
                 FROM " . $this->tableName . " a 
                 LEFT JOIN books b ON a.id = b.author_id 
                 GROUP BY a.id 
                 ORDER BY book_count DESC 
                 LIMIT :limit";
        
        $statement = $this->dbConnection->prepare($query);
        $statement->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $statement->execute();
        
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene todos los autores para dropdowns
     * 
     * @return array Lista de autores para formularios
     */
    public function getAuthorsForDropdown() {
        $query = "SELECT id, name FROM " . $this->tableName . " ORDER BY name";
        $statement = $this->dbConnection->prepare($query);
        $statement->execute();
        
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>