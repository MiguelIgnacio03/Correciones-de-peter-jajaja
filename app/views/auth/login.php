<?php require_once '../app/views/layout/header.php'; ?>

<div class="auth-container">
    <div class="auth-card">
        <h2>Iniciar Sesión</h2>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error">
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="index.php?action=processLogin">
            <div class="form-group">
                <label for="username">Usuario:</label>
                <input type="text" id="username" name="username" required 
                       value="<?php echo $_SESSION['form_data']['username'] ?? ''; ?>">
            </div>
            
            <div class="form-group">
                <label for="password">Contraseña:</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <button type="submit" class="btn btn-primary">Iniciar Sesión</button>
        </form>

        <div class="auth-links">
            <p>¿No tienes cuenta? <a href="index.php?action=register">Regístrate aquí</a></p>
        </div>
    </div>
</div>

<?php require_once '../app/views/layout/footer.php'; ?>