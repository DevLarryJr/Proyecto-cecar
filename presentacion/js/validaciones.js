document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('solicitudForm');
    if (!form) return;

    /**
     * Utility to manage visual error states
     */
    const UI = {
        /**
         * @param {HTMLElement|string} target - ID or Element
         */
        showError: (target, message) => {
            const el = typeof target === 'string' ? document.getElementById(target) : target;
            if (!el) return;

            // Highlight field
            el.classList.add('border-red-500', 'ring-2', 'ring-red-100');
            el.classList.remove('border-gray-200');

            // Add or update error message
            if (!el.name?.includes('[]')) {
                let errorMsg = el.parentElement.querySelector('.error-message');
                if (!errorMsg) {
                    errorMsg = document.createElement('p');
                    errorMsg.className = 'error-message text-red-500 text-xs mt-1 font-medium animate-pulse flex items-center gap-1';
                    el.parentElement.appendChild(errorMsg);
                }
                errorMsg.innerHTML = `
                    <svg style="width: 0.9em; height: 0.9em;" class="inline-block shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    ${message}`;
                errorMsg.classList.remove('hidden');
            }
        },


        /**
         * @param {HTMLElement|string} target - ID or Element
         */
        clearError: (target) => {
            const el = typeof target === 'string' ? document.getElementById(target) : target;
            if (!el) return;

            el.classList.remove('border-red-500', 'ring-2', 'ring-red-100');
            el.classList.add('border-gray-200', 'bg-gray-50');

            if (!el.name?.includes('[]')) {
                const errorMsg = el.parentElement.querySelector('.error-message');
                if (errorMsg) {
                    errorMsg.classList.add('hidden');
                }
            }
        }
    };

    /**
     * Real-time validation: clear errors as user types/interacts.
     * Listener global que remueve visualmente el error apenas el usuario empieza a solucionar
     * un campo marcado en rojo (cuando escribe o selecciona algo diferente).
     */
    ['input', 'change'].forEach(eventType => {
        form.addEventListener(eventType, (e) => {
            UI.clearError(e.target);

            // Special case: Clear table error if any row becomes valid/active
            const tableContainer = document.querySelector('.table-container');
            const existingTableError = tableContainer?.parentElement.querySelector('.table-error');
            if (existingTableError && e.target.name?.includes('[]')) {
                existingTableError.remove();
            }
        });
    });

    form.addEventListener('submit', (e) => {
        let isValid = true;

        // 1. Validar los campos estáticos principales
        const generalFields = [
            { id: 'nombre_solicitante', label: 'El nombre del solicitante' },
            { id: 'dependencia', label: 'La dependencia' },
            { id: 'fecha_solicitud', label: 'La fecha de solicitud' },
            { id: 'justificacion', label: 'La justificación' }
        ];

        // Recorremos los campos y si están vacíos disparamos (UI.showError)
        generalFields.forEach(field => {
            const el = document.getElementById(field.id);
            if (!el || !el.value.trim()) {
                isValid = false;
                UI.showError(field.id, `${field.label} es obligatorio.`);
            } else {
                UI.clearError(field.id);
            }
        });

        // 2. Validate Services Table (Row by Row)
        // Se valida iterativamente el modelo dinámico de filas.
        const rows = document.querySelectorAll('#items tr');
        let hasAnyService = false;
        const tableContainer = document.querySelector('.table-container');

        // Remove previous table-wide error
        const existingTableError = tableContainer?.parentElement.querySelector('.table-error');
        if (existingTableError) existingTableError.remove();

        rows.forEach((row, index) => {
            const inputs = Array.from(row.querySelectorAll('input, select'));
            // Detectamos si la fila fue empezada: al menos uno de sus campos tiene valor.
            const isRowStarted = inputs.some(i => i.value.trim() !== '');

            if (isRowStarted) {
                hasAnyService = true;
                // SI fue empezada, TODOS los elementos listados abajo se vuelven obligatorios.
                const requiredNames = [
                    'servicios[]', 'cantidades[]',
                    'cc_nombres[]', 'cc_codigos[]',
                    'rubro_nombres[]', 'rubro_codigos[]',
                    'disponibilidades[]', 'fondos[]',
                    'funcion_nombres[]', 'funcion_codigos[]'
                ];

                inputs.forEach(input => {
                    if (requiredNames.includes(input.name) && !input.value.trim()) {
                        isValid = false;
                        UI.showError(input, 'Requerido');
                    }
                });
            }
        });

        if (!hasAnyService) {
            isValid = false;
            const errorMsg = document.createElement('p');
            errorMsg.className = 'table-error text-red-500 text-sm mt-3 font-semibold text-center py-2 bg-red-50 rounded-lg animate-bounce flex items-center justify-center gap-2';
            errorMsg.innerHTML = `
                <svg style="width: 0.9em; height: 0.9em;" class="inline-block shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
                Debes agregar y completar al menos un servicio.`;
            tableContainer.after(errorMsg);
        }

        // Si la validación no pasó, anulamos explícitamente el "submit"
        // para asegurar que el AJAX principal nunca se dispare o recargue la página.
        if (!isValid) {
            e.preventDefault();
            // Scroll suave (auto-enfoque) hacia el primer error visible
            const firstError = document.querySelector('.border-red-500');
            if (firstError) {
                firstError.focus();
                firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }
    });
});


