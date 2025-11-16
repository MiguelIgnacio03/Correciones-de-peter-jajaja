<?php require_once '../app/views/layout/header.php'; ?>

<div class="form-page">
    <h2>Agregar Nuevo Autor</h2>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-error">
            <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="index.php?action=storeAuthor" class="author-form">
        <div class="form-group">
            <label for="name">Nombre Completo *</label>
            <input type="text" id="name" name="name" required 
                   value="<?php echo $_SESSION['form_data']['name'] ?? ''; ?>"
                   placeholder="Ej: Gabriel García Márquez">
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="nationality">Nacionalidad *</label>
                <input type="text" id="nationality" name="nationality" required 
                       value="<?php echo $_SESSION['form_data']['nationality'] ?? ''; ?>"
                       placeholder="Ej: Colombiana">
            </div>

            <div class="form-group">
                <label for="birth_date">Fecha de Nacimiento</label>
                <input type="date" id="birth_date" name="birth_date" 
                       value="<?php echo $_SESSION['form_data']['birthDate'] ?? ''; ?>">
            </div>
        </div>

        <div class="form-group">
            <label for="biography">Biografía</label>
            <textarea id="biography" name="biography" rows="6" 
                      placeholder="Escribe una breve biografía del autor..."><?php echo $_SESSION['form_data']['biography'] ?? ''; ?></textarea>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Guardar Autor</button>
            <a href="index.php?action=authors" class="btn btn-outline">Cancelar</a>
        </div>
    </form>
</div>

<?php 
// Limpiar datos del formulario de la sesión
unset($_SESSION['form_data']);
require_once '../app/views/layout/footer.php'; 
?>