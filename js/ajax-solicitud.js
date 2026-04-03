document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('solicitudForm');
    const statusContainer = document.getElementById('status-container');
    const statusMessage = document.getElementById('status-message');
    const submitBtn = form.querySelector('button[type="submit"]');

    if (!form) return;

    form.addEventListener('submit', async (e) => {
        // Primero dejamos que validaciones.js haga su trabajo si existe
        // Pero como queremos AJAX total, interceptamos aquí.
        e.preventDefault();

        // Limpiar estados previos
        statusContainer.classList.add('hidden');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="animate-spin mr-2">◌</span> Procesando...';

        const formData = new FormData(form);

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const result = await response.json();

            if (result.success) {
                // ÉXITO
                statusContainer.className = "mb-6 p-4 rounded-xl border border-green-200 bg-green-50 text-green-700 block animate-fade-in";
                statusMessage.textContent = result.message;
                
                // Opcional: Limpiar formulario o redirigir
                form.reset();
                // Si hay una tabla dinámica, se podría recargar aquí de ser necesario.
                
                // Redirigir suavemente tras 2 segundos
                setTimeout(() => {
                    window.location.href = 'solicitudes.php';
                }, 2000);

            } else {
                // ERROR (Validación backend)
                statusContainer.className = "mb-6 p-4 rounded-xl border border-red-200 bg-red-50 text-red-700 block animate-fade-in";
                statusMessage.innerHTML = "<strong>Error:</strong><br>" + result.errors.join('<br>');
                submitBtn.disabled = false;
                submitBtn.textContent = 'Enviar Solicitud';
            }

        } catch (error) {
            console.error('Error AJAX:', error);
            statusContainer.className = "mb-6 p-4 rounded-xl border border-red-200 bg-red-50 text-red-700 block animate-fade-in";
            statusMessage.textContent = "Ocurrió un problema de conexión al enviar la solicitud.";
            submitBtn.disabled = false;
            submitBtn.textContent = 'Enviar Solicitud';
        }
    });
});
