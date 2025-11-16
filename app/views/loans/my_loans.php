<?php require_once '../app/views/layout/header.php'; ?>

<div class="my-loans-page">
    <div class="page-header">
        <h2>Mis Préstamos</h2>
        <a href="index.php?action=loans" class="btn btn-outline">Ver Todos los Préstamos</a>
    </div>

    <!-- Préstamos Activos -->
    <div class="loans-section">
        <h3>Préstamos Activos</h3>
        
        <?php if (empty($activeLoans)): ?>
            <div class="no-data">
                <p>No tienes préstamos activos en este momento.</p>
                <a href="index.php?action=books" class="btn btn-primary">Explorar Libros</a>
            </div>
        <?php else: ?>
            <div class="loans-grid">
                <?php foreach ($activeLoans as $loan): ?>
                    <div class="loan-card <?php echo $loan['status']; ?>">
                        <div class="loan-header">
                            <h4><?php echo htmlspecialchars($loan['title']); ?></h4>
                            <span class="loan-status status-<?php echo $loan['status']; ?>">
                                <?php echo $loan['status'] === 'overdue' ? 'Vencido' : 'Activo'; ?>
                            </span>
                        </div>
                        
                        <div class="loan-info">
                            <p><strong>Autor:</strong> <?php echo htmlspecialchars($loan['author_name']); ?></p>
                            <p><strong>ISBN:</strong> <?php echo htmlspecialchars($loan['isbn']); ?></p>
                            <p><strong>Fecha de préstamo:</strong> <?php echo date('d/m/Y', strtotime($loan['loan_date'])); ?></p>
                            <p><strong>Fecha de devolución:</strong> <?php echo date('d/m/Y', strtotime($loan['due_date'])); ?></p>
                            
                            <?php if (strtotime($loan['due_date']) < time()): ?>
                                <p class="due-warning"><strong>¡Este préstamo está vencido!</strong></p>
                            <?php else: ?>
                                <?php 
                                $daysLeft = ceil((strtotime($loan['due_date']) - time()) / (60 * 60 * 24));
                                if ($daysLeft <= 3): ?>
                                    <p class="due-soon"><strong>Vence en <?php echo $daysLeft; ?> día(s)</strong></p>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Historial de Préstamos -->
    <div class="loans-section">
        <h3>Historial de Préstamos</h3>
        
        <?php if (empty($loanHistory)): ?>
            <div class="no-data">
                <p>No tienes historial de préstamos.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Libro</th>
                            <th>Fecha Préstamo</th>
                            <th>Fecha Devolución</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($loanHistory as $loan): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($loan['title']); ?></strong>
                                    <br><small>por <?php echo htmlspecialchars($loan['author_name']); ?></small>
                                </td>
                                <td><?php echo date('d/m/Y', strtotime($loan['loan_date'])); ?></td>
                                <td>
                                    <?php echo $loan['return_date'] ? date('d/m/Y', strtotime($loan['return_date'])) : 'Pendiente'; ?>
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
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../app/views/layout/footer.php'; ?>