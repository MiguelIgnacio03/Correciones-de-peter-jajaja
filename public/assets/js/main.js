/**
 * Funciones JavaScript principales del sistema de biblioteca
 * 
 * @file Maneja la interactividad general de la aplicación
 */

/**
 * Muestra un mensaje de confirmación antes de realizar acciones destructivas
 * 
 * @param {string} message Mensaje a mostrar en la confirmación
 * @returns {boolean} True si el usuario confirma, false en caso contrario
 */
function confirmAction(message = '¿Estás seguro de que quieres realizar esta acción?') {
    return confirm(message);
}

/**
 * Formatea una fecha en formato legible
 * 
 * @param {string} dateString String de fecha a formatear
 * @returns {string} Fecha formateada
 */
function formatDate(dateString) {
    const options = { 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric' 
    };
    return new Date(dateString).toLocaleDateString('es-ES', options);
}

/**
 * Maneja la visualización de mensajes flash
 */
document.addEventListener('DOMContentLoaded', function() {
    // Auto-ocultar alertas después de 5 segundos
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            alert.style.transition = 'opacity 0.5s ease';
            setTimeout(() => alert.remove(), 500);
        }, 5000);
    });

    // Validación básica de formularios
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const requiredFields = form.querySelectorAll('[required]');
            let isValid = true;

            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    field.style.borderColor = 'var(--danger-color)';
                } else {
                    field.style.borderColor = '';
                }
            });

            if (!isValid) {
                e.preventDefault();
                alert('Por favor, completa todos los campos requeridos.');
            }
        });
    });
});

/**
 * Realiza una petición AJAX
 * 
 * @param {string} url URL a la que hacer la petición
 * @param {string} method Método HTTP (GET, POST, etc.)
 * @param {Object} data Datos a enviar
 * @returns {Promise} Promise con la respuesta
 */
function ajaxRequest(url, method = 'GET', data = null) {
    const options = {
        method: method,
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        }
    };

    if (data && method !== 'GET') {
        options.body = new URLSearchParams(data).toString();
    }

    return fetch(url, options)
        .then(response => {
            if (!response.ok) {
                throw new Error('Error en la petición');
            }
            return response.json();
        })
        .catch(error => {
            console.error('Error:', error);
            throw error;
        });
}