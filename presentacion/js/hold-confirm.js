/**

* hold-confirm.js
* Mantener presionado confirma la acción.
* Envía el formulario mediante AJAX y recarga automáticamente
* la vista para mostrar el botón correspondiente al nuevo estado.
  */

document.addEventListener('DOMContentLoaded', () => {
const HOLD_DURATION = 1500;

```
document.querySelectorAll('.hold-trigger').forEach((btn) => {
    let holdTimer = null;
    let isProcessing = false;

    const originalHtml = btn.innerHTML;
    const formId = btn.getAttribute('data-form');
    const form = formId ? document.getElementById(formId) : null;

    /**
     * Obtiene nuevamente la barra de progreso.
     * Se consulta cada vez porque el contenido del botón puede cambiar.
     */
    const getProgressFill = () => btn.querySelector('.progress-fill');

    /**
     * Restablece la apariencia inicial del botón.
     */
    const resetVisualState = () => {
        btn.classList.remove('holding');

        const progressFill = getProgressFill();

        if (progressFill) {
            progressFill.style.transition = 'width 0.2s ease-out';
            progressFill.style.width = '0%';
        }
    };

    /**
     * Inicia el conteo cuando el usuario mantiene presionado el botón.
     */
    const startHold = (event) => {
        event.preventDefault();

        if (btn.disabled || isProcessing || holdTimer !== null) {
            return;
        }

        btn.classList.add('holding');

        const progressFill = getProgressFill();

        if (progressFill) {
            progressFill.style.transition = `width ${HOLD_DURATION}ms linear`;
            progressFill.style.width = '100%';
        }

        holdTimer = setTimeout(() => {
            holdTimer = null;
            confirmAction();
        }, HOLD_DURATION);
    };

    /**
     * Cancela la acción si el usuario deja de presionar antes del tiempo requerido.
     */
    const cancelHold = () => {
        if (isProcessing) {
            return;
        }

        if (holdTimer !== null) {
            clearTimeout(holdTimer);
            holdTimer = null;
        }

        resetVisualState();
    };

    /**
     * Envía el avance de estado al controlador.
     */
    const confirmAction = async () => {
        if (isProcessing) {
            return;
        }

        if (!form) {
            alert('Error interno: formulario no encontrado.');
            resetVisualState();
            return;
        }

        isProcessing = true;
        btn.disabled = true;
        btn.innerHTML = '<span>Procesando...</span>';

        const formData = new FormData(form);

        if (!formData.has('ajax')) {
            formData.append('ajax', '1');
        }

        try {
            const response = await fetch('../../negocio/RevisionController.php', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const rawResponse = await response.text();

            let data;

            try {
                data = JSON.parse(rawResponse);
            } catch (error) {
                console.error('Respuesta no válida del servidor:', rawResponse);
                throw new Error('El servidor devolvió una respuesta inesperada.');
            }

            if (!response.ok || !data.success) {
                const message = Array.isArray(data.errors)
                    ? data.errors.join(', ')
                    : (data.message || 'No se pudo avanzar el estado.');

                throw new Error(message);
            }

            showToast('✓ Estado avanzado correctamente');

            /**
             * Recargar la página permite que PHP consulte el nuevo estado
             * y genere inmediatamente el botón correspondiente al siguiente paso.
             */
            setTimeout(() => {
                window.location.reload();
            }, 500);

        } catch (error) {
            console.error('Error en avance de estado:', error);

            alert('Error: ' + error.message);

            isProcessing = false;
            btn.disabled = false;
            btn.innerHTML = originalHtml;

            resetVisualState();
        }
    };

    /**
     * Pointer Events funcionan tanto con mouse como con pantalla táctil.
     * Evitan registrar dos veces la misma acción en dispositivos móviles.
     */
    btn.addEventListener('pointerdown', startHold);
    btn.addEventListener('pointerup', cancelHold);
    btn.addEventListener('pointerleave', cancelHold);
    btn.addEventListener('pointercancel', cancelHold);

    /**
     * Evita que aparezca el menú contextual al mantener presionado.
     */
    btn.addEventListener('contextmenu', (event) => {
        event.preventDefault();
    });
});

/**
 * Muestra una notificación temporal en la esquina inferior derecha.
 */
function showToast(message) {
    const toast = document.createElement('div');

    toast.textContent = message;

    toast.style.cssText = `
        position: fixed;
        bottom: 24px;
        right: 24px;
        z-index: 9999;
        background: #064c2b;
        color: white;
        padding: 14px 24px;
        border-radius: 14px;
        font-size: 13px;
        font-weight: 700;
        box-shadow: 0 8px 30px rgba(6, 76, 43, 0.3);
        opacity: 0;
        transform: translateY(10px);
        transition: all 0.3s ease;
    `;

    document.body.appendChild(toast);

    requestAnimationFrame(() => {
        toast.style.opacity = '1';
        toast.style.transform = 'translateY(0)';
    });

    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(10px)';

        setTimeout(() => {
            toast.remove();
        }, 300);
    }, 3000);
}
```

});
