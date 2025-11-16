<?php require_once '../app/views/layout/header.php'; ?>

<div class="alerts-page">
    <div class="page-header">
        <h2>Alertas del Sistema</h2>
        <a href="index.php?action=adminDashboard" class="btn btn-outline">Volver al Dashboard</a>
    </div>

    <!-- Alertas de Stock Bajo -->
    <div class="alert-section">
        <h3>📦 Alertas de Stock Bajo</h3>
        
        <?php if (empty($lowStockBooks)): ?>
            <div class="no-alerts">
                <p>✅ No hay alertas de stock bajo en este momento.</p>
            </div>
        <?php else: ?>
            <div class="alert-list">
                <?php foreach ($lowStockBooks as $book): ?>
                    <div class="alert-item warning">
                        <div class="alert-icon">⚠️</div>
                        <div class="alert-content">
                            <h4><?php echo htmlspecialchars($book['title']); ?></h4>
                            <p><strong>Autor:</strong> <?php echo htmlspecialchars($book['author_name']); ?></p>
                            <p><strong>Copias disponibles:</strong> <?php echo $book['available_copies']; ?> de <?php echo $book['total_copies']; ?></p>
                            <p><strong>ISBN:</strong> <?php echo htmlspecialchars($book['isbn']); ?></p>
                        </div>
                        <div class="alert-actions">
                            <a href="index.php?action=editBook&id=<?php echo $book['id']; ?>" class="btn btn-warning btn-sm">Actualizar Stock</a>
                            <a href="index.php?action=books" class="btn btn-secondary btn-sm">Ver Libros</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Préstamos Vencidos -->
    <div class="alert-section">
        <h3>⏰ Préstamos Vencidos</h3>
        
        <?php if (empty($overdueLoans)): ?>
            <div class="no-alerts">
                <p>✅ No hay préstamos vencidos en este momento.</p>
            </div>
        <?php else: ?>
            <div class="alert-list">
                <?php foreach ($overdueLoans as $loan): ?>
                    <div class="alert-item danger">
                        <div class="alert-icon">🚨</div>
                        <div class="alert-content">
                            <h4><?php echo htmlspecialchars($loan['book_title']); ?></h4>
                            <p><strong>Usuario:</strong> <?php echo htmlspecialchars($loan['first_name'] . ' ' . $loan['last_name']); ?> (<?php echo htmlspecialchars($loan['username']); ?>)</p>
                            <p><strong>Fecha de préstamo:</strong> <?php echo date('d/m/Y', strtotime($loan['loan_date'])); ?></p>
                            <p><strong>Fecha de vencimiento:</strong> <?php echo date('d/m/Y', strtotime($loan['due_date'])); ?></p>
                            <p><strong>Días de retraso:</strong> 
                                <?php 
                                $daysOverdue = floor((time() - strtotime($loan['due_date'])) / (60 * 60 * 24));
                                echo max(0, $daysOverdue); 
                                ?> días
                            </p>
                        </div>
                        <div class="alert-actions">
                            <a href="index.php?action=returnLoan&id=<?php echo $loan['id']; ?>" class="btn btn-success btn-sm">Registrar Devolución</a>
                            <a href="index.php?action=loans&status=overdue" class="btn btn-secondary btn-sm">Ver Préstamos</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Préstamos por Vencer -->
    <div class="alert-section">
        <h3>📖 Préstamos por Vencer (Próximos 3 días)</h3>
        
        <?php if (empty($upcomingDueLoans)): ?>
            <div class="no-alerts">
                <p>✅ No hay préstamos por vencer en los próximos 3 días.</p>
            </div>
        <?php else: ?>
            <div class="alert-list">
                <?php foreach ($upcomingDueLoans as $loan): ?>
                    <div class="alert-item info">
                        <div class="alert-icon">📅</div>
                        <div class="alert-content">
                            <h4><?php echo htmlspecialchars($loan['title']); ?></h4>
                            <p><strong>Usuario:</strong> <?php echo htmlspecialchars($loan['first_name'] . ' ' . $loan['last_name']); ?></p>
                            <p><strong>Fecha de préstamo:</strong> <?php echo date('d/m/Y', strtotime($loan['loan_date'])); ?></p>
                            <p><strong>Fecha de vencimiento:</strong> <?php echo date('d/m/Y', strtotime($loan['due_date'])); ?></p>
                            <p><strong>Días restantes:</strong> <?php echo $loan['days_remaining'] ?? 'N/A'; ?> días</p>
                            <p><strong>Email:</strong> <?php echo htmlspecialchars($loan['email'] ?? 'N/A'); ?></p>
                        </div>
                        <div class="alert-actions">
                            <?php if (!empty($loan['email'])): ?>
                                <a href="mailto:<?php echo htmlspecialchars($loan['email']); ?>?subject=Recordatorio de préstamo&body=Hola <?php echo htmlspecialchars($loan['first_name']); ?>, tu préstamo del libro '<?php echo htmlspecialchars($loan['title']); ?>' vence el <?php echo date('d/m/Y', strtotime($loan['due_date'])); ?>. Por favor, recuerda devolverlo a tiempo." class="btn btn-primary btn-sm">Enviar Recordatorio</a>
                            <?php endif; ?>
                            <a href="index.php?action=loans" class="btn btn-secondary btn-sm">Ver Préstamos</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Resumen de Alertas -->
    <div class="alerts-summary">
        <h3>📊 Resumen de Alertas</h3>
        <div class="summary-cards">
            <div class="summary-card <?php echo !empty($lowStockBooks) ? 'warning' : 'success'; ?>">
                <div class="summary-number"><?php echo count($lowStockBooks); ?></div>
                <div class="summary-label">Libros con Stock Bajo</div>
            </div>
            <div class="summary-card <?php echo !empty($overdueLoans) ? 'danger' : 'success'; ?>">
                <div class="summary-number"><?php echo count($overdueLoans); ?></div>
                <div class="summary-label">Préstamos Vencidos</div>
            </div>
            <div class="summary-card <?php echo !empty($upcomingDueLoans) ? 'info' : 'success'; ?>">
                <div class="summary-number"><?php echo count($upcomingDueLoans); ?></div>
                <div class="summary-label">Préstamos por Vencer</div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../app/views/layout/footer.php'; ?>