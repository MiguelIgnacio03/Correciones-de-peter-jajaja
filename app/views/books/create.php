<?php require_once '../app/views/layout/header.php'; ?>

<div class="form-page">
    <h2>Agregar Nuevo Libro</h2>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-error">
            <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="index.php?action=storeBook" class="book-form">
        <div class="form-row">
            <div class="form-group">
                <label for="title">Título *</label>
                <input type="text" id="title" name="title" required 
                       value="<?php echo $_SESSION['form_data']['title'] ?? ''; ?>">
            </div>

            <div class="form-group">
                <label for="isbn">ISBN *</label>
                <input type="text" id="isbn" name="isbn" required 
                       value="<?php echo $_SESSION['form_data']['isbn'] ?? ''; ?>">
            </div>
        </div>

        <div class="form-group">
            <label for="author_id">Autor *</label>
            <select id="author_id" name="author_id" required>
                <option value="">Seleccionar Autor</option>
                <?php foreach ($authors as $author): ?>
                    <option value="<?php echo $author['id']; ?>" 
                        <?php echo (isset($_SESSION['form_data']['authorId']) && $_SESSION['form_data']['authorId'] == $author['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($author['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="publication_year">Año de Publicación</label>
                <input type="number" id="publication_year" name="publication_year" 
                       min="1000" max="<?php echo date('Y'); ?>"
                       value="<?php echo $_SESSION['form_data']['publicationYear'] ?? ''; ?>">
            </div>

            <div class="form-group">
                <label for="genre">Género</label>
                <input type="text" id="genre" name="genre" 
                       value="<?php echo $_SESSION['form_data']['genre'] ?? ''; ?>">
            </div>
        </div>

        <div class="form-group">
            <label for="publisher">Editorial</label>
            <input type="text" id="publisher" name="publisher" 
                   value="<?php echo $_SESSION['form_data']['publisher'] ?? ''; ?>">
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="total_copies">Total de Copias *</label>
                <input type="number" id="total_copies" name="total_copies" required 
                       min="1" value="<?php echo $_SESSION['form_data']['totalCopies'] ?? 1; ?>">
            </div>

            <div class="form-group">
                <label for="available_copies">Copias Disponibles *</label>
                <input type="number" id="available_copies" name="available_copies" required 
                       min="0" value="<?php echo $_SESSION['form_data']['availableCopies'] ?? 1; ?>">
            </div>
        </div>

        <div class="form-group">
            <label for="description">Descripción</label>
            <textarea id="description" name="description" rows="4"><?php echo $_SESSION['form_data']['description'] ?? ''; ?></textarea>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Guardar Libro</button>
            <a href="index.php?action=books" class="btn btn-outline">Cancelar</a>
        </div>
    </form>
</div>

<?php 
// Limpiar datos del formulario de la sesión
unset($_SESSION['form_data']);
require_once '../app/views/layout/footer.php'; 
?>