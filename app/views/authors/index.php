<?php require_once '../app/views/layout/header.php'; ?>

<div class="authors-page">
    <div class="page-header">
        <h2>Gestión de Autores</h2>
        <a href="index.php?action=createAuthor" class="btn btn-primary">Agregar Autor</a>
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

    <!-- Formulario de búsqueda -->
    <div class="search-section">
        <form method="GET" class="search-form">
            <input type="hidden" name="action" value="authors">
            <div class="search-group">
                <input type="text" name="search" placeholder="Buscar por nombre o nacionalidad..." 
                       value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                <button type="submit" class="btn btn-secondary">Buscar</button>
                <?php if (!empty($search)): ?>
                    <a href="index.php?action=authors" class="btn btn-outline">Limpiar</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Lista de autores -->
    <div class="authors-list">
        <?php if (empty($authors)): ?>
            <div class="no-data">
                <p>No se encontraron autores</p>
                <?php if (!empty($search)): ?>
                    <a href="index.php?action=authors" class="btn btn-primary">Ver Todos los Autores</a>
                <?php else: ?>
                    <a href="index.php?action=createAuthor" class="btn btn-primary">Agregar el Primer Autor</a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="authors-grid">
                <?php foreach ($authors as $author): ?>
                    <div class="author-card">
                        <div class="author-header">
                            <h3><?php echo htmlspecialchars($author['name']); ?></h3>
                            <span class="author-nationality"><?php echo htmlspecialchars($author['nationality']); ?></span>
                        </div>
                        
                        <div class="author-info">
                            <?php if ($author['birth_date']): ?>
                                <p><strong>Nacimiento:</strong> <?php echo date('d/m/Y', strtotime($author['birth_date'])); ?></p>
                            <?php endif; ?>
                            
                            <?php if ($author['biography']): ?>
                                <p class="author-bio"><?php echo nl2br(htmlspecialchars(substr($author['biography'], 0, 150) . '...')); ?></p>
                            <?php endif; ?>
                        </div>
                        
                        <div class="author-actions">
                            <a href="index.php?action=showAuthor&id=<?php echo $author['id']; ?>" 
                               class="btn btn-secondary btn-sm">Ver Perfil</a>
                            <a href="index.php?action=editAuthor&id=<?php echo $author['id']; ?>" 
                               class="btn btn-warning btn-sm">Editar</a>
                            <a href="index.php?action=deleteAuthor&id=<?php echo $author['id']; ?>" 
                               class="btn btn-danger btn-sm" 
                               onclick="return confirm('¿Estás seguro de que quieres eliminar este autor?')">Eliminar</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Paginación -->
            <?php if (empty($search) && $totalPages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="index.php?action=authors&page=<?php echo $page - 1; ?>" class="btn btn-outline">Anterior</a>
                    <?php endif; ?>
                    
                    <span>Página <?php echo $page; ?> de <?php echo $totalPages; ?></span>
                    
                    <?php if ($page < $totalPages): ?>
                        <a href="index.php?action=authors&page=<?php echo $page + 1; ?>" class="btn btn-outline">Siguiente</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../app/views/layout/footer.php'; ?>