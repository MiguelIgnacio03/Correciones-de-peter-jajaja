<?php require_once '../app/views/layout/header.php'; ?>

<div class="report-page">
    <div class="page-header">
        <h2>Reporte de Préstamos</h2>
        <div class="header-actions">
            <a href="index.php?action=loans" class="btn btn-outline">Volver a Préstamos</a>
            <?php if (!empty($filteredLoans)): ?>
                <a href="index.php?action=exportReport&type=loans&format=csv&start_date=<?php echo $startDate; ?>&end_date=<?php echo $endDate; ?>" 
                   class="btn btn-secondary">Exportar CSV</a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Filtros del Reporte -->
    <div class="report-filters">
        <h3>Filtros del Reporte</h3>
        <form method="GET" class="filters-form">
            <input type="hidden" name="action" value="generateReport">
            
            <div class="filter-row">
                <div class="filter-group">
                    <label for="start_date">Fecha Inicio:</label>
                    <input type="date" id="start_date" name="start_date" 
                           value="<?php echo $startDate; ?>">
                </div>
                
                <div class="filter-group">
                    <label for="end_date">Fecha Fin:</label>
                    <input type="date" id="end_date" name="end_date" 
                           value="<?php echo $endDate; ?>">
                </div>
                
                <div class="filter-group">
                    <button type="submit" class="btn btn-primary">Generar Reporte</button>
                </div>
            </div>
        </form>
    </div>

    <!-- Resumen del Reporte -->
    <div class="report-summary">
        <h3>Resumen del Período: <?php echo date('d/m/Y', strtotime($startDate)); ?> - <?php echo date('d/m/Y', strtotime($endDate)); ?></h3>
        <div class="summary-cards">
            <div class="summary-card">
                <div class="summary-number"><?php echo count($filteredLoans); ?></div>
                <div class="summary-label">Total Préstamos</div>
            </div>
            <div class="summary-card">
                <div class="summary-number">
                    <?php 
                    $activeLoans = array_filter($filteredLoans, function($loan) {
                        return $loan['status'] === 'active';
                    });
                    echo count($activeLoans);
                    ?>
                </div>
                <div class="summary-label">Préstamos Activos</div>
            </div>
            <div class="summary-card">
                <div class="summary-number">
                    <?php 
                    $returnedLoans = array_filter($filteredLoans, function($loan) {
                        return $loan['status'] === 'returned';
                    });
                    echo count($returnedLoans);
                    ?>
                </div>
                <div class="summary-label">Préstamos Devueltos</div>
            </div>
            <div class="summary-card">
                <div class="summary-number">
                    <?php 
                    $overdueLoans = array_filter($filteredLoans, function($loan) {
                        return $loan['status'] === 'overdue';
                    });
                    echo count($overdueLoans);
                    ?>
                </div>
                <div class="summary-label">Préstamos Vencidos</div>
            </div>
        </div>
    </div>

    <!-- Detalles del Reporte -->
    <div class="report-details">
        <h3>Detalles de Préstamos</h3>
        
        <?php if (empty($filteredLoans)): ?>
            <div class="no-data">
                <p>No hay préstamos registrados en el período seleccionado.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Usuario</th>
                            <th>Libro</th>
                            <th>Fecha Préstamo</th>
                            <th>Fecha Devolución</th>
                            <th>Fecha Retorno</th>
                            <th>Estado</th>
                            <th>Días Transcurridos</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($filteredLoans as $loan): ?>
                            <tr>
                                <td><?php echo $loan['id'] ?? 'N/A'; ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars(($loan['first_name'] ?? '') . ' ' . ($loan['last_name'] ?? '')); ?></strong>
                                    <br><small><?php echo htmlspecialchars($loan['username'] ?? 'N/A'); ?></small>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($loan['book_title'] ?? $loan['title'] ?? 'Libro no encontrado'); ?></strong>
                                    <br><small>por <?php echo htmlspecialchars($loan['author_name'] ?? 'Autor desconocido'); ?></small>
                                </td>
                                <td><?php echo date('d/m/Y', strtotime($loan['loan_date'] ?? '')); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($loan['due_date'] ?? '')); ?></td>
                                <td>
                                    <?php if (!empty($loan['return_date'])): ?>
                                        <?php echo date('d/m/Y', strtotime($loan['return_date'])); ?>
                                    <?php else: ?>
                                        <span class="text-muted">Pendiente</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="status-badge status-<?php echo $loan['status'] ?? 'unknown'; ?>">
                                        <?php 
                                        $statusLabels = [
                                            'active' => 'Activo',
                                            'overdue' => 'Vencido', 
                                            'returned' => 'Devuelto'
                                        ];
                                        echo $statusLabels[$loan['status'] ?? ''] ?? 'Desconocido'; 
                                        ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <?php
                                    if (!empty($loan['loan_date'])) {
                                        $start = new DateTime($loan['loan_date']);
                                        $end = !empty($loan['return_date']) ? new DateTime($loan['return_date']) : new DateTime();
                                        $interval = $start->diff($end);
                                        echo $interval->days;
                                    } else {
                                        echo 'N/A';
                                    }
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <!-- Estadísticas Adicionales -->
    <?php if (!empty($filteredLoans)): ?>
    <div class="report-stats">
        <h3>Estadísticas Adicionales</h3>
        <div class="stats-grid">
            <div class="stat-item">
                <span class="stat-label">Período del Reporte:</span>
                <span class="stat-value"><?php echo date('d/m/Y', strtotime($startDate)); ?> - <?php echo date('d/m/Y', strtotime($endDate)); ?></span>
            </div>
            <div class="stat-item">
                <span class="stat-label">Días del Período:</span>
                <span class="stat-value">
                    <?php
                    $start = new DateTime($startDate);
                    $end = new DateTime($endDate);
                    $interval = $start->diff($end);
                    echo $interval->days + 1;
                    ?>
                </span>
            </div>
            <div class="stat-item">
                <span class="stat-label">Promedio Diario:</span>
                <span class="stat-value">
                    <?php
                    $totalDays = (new DateTime($startDate))->diff(new DateTime($endDate))->days + 1;
                    echo round(count($filteredLoans) / $totalDays, 2);
                    ?>
                </span>
            </div>
            <div class="stat-item">
                <span class="stat-label">Tasa de Devolución:</span>
                <span class="stat-value">
                    <?php
                    $returnedCount = count(array_filter($filteredLoans, function($loan) {
                        return $loan['status'] === 'returned';
                    }));
                    echo $totalLoans = count($filteredLoans) > 0 ? round(($returnedCount / count($filteredLoans)) * 100, 2) . '%' : '0%';
                    ?>
                </span>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php require_once '../app/views/layout/footer.php'; ?>