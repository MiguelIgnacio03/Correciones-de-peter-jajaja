<?php
/**
 * Modelo para generar reportes y estadísticas del sistema
 * 
 * Maneja consultas complejas para reportes y dashboard
 */
class ReportModel {
    private $dbConnection;

    /**
     * Constructor del modelo de reportes
     * 
     * @param PDO $database Conexión a la base de datos
     */
    public function __construct($database) {
        $this->dbConnection = $database;
    }

    /**
     * Obtiene estadísticas generales del sistema
     * 
     * @return array Estadísticas completas del sistema
     */
    public function getSystemStats() {
        $stats = [];
        
        // Total de libros
        $query = "SELECT COUNT(*) as count FROM books";
        $statement = $this->dbConnection->prepare($query);
        $statement->execute();
        $stats['total_books'] = $statement->fetch(PDO::FETCH_ASSOC)['count'];
        
        // Total de autores
        $query = "SELECT COUNT(*) as count FROM authors";
        $statement = $this->dbConnection->prepare($query);
        $statement->execute();
        $stats['total_authors'] = $statement->fetch(PDO::FETCH_ASSOC)['count'];
        
        // Total de usuarios
        $query = "SELECT COUNT(*) as count FROM users WHERE is_active = TRUE";
        $statement = $this->dbConnection->prepare($query);
        $statement->execute();
        $stats['total_users'] = $statement->fetch(PDO::FETCH_ASSOC)['count'];
        
        // Total de préstamos
        $query = "SELECT COUNT(*) as count FROM loans";
        $statement = $this->dbConnection->prepare($query);
        $statement->execute();
        $stats['total_loans'] = $statement->fetch(PDO::FETCH_ASSOC)['count'];
        
        // Préstamos activos
        $query = "SELECT COUNT(*) as count FROM loans WHERE status = 'active'";
        $statement = $this->dbConnection->prepare($query);
        $statement->execute();
        $stats['active_loans'] = $statement->fetch(PDO::FETCH_ASSOC)['count'];
        
        // Préstamos vencidos
        $query = "SELECT COUNT(*) as count FROM loans WHERE status = 'overdue'";
        $statement = $this->dbConnection->prepare($query);
        $statement->execute();
        $stats['overdue_loans'] = $statement->fetch(PDO::FETCH_ASSOC)['count'];
        
        // Libros más prestados
        $query = "SELECT b.title, b.isbn, a.name as author_name, COUNT(l.id) as loan_count 
                 FROM books b 
                 JOIN authors a ON b.author_id = a.id 
                 LEFT JOIN loans l ON b.id = l.book_id 
                 GROUP BY b.id 
                 ORDER BY loan_count DESC 
                 LIMIT 10";
        $statement = $this->dbConnection->prepare($query);
        $statement->execute();
        $stats['most_borrowed_books'] = $statement->fetchAll(PDO::FETCH_ASSOC);
        
        // Autores más populares
        $query = "SELECT a.name, a.nationality, COUNT(l.id) as loan_count 
                 FROM authors a 
                 LEFT JOIN books b ON a.id = b.author_id 
                 LEFT JOIN loans l ON b.id = l.book_id 
                 GROUP BY a.id 
                 ORDER BY loan_count DESC 
                 LIMIT 10";
        $statement = $this->dbConnection->prepare($query);
        $statement->execute();
        $stats['most_popular_authors'] = $statement->fetchAll(PDO::FETCH_ASSOC);
        
        // Usuarios más activos
        $query = "SELECT u.username, u.first_name, u.last_name, COUNT(l.id) as loan_count 
                 FROM users u 
                 LEFT JOIN loans l ON u.id = l.user_id 
                 WHERE u.is_active = TRUE 
                 GROUP BY u.id 
                 ORDER BY loan_count DESC 
                 LIMIT 10";
        $statement = $this->dbConnection->prepare($query);
        $statement->execute();
        $stats['most_active_users'] = $statement->fetchAll(PDO::FETCH_ASSOC);
        
        return $stats;
    }

    /**
     * Genera reporte de préstamos por período
     * 
     * @param string $startDate Fecha de inicio
     * @param string $endDate Fecha de fin
     * @return array Datos del reporte
     */
    public function getLoansReport($startDate, $endDate) {
        $query = "SELECT l.*, 
                         u.username, u.first_name, u.last_name,
                         b.title, b.isbn, 
                         a.name as author_name
                 FROM loans l 
                 JOIN users u ON l.user_id = u.id 
                 JOIN books b ON l.book_id = b.id 
                 JOIN authors a ON b.author_id = a.id 
                 WHERE l.loan_date BETWEEN :start_date AND :end_date 
                 ORDER BY l.loan_date DESC";
        
        $statement = $this->dbConnection->prepare($query);
        $statement->bindParam(':start_date', $startDate);
        $statement->bindParam(':end_date', $endDate);
        $statement->execute();
        
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Genera reporte de libros por género
     * 
     * @return array Libros agrupados por género
     */
    public function getBooksByGenreReport() {
        $query = "SELECT genre, COUNT(*) as book_count, 
                         SUM(total_copies) as total_copies,
                         SUM(available_copies) as available_copies
                 FROM books 
                 WHERE genre IS NOT NULL AND genre != '' 
                 GROUP BY genre 
                 ORDER BY book_count DESC";
        
        $statement = $this->dbConnection->prepare($query);
        $statement->execute();
        
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Genera reporte de préstamos mensuales
     * 
     * @param int $year Año del reporte
     * @return array Préstamos por mes
     */
    public function getMonthlyLoansReport($year) {
        $query = "SELECT 
                    MONTH(loan_date) as month,
                    COUNT(*) as total_loans,
                    SUM(CASE WHEN status = 'returned' THEN 1 ELSE 0 END) as returned_loans,
                    SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_loans,
                    SUM(CASE WHEN status = 'overdue' THEN 1 ELSE 0 END) as overdue_loans
                 FROM loans 
                 WHERE YEAR(loan_date) = :year 
                 GROUP BY MONTH(loan_date) 
                 ORDER BY month";
        
        $statement = $this->dbConnection->prepare($query);
        $statement->bindParam(':year', $year, PDO::PARAM_INT);
        $statement->execute();
        
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene libros con bajo stock
     * 
     * @param int $threshold Umbral de copias disponibles
     * @return array Libros con stock bajo
     */
    public function getLowStockBooks($threshold = 3) {
        $query = "SELECT b.*, a.name as author_name 
                 FROM books b 
                 JOIN authors a ON b.author_id = a.id 
                 WHERE b.available_copies <= :threshold 
                 ORDER BY b.available_copies ASC, b.title";
        
        $statement = $this->dbConnection->prepare($query);
        $statement->bindParam(':threshold', $threshold, PDO::PARAM_INT);
        $statement->execute();
        
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene préstamos próximos a vencer
     * 
     * @param int $days Días para considerar próximo a vencer
     * @return array Préstamos próximos a vencer
     */
    public function getUpcomingDueLoans($days = 3) {
        $query = "SELECT l.*, 
                         u.username, u.first_name, u.last_name, u.email,
                         b.title, b.isbn,
                         a.name as author_name,
                         DATEDIFF(l.due_date, CURDATE()) as days_remaining
                 FROM loans l 
                 JOIN users u ON l.user_id = u.id 
                 JOIN books b ON l.book_id = b.id 
                 JOIN authors a ON b.author_id = a.id 
                 WHERE l.status = 'active' 
                 AND l.due_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL :days DAY)
                 ORDER BY l.due_date ASC";
        
        $statement = $this->dbConnection->prepare($query);
        $statement->bindParam(':days', $days, PDO::PARAM_INT);
        $statement->execute();
        
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Genera reporte de actividad de usuarios
     * 
     * @param string $startDate Fecha de inicio
     * @param string $endDate Fecha de fin
     * @return array Actividad de usuarios
     */
    public function getUserActivityReport($startDate, $endDate) {
        $query = "SELECT u.*, 
                         COUNT(l.id) as total_loans,
                         SUM(CASE WHEN l.status = 'active' THEN 1 ELSE 0 END) as active_loans,
                         SUM(CASE WHEN l.status = 'overdue' THEN 1 ELSE 0 END) as overdue_loans,
                         MAX(l.loan_date) as last_loan_date
                 FROM users u 
                 LEFT JOIN loans l ON u.id = l.user_id 
                 WHERE u.is_active = TRUE 
                 AND (l.loan_date BETWEEN :start_date AND :end_date OR l.id IS NULL)
                 GROUP BY u.id 
                 ORDER BY total_loans DESC";
        
        $statement = $this->dbConnection->prepare($query);
        $statement->bindParam(':start_date', $startDate);
        $statement->bindParam(':end_date', $endDate);
        $statement->execute();
        
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>