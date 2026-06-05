/**
 * SolicitudManager
 * Handles the dynamic rows and automated data filling for the Request Form.
 */
class SolicitudManager {
    constructor() {
        this.CC_DATA = {
            "": "",
            "ACTIVIDADES CULTURALES Y CIVICAS": "74",
            "ACTIVIDADES DE FOMENTO A LA SALUD": "366",
            "APOYO ADMINISTRATIVO": "105",
            "MANTENIMIENTO GENERAL": "210"
        };

        this.RUBRO_DATA = {
            "": "",
            "ACTIVIDADES CULTURALES, DE BIENESTAR Y RECREACION": "519514",
            "ACUEDUCTO Y ALCANTARILLADO": "513504",
            "PAPELERIA Y UTILES DE ESCRITORIO": "512001",
            "ASEO Y CAFETERIA": "513502"
        };

        this.FUNCION_DATA = {
            "": "",
            "CONTRIBUCIONES Y AFILIACIONES": "505",
            "ACTIVIDADES DE BIENESTAR": "510",
            "MANTENIMIENTO Y REPARACION": "210",
            "HONORARIOS": "201",
            "COMPRA DE EQUIPO": "601",
            "SUMINISTROS Y MATERIALES": "501"
        };

        this.tbody = document.getElementById("items");
        this.inputClass = "w-full px-2 py-2 rounded-xl bg-gray-50 border border-gray-200 focus:border-brand-main focus:ring-1 focus:ring-brand-soft outline-none focus:bg-white text-sm transition-all";
        
        // Función helper para auto-ajustar altura
        this.autoResize = (e) => {
            const el = e.target;
            el.style.height = 'auto';
            el.style.height = (el.scrollHeight) + 'px';
        };
    }

    /**
     * Initializes the manager and adds the first row.
     */
    init() {
        if (!this.tbody) return;
        // La inicialización de catálogos ahora la maneja ajax-solicitud.js
        // para mayor eficiencia con grandes volúmenes de datos.
    }

    /**
     * Agrega una nueva fila lateral condensada (8 columnas).
     * @param {Object} data - Datos para pre-poblar.
     */
    addRow(data = {}) {
        const tr = document.createElement("tr");
        tr.className = "hover:bg-gray-50/10 transition-colors";

        tr.innerHTML = `
            <!-- 1. Servicio / Descripción -->
            <td class="px-2 py-4 border-r border-gray-100 align-top">
                <textarea name="servicio[]" placeholder="Servicio..." class="${this.inputClass} min-h-[42px] resize-none overflow-hidden" required>${data.servicio || ''}</textarea>
            </td>

            <!-- 2. Cantidad -->
            <td class="px-1 py-4 border-r border-gray-100 text-center align-top">
                <input type="number" name="cantidad[]" value="${data.cantidad || '1'}" class="${this.inputClass} text-center font-bold" min="1" required>
            </td>
            
            <!-- 3. Centro de Costos + Código -->
            <td class="px-2 py-4 border-r border-gray-100 align-top">
                <select name="centro_costo_id[]" class="select-cc ${this.inputClass} text-[10px]" data-selected="${data.centro_costo_id || ''}" required>
                    <option value="">Buscar CC...</option>
                </select>
                <div class="mt-1 flex items-center justify-between px-1 bg-gray-50/50 rounded border border-gray-100/50">
                    <span class="text-[9px] text-gray-400 font-bold uppercase">CÓD:</span>
                    <input type="text" name="cc_codigo[]" class="codigo-cc w-20 text-right bg-transparent font-mono text-[10px] text-primary font-bold outline-none" value="${data.cc_codigo || ''}" readonly>
                </div>
                <input type="hidden" name="centro_costos[]" class="cc-nombre-hidden" value="${data.centro_costos || ''}">
            </td>

            <!-- 4. Rubro + Código -->
            <td class="px-2 py-4 border-r border-gray-100 align-top">
                <select name="rubro_id[]" class="select-rubro ${this.inputClass} text-[10px]" data-selected="${data.rubro_id || ''}" required>
                    <option value="">Buscar Rubro...</option>
                </select>
                <div class="mt-1 flex items-center justify-between px-1 bg-gray-50/50 rounded border border-gray-100/50">
                    <span class="text-[9px] text-gray-400 font-bold uppercase">CÓD:</span>
                    <input type="text" name="rubro_codigo[]" class="codigo-rubro w-20 text-right bg-transparent font-mono text-[10px] text-primary font-bold outline-none" value="${data.rubro_codigo || ''}" readonly>
                </div>
                <input type="hidden" name="rubro[]" class="rubro-nombre-hidden" value="${data.rubro || ''}">
            </td>

            <!-- 5. Disponibilidad -->
            <td class="px-2 py-4 border-r border-gray-100 align-top">
                <input type="number" step="0.01" name="disponibilidad[]" placeholder="0.00" class="${this.inputClass} text-right font-mono text-[11px]" value="${data.disponibilidad || ''}">
            </td>

            <!-- 6. Fondo -->
            <td class="px-2 py-4 border-r border-gray-100 align-top">
                <input type="text" name="fondo[]" placeholder="Ordinario" class="${this.inputClass} text-[10px]" value="${data.fondo || ''}" required>
            </td>

            <!-- 7. Función + Código -->
            <td class="px-2 py-4 border-r border-gray-100 align-top">
                <select name="funcion_id[]" class="select-funcion ${this.inputClass} text-[10px]" data-selected="${data.funcion_id || ''}" required>
                    <option value="">Función...</option>
                </select>
                <div class="mt-1 flex items-center justify-between px-1 bg-gray-50/50 rounded border border-gray-100/50">
                    <span class="text-[9px] text-gray-400 font-bold uppercase">CÓD:</span>
                    <input type="text" name="funcion_codigo[]" class="codigo-funcion w-20 text-right bg-transparent font-mono text-[10px] text-primary font-bold outline-none" value="${data.funcion_codigo || ''}" readonly>
                </div>
                <input type="hidden" name="funcion_nombre[]" class="funcion-nombre-hidden" value="${data.funcion || ''}">
            </td>
            
            <!-- 8. Acción -->
            <td class="px-1 py-4 text-center align-top">
                <div class="flex items-center justify-center gap-1">
                    <button type="button" class="btnLimpiarFila text-gray-300 hover:text-amber-500 transition-all" title="Limpiar fila">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </button>
                    <button type="button" class="btnEliminarFila text-gray-300 hover:text-red-500 transition-all" title="Eliminar fila">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-4v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </button>
                </div>
            </td>
        `;

        // Acción: Limpiar Fila
        tr.querySelector('.btnLimpiarFila').onclick = () => {
            tr.querySelectorAll('select').forEach(select => {
                if (select.tomselect) select.tomselect.clear(true);
            });
            tr.querySelectorAll('input:not([type="hidden"]), textarea').forEach(input => {
                input.value = input.name === 'cantidad[]' ? '1' : '';
            });
            tr.querySelectorAll('input[type="hidden"]').forEach(h => h.value = '');
        };

        // Acción: Eliminar Fila
        tr.querySelector('.btnEliminarFila').onclick = () => {
            if (this.tbody.rows.length > 1) {
                tr.remove();
            } else {
                alert("Debe haber al menos una fila.");
            }
        };

        this.tbody.appendChild(tr);
        
        // Vincular auto-ajuste al textarea de esta fila
        const txtArea = tr.querySelector('textarea[name="servicio[]"]');
        if (txtArea) {
            txtArea.oninput = this.autoResize;
            setTimeout(() => {
                txtArea.style.height = 'auto';
                txtArea.style.height = txtArea.scrollHeight + 'px';
            }, 0);
        }

        if (typeof window.llenarCatalogosEnFilas === 'function') {
            window.llenarCatalogosEnFilas(tr);
        }
    }
}

// Instancia global del Manager
window.solicitudManager = new SolicitudManager();

// Wrapper global para el botón "Añadir Fila" del HTML
window.agregarFila = function(data = {}, esFilaPrincipal = false) {
    window.solicitudManager.addRow(data, esFilaPrincipal);
};

// Inicialización basada en Edición
document.addEventListener('DOMContentLoaded', () => {
    const itemsEdit = window.ITEMS_EDIT || [];
    const isEdit = window.IS_EDIT || false;

    if (isEdit && itemsEdit.length > 0) {
        itemsEdit.forEach((item, index) => {
            window.agregarFila(item, index === 0);
        });
    } else {
        // En creación o si no hay items, agregamos la fila principal vacía
        window.agregarFila({}, true);
    }

    // Vincular el botón "Añadir Fila" del HTML
    const btnAgregar = document.getElementById('btnAgregarFila');
    if (btnAgregar) {
        btnAgregar.addEventListener('click', () => {
            window.agregarFila({}, false);
        });
    }
});
