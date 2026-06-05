/**
 * ajax-solicitudes.js
 * Maneja la búsqueda y eliminación asíncrona en solicitudes.php
 */

document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('searchInput');
    const statusFilter = document.getElementById('statusFilter');
    const dateFilter = document.getElementById('dateFilter');
    const tableBody = document.querySelector('tbody');
    const rowCountDisplay = document.getElementById('rowCount');

    if (!searchInput || !tableBody) return;

    /**
     * Realiza la petición asíncrona enviando todos los filtros.
     */
    function filterAJAX() {
        const query = searchInput.value.trim();
        const status = statusFilter ? statusFilter.value : 'all';
        const date = dateFilter ? dateFilter.value : '';
        
        fetch(`../../negocio/SolicitudAjaxController.php?action=buscar&q=${encodeURIComponent(query)}&estado=${status}&fecha=${date}`)
            .then(res => res.json())
            .then(res => {
                if (res.success) {
                    actualizarTabla(res.data);
                }
            })
            .catch(err => console.error('Error en búsqueda AJAX:', err));
    }

    // 1. Listeners para Búsqueda y Filtros
    let timeout = null;
    searchInput.addEventListener('input', () => {
        clearTimeout(timeout);
        timeout = setTimeout(filterAJAX, 300);
    });

    if (statusFilter) statusFilter.addEventListener('change', filterAJAX);
    if (dateFilter) dateFilter.addEventListener('change', filterAJAX);

    /**
     * Reconstruye el DOM de la tabla dinámicamente con los resultados del servidor.
     */
    function actualizarTabla(items) {
        tableBody.innerHTML = '';
        
        if (items.length === 0) {
            tableBody.innerHTML = `<tr><td colspan="6" class="px-8 py-20 text-center text-gray-400 italic">No se encontraron resultados en el servidor.</td></tr>`;
            rowCountDisplay.textContent = '0';
            return;
        }

        items.forEach(s => {
            const tr = document.createElement('tr');
            tr.className = "hover:bg-emerald-50/30 transition-colors";
            tr.setAttribute('data-id', s.id);

            // Determinar color del badge por estado
            let color = 'bg-amber-100 text-amber-700 border-amber-200';
            let txt = 'En Revisión';
            if (s.estado === 'aprobado') {
                color = 'bg-emerald-100 text-emerald-700 border-emerald-200';
                txt = 'Aprobado';
            } else if (s.estado === 'rechazado') {
                color = 'bg-red-100 text-red-700 border-red-200';
                txt = 'Rechazado';
            }

            tr.innerHTML = `
                <td class="px-8 py-6 font-medium text-gray-900">#${s.id}</td>
                <td class="px-8 py-6 font-semibold text-brand-dark">${s.nombre}</td>
                <td class="px-8 py-6">
                    <span class="px-3 py-1 bg-brand-soft text-brand-main text-xs font-bold rounded-full uppercase border border-brand-light">
                        ${s.dependencia || '—'}
                    </span>
                </td>
                <td class="px-8 py-6 text-gray-600">${s.fecha}</td>
                <td class="px-8 py-6">
                    <span class="inline-flex items-center px-3 py-1 ${color} text-[10px] font-black rounded-full uppercase border shadow-sm">
                        ${txt}
                    </span>
                </td>
                <td class="px-8 py-6 text-right flex items-center justify-end gap-3">
                    <a href="detalle.php?id=${s.id}" class="inline-flex items-center text-brand-main font-bold hover:text-brand-hover group" title="Ver Detalles">
                        Detalles
                    </a>
                    ${s.estado === 'revision' ? `
                    <button onclick="eliminarSolicitud(${s.id})" class="text-red-300 hover:text-red-500 transition-colors p-2" title="Eliminar">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-4v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </button>` : ''}
                </td>
            `;
            tableBody.appendChild(tr);
        });

        rowCountDisplay.textContent = items.length;
    }

    // 2. Eliminación Asíncrona (AJAX + DOM Update)
    window.eliminarSolicitud = function(id) {
        if (!confirm('¿Estás seguro de eliminar esta solicitud de forma permanente?')) return;

        const formData = new FormData();
        formData.append('id', id);

        fetch('../../negocio/SolicitudAjaxController.php?action=eliminar', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(res => {
            if (res.success) {
                // Eliminar la fila del DOM sin recargar
                const row = document.querySelector(`tr[data-id="${id}"]`);
                if (row) {
                    row.classList.add('opacity-0', 'transition-opacity');
                    setTimeout(() => {
                        row.remove();
                        rowCountDisplay.textContent = parseInt(rowCountDisplay.textContent) - 1;
                    }, 300);
                }
            } else {
                alert('Error: ' + res.error);
            }
        })
        .catch(err => console.error('Error al eliminar:', err));
    };
});
