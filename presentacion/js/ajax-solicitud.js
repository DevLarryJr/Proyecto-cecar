document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('solicitudForm');
    const statusContainer = document.getElementById('status-container');
    const statusMessage = document.getElementById('status-message');
    const submitBtn = form.querySelector('button[type="submit"]');

    if (!form) return;

    form.addEventListener('submit', async (e) => {
        // Interceptamos el envío por defecto del formulario (recarga de la página).
        // Las validaciones de validaciones.js ocurren simultáneamente.
        e.preventDefault();

        // Limpiar estados previos y mostrar icono de carga en el botón
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
            // Utilizamos la API Fetch para enviar los datos al archivo procesador
            const response = await fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    // Cabecera clave para que el servidor (PHP) detecte que es una llamada asíncrona AJAX
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            // Convertimos la respuesta binaria a un objeto JSON usable
            const result = await response.json();

            if (result.success) {
                // ÉXITO
                statusContainer.className = 'mb-6 p-4 rounded-xl border border-emerald-100 bg-emerald-50 text-emerald-800 animate-fade-in';
                statusMessage.innerHTML = `
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        <strong>¡Éxito!</strong> ${result.message || 'Redirigiendo...'}
                    </div>`;

                // Redirigir al detalle después de un breve momento
                setTimeout(() => {
                    window.location.href = `detalle.php?id=${result.id}`;
                }, 1500);

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

/**
 * Módulo de Centros de Costo (Módulo 2)
 * Carga dinámica de centros desde SolicitudAjaxController.php
 */
document.addEventListener('DOMContentLoaded', function () {
    cargarCatalogos();
});

let catalogosGlobal = {
    centrosCosto: [],
    funciones: [],
    rubros: []
};

async function obtenerCatalogo(action) {
    const respuesta = await fetch(`../../negocio/SolicitudAjaxController.php?action=${action}`);
    const resultado = await respuesta.json();
    return resultado.success ? resultado.data : [];
}

/**
 * Inicializa TomSelect de forma eficiente usando datos directos.
 */
function initTomSelect(element, datos = [], settings = {}) {
    if (!element || element.tomselect) return;

    // Aseguramos que datos sea un array y limpiamos posibles nulos
    const listaLimpia = (Array.isArray(datos) ? datos : []).filter(item => item && typeof item === 'object');

    const config = {
        options: listaLimpia,
        valueField: 'id',
        labelField: 'nombre',
        searchField: ['nombre', 'codigo'],
        placeholder: "Buscar...",
        allowEmptyOption: true,
        maxOptions: 2000,
        maxItems: 1,
        dropdownParent: 'body',
        render: {
            option: function(item, escape) {
                const cod = item.codigo ? escape(item.codigo) : '---';
                const nom = item.nombre ? escape(item.nombre) : 'Sin nombre';
                return `<div><span class="font-bold">${cod}</span> - ${nom}</div>`;
            },
            item: function(item, escape) {
                const cod = item.codigo ? escape(item.codigo) : '---';
                const nom = item.nombre ? escape(item.nombre) : 'Sin nombre';
                return `<div class="break-words whitespace-normal text-[10px] leading-tight"><span class="font-bold text-primary">${cod}</span> - ${nom}</div>`;
            }
        },
        onChange: function() {
            element.dispatchEvent(new Event('change', { bubbles: true }));
        }
    };

    try {
        const mergedConfig = Object.assign({}, config, settings);
        return new TomSelect(element, mergedConfig);
    } catch (err) {
        console.warn('[TomSelect Web] Error al instanciar:', err.message);
        return null;
    }
}

function llenarSelect(select, datos, textoInicial, tsSettings = {}) {
    if (!select) return;
    
    // Si ya existe una instancia de TomSelect, la destruimos
    if (select.tomselect) {
        select.tomselect.destroy();
    }

    // Limpiar el select y poner la opción por defecto
    select.innerHTML = `<option value="">${textoInicial}</option>`;

    // Usar un pequeño retardo para evitar conflictos de renderizado
    setTimeout(() => {
        const ts = initTomSelect(select, datos, tsSettings);
        
        if (ts) {
            const valorGuardado = select.dataset.selected || select.value;
            if (valorGuardado && valorGuardado !== "undefined") {
                ts.setValue(String(valorGuardado));
            }
        }
    }, 10);
}

async function cargarCatalogos() {
    try {
        console.log('[Catalogo] Descargando datos...');
        
        catalogosGlobal.centrosCosto = await obtenerCatalogo('centros_costo');
        console.log(`[Catalogo] CC cargados: ${catalogosGlobal.centrosCosto.length}`);

        catalogosGlobal.funciones = await obtenerCatalogo('funciones');
        console.log(`[Catalogo] Funciones cargadas: ${catalogosGlobal.funciones.length}`);

        catalogosGlobal.rubros = await obtenerCatalogo('rubros');
        console.log(`[Catalogo] Rubros cargados: ${catalogosGlobal.rubros.length}`);

        llenarCatalogosEnFilas();
        console.log('[Catalogo] Proceso completado.');

    } catch (error) {
        console.error('[Catalogo] Error crítico cargando catálogos:', error);
    }
}

function llenarCatalogosEnFilas(container = document) {
    // Solo procesar si el contenedor existe
    if (!container) return;

    // Llenar catálogos usando el contenedor para mayor eficiencia
    container.querySelectorAll('.select-cc').forEach(select => {
        llenarSelect(select, catalogosGlobal.centrosCosto, 'Seleccione...');
    });

    container.querySelectorAll('.select-rubro').forEach(select => {
        llenarSelect(select, catalogosGlobal.rubros, 'Seleccione...');
    });

    container.querySelectorAll('.select-funcion').forEach(select => {
        llenarSelect(select, catalogosGlobal.funciones, 'Seleccione...');
    });
}

window.llenarCatalogosEnFilas = llenarCatalogosEnFilas;

document.addEventListener('change', function (e) {
    const target = e.target;
    const fila = target.closest('tr');
    if (!fila) return;

    const idValue = target.value;
    let itemData = null;

    // 1. Sincronización: Centro de Costos
    if (target.classList.contains('select-cc')) {
        itemData = catalogosGlobal.centrosCosto.find(i => String(i.id) === String(idValue));
        const codigoInput = fila.querySelector('.codigo-cc');
        const hiddenNombre = fila.querySelector('.cc-nombre-hidden');
        if (codigoInput) codigoInput.value = itemData ? (itemData.codigo || '') : '';
        if (hiddenNombre) hiddenNombre.value = itemData ? (itemData.nombre || '') : '';
    }

    // 2. Sincronización: Función
    if (target.classList.contains('select-funcion')) {
        itemData = catalogosGlobal.funciones.find(i => String(i.id) === String(idValue));
        const codigoInput = fila.querySelector('.codigo-funcion');
        const hiddenNombre = fila.querySelector('.funcion-nombre-hidden');
        if (codigoInput) codigoInput.value = itemData ? (itemData.codigo || '') : '';
        if (hiddenNombre) hiddenNombre.value = itemData ? (itemData.nombre || '') : '';
    }

    // 3. Sincronización: Rubro
    if (target.classList.contains('select-rubro')) {
        itemData = catalogosGlobal.rubros.find(i => String(i.id) === String(idValue));
        const codigoRubro = fila.querySelector('.codigo-rubro');
        const hiddenNombre = fila.querySelector('.rubro-nombre-hidden');
        if (codigoRubro) codigoRubro.value = itemData ? (itemData.codigo || '') : '';
        if (hiddenNombre) hiddenNombre.value = itemData ? (itemData.nombre || '') : '';
    }
});
