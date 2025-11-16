<?php require_once '../app/views/layout/header.php'; ?>

<div class="form-page">
    <h2>Registrar Nuevo Préstamo</h2>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-error">
            <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="index.php?action=storeLoan" class="loan-form" id="loanForm">
        <div class="form-row">
            <div class="form-group">
                <label for="user_id">Usuario *</label>
                <select id="user_id" name="user_id" required onchange="loadUserInfo(this.value)">
                    <option value="">Seleccionar Usuario</option>
                    <?php foreach ($users as $user): ?>
                        <option value="<?php echo $user['id']; ?>" 
                            <?php echo (isset($_SESSION['form_data']['userId']) && $_SESSION['form_data']['userId'] == $user['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name'] . ' (' . $user['username'] . ')'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <div id="user-info" class="info-box" style="display: none;">
                    <h4>Información del Usuario</h4>
                    <div id="user-details"></div>
                </div>
            </div>

            <div class="form-group">
                <label for="book_id">Libro *</label>
                <select id="book_id" name="book_id" required onchange="loadBookInfo(this.value)">
                    <option value="">Seleccionar Libro</option>
                    <?php foreach ($availableBooks as $book): ?>
                        <option value="<?php echo $book['id']; ?>" 
                            <?php echo (isset($_SESSION['form_data']['bookId']) && $_SESSION['form_data']['bookId'] == $book['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($book['title'] . ' - ' . $book['author_name']); ?>
                            (<?php echo $book['available_copies']; ?> disponibles)
                        </option>
                    <?php endforeach; ?>
                </select>
                <div id="book-info" class="info-box" style="display: none;">
                    <h4>Información del Libro</h4>
                    <div id="book-details"></div>
                </div>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="loan_date">Fecha de Préstamo *</label>
                <input type="date" id="loan_date" name="loan_date" required 
                       value="<?php echo $_SESSION['form_data']['loanDate'] ?? date('Y-m-d'); ?>">
            </div>

            <div class="form-group">
                <label for="due_date">Fecha de Devolución *</label>
                <input type="date" id="due_date" name="due_date" required 
                       value="<?php echo $_SESSION['form_data']['dueDate'] ?? date('Y-m-d', strtotime('+14 days')); ?>">
                <small>Préstamo por <?php echo MAX_LOAN_DAYS; ?> días</small>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Registrar Préstamo</button>
            <a href="index.php?action=loans" class="btn btn-outline">Cancelar</a>
        </div>
    </form>
</div>

<script>
function loadUserInfo(userId) {
    if (!userId) {
        document.getElementById('user-info').style.display = 'none';
        return;
    }
    
    const formData = new FormData();
    formData.append('user_id', userId);
    
    fetch('index.php?action=getUserInfo', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.error) {
            document.getElementById('user-info').style.display = 'none';
            return;
        }
        
        document.getElementById('user-details').innerHTML = `
            <p><strong>Nombre:</strong> ${data.firstName} ${data.lastName}</p>
            <p><strong>Email:</strong> ${data.email}</p>
            <p><strong>Préstamos activos:</strong> ${data.activeLoans}</p>
        `;
        document.getElementById('user-info').style.display = 'block';
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('user-info').style.display = 'none';
    });
}

function loadBookInfo(bookId) {
    if (!bookId) {
        document.getElementById('book-info').style.display = 'none';
        return;
    }
    
    const formData = new FormData();
    formData.append('book_id', bookId);
    
    fetch('index.php?action=getBookInfo', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.error) {
            document.getElementById('book-info').style.display = 'none';
            return;
        }
        
        document.getElementById('book-details').innerHTML = `
            <p><strong>Título:</strong> ${data.title}</p>
            <p><strong>Autor:</strong> ${data.author}</p>
            <p><strong>ISBN:</strong> ${data.isbn}</p>
            <p><strong>Copias disponibles:</strong> ${data.available_copies}</p>
        `;
        document.getElementById('book-info').style.display = 'block';
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('book-info').style.display = 'none';
    });
}

// Cargar información inicial si hay valores seleccionados
document.addEventListener('DOMContentLoaded', function() {
    const userId = document.getElementById('user_id').value;
    const bookId = document.getElementById('book_id').value;
    
    if (userId) loadUserInfo(userId);
    if (bookId) loadBookInfo(bookId);
});
</script>

<?php 
// Limpiar datos del formulario de la sesión
unset($_SESSION['form_data']);
require_once '../app/views/layout/footer.php'; 
?>