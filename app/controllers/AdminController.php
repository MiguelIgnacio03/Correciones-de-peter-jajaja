<?php
/**
 * Controlador para operaciones de administración y reportes
 * 
 * Gestiona dashboard, reportes y funciones administrativas
 */
class AdminController {
    private $reportModel;
    private $userModel;
    private $bookModel;
    private $loanModel;

    /**
     * Constructor del controlador de administración
     * 
     * @param PDO $database Conexión a la base de datos
     */
    public function __construct($database) {
        $this->reportModel = new ReportModel($database);
        $this->userModel = new UserModel($database);
        $this->bookModel = new BookModel($database);
        $this->loanModel = new LoanModel($database);
    }

    /**
     * Verifica si el usuario actual es administrador
     * 
     * @return bool True si es administrador
     */
    private function checkAdminAccess() {
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            $_SESSION['error'] = 'Acceso denegado. Se requieren privilegios de administrador.';
            header('Location: index.php?action=dashboard');
            exit;
        }
        return true;
    }

    /**
     * Muestra el dashboard de administración
     */
    public function adminDashboard() {
        $this->checkAdminAccess();
        
        $systemStats = $this->reportModel->getSystemStats();
        $lowStockBooks = $this->reportModel->getLowStockBooks(2);
        $upcomingDueLoans = $this->reportModel->getUpcomingDueLoans(3);
        
        require_once '../app/views/admin/dashboard.php';
    }

    /**
     * Muestra la gestión de usuarios
     */
    public function manageUsers() {
        $this->checkAdminAccess();
        
        $page = $_GET['page'] ?? 1;
        $search = $_GET['search'] ?? '';
        
        if (!empty($search)) {
            $users = $this->userModel->searchUsers($search);
            $totalUsers = count($users);
        } else {
            $users = $this->userModel->getAllUsers();
            $totalUsers = count($users);
        }
        
        require_once '../app/views/admin/users.php';
    }

    /**
     * Muestra los reportes del sistema
     */
    public function showReports() {
        $this->checkAdminAccess();
        
        $reportType = $_GET['type'] ?? 'general';
        $startDate = $_GET['start_date'] ?? date('Y-m-01');
        $endDate = $_GET['end_date'] ?? date('Y-m-t');
        $year = $_GET['year'] ?? date('Y');
        
        $reportData = [];
        
        switch ($reportType) {
            case 'loans':
                $reportData = $this->reportModel->getLoansReport($startDate, $endDate);
                break;
            case 'monthly':
                $reportData = $this->reportModel->getMonthlyLoansReport($year);
                break;
            case 'genres':
                $reportData = $this->reportModel->getBooksByGenreReport();
                break;
            case 'user_activity':
                $reportData = $this->reportModel->getUserActivityReport($startDate, $endDate);
                break;
            case 'general':
            default:
                $reportData = $this->reportModel->getSystemStats();
                break;
        }
        
        require_once '../app/views/admin/reports.php';
    }

    /**
     * Exporta reportes a diferentes formatos
     */
    public function exportReport() {
        $this->checkAdminAccess();
        
        $format = $_GET['format'] ?? 'csv';
        $reportType = $_GET['type'] ?? 'general';
        $startDate = $_GET['start_date'] ?? date('Y-m-01');
        $endDate = $_GET['end_date'] ?? date('Y-m-t');
        
        // Obtener datos según el tipo de reporte
        switch ($reportType) {
            case 'loans':
                $data = $this->reportModel->getLoansReport($startDate, $endDate);
                $filename = "prestamos_{$startDate}_a_{$endDate}";
                break;
            case 'user_activity':
                $data = $this->reportModel->getUserActivityReport($startDate, $endDate);
                $filename = "actividad_usuarios_{$startDate}_a_{$endDate}";
                break;
            default:
                $_SESSION['error'] = 'Tipo de reporte no válido';
                header('Location: index.php?action=reports');
                exit;
        }
        
        if ($format === 'csv') {
            $this->exportToCSV($data, $filename);
        } elseif ($format === 'pdf') {
            $this->exportToPDF($data, $filename, $reportType);
        }
    }

    /**
     * Exporta datos a CSV
     */
    private function exportToCSV($data, $filename) {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
        
        $output = fopen('php://output', 'w');
        
        // Escribir headers
        if (!empty($data)) {
            fputcsv($output, array_keys($data[0]));
        }
        
        // Escribir datos
        foreach ($data as $row) {
            fputcsv($output, $row);
        }
        
        fclose($output);
        exit;
    }

    /**
     * Exporta datos a PDF (placeholder - implementar con librería PDF)
     */
    private function exportToPDF($data, $filename, $reportType) {
        // Esta función requeriría una librería como TCPDF o Dompdf
        // Por ahora, redirigimos a CSV
        $_SESSION['info'] = 'Exportación a PDF no disponible. Se descargará en formato CSV.';
        $this->exportToCSV($data, $filename);
    }

    /**
     * Actualiza el rol de un usuario
     */
    public function updateUserRole() {
        $this->checkAdminAccess();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['error'] = 'Método no permitido';
            header('Location: index.php?action=manageUsers');
            exit;
        }
        
        $userId = $_POST['user_id'] ?? 0;
        $newRole = $_POST['role'] ?? '';
        
        if (!$userId || !in_array($newRole, ['admin', 'librarian', 'user'])) {
            $_SESSION['error'] = 'Datos inválidos';
            header('Location: index.php?action=manageUsers');
            exit;
        }
        
        // Actualizar rol usando el método del UserModel
        if ($this->userModel->updateUserRole($userId, $newRole)) {
            $_SESSION['success'] = 'Rol de usuario actualizado exitosamente';
        } else {
            $_SESSION['error'] = 'Error al actualizar el rol del usuario';
        }
        
        header('Location: index.php?action=manageUsers');
        exit;
    }

    /**
     * Activa/desactiva un usuario
     */
    public function toggleUserStatus() {
        $this->checkAdminAccess();
        
        $userId = $_GET['id'] ?? 0;
        $action = $_GET['action'] ?? '';
        
        if (!$userId) {
            $_SESSION['error'] = 'ID de usuario no válido';
            header('Location: index.php?action=manageUsers');
            exit;
        }
        
        // Cambiar estado usando el método del UserModel
        $isActive = ($action === 'activate');
        
        if ($this->userModel->updateUserStatus($userId, $isActive)) {
            if ($action === 'activate') {
                $_SESSION['success'] = 'Usuario activado exitosamente';
            } else {
                $_SESSION['success'] = 'Usuario desactivado exitosamente';
            }
        } else {
            $_SESSION['error'] = 'Error al cambiar el estado del usuario';
        }
        
        header('Location: index.php?action=manageUsers');
        exit;
    }

    /**
     * Muestra alertas y notificaciones del sistema
     */
    public function showAlerts() {
        $this->checkAdminAccess();
        
        // Obtener libros con stock bajo (menos de 3 copias)
        $lowStockBooks = $this->reportModel->getLowStockBooks(3);
        
        // Obtener préstamos vencidos
        $allLoans = $this->loanModel->getAllLoans(1, 1000);
        $overdueLoans = array_filter($allLoans, function($loan) {
            return $loan['status'] === 'overdue' || 
                   ($loan['status'] === 'active' && strtotime($loan['due_date']) < time());
        });
        
        // Obtener préstamos por vencer (próximos 3 días)
        $upcomingDueLoans = $this->reportModel->getUpcomingDueLoans(3);
        
        require_once '../app/views/admin/alerts.php';
    }

    /**
     * Formatea las claves de estadísticas para mostrar en español
     * 
     * @param string $key Clave de la estadística
     * @return string Texto formateado en español
     */
    public function formatStatKey($key) {
        $translations = [
            'total_books' => 'Total de Libros',
            'total_authors' => 'Total de Autores',
            'total_users' => 'Total de Usuarios',
            'total_loans' => 'Total de Préstamos',
            'active_loans' => 'Préstamos Activos',
            'overdue_loans' => 'Préstamos Vencidos',
            'returned_this_month' => 'Devueltos Este Mes',
            'most_borrowed_books' => 'Libros Más Prestados',
            'most_popular_authors' => 'Autores Más Populares',
            'most_active_users' => 'Usuarios Más Activos'
        ];
        
        return $translations[$key] ?? ucfirst(str_replace('_', ' ', $key));
    }

    /**
     * Formatea el estado del préstamo para mostrar en español
     * 
     * @param string $status Estado del préstamo
     * @return string Texto formateado en español
     */
    public function formatLoanStatus($status) {
        $statusLabels = [
            'active' => 'Activo',
            'overdue' => 'Vencido', 
            'returned' => 'Devuelto'
        ];
        
        return $statusLabels[$status] ?? $status;
    }

    /**
     * Formatea el nombre del mes en español
     * 
     * @param int $monthNumber Número del mes (1-12)
     * @return string Nombre del mes en español
     */
    public function formatMonthName($monthNumber) {
        $monthNames = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
        ];
        
        return $monthNames[$monthNumber] ?? 'Mes ' . $monthNumber;
    }

    /**
     * Formatea el nombre del rol para mostrar en español
     * 
     * @param string $role Rol del usuario
     * @return string Texto formateado en español
     */
    public function formatUserRole($role) {
        $roleLabels = [
            'admin' => 'Administrador',
            'librarian' => 'Bibliotecario',
            'user' => 'Usuario'
        ];
        
        return $roleLabels[$role] ?? $role;
    }

    /**
     * Prepara los datos del reporte para la vista
     * 
     * @param array $reportData Datos del reporte
     * @param string $reportType Tipo de reporte
     * @return array Datos formateados para la vista
     */
    private function prepareReportData($reportData, $reportType) {
        $formattedData = [];
        
        switch ($reportType) {
            case 'general':
                foreach ($reportData as $key => $value) {
                    if (!is_array($value)) {
                        $formattedData[$this->formatStatKey($key)] = $value;
                    } else {
                        $formattedData[$key] = $value;
                    }
                }
                break;
                
            case 'monthly':
                foreach ($reportData as $monthData) {
                    $monthData['month_name'] = $this->formatMonthName($monthData['month']);
                    $formattedData[] = $monthData;
                }
                break;
                
            default:
                $formattedData = $reportData;
                break;
        }
        
        return $formattedData;
    }
}
?>