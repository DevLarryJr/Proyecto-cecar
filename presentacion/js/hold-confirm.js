/**
 * hold-confirm.js
 * Mantener presionado confirma la acción. Usa fetch(FormData) para
 * enviar el formulario sin recargar la página, actualizando la fila en vivo.
 */

document.addEventListener('DOMContentLoaded', () => {
    const holdButtons = document.querySelectorAll('.hold-trigger');
    const HOLD_DURATION = 1500;

    holdButtons.forEach(btn => {
        let holdTimer = null;
        const progressFill = btn.querySelector('.progress-fill');
        const formId = btn.getAttribute('data-form');
        const form = formId ? document.getElementById(formId) : null;

        const startHold = () => {
            btn.classList.add('holding');
            if (progressFill) {
                progressFill.style.transition = `width ${HOLD_DURATION}ms linear`;
                progressFill.style.width = '100%';
            }
            holdTimer = setTimeout(() => confirmAction(btn, form), HOLD_DURATION);
        };

        const cancelHold = () => {
            if (holdTimer) {
                clearTimeout(holdTimer);
                holdTimer = null;
            }
            btn.classList.remove('holding');
            if (progressFill) {
                progressFill.style.transition = 'width 0.2s ease-out';
                progressFill.style.width = '0%';
            }
        };

        const confirmAction = (element, targetForm) => {
            if (!targetForm) {
                alert('Error interno: formulario no encontrado.');
                return;
            }

            // Feedback visual inmediato
            element.disabled = true;
            element.innerHTML = '<span>Procesando...</span>';

            // Capturar todos los campos del form (incluido textarea) con FormData
            const formData = new FormData(targetForm);
            formData.append('ajax', '1'); // Indicar que es AJAX

            fetch('../../negocio/RevisionController.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    // Actualizar la fila sin recargar: remover la tarjeta de esta solicitud
                    const row = element.closest('tr');
                    if (row) {
                        // Animación de salida
                        row.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                        row.style.opacity = '0';
                        row.style.transform = 'translateX(30px)';
                        setTimeout(() => {
                            row.remove();
                            // Actualizar el contador de pendientes
                            const counter = document.getElementById('revCount');
                            if (counter) {
                                const current = parseInt(counter.textContent, 10);
                                counter.textContent = Math.max(0, current - 1);
                            }
                        }, 500);
                    }

                    // Mostrar notificación temporal de éxito
                    showToast('✓ Estado avanzado correctamente');
                } else {
                    const msg = data.errors ? data.errors.join(', ') : (data.message || 'No se pudo avanzar el estado');
                    alert('Error: ' + msg);
                    element.disabled = false;
                    element.innerHTML = `<span class="relative z-10">Reintentar (Mantener)</span><div class="progress-fill"></div>`;
                    if (progressFill) progressFill.style.width = '0%';
                }
            })
            .catch(err => {
                console.error('Error en avance de estado:', err);
                alert('Ocurrió un error en la conexión.');
                element.disabled = false;
            });
        };

        btn.addEventListener('mousedown', startHold);
        btn.addEventListener('mouseup', cancelHold);
        btn.addEventListener('mouseleave', cancelHold);
        btn.addEventListener('touchstart', startHold, { passive: true });
        btn.addEventListener('touchend', cancelHold);
        btn.addEventListener('touchcancel', cancelHold);
    });

    // Toast de notificación temporal
    function showToast(message) {
        const toast = document.createElement('div');
        toast.textContent = message;
        toast.style.cssText = `
            position: fixed; bottom: 24px; right: 24px; z-index: 9999;
            background: #064c2b; color: white;
            padding: 14px 24px; border-radius: 14px;
            font-size: 13px; font-weight: 700;
            box-shadow: 0 8px 30px rgba(6,76,43,0.3);
            opacity: 0; transform: translateY(10px);
            transition: all 0.3s ease;
        `;
        document.body.appendChild(toast);
        // Animar entrada
        requestAnimationFrame(() => {
            toast.style.opacity = '1';
            toast.style.transform = 'translateY(0)';
        });
        // Remover después de 3 segundos
        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateY(10px)';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }
});
