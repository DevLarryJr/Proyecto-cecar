/**
 * hold-confirm.js
 * Mantener presionado durante 1.5 segundos confirma la acción.
 * Envía el formulario mediante AJAX y recarga automáticamente
 * la vista para mostrar el botón correspondiente al nuevo estado.
 */

document.addEventListener('DOMContentLoaded', () => {
    const HOLD_DURATION = 1500;

    document.querySelectorAll('.hold-trigger').forEach((btn) => {
        let holdTimer = null;
        let activePointerId = null;
        let isHolding = false;
        let isProcessing = false;

        const formId = btn.getAttribute('data-form');
        const form = formId ? document.getElementById(formId) : null;
        const originalHtml = btn.innerHTML;

        /**
         * Evita comportamientos extraños en pantallas táctiles
         * cuando el usuario mantiene presionado el botón.
         */
        btn.style.touchAction = 'none';
        btn.style.userSelect = 'none';

        /**
         * Obtiene la barra interna de progreso.
         * Se consulta nuevamente porque el HTML del botón puede restaurarse.
         */
        const getProgressFill = () => btn.querySelector('.progress-fill');

        /**
         * Devuelve la barra visual a su estado inicial.
         */
        const resetProgress = () => {
            const progressFill = getProgressFill();

            btn.classList.remove('holding');

            if (progressFill) {
                progressFill.style.transition = 'none';
                progressFill.style.width = '0%';

                /**
                 * Obliga al navegador a aplicar primero el ancho 0%.
                 * Sin esto, algunos navegadores no muestran la animación.
                 */
                void progressFill.offsetWidth;
            }
        };

        /**
         * Libera la captura del puntero de manera segura.
         */
        const releasePointer = () => {
            if (
                activePointerId !== null &&
                typeof btn.hasPointerCapture === 'function' &&
                btn.hasPointerCapture(activePointerId)
            ) {
                try {
                    btn.releasePointerCapture(activePointerId);
                } catch (error) {
                    console.warn('No fue posible liberar el puntero:', error);
                }
            }

            activePointerId = null;
        };

        /**
         * Cancela el conteo si el usuario deja de presionar
         * antes de completar los 1.5 segundos.
         */
        const cancelHold = (event = null) => {
            if (
                event &&
                activePointerId !== null &&
                typeof event.pointerId !== 'undefined' &&
                event.pointerId !== activePointerId
            ) {
                return;
            }

            if (holdTimer !== null) {
                clearTimeout(holdTimer);
                holdTimer = null;
            }

            isHolding = false;
            releasePointer();

            if (!isProcessing) {
                resetProgress();
            }
        };

        /**
         * Inicia la animación y el conteo al mantener presionado.
         */
        const startHold = (event) => {
            /**
             * Solo aceptar el clic principal del mouse.
             * En táctil, event.button normalmente es 0.
             */
            if (typeof event.button !== 'undefined' && event.button !== 0) {
                return;
            }

            if (btn.disabled || isHolding || isProcessing) {
                return;
            }

            event.preventDefault();

            activePointerId = event.pointerId;
            isHolding = true;

            /**
             * Mantener el control del puntero aunque el usuario
             * mueva ligeramente el mouse fuera del botón.
             */
            if (typeof btn.setPointerCapture === 'function') {
                try {
                    btn.setPointerCapture(event.pointerId);
                } catch (error) {
                    console.warn('No fue posible capturar el puntero:', error);
                }
            }

            btn.classList.add('holding');

            const progressFill = getProgressFill();

            if (progressFill) {
                progressFill.style.transition = 'none';
                progressFill.style.width = '0%';

                /**
                 * Forzar un ciclo de renderizado antes de iniciar
                 * la transición para que la barra avance visualmente.
                 */
                void progressFill.offsetWidth;

                requestAnimationFrame(() => {
                    if (!isHolding || isProcessing) {
                        return;
                    }

                    progressFill.style.transition = `width ${HOLD_DURATION}ms linear`;
                    progressFill.style.width = '100%';
                });
            }

            holdTimer = setTimeout(() => {
                holdTimer = null;
                isHolding = false;

                confirmAction();
            }, HOLD_DURATION);
        };

        /**
         * Envía el formulario al controlador cuando se completa
         * el tiempo de pulsación.
         */
        const confirmAction = async () => {
            if (isProcessing) {
                return;
            }

            if (!form) {
                alert('Error interno: formulario no encontrado.');
                cancelHold();
                return;
            }

            isProcessing = true;
            btn.disabled = true;
            btn.setAttribute('aria-busy', 'true');
            btn.classList.remove('holding');

            btn.innerHTML = '<span class="relative z-10">Procesando...</span>';

            const formData = new FormData(form);

            /**
             * set() evita duplicar el campo ajax si ya estaba presente.
             */
            formData.set('ajax', '1');

            try {
                const response = await fetch('../../negocio/RevisionController.php', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                /**
                 * Leer primero como texto permite mostrar en consola
                 * cualquier error PHP que no venga en formato JSON.
                 */
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
                 * Recargar automáticamente permite que PHP consulte
                 * el nuevo estado y construya el botón del siguiente paso.
                 */
                setTimeout(() => {
                    window.location.reload();
                }, 550);

            } catch (error) {
                console.error('Error en avance de estado:', error);
                alert('Error: ' + error.message);

                isProcessing = false;
                btn.disabled = false;
                btn.removeAttribute('aria-busy');
                btn.innerHTML = originalHtml;

                resetProgress();
                releasePointer();
            }
        };

        btn.addEventListener('pointerdown', startHold);
        btn.addEventListener('pointerup', cancelHold);
        btn.addEventListener('pointercancel', cancelHold);
        btn.addEventListener('lostpointercapture', () => {
            if (!isProcessing && isHolding) {
                cancelHold();
            }
        });

        btn.addEventListener('click', (event) => {
            event.preventDefault();
        });

        btn.addEventListener('contextmenu', (event) => {
            event.preventDefault();
        });

        btn.addEventListener('dragstart', (event) => {
            event.preventDefault();
        });

        resetProgress();
    });
});

/**
 * Muestra una notificación temporal de éxito.
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
