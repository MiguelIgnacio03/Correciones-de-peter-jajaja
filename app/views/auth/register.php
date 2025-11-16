<?php require_once '../app/views/layout/header.php'; ?>

<div class="auth-container">
    <div class="auth-card">
        <h2>Crear Cuenta</h2>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error">
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="index.php?action=processRegister">
            <div class="form-group">
                <label for="username">Usuario:</label>
                <input type="text" id="username" name="username" required 
                       value="<?php echo $_SESSION['form_data']['username'] ?? ''; ?>">
            </div>

            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required 
                       value="<?php echo $_SESSION['form_data']['email'] ?? ''; ?>">
            </div>
            
            <div class="form-group">
                <label for="first_name">Nombre:</label>
                <input type="text" id="first_name" name="first_name" required 
                       value="<?php echo $_SESSION['form_data']['firstName'] ?? ''; ?>">
            </div>

            <div class="form-group">
                <label for="last_name">Apellido:</label>
                <input type="text" id="last_name" name="last_name" required 
                       value="<?php echo $_SESSION['form_data']['lastName'] ?? ''; ?>">
            </div>
            
            <div class="form-group">
                <label for="password">Contraseña:</label>
                <input type="password" id="password" name="password" required>
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirmar Contraseña:</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
            
            <button type="submit" class="btn btn-primary">Registrarse</button>
        </form>

        <div class="auth-links">
            <p>¿Ya tienes cuenta? <a href="index.php?action=login">Inicia sesión aquí</a></p>
        </div>
    </div>
</div>

<?php 
// Limpiar datos del formulario de la sesión
unset($_SESSION['form_data']);
require_once '../app/views/layout/footer.php'; 
?>