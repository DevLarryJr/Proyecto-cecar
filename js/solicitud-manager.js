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

        this.tbody = document.getElementById("items");
        this.inputClass = "w-full px-2 py-2 rounded-xl bg-gray-50 border border-gray-200 focus:border-brand-main focus:ring-1 focus:ring-brand-soft outline-none focus:bg-white text-sm transition-all";
    }

    /**
     * Initializes the manager and adds the first row.
     */
    init() {
        if (!this.tbody) return;
        this.addInitialRow();
        this.setupEventListeners();
    }

    /**
     * Sets up event delegation for the table to avoid inline onchange handlers.
     */
    setupEventListeners() {
        this.tbody.addEventListener('change', (e) => {
            const target = e.target;
            const tr = target.closest('tr');
            if (!tr) return;

            if (target.name === 'cc_nombres[]') {
                const codeInput = tr.querySelector('[name="cc_codigos[]"]');
                if (codeInput) codeInput.value = this.CC_DATA[target.value] || '';
            }

            if (target.name === 'rubro_nombres[]') {
                const codeInput = tr.querySelector('[name="rubro_codigos[]"]');
                if (codeInput) codeInput.value = this.RUBRO_DATA[target.value] || '';
            }
        });
    }

    /**
     * Internal method to add a row.
     */
    addRow() {
        const tr = document.createElement("tr");
        tr.className = "hover:bg-gray-50/30 transition-colors group";

        const ccOptions = this._buildOptions(this.CC_DATA, 'Seleccione CC...');
        const rubroOptions = this._buildOptions(this.RUBRO_DATA, 'Seleccione Rubro...');

        tr.innerHTML = `
            <td class="px-3 py-2 border-r border-gray-100"><input type="text" name="servicios[]" placeholder="Servicio..." class="${this.inputClass}"></td>
            <td class="px-3 py-2 border-r border-gray-100"><input type="number" name="cantidades[]" placeholder="0" class="${this.inputClass} text-center"></td>
            
            <td class="px-3 py-2 border-r border-gray-100">
                <select name="cc_nombres[]" class="${this.inputClass}">${ccOptions}</select>
            </td>
            <td class="px-3 py-2 border-r border-gray-100"><input type="text" name="cc_codigos[]" placeholder="Cód. CC" class="${this.inputClass} text-center" readonly></td>
            
            <td class="px-3 py-2 border-r border-gray-100">
                <select name="rubro_nombres[]" class="${this.inputClass}">${rubroOptions}</select>
            </td>
            <td class="px-3 py-2 border-r border-gray-100"><input type="text" name="rubro_codigos[]" placeholder="Cód. Rubro" class="${this.inputClass} text-center" readonly></td>
            
            <td class="px-3 py-2 border-r border-gray-100"><input type="text" name="disponibilidades[]" placeholder="Disponibilidad" class="${this.inputClass} text-center"></td>
            <td class="px-3 py-2 border-r border-gray-100"><input type="text" name="fondos[]" placeholder="Fondo" class="${this.inputClass} text-center"></td>
            
            <td class="px-3 py-2 border-r border-gray-100"><input type="text" name="funcion_nombres[]" placeholder="Función..." class="${this.inputClass}"></td>
            <td class="px-3 py-2 border-r border-gray-100"><input type="text" name="funcion_codigos[]" placeholder="Cód. Func." class="${this.inputClass} text-center"></td>
            
            <td class="px-3 py-2 border-r border-gray-100 text-center w-16">
                <button type="button" class="btn-remove text-red-200 hover:text-red-400 p-2 rounded-xl transition-all">
                    <svg class="w-6 h-6 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-4v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </button>
            </td>
        `;

        // Event for remove button
        tr.querySelector('.btn-remove').onclick = () => tr.remove();

        this.tbody.appendChild(tr);
    }

    addInitialRow() {
        if (this.tbody && this.tbody.children.length === 0) {
            this.addRow();
        }
    }

    _buildOptions(data, defaultLabel) {
        return Object.keys(data).map(name =>
            `<option value="${name}">${name || defaultLabel}</option>`
        ).join('');
    }
}

// Global instance to bridge with HTML's onclick
window.solicitudManager = new SolicitudManager();

// Define exactly the function the HTML is looking for
window.agregarFila = function() {
    window.solicitudManager.addRow();
};

document.addEventListener('DOMContentLoaded', () => {
    window.solicitudManager.init();
});
