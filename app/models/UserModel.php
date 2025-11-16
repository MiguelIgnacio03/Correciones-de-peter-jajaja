<?php
/**
 * Modelo para gestionar operaciones de usuarios en la base de datos
 * 
 * Maneja el CRUD de usuarios y operaciones de autenticación
 */
class UserModel {
    private $dbConnection;
    private $tableName = 'users';

    /**
     * Constructor del modelo de usuario
     * 
     * @param PDO $database Conexión a la base de datos
     */
    public function __construct($database) {
        $this->dbConnection = $database;
    }

    /**
     * Busca usuario por nombre de usuario
     * 
     * @param string $username Nombre de usuario a buscar
     * @return array|null Datos del usuario o null si no existe
     */
    public function findByUsername($username) {
        $query = "SELECT * FROM " . $this->tableName . " WHERE username = :username AND is_active = TRUE";
        $statement = $this->dbConnection->prepare($query);
        $statement->bindParam(':username', $username);
        $statement->execute();
        
        return $statement->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Busca usuario por email
     * 
     * @param string $email Email a buscar
     * @return array|null Datos del usuario o null si no existe
     */
    public function findByEmail($email) {
        $query = "SELECT * FROM " . $this->tableName . " WHERE email = :email AND is_active = TRUE";
        $statement = $this->dbConnection->prepare($query);
        $statement->bindParam(':email', $email);
        $statement->execute();
        
        return $statement->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Busca usuario por ID
     * 
     * @param int $userId ID del usuario a buscar
     * @return array|null Datos del usuario o null si no existe
     */
    public function findById($userId) {
        $query = "SELECT * FROM " . $this->tableName . " WHERE id = :id AND is_active = TRUE";
        $statement = $this->dbConnection->prepare($query);
        $statement->bindParam(':id', $userId, PDO::PARAM_INT);
        $statement->execute();
        
        return $statement->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Crea un nuevo usuario en el sistema
     * 
     * @param array $userData Datos del usuario a crear
     * @return bool True si se creó exitosamente, false en caso contrario
     */
    public function createUser($userData) {
        $query = "INSERT INTO " . $this->tableName . " 
                 (username, email, password_hash, first_name, last_name, role) 
                 VALUES (:username, :email, :password_hash, :first_name, :last_name, :role)";
        
        $statement = $this->dbConnection->prepare($query);
        
        // Bind de parámetros
        $statement->bindParam(':username', $userData['username']);
        $statement->bindParam(':email', $userData['email']);
        $statement->bindParam(':password_hash', $userData['password_hash']);
        $statement->bindParam(':first_name', $userData['firstName']);
        $statement->bindParam(':last_name', $userData['lastName']);
        $statement->bindParam(':role', $userData['role']);
        
        return $statement->execute();
    }

    /**
     * Verifica si las credenciales de login son válidas
     * 
     * @param string $username Nombre de usuario
     * @param string $password Contraseña sin encriptar
     * @return array|null Datos del usuario si las credenciales son válidas
     */
    public function verifyCredentials($username, $password) {
        $user = $this->findByUsername($username);
        
        if ($user && password_verify($password, $user['password_hash'])) {
            return $user;
        }
        
        return null;
    }

    /**
     * Obtiene todos los usuarios del sistema
     * 
     * @return array Lista de usuarios
     */
    public function getAllUsers() {
        $query = "SELECT id, username, email, first_name, last_name, role, is_active, created_at 
                 FROM " . $this->tableName . " 
                 ORDER BY created_at DESC";
        $statement = $this->dbConnection->prepare($query);
        $statement->execute();
        
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene el total de usuarios activos en el sistema
     * 
     * @return int Número total de usuarios activos
     */
    public function getTotalUsers() {
        $query = "SELECT COUNT(*) as total FROM " . $this->tableName . " WHERE is_active = TRUE";
        $statement = $this->dbConnection->prepare($query);
        $statement->execute();
        
        $result = $statement->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }

    /**
     * Actualiza el rol de un usuario
     * 
     * @param int $userId ID del usuario
     * @param string $newRole Nuevo rol del usuario
     * @return bool True si se actualizó correctamente
     */
    public function updateUserRole($userId, $newRole) {
        $query = "UPDATE " . $this->tableName . " SET role = :role WHERE id = :id";
        $statement = $this->dbConnection->prepare($query);
        $statement->bindParam(':role', $newRole);
        $statement->bindParam(':id', $userId, PDO::PARAM_INT);
        
        return $statement->execute();
    }

    /**
     * Activa o desactiva un usuario
     * 
     * @param int $userId ID del usuario
     * @param bool $isActive Estado del usuario (true = activo, false = inactivo)
     * @return bool True si se actualizó correctamente
     */
    public function updateUserStatus($userId, $isActive) {
        $query = "UPDATE " . $this->tableName . " SET is_active = :is_active WHERE id = :id";
        $statement = $this->dbConnection->prepare($query);
        $statement->bindParam(':is_active', $isActive, PDO::PARAM_BOOL);
        $statement->bindParam(':id', $userId, PDO::PARAM_INT);
        
        return $statement->execute();
    }

    /**
     * Verifica si un username ya existe
     * 
     * @param string $username Username a verificar
     * @param int $excludeUserId ID de usuario a excluir (para ediciones)
     * @return bool True si el username ya existe
     */
    public function usernameExists($username, $excludeUserId = null) {
        $query = "SELECT COUNT(*) as count FROM " . $this->tableName . " 
                 WHERE username = :username AND is_active = TRUE";
        
        if ($excludeUserId) {
            $query .= " AND id != :exclude_id";
        }
        
        $statement = $this->dbConnection->prepare($query);
        $statement->bindParam(':username', $username);
        
        if ($excludeUserId) {
            $statement->bindParam(':exclude_id', $excludeUserId, PDO::PARAM_INT);
        }
        
        $statement->execute();
        $result = $statement->fetch(PDO::FETCH_ASSOC);
        
        return $result['count'] > 0;
    }

    /**
     * Verifica si un email ya existe
     * 
     * @param string $email Email a verificar
     * @param int $excludeUserId ID de usuario a excluir (para ediciones)
     * @return bool True si el email ya existe
     */
    public function emailExists($email, $excludeUserId = null) {
        $query = "SELECT COUNT(*) as count FROM " . $this->tableName . " 
                 WHERE email = :email AND is_active = TRUE";
        
        if ($excludeUserId) {
            $query .= " AND id != :exclude_id";
        }
        
        $statement = $this->dbConnection->prepare($query);
        $statement->bindParam(':email', $email);
        
        if ($excludeUserId) {
            $statement->bindParam(':exclude_id', $excludeUserId, PDO::PARAM_INT);
        }
        
        $statement->execute();
        $result = $statement->fetch(PDO::FETCH_ASSOC);
        
        return $result['count'] > 0;
    }

    /**
     * Actualiza la contraseña de un usuario
     * 
     * @param int $userId ID del usuario
     * @param string $newPasswordHash Nueva contraseña hasheada
     * @return bool True si se actualizó correctamente
     */
    public function updatePassword($userId, $newPasswordHash) {
        $query = "UPDATE " . $this->tableName . " SET password_hash = :password_hash WHERE id = :id";
        $statement = $this->dbConnection->prepare($query);
        $statement->bindParam(':password_hash', $newPasswordHash);
        $statement->bindParam(':id', $userId, PDO::PARAM_INT);
        
        return $statement->execute();
    }

    /**
     * Busca usuarios por nombre o apellido
     * 
     * @param string $searchTerm Término de búsqueda
     * @return array Lista de usuarios que coinciden
     */
    public function searchUsers($searchTerm) {
        $query = "SELECT id, username, email, first_name, last_name, role, is_active, created_at 
                 FROM " . $this->tableName . " 
                 WHERE (first_name LIKE :search OR last_name LIKE :search OR username LIKE :search OR email LIKE :search)
                 AND is_active = TRUE 
                 ORDER BY first_name, last_name";
        
        $statement = $this->dbConnection->prepare($query);
        $searchTerm = '%' . $searchTerm . '%';
        $statement->bindParam(':search', $searchTerm);
        $statement->execute();
        
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>