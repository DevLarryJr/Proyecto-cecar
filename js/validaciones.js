document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('solicitudForm');
    if (!form) return;

    /**
     * Utility to manage visual error states
     */
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

            // Add or update error message (optional for dense tables, but we'll try to fit it)
            // For table inputs, we'll just use the tooltip or a simpler highlight
            if (!el.name?.includes('[]')) {
                let errorMsg = el.parentElement.querySelector('.error-message');
                if (!errorMsg) {
                    errorMsg = document.createElement('p');
                    errorMsg.className = 'error-message text-red-500 text-xs mt-1 font-medium animate-pulse';
                    el.parentElement.appendChild(errorMsg);
                }
                errorMsg.textContent = message;
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
     * Real-time validation: clear errors as user types/interacts
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

        // 1. Validate General Fields
        const generalFields = [
            { id: 'nombre_solicitante', label: 'El nombre del solicitante' },
            { id: 'dependencia', label: 'La dependencia' },
            { id: 'fecha_solicitud', label: 'La fecha de solicitud' },
            { id: 'justificacion', label: 'La justificación' }
        ];

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
        const rows = document.querySelectorAll('#items tr');
        let hasAnyService = false;
        const tableContainer = document.querySelector('.table-container');

        // Remove previous table-wide error
        const existingTableError = tableContainer?.parentElement.querySelector('.table-error');
        if (existingTableError) existingTableError.remove();

        rows.forEach((row, index) => {
            const inputs = Array.from(row.querySelectorAll('input, select'));
            const isRowStarted = inputs.some(i => i.value.trim() !== '');

            if (isRowStarted) {
                hasAnyService = true;
                // ALL row fields are required if row is started
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
            errorMsg.className = 'table-error text-red-500 text-sm mt-3 font-semibold text-center py-2 bg-red-50 rounded-lg animate-bounce';
            errorMsg.textContent = '❌ Debes agregar y completar al menos un servicio.';
            tableContainer.after(errorMsg);
        }

        if (!isValid) {
            e.preventDefault();
            const firstError = document.querySelector('.border-red-500');
            if (firstError) {
                firstError.focus();
                firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }
    });
});
