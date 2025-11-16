<?php 
// Verificar que las variables estén definidas
$totalBooks = $totalBooks ?? 0;
$recentBooks = $recentBooks ?? [];
$loanStats = $loanStats ?? [
    'active_loans' => 0,
    'overdue_loans' => 0,
    'returned_this_month' => 0
];
$totalUsers = $totalUsers ?? 0;
?>

<?php require_once '../app/views/layout/header.php'; ?>

<div class="dashboard">
    <h2>Dashboard</h2>
    
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">📚</div>
            <div class="stat-info">
                <h3><?php echo $totalBooks; ?></h3>
                <p>Total Libros</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">👥</div>
            <div class="stat-info">
                <h3><?php echo $totalUsers; ?></h3>
                <p>Usuarios Registrados</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">📖</div>
            <div class="stat-info">
                <h3><?php echo $loanStats['active_loans']; ?></h3>
                <p>Préstamos Activos</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">⏰</div>
            <div class="stat-info">
                <h3><?php echo $loanStats['overdue_loans']; ?></h3>
                <p>Préstamos Vencidos</p>
            </div>
        </div>
    </div>

    <!-- Agregar sección de acciones rápidas -->
    <div class="quick-actions">
        <h3>Acciones Rápidas</h3>
        <div class="action-buttons">
            <a href="index.php?action=books" class="btn btn-primary">Explorar Libros</a>
            <a href="index.php?action=myLoans" class="btn btn-secondary">Mis Préstamos</a>
            <a href="index.php?action=authors" class="btn btn-secondary">Ver Autores</a>
        </div>
    </div>

    <div class="recent-books">
        <h3>Libros Recientes</h3>
        
        <?php if (empty($recentBooks)): ?>
            <div class="no-data">No hay libros registrados</div>
        <?php else: ?>
            <div class="books-list">
                <?php foreach ($recentBooks as $book): ?>
                    <div class="book-item">
                        <h4><?php echo htmlspecialchars($book['title'] ?? ''); ?></h4>
                        <p class="book-author">por <?php echo htmlspecialchars($book['author_name'] ?? ''); ?></p>
                        <p class="book-copies">
                            <?php echo $book['available_copies'] ?? 0; ?> de <?php echo $book['total_copies'] ?? 0; ?> disponibles
                        </p>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <div class="dashboard-actions">
            <a href="index.php?action=books" class="btn btn-primary">Ver Todos los Libros</a>
        </div>
    </div>
</div>

<?php require_once '../app/views/layout/footer.php'; ?>