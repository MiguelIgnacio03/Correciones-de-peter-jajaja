<?php require_once '../app/views/layout/header.php'; ?>

<div class="author-profile">
    <div class="profile-header">
        <div class="profile-info">
            <h1><?php echo htmlspecialchars($author['name']); ?></h1>
            <p class="author-meta">
                <strong>Nacionalidad:</strong> <?php echo htmlspecialchars($author['nationality']); ?>
                <?php if ($author['birth_date']): ?>
                    | <strong>Nacimiento:</strong> <?php echo date('d/m/Y', strtotime($author['birth_date'])); ?>
                <?php endif; ?>
            </p>
        </div>
        <div class="profile-actions">
            <a href="index.php?action=editAuthor&id=<?php echo $author['id']; ?>" class="btn btn-warning">Editar</a>
            <a href="index.php?action=authors" class="btn btn-outline">Volver a Autores</a>
        </div>
    </div>

    <?php if ($author['biography']): ?>
        <div class="author-biography">
            <h3>Biografía</h3>
            <div class="bio-content">
                <?php echo nl2br(htmlspecialchars($author['biography'])); ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="author-books-section">
        <h3>Libros del Autor</h3>
        
        <?php if (empty($books)): ?>
            <div class="no-data">
                <p>Este autor no tiene libros registrados en el sistema.</p>
                <a href="index.php?action=createBook" class="btn btn-primary">Agregar Libro</a>
            </div>
        <?php else: ?>
            <div class="books-grid">
                <?php foreach ($books as $book): ?>
                    <div class="book-card">
                        <h4><?php echo htmlspecialchars($book['title']); ?></h4>
                        <p class="book-isbn">ISBN: <?php echo htmlspecialchars($book['isbn']); ?></p>
                        <p class="book-year">Año: <?php echo $book['publication_year'] ?? 'N/A'; ?></p>
                        <p class="book-genre">Género: <?php echo htmlspecialchars($book['genre'] ?? 'N/A'); ?></p>
                        <div class="book-copies">
                            <span class="status-badge <?php echo $book['available_copies'] > 0 ? 'available' : 'unavailable'; ?>">
                                <?php echo $book['available_copies']; ?> de <?php echo $book['total_copies']; ?> disponibles
                            </span>
                        </div>
                        <div class="book-actions">
                            <a href="index.php?action=editBook&id=<?php echo $book['id']; ?>" class="btn btn-secondary btn-sm">Editar</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../app/views/layout/footer.php'; ?>