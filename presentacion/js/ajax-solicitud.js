/**
 * ajax-solicitud.js — Capa de Comportamiento (Frontend)
 * 
 * Gestiona el envío asíncrono del formulario de solicitud y la orquestación 
 * de los componentes de búsqueda avanzada (TomSelect).
 */

document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('solicitudForm');
    const statusContainer = document.getElementById('status-container');
    const statusMessage = document.getElementById('status-message');
    const submitBtn = form.querySelector('button[type="submit"]');

    if (!form) return;

    /**
     * INTERCEPTOR DE FORMULARIO
     * Envía los datos vía Fetch API para evitar recargas de página.
     */
    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        // 1. Reset visual de estados y feedback de carga
        statusContainer.classList.add('hidden');
        submitBtn.disabled = true;
        submitBtn.innerHTML = `
            <svg style="width: 0.9em; height: 0.9em;" class="animate-spin -ml-1 mr-3 text-white inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Procesando...`;

        const formData = new FormData(form);

        try {
            // Envío al controlador en el servidor
            const response = await fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });

            const result = await response.json();

            if (result.success) {
                // FEEDBACK POSITIVO
                statusContainer.className = 'mb-6 p-4 rounded-xl border border-emerald-100 bg-emerald-50 text-emerald-800 animate-fade-in';
                statusMessage.innerHTML = `
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        <strong>¡Éxito!</strong> ${result.message || 'Redirigiendo...'}
                    </div>`;

                // Navegación automática al detalle del nuevo registro
                setTimeout(() => {
                    window.location.href = `detalle.php?id=${result.id}`;
                }, 1500);

            } else {
                // MANEJO DE ERRORES (Ej. Validación de campos obligatorios en backend)
                statusContainer.className = "mb-6 p-4 rounded-xl border border-red-200 bg-red-50 text-red-700 block animate-fade-in";
                statusMessage.innerHTML = "<strong>Error en la solicitud:</strong><br>" + (result.errors ? result.errors.join('<br>') : 'Error desconocido');
                submitBtn.disabled = false;
                submitBtn.textContent = window.IS_EDIT ? 'Actualizar Solicitud' : 'Enviar Solicitud';
            }

        } catch (error) {
            console.error('[AJAX Fatal Error]', error);
            statusContainer.className = "mb-6 p-4 rounded-xl border border-red-200 bg-red-50 text-red-700 block animate-fade-in";
            statusMessage.textContent = "Error de red: No se pudo contactar con el servidor institucional.";
            submitBtn.disabled = false;
            submitBtn.textContent = window.IS_EDIT ? 'Actualizar Solicitud' : 'Enviar Solicitud';
        }
    });
});

/** 
 * GESTIÓN DE CATÁLOGOS DINÁMICOS
 * Almacena los Centros de Costo, Funciones y Rubros en memoria para búsquedas instantáneas.
 */
let catalogosGlobal = { centrosCosto: [], funciones: [], rubros: [] };

document.addEventListener('DOMContentLoaded', cargarCatalogos);

/** Consume los datos desde el SolicitudAjaxController */
async function obtenerCatalogo(action) {
    const respuesta = await fetch(`../../negocio/SolicitudAjaxController.php?action=${action}`);
    const resultado = await respuesta.json();
    return resultado.success ? resultado.data : [];
}

/** Descarga todos los catálogos necesarios para la sesión actual */
async function cargarCatalogos() {
    try {
        catalogosGlobal.centrosCosto = await obtenerCatalogo('centros_costo');
        catalogosGlobal.funciones = await obtenerCatalogo('funciones');
        catalogosGlobal.rubros = await obtenerCatalogo('rubros');
        
        // Inyectar datos en las filas que ya existen (Modo Edición)
        llenarCatalogosEnFilas();
    } catch (error) {
        console.error('[Catalogo] Error al precargar datos maestros:', error);
    }
}

/**
 * MOTOR DE BÚSQUEDA AVANZADA (TomSelect)
 * Convierte un <select> estándar en un buscador potente con autocompletado por nombre y código.
 */
function initTomSelect(element, datos = [], settings = {}) {
    if (!element || element.tomselect) return;

    const listaLimpia = (Array.isArray(datos) ? datos : []).filter(item => item && typeof item === 'object');

    const config = {
        options: listaLimpia,
        valueField: 'id',
        labelField: 'nombre',
        searchField: ['nombre', 'codigo'],
        placeholder: "Escriba código o nombre...",
        allowEmptyOption: true,
        maxOptions: 2000,
        maxItems: 1,
        dropdownParent: 'body',
        render: {
            option: (item, esc) => `<div><span class="font-bold text-primary">${esc(item.codigo || '---')}</span> - ${esc(item.nombre)}</div>`,
            item: (item, esc) => `<div class="text-[10px]"><span class="font-bold text-primary">${esc(item.codigo || '---')}</span> - ${esc(item.nombre)}</div>`
        },
        onChange: () => element.dispatchEvent(new Event('change', { bubbles: true }))
    };

    try { return new TomSelect(element, Object.assign({}, config, settings)); } 
    catch (err) { return null; }
}

/** Organiza la inserción de datos en los selectores inteligentes */
function llenarSelect(select, datos, textoInicial) {
    if (!select) return;
    if (select.tomselect) select.tomselect.destroy();

    select.innerHTML = `<option value="">${textoInicial}</option>`;
    setTimeout(() => {
        const ts = initTomSelect(select, datos);
        if (ts) {
            const valor = select.dataset.selected || select.value;
            if (valor) ts.setValue(String(valor));
        }
    }, 10);
}

/** Itera por todas las filas de la tabla de servicios para inicializar sus buscadores */
function llenarCatalogosEnFilas(container = document) {
    if (!container) return;
    container.querySelectorAll('.select-cc').forEach(s => llenarSelect(s, catalogosGlobal.centrosCosto, 'Seleccione CC...'));
    container.querySelectorAll('.select-rubro').forEach(s => llenarSelect(s, catalogosGlobal.rubros, 'Seleccione Rubro...'));
    container.querySelectorAll('.select-funcion').forEach(s => llenarSelect(s, catalogosGlobal.funciones, 'Seleccione Función...'));
}

window.llenarCatalogosEnFilas = llenarCatalogosEnFilas;

/**
 * SINCRONIZACIÓN REACTIVA
 * Escucha cambios en los selectores para autocompletar campos de CÓDIGO y NOMBRES ocultos.
 */
document.addEventListener('change', function (e) {
    const target = e.target;
    const fila = target.closest('tr');
    if (!fila) return;

    const val = target.value;
    let item = null;

    // Sincronizar Centro de Costos
    if (target.classList.contains('select-cc')) {
        item = catalogosGlobal.centrosCosto.find(i => String(i.id) === String(val));
        fila.querySelector('.codigo-cc').value = item ? (item.codigo || '') : '';
        fila.querySelector('.cc-nombre-hidden').value = item ? (item.nombre || '') : '';
    }
    // Sincronizar Función
    if (target.classList.contains('select-funcion')) {
        item = catalogosGlobal.funciones.find(i => String(i.id) === String(val));
        fila.querySelector('.codigo-funcion').value = item ? (item.codigo || '') : '';
        fila.querySelector('.funcion-nombre-hidden').value = item ? (item.nombre || '') : '';
    }
    // Sincronizar Rubro
    if (target.classList.contains('select-rubro')) {
        item = catalogosGlobal.rubros.find(i => String(i.id) === String(val));
        fila.querySelector('.codigo-rubro').value = item ? (item.codigo || '') : '';
        fila.querySelector('.rubro-nombre-hidden').value = item ? (item.nombre || '') : '';
    }
});
