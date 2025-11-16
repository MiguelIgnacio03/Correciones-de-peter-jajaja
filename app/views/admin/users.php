<?php require_once '../app/views/layout/header.php'; ?>

<div class="users-management">
    <div class="page-header">
        <h2>Gestión de Usuarios</h2>
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

    <!-- Lista de Usuarios -->
    <div class="users-list">
        <?php if (empty($users)): ?>
            <div class="no-data">
                <p>No hay usuarios registrados en el sistema.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Usuario</th>
                            <th>Nombre Completo</th>
                            <th>Email</th>
                            <th>Rol</th>
                            <th>Estado</th>
                            <th>Fecha Registro</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($user['username']); ?></strong>
                                </td>
                                <td><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td>
                                    <span class="role-badge role-<?php echo $user['role']; ?>">
                                        <?php 
                                        $roleLabels = [
                                            'admin' => 'Administrador',
                                            'librarian' => 'Bibliotecario',
                                            'user' => 'Usuario'
                                        ];
                                        echo $roleLabels[$user['role']] ?? $user['role']; 
                                        ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="status-badge <?php echo $user['is_active'] ? 'active' : 'inactive'; ?>">
                                        <?php echo $user['is_active'] ? 'Activo' : 'Inactivo'; ?>
                                    </span>
                                </td>
                                <td><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></td>
                                <td class="actions">
                                    <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                        <form method="POST" action="index.php?action=updateUserRole" class="inline-form">
                                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                            <select name="role" onchange="this.form.submit()" class="role-select">
                                                <option value="user" <?php echo $user['role'] === 'user' ? 'selected' : ''; ?>>Usuario</option>
                                                <option value="librarian" <?php echo $user['role'] === 'librarian' ? 'selected' : ''; ?>>Bibliotecario</option>
                                                <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>Administrador</option>
                                            </select>
                                        </form>
                                        
                                        <?php if ($user['is_active']): ?>
                                            <a href="index.php?action=toggleUserStatus&id=<?php echo $user['id']; ?>&action=deactivate" 
                                               class="btn btn-warning btn-sm"
                                               onclick="return confirm('¿Desactivar este usuario?')">Desactivar</a>
                                        <?php else: ?>
                                            <a href="index.php?action=toggleUserStatus&id=<?php echo $user['id']; ?>&action=activate" 
                                               class="btn btn-success btn-sm"
                                               onclick="return confirm('¿Activar este usuario?')">Activar</a>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-muted">Tu usuario</span>
                                    <?php endif; ?>
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