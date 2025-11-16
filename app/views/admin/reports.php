<?php require_once '../app/views/layout/header.php'; ?>

<?php
// Funciones auxiliares para formatear datos
function formatStatKey($key) {
    $translations = [
        'total_books' => 'Total de Libros',
        'total_authors' => 'Total de Autores',
        'total_users' => 'Total de Usuarios',
        'total_loans' => 'Total de Préstamos',
        'active_loans' => 'Préstamos Activos',
        'overdue_loans' => 'Préstamos Vencidos',
        'returned_this_month' => 'Devueltos Este Mes'
    ];
    return $translations[$key] ?? ucfirst(str_replace('_', ' ', $key));
}

function formatLoanStatus($status) {
    $statusLabels = [
        'active' => 'Activo',
        'overdue' => 'Vencido', 
        'returned' => 'Devuelto'
    ];
    return $statusLabels[$status] ?? $status;
}

function formatMonthName($monthNumber) {
    $monthNames = [
        1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
        5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
        9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
    ];
    return $monthNames[$monthNumber] ?? 'Mes ' . $monthNumber;
}
?>

<div class="reports-page">
    <div class="page-header">
        <h2>Reportes del Sistema</h2>
        <div class="header-actions">
            <?php if (!empty($reportData) && $reportType !== 'general'): ?>
                <a href="index.php?action=exportReport&type=<?php echo $reportType; ?>&format=csv&start_date=<?php echo $startDate; ?>&end_date=<?php echo $endDate; ?>" 
                   class="btn btn-secondary">Exportar CSV</a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Filtros de Reportes -->
    <div class="report-filters">
        <form method="GET" class="filters-form">
            <input type="hidden" name="action" value="reports">
            
            <div class="filter-group">
                <label for="report_type">Tipo de Reporte:</label>
                <select id="report_type" name="type" onchange="this.form.submit()">
                    <option value="general" <?php echo $reportType === 'general' ? 'selected' : ''; ?>>Estadísticas Generales</option>
                    <option value="loans" <?php echo $reportType === 'loans' ? 'selected' : ''; ?>>Préstamos por Período</option>
                    <option value="monthly" <?php echo $reportType === 'monthly' ? 'selected' : ''; ?>>Préstamos Mensuales</option>
                    <option value="genres" <?php echo $reportType === 'genres' ? 'selected' : ''; ?>>Libros por Género</option>
                    <option value="user_activity" <?php echo $reportType === 'user_activity' ? 'selected' : ''; ?>>Actividad de Usuarios</option>
                </select>
            </div>

            <?php if (in_array($reportType, ['loans', 'user_activity'])): ?>
            <div class="filter-group">
                <label for="start_date">Desde:</label>
                <input type="date" id="start_date" name="start_date" 
                       value="<?php echo $startDate; ?>">
            </div>
            
            <div class="filter-group">
                <label for="end_date">Hasta:</label>
                <input type="date" id="end_date" name="end_date" 
                       value="<?php echo $endDate; ?>">
            </div>

            <div class="filter-group">
                <button type="submit" class="btn btn-primary">Aplicar Filtros</button>
            </div>
            <?php endif; ?>

            <?php if ($reportType === 'monthly'): ?>
            <div class="filter-group">
                <label for="year">Año:</label>
                <select id="year" name="year" onchange="this.form.submit()">
                    <?php for ($y = date('Y'); $y >= 2020; $y--): ?>
                        <option value="<?php echo $y; ?>" <?php echo $year == $y ? 'selected' : ''; ?>>
                            <?php echo $y; ?>
                        </option>
                    <?php endfor; ?>
                </select>
            </div>
            <?php endif; ?>
        </form>
    </div>

    <!-- Contenido del Reporte -->
    <div class="report-content">
        <?php if ($reportType === 'general'): ?>
            <!-- Reporte General -->
            <div class="report-section">
                <h3>Estadísticas Generales del Sistema</h3>
                <div class="stats-grid">
                    <?php foreach ($reportData as $key => $value): ?>
                        <?php if (!is_array($value)): ?>
                            <div class="stat-item">
                                <span class="stat-label"><?php echo formatStatKey($key); ?>:</span>
                                <span class="stat-value"><?php echo $value; ?></span>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Libros Más Prestados -->
            <?php if (!empty($reportData['most_borrowed_books'])): ?>
            <div class="report-section">
                <h3>Top 10 Libros Más Prestados</h3>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Libro</th>
                                <th>Autor</th>
                                <th>Préstamos</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reportData['most_borrowed_books'] as $index => $book): ?>
                                <tr>
                                    <td><?php echo $index + 1; ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($book['title']); ?></strong>
                                        <br><small>ISBN: <?php echo htmlspecialchars($book['isbn']); ?></small>
                                    </td>
                                    <td><?php echo htmlspecialchars($book['author_name']); ?></td>
                                    <td class="text-center"><?php echo $book['loan_count']; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>

        <?php elseif ($reportType === 'loans' && !empty($reportData)): ?>
            <!-- Reporte de Préstamos -->
            <div class="report-section">
                <h3>Préstamos del <?php echo date('d/m/Y', strtotime($startDate)); ?> al <?php echo date('d/m/Y', strtotime($endDate)); ?></h3>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Usuario</th>
                                <th>Libro</th>
                                <th>Fecha Préstamo</th>
                                <th>Fecha Devolución</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reportData as $loan): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($loan['first_name'] . ' ' . $loan['last_name']); ?></strong>
                                        <br><small><?php echo htmlspecialchars($loan['username']); ?></small>
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($loan['title']); ?></strong>
                                        <br><small>por <?php echo htmlspecialchars($loan['author_name']); ?></small>
                                    </td>
                                    <td><?php echo date('d/m/Y', strtotime($loan['loan_date'])); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($loan['due_date'])); ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo $loan['status']; ?>">
                                            <?php echo formatLoanStatus($loan['status']); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        <?php elseif ($reportType === 'genres' && !empty($reportData)): ?>
            <!-- Reporte por Géneros -->
            <div class="report-section">
                <h3>Distribución de Libros por Género</h3>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Género</th>
                                <th>Cantidad de Libros</th>
                                <th>Total de Copias</th>
                                <th>Copias Disponibles</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reportData as $genre): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($genre['genre']); ?></strong></td>
                                    <td class="text-center"><?php echo $genre['book_count']; ?></td>
                                    <td class="text-center"><?php echo $genre['total_copies']; ?></td>
                                    <td class="text-center"><?php echo $genre['available_copies']; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        <?php elseif ($reportType === 'monthly' && !empty($reportData)): ?>
            <!-- Reporte Mensual -->
            <div class="report-section">
                <h3>Préstamos Mensuales - Año <?php echo $year; ?></h3>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Mes</th>
                                <th>Total Préstamos</th>
                                <th>Devueltos</th>
                                <th>Activos</th>
                                <th>Vencidos</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reportData as $monthData): ?>
                                <tr>
                                    <td><strong><?php echo formatMonthName($monthData['month']); ?></strong></td>
                                    <td class="text-center"><?php echo $monthData['total_loans']; ?></td>
                                    <td class="text-center"><?php echo $monthData['returned_loans']; ?></td>
                                    <td class="text-center"><?php echo $monthData['active_loans']; ?></td>
                                    <td class="text-center"><?php echo $monthData['overdue_loans']; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        <?php elseif ($reportType === 'user_activity' && !empty($reportData)): ?>
            <!-- Reporte de Actividad de Usuarios -->
            <div class="report-section">
                <h3>Actividad de Usuarios - <?php echo date('d/m/Y', strtotime($startDate)); ?> al <?php echo date('d/m/Y', strtotime($endDate)); ?></h3>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Usuario</th>
                                <th>Nombre Completo</th>
                                <th>Total Préstamos</th>
                                <th>Activos</th>
                                <th>Vencidos</th>
                                <th>Último Préstamo</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reportData as $user): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($user['username']); ?></strong>
                                        <br><small><?php echo htmlspecialchars($user['email']); ?></small>
                                    </td>
                                    <td><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></td>
                                    <td class="text-center"><?php echo $user['total_loans']; ?></td>
                                    <td class="text-center"><?php echo $user['active_loans']; ?></td>
                                    <td class="text-center"><?php echo $user['overdue_loans']; ?></td>
                                    <td>
                                        <?php if ($user['last_loan_date']): ?>
                                            <?php echo date('d/m/Y', strtotime($user['last_loan_date'])); ?>
                                        <?php else: ?>
                                            <span class="text-muted">Sin préstamos</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        <?php else: ?>
            <div class="no-data">
                <p>No hay datos disponibles para el reporte seleccionado.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../app/views/layout/footer.php'; ?>