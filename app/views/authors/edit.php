<?php require_once '../app/views/layout/header.php'; ?>

<div class="form-page">
    <h2>Editar Autor: <?php echo htmlspecialchars($author['name']); ?></h2>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-error">
            <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="index.php?action=updateAuthor" class="author-form">
        <input type="hidden" name="id" value="<?php echo $author['id']; ?>">
        
        <div class="form-group">
            <label for="name">Nombre Completo *</label>
            <input type="text" id="name" name="name" required 
                   value="<?php echo $_SESSION['form_data']['name'] ?? htmlspecialchars($author['name']); ?>">
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="nationality">Nacionalidad *</label>
                <input type="text" id="nationality" name="nationality" required 
                       value="<?php echo $_SESSION['form_data']['nationality'] ?? htmlspecialchars($author['nationality']); ?>">
            </div>

            <div class="form-group">
                <label for="birth_date">Fecha de Nacimiento</label>
                <input type="date" id="birth_date" name="birth_date" 
                       value="<?php echo $_SESSION['form_data']['birthDate'] ?? $author['birth_date']; ?>">
            </div>
        </div>

        <div class="form-group">
            <label for="biography">Biografía</label>
            <textarea id="biography" name="biography" rows="6"><?php echo $_SESSION['form_data']['biography'] ?? htmlspecialchars($author['biography'] ?? ''); ?></textarea>
        </div>

        <!-- Lista de libros del autor -->
        <?php if (!empty($books)): ?>
            <div class="author-books">
                <h3>Libros de este autor</h3>
                <div class="books-list-mini">
                    <?php foreach ($books as $book): ?>
                        <div class="book-item-mini">
                            <strong><?php echo htmlspecialchars($book['title']); ?></strong>
                            <span class="book-copies">(<?php echo $book['available_copies']; ?> disponibles)</span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Actualizar Autor</button>
            <a href="index.php?action=authors" class="btn btn-outline">Cancelar</a>
        </div>
    </form>
</div>

<?php 
// Limpiar datos del formulario de la sesión
unset($_SESSION['form_data']);
require_once '../app/views/layout/footer.php'; 
?>