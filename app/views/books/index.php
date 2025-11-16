<?php require_once '../app/views/layout/header.php'; ?>

<div class="books-page">
    <div class="page-header">
        <h2>Gestión de Libros</h2>
        <a href="index.php?action=createBook" class="btn btn-primary">Agregar Libro</a>
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
            <input type="hidden" name="action" value="books">
            <div class="search-group">
                <input type="text" name="search" placeholder="Buscar por título, autor o ISBN..." 
                       value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                <button type="submit" class="btn btn-secondary">Buscar</button>
                <?php if (!empty($search)): ?>
                    <a href="index.php?action=books" class="btn btn-outline">Limpiar</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Lista de libros -->
    <div class="books-list">
        <?php if (empty($books)): ?>
            <div class="no-data">
                <p>No se encontraron libros</p>
                <?php if (!empty($search)): ?>
                    <a href="index.php?action=books" class="btn btn-primary">Ver Todos los Libros</a>
                <?php else: ?>
                    <a href="index.php?action=createBook" class="btn btn-primary">Agregar el Primer Libro</a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Título</th>
                            <th>Autor</th>
                            <th>ISBN</th>
                            <th>Género</th>
                            <th>Copias</th>
                            <th>Disponibles</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($books as $book): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($book['title']); ?></strong>
                                    <?php if ($book['publication_year']): ?>
                                        <br><small><?php echo $book['publication_year']; ?></small>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($book['author_name']); ?></td>
                                <td><?php echo htmlspecialchars($book['isbn']); ?></td>
                                <td><?php echo htmlspecialchars($book['genre'] ?? 'N/A'); ?></td>
                                <td><?php echo $book['total_copies']; ?></td>
                                <td>
                                    <span class="status-badge <?php echo $book['available_copies'] > 0 ? 'available' : 'unavailable'; ?>">
                                        <?php echo $book['available_copies']; ?>
                                    </span>
                                </td>
                                <td class="actions">
                                    <a href="index.php?action=editBook&id=<?php echo $book['id']; ?>" 
                                       class="btn btn-warning btn-sm">Editar</a>
                                    <a href="index.php?action=deleteBook&id=<?php echo $book['id']; ?>" 
                                       class="btn btn-danger btn-sm" 
                                       onclick="return confirm('¿Estás seguro de que quieres eliminar este libro?')">Eliminar</a>
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
                        <a href="index.php?action=books&page=<?php echo $page - 1; ?>" class="btn btn-outline">Anterior</a>
                    <?php endif; ?>
                    
                    <span>Página <?php echo $page; ?> de <?php echo $totalPages; ?></span>
                    
                    <?php if ($page < $totalPages): ?>
                        <a href="index.php?action=books&page=<?php echo $page + 1; ?>" class="btn btn-outline">Siguiente</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../app/views/layout/footer.php'; ?>