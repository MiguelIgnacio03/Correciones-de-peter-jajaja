<?php
/**
 * Configuración de la base de datos del sistema de biblioteca
 * 
 * Esta clase maneja la conexión a la base de datos MySQL usando PDO
 * Incluye métodos para establecer y cerrar conexiones
 */
class DatabaseConfig {
    // Propiedades de configuración de la base de datos
    private $hostName = 'localhost';
    private $userName = 'root';
    private $password = '';
    private $databaseName = 'library_management';
    private $charset = 'utf8mb4';
    
    // Objeto de conexión PDO
    public $connection;

    /**
     * Establece conexión con la base de datos
     * 
     * @return PDO|null Retorna el objeto PDO de conexión o null en caso de error
     */
    public function connect() {
        $this->connection = null;
        
        try {
            // Cadena de conexión DSN
            $dsn = "mysql:host=" . $this->hostName . ";dbname=" . $this->databaseName . ";charset=" . $this->charset;
            
            // Opciones de PDO
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            
            $this->connection = new PDO($dsn, $this->userName, $this->password, $options);
            
        } catch(PDOException $exception) {
            // En producción, registrar en log en lugar de mostrar error
            error_log("Error de conexión: " . $exception->getMessage());
            return null;
        }
        
        return $this->connection;
    }

    /**
     * Cierra la conexión a la base de datos
     */
    public function disconnect() {
        $this->connection = null;
    }
}
?>