<?php require_once '../app/views/layout/header.php'; ?>

<div class="admin-dashboard">
    <div class="page-header">
        <h2>Panel de Administración</h2>
        <div class="header-actions">
            <a href="index.php?action=reports" class="btn btn-secondary">Reportes</a>
            <a href="index.php?action=manageUsers" class="btn btn-secondary">Gestionar Usuarios</a>
            <a href="index.php?action=alerts" class="btn btn-warning">Ver Alertas</a>
        </div>
    </div>

    <!-- Estadísticas Principales -->
    <div class="stats-grid-large">
        <div class="stat-card-large">
            <div class="stat-icon">📚</div>
            <div class="stat-info">
                <h3><?php echo $systemStats['total_books']; ?></h3>
                <p>Total de Libros</p>
            </div>
        </div>
        
        <div class="stat-card-large">
            <div class="stat-icon">👥</div>
            <div class="stat-info">
                <h3><?php echo $systemStats['total_users']; ?></h3>
                <p>Usuarios Registrados</p>
            </div>
        </div>
        
        <div class="stat-card-large">
            <div class="stat-icon">📖</div>
            <div class="stat-info">
                <h3><?php echo $systemStats['total_loans']; ?></h3>
                <p>Total Préstamos</p>
            </div>
        </div>
        
        <div class="stat-card-large">
            <div class="stat-icon">⏰</div>
            <div class="stat-info">
                <h3><?php echo $systemStats['overdue_loans']; ?></h3>
                <p>Préstamos Vencidos</p>
            </div>
        </div>
    </div>

    <div class="dashboard-content">
        <!-- Alertas Rápidas -->
        <div class="dashboard-section">
            <h3>Alertas del Sistema</h3>
            <div class="alerts-grid">
                <?php if (!empty($lowStockBooks)): ?>
                    <div class="alert-card warning">
                        <h4>Stock Bajo</h4>
                        <p><?php echo count($lowStockBooks); ?> libros tienen menos de 3 copias disponibles</p>
                        <a href="index.php?action=alerts" class="btn btn-sm btn-warning">Ver Detalles</a>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($upcomingDueLoans)): ?>
                    <div class="alert-card info">
                        <h4>Préstamos por Vencer</h4>
                        <p><?php echo count($upcomingDueLoans); ?> préstamos vencen en los próximos 3 días</p>
                        <a href="index.php?action=alerts" class="btn btn-sm btn-secondary">Ver Detalles</a>
                    </div>
                <?php endif; ?>
                
                <?php if ($systemStats['overdue_loans'] > 0): ?>
                    <div class="alert-card danger">
                        <h4>Préstamos Vencidos</h4>
                        <p><?php echo $systemStats['overdue_loans']; ?> préstamos están vencidos</p>
                        <a href="index.php?action=loans&status=overdue" class="btn btn-sm btn-danger">Gestionar</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Libros Más Prestados -->
        <div class="dashboard-section">
            <h3>Libros Más Prestados</h3>
            <?php if (!empty($systemStats['most_borrowed_books'])): ?>
                <div class="top-list">
                    <?php foreach (array_slice($systemStats['most_borrowed_books'], 0, 5) as $index => $book): ?>
                        <div class="top-item">
                            <span class="rank"><?php echo $index + 1; ?></span>
                            <div class="item-info">
                                <strong><?php echo htmlspecialchars($book['title']); ?></strong>
                                <small>por <?php echo htmlspecialchars($book['author_name']); ?></small>
                            </div>
                            <span class="count"><?php echo $book['loan_count']; ?> préstamos</span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="no-data">No hay datos de préstamos</p>
            <?php endif; ?>
        </div>

        <!-- Usuarios Más Activos -->
        <div class="dashboard-section">
            <h3>Usuarios Más Activos</h3>
            <?php if (!empty($systemStats['most_active_users'])): ?>
                <div class="top-list">
                    <?php foreach (array_slice($systemStats['most_active_users'], 0, 5) as $index => $user): ?>
                        <div class="top-item">
                            <span class="rank"><?php echo $index + 1; ?></span>
                            <div class="item-info">
                                <strong><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></strong>
                                <small><?php echo htmlspecialchars($user['username']); ?></small>
                            </div>
                            <span class="count"><?php echo $user['loan_count']; ?> préstamos</span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="no-data">No hay datos de usuarios</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once '../app/views/layout/footer.php'; ?>