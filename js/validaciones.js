document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('solicitudForm');

    if (form) {
        form.addEventListener('submit', (e) => {
            let isValid = true;
            let messages = [];

            // Validar campos generales
            const nombre = document.getElementById('nombre_solicitante');
            const dependencia = document.getElementById('dependencia');
            const fecha = document.getElementById('fecha_solicitud');

            if (!nombre.value.trim()) {
                isValid = false;
                messages.push('El nombre del solicitante es obligatorio.');
                nombre.classList.add('border-red-500');
            } else {
                nombre.classList.remove('border-red-500');
            }

            if (!dependencia.value.trim()) {
                isValid = false;
                messages.push('La dependencia es obligatoria.');
                dependencia.classList.add('border-red-500');
            } else {
                dependencia.classList.remove('border-red-500');
            }

            if (!fecha.value) {
                isValid = false;
                messages.push('La fecha de solicitud es obligatoria.');
                fecha.classList.add('border-red-500');
            } else {
                fecha.classList.remove('border-red-500');
            }

            // Validar que haya al menos una fila de servicios
            const servicios = document.getElementsByName('servicios[]');
            if (servicios.length === 0) {
                isValid = false;
                messages.push('Debe agregar al menos un servicio.');
            } else {
                let hasService = false;
                for (let s of servicios) {
                    if (s.value.trim()) {
                        hasService = true;
                        break;
                    }
                }
                if (!hasService) {
                    isValid = false;
                    messages.push('Debe completar al menos un servicio.');
                }
            }

            if (!isValid) {
                e.preventDefault();
                alert('Por favor, corrija los siguientes errores:\n- ' + messages.join('\n- '));
            }
        });
    }
});
