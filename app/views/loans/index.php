<?php require_once '../app/views/layout/header.php'; ?>

<div class="loans-page">
    <div class="page-header">
        <h2>Gestión de Préstamos</h2>
        <div class="header-actions">
            <a href="index.php?action=createLoan" class="btn btn-primary">Nuevo Préstamo</a>
            <a href="index.php?action=generateReport" class="btn btn-secondary">Generar Reporte</a>
        </div>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-error">
            <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <!-- Estadísticas rápidas -->
    <div class="loan-stats">
        <div class="stat-item">
            <span class="stat-number"><?php echo $loanStats['active_loans']; ?></span>
            <span class="stat-label">Préstamos Activos</span>
        </div>
        <div class="stat-item">
            <span class="stat-number"><?php echo $loanStats['overdue_loans']; ?></span>
            <span class="stat-label">Vencidos</span>
        </div>
        <div class="stat-item">
            <span class="stat-number"><?php echo $loanStats['returned_this_month']; ?></span>
            <span class="stat-label">Devueltos este Mes</span>
        </div>
    </div>

    <!-- Filtros y búsqueda -->
    <div class="filters-section">
        <form method="GET" class="filters-form">
            <input type="hidden" name="action" value="loans">
            
            <div class="filter-group">
                <label for="status">Filtrar por estado:</label>
                <select id="status" name="status" onchange="this.form.submit()">
                    <option value="all" <?php echo empty($status) || $status === 'all' ? 'selected' : ''; ?>>Todos los estados</option>
                    <option value="active" <?php echo $status === 'active' ? 'selected' : ''; ?>>Activos</option>
                    <option value="overdue" <?php echo $status === 'overdue' ? 'selected' : ''; ?>>Vencidos</option>
                    <option value="returned" <?php echo $status === 'returned' ? 'selected' : ''; ?>>Devueltos</option>
                </select>
            </div>

            <div class="search-group">
                <input type="text" name="search" placeholder="Buscar por usuario, libro o autor..." 
                       value="<?php echo htmlspecialchars($search ?? ''); ?>">
                <button type="submit" class="btn btn-secondary">Buscar</button>
                <?php if (!empty($search) || !empty($status)): ?>
                    <a href="index.php?action=loans" class="btn btn-outline">Limpiar</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Lista de préstamos -->
    <div class="loans-list">
        <?php if (empty($loans)): ?>
            <div class="no-data">
                <p>No se encontraron préstamos</p>
                <?php if (!empty($search) || !empty($status)): ?>
                    <a href="index.php?action=loans" class="btn btn-primary">Ver Todos los Préstamos</a>
                <?php else: ?>
                    <a href="index.php?action=createLoan" class="btn btn-primary">Registrar el Primer Préstamo</a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Usuario</th>
                            <th>Libro</th>
                            <th>Fecha Préstamo</th>
                            <th>Fecha Devolución</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($loans as $loan): ?>
                            <tr class="loan-row <?php echo $loan['status']; ?>">
                                <td>
                                    <strong><?php echo htmlspecialchars($loan['first_name'] . ' ' . $loan['last_name']); ?></strong>
                                    <br><small><?php echo htmlspecialchars($loan['username']); ?></small>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($loan['book_title']); ?></strong>
                                    <br><small>por <?php echo htmlspecialchars($loan['author_name']); ?></small>
                                    <br><small>ISBN: <?php echo htmlspecialchars($loan['book_isbn']); ?></small>
                                </td>
                                <td><?php echo date('d/m/Y', strtotime($loan['loan_date'])); ?></td>
                                <td>
                                    <?php echo date('d/m/Y', strtotime($loan['due_date'])); ?>
                                    <?php if ($loan['status'] === 'active' && strtotime($loan['due_date']) < time()): ?>
                                        <br><span class="due-warning">¡Vencido!</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="status-badge status-<?php echo $loan['status']; ?>">
                                        <?php 
                                        $statusLabels = [
                                            'active' => 'Activo',
                                            'overdue' => 'Vencido', 
                                            'returned' => 'Devuelto'
                                        ];
                                        echo $statusLabels[$loan['status']] ?? $loan['status']; 
                                        ?>
                                    </span>
                                    <?php if ($loan['return_date']): ?>
                                        <br><small>Devuelto: <?php echo date('d/m/Y', strtotime($loan['return_date'])); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td class="actions">
                                    <?php if ($loan['status'] === 'active' || $loan['status'] === 'overdue'): ?>
                                        <a href="index.php?action=returnLoan&id=<?php echo $loan['id']; ?>" 
                                           class="btn btn-success btn-sm"
                                           onclick="return confirm('¿Registrar devolución de este libro?')">Devolver</a>
                                    <?php endif; ?>
                                    <a href="index.php?action=viewLoan&id=<?php echo $loan['id']; ?>" 
                                       class="btn btn-secondary btn-sm"></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            <?php if (empty($search) && $totalPages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="index.php?action=loans&page=<?php echo $page - 1; ?>" class="btn btn-outline">Anterior</a>
                    <?php endif; ?>
                    
                    <span>Página <?php echo $page; ?> de <?php echo $totalPages; ?></span>
                    
                    <?php if ($page < $totalPages): ?>
                        <a href="index.php?action=loans&page=<?php echo $page + 1; ?>" class="btn btn-outline">Siguiente</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../app/views/layout/footer.php'; ?>