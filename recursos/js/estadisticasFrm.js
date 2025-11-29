// ==========================================
// ARCHIVO: recursos/js/estadisticasFrm.js
// ==========================================

// 1. VARIABLES GLOBALES
let allEstadisticas = []; // Almacena los datos actuales de la tabla
let allCategorias = [];   // Almacena la lista de categorías para los filtros y el reporte

// Referencias a elementos del DOM (Interfaz)
const menuToggle = document.getElementById('menuToggle');
const sidebar = document.getElementById('sidebar');
const overlay = document.getElementById('overlay');
const profileToggle = document.getElementById('profileToggle');
const profileDropdown = document.getElementById('profileDropdown');

// Referencias a elementos de la Vista de Estadísticas
const tablaEstadisticasPlantilla = document.getElementById('tablaEstadisticasPlantilla');
const totalJugadoresStatsSpan = document.getElementById('totalJugadoresStats');
const filterCategoriaSelect = document.getElementById('filterCategoria');
const sortOrderSelect = document.getElementById('sortOrder');
const btnDescargarInforme = document.getElementById('btnDescargarInforme');

// ==========================================
// 2. LÓGICA DE INTERFAZ (SIDEBAR Y MENÚ)
// ==========================================

function toggleMenu() {
    if (sidebar) sidebar.classList.toggle('-translate-x-full');
    if (overlay) overlay.classList.toggle('hidden');
}

function toggleProfile() {
    if (profileDropdown) profileDropdown.classList.toggle('hidden');
}

// Event Listeners para el menú
if (menuToggle) menuToggle.addEventListener('click', toggleMenu);
if (overlay) overlay.addEventListener('click', toggleMenu);
if (profileToggle) profileToggle.addEventListener('click', toggleProfile);

// Cerrar dropdown si se hace clic fuera
document.addEventListener('click', (e) => {
    if (profileToggle && profileDropdown && !profileToggle.contains(e.target) && !profileDropdown.contains(e.target)) {
        profileDropdown.classList.add('hidden');
    }
});

// Cerrar sidebar en móvil al hacer clic en un enlace
if (sidebar) { 
    const menuItems = sidebar.querySelectorAll('a');
    menuItems.forEach(item => {
        item.addEventListener('click', () => {
            if (window.innerWidth < 1024) { 
                 sidebar.classList.add('-translate-x-full');
                 overlay.classList.add('hidden');
            }
        });
    });
}

// Función para mostrar notificaciones tipo Toast
function mostrarNotificacion(mensaje, tipo = 'success') {
    const notification = document.createElement('div');
    // Verde para éxito, Rojo para error
    const colorClass = tipo === 'success' ? 'bg-green-600' : 'bg-red-600';
    
    notification.className = `fixed top-4 right-4 p-4 rounded-lg shadow-lg z-50 text-white transition-all transform duration-300 ${colorClass}`;
    notification.innerHTML = `<div class="flex items-center"><span class="font-bold mr-2">${tipo === 'success' ? '✓' : '✕'}</span> ${mensaje}</div>`;
    
    document.body.appendChild(notification);
    
    // Desaparecer automáticamente
    setTimeout(() => {
        notification.classList.add('opacity-0', 'translate-x-full');
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

// ==========================================
// 3. LÓGICA DE DATOS (API Y TABLA)
// ==========================================

/**
 * Renderiza (dibuja) la tabla de estadísticas en el HTML
 */
function renderizarTablaEstadisticas(estadisticasToRender) {
    if (!tablaEstadisticasPlantilla) return;
    tablaEstadisticasPlantilla.innerHTML = ''; // Limpiar tabla

    if (estadisticasToRender.length === 0) {
        tablaEstadisticasPlantilla.innerHTML = `
            <tr>
                <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                    <div class="flex flex-col items-center justify-center">
                        <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                        <p class="text-lg font-medium">No se encontraron datos.</p>
                    </div>
                </td>
            </tr>
        `;
        if (totalJugadoresStatsSpan) totalJugadoresStatsSpan.textContent = 0;
        return;
    }

    estadisticasToRender.forEach(stat => {
        // Iniciales para el avatar
        const iniciales = (stat.nombre.charAt(0) + stat.apellido.charAt(0)).toUpperCase();
        
        const row = `
            <tr class="hover:bg-gray-50 transition-colors border-b border-gray-100">
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 h-10 w-10">
                            <div class="h-10 w-10 rounded-full bg-green-700 flex items-center justify-center text-white font-bold text-sm shadow-sm">
                                ${iniciales}
                            </div>
                        </div>
                        <div class="ml-4">
                            <div class="text-sm font-medium text-gray-900">${stat.nombre} ${stat.apellido}</div>
                            <div class="text-xs text-gray-500">
                                <span class="font-semibold">#${stat.dorsal || '?'}</span> - ${stat.posicion || 'N/A'}
                            </div>
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                        ${stat.categoria_nombre || 'Sin Asignar'}
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-center">
                    <span class="text-sm font-bold text-gray-900">${stat.goles_totales}</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-600">
                    ${stat.partidos_jugados}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-600">
                    ${stat.minutos_jugados}'
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-center">
                    <span class="inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-yellow-800 bg-yellow-100 rounded">
                        ${stat.tarjetas_amarillas}
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-center">
                    <span class="inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-red-800 bg-red-100 rounded">
                        ${stat.tarjetas_rojas}
                    </span>
                </td>
            </tr>
        `;
        tablaEstadisticasPlantilla.innerHTML += row;
    });
    
    // Actualizar contador
    if (totalJugadoresStatsSpan) totalJugadoresStatsSpan.textContent = estadisticasToRender.length;
}

/**
 * Carga las categorías desde la API para llenar el select de filtros y el SweetAlert
 */
async function obtenerCategoriasParaFiltro() {
    try {
        const response = await fetch('../api/categoriaAPI.php');
        if (!response.ok) throw new Error(`Error HTTP: ${response.status}`);
        
        const data = await response.json();
        allCategorias = data; // Guardamos en variable global

        // Llenar el select de la interfaz principal
        if (filterCategoriaSelect) {
            filterCategoriaSelect.innerHTML = '<option value="">Todas las categorías</option>';
            allCategorias.forEach(categoria => {
                const option = document.createElement('option');
                option.value = categoria.id_categoria;
                option.textContent = categoria.nombre;
                filterCategoriaSelect.appendChild(option);
            });
        }
    } catch (error) {
        console.error('Error al cargar categorías:', error);
        mostrarNotificacion('No se pudieron cargar las categorías.', 'error');
    }
}

/**
 * Carga las estadísticas desde la API
 */
async function obtenerEstadisticas(id_categoria = 0, ordenar_por = '') {
    let url = `../api/estadisticasAPI.php?id_categoria=${id_categoria}&ordenar_por=${ordenar_por}`;
    
    // Mostrar estado de carga
    if (tablaEstadisticasPlantilla) {
      tablaEstadisticasPlantilla.innerHTML = `<tr><td colspan="7" class="px-6 py-8 text-center text-gray-500 animate-pulse">Cargando datos...</td></tr>`;
    }

    try {
        const response = await fetch(url);
        if (!response.ok) throw new Error(`Error HTTP: ${response.status}`);
        
        const data = await response.json();
        allEstadisticas = data;
        renderizarTablaEstadisticas(allEstadisticas);
    } catch (error) {
        console.error('Error al cargar estadísticas:', error);
        mostrarNotificacion('Error al obtener estadísticas.', 'error');
        if (tablaEstadisticasPlantilla) {
            tablaEstadisticasPlantilla.innerHTML = `<tr><td colspan="7" class="px-6 py-8 text-center text-red-500">Error de conexión. Intente nuevamente.</td></tr>`;
        }
    }
}

// ==========================================
// 4. LÓGICA DE GENERACIÓN DE INFORME PDF
// ==========================================

if (btnDescargarInforme) {
    btnDescargarInforme.addEventListener('click', async () => {
        
        // 1. Preparar las opciones para la ventana emergente (SweetAlert)
        const inputOptions = {
            '0': 'Todas las categorías'
        };
        // Llenar con las categorías cargadas previamente
        allCategorias.forEach(cat => {
            inputOptions[cat.id_categoria] = cat.nombre;
        });

        // 2. Mostrar la ventana emergente
        const { value: idCategoriaSeleccionada } = await Swal.fire({
            title: 'Generar Informe PDF',
            text: 'Selecciona la categoría que deseas descargar:',
            icon: 'question',
            input: 'select',
            inputOptions: inputOptions,
            inputPlaceholder: 'Selecciona una opción',
            showCancelButton: true,
            confirmButtonText: 'Generar PDF',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#166534', // Verde oscuro (Tailwind green-800)
            cancelButtonColor: '#6B7280', // Gris
            inputValidator: (value) => {
                if (!value) {
                    return 'Debes seleccionar una opción';
                }
            }
        });

        // 3. Si el usuario seleccionó una categoría y dio "Generar"
        if (idCategoriaSeleccionada) {
            try {
                // Mostrar alerta de carga
                Swal.fire({
                    title: 'Generando informe...',
                    html: 'Por favor espere mientras procesamos los datos.',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                // Hacemos una petición nueva a la API para obtener datos frescos de ESA categoría
                const response = await fetch(`../api/estadisticasAPI.php?id_categoria=${idCategoriaSeleccionada}&ordenar_por=nombre_asc`);
                const datosReporte = await response.json();

                if (!datosReporte || datosReporte.length === 0) {
                    Swal.fire('Sin datos', 'No hay jugadores ni estadísticas registradas para la categoría seleccionada.', 'info');
                    return;
                }

                // Generar el archivo PDF
                generarPDF(datosReporte, idCategoriaSeleccionada);
                
                // Cerrar alerta de carga
                Swal.close();
                mostrarNotificacion('¡Informe descargado exitosamente!');

            } catch (error) {
                console.error(error);
                Swal.fire('Error', 'Ocurrió un error al generar el documento.', 'error');
            }
        }
    });
}

/**
 * Función que construye y descarga el PDF usando jsPDF
 */
function generarPDF(datos, idCategoria) {
    // Verificar que la librería esté cargada
    if (typeof window.jspdf === 'undefined') {
        alert("Error: Las librerías de PDF no se cargaron correctamente.");
        return;
    }

    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();

    // Determinar nombre de la categoría para el título
    let nombreCategoria = "General (Todas las Categorías)";
    if (idCategoria !== '0') {
        const cat = allCategorias.find(c => c.id_categoria == idCategoria);
        if (cat) nombreCategoria = `Categoría ${cat.nombre}`;
    }

    // --- ENCABEZADO DEL PDF ---
    // Logo (Simulado con texto o cuadro si no tienes imagen base64 a mano)
    doc.setFillColor(22, 101, 52); // Verde
    doc.rect(0, 0, 210, 25, 'F'); // Barra superior verde
    
    doc.setFontSize(22);
    doc.setTextColor(255, 255, 255); // Blanco
    doc.text("Club Deportivo Momil", 14, 17);
    
    doc.setFontSize(16);
    doc.setTextColor(40, 40, 40); // Gris oscuro
    doc.text(`Informe de Rendimiento`, 14, 40);
    
    doc.setFontSize(12);
    doc.setTextColor(100, 100, 100); // Gris
    doc.text(`${nombreCategoria}`, 14, 48);
    
    doc.setFontSize(10);
    doc.text(`Fecha de emisión: ${new Date().toLocaleDateString()}`, 180, 40, { align: 'right' });

    // --- TABLA DE DATOS ---
    // Columnas
    const head = [['Jugador', 'Posición', 'Cat', 'Goles', 'Partidos', 'Minutos', 'TA', 'TR']];
    
    // Filas
    const body = datos.map(stat => [
        `${stat.nombre} ${stat.apellido} (#${stat.dorsal || '-'})`,
        stat.posicion || 'N/A',
        stat.categoria_nombre || 'N/A',
        stat.goles_totales,
        stat.partidos_jugados,
        stat.minutos_jugados,
        stat.tarjetas_amarillas,
        stat.tarjetas_rojas
    ]);

    // Generar tabla con AutoTable
    doc.autoTable({
        head: head,
        body: body,
        startY: 55,
        theme: 'grid',
        headStyles: { 
            fillColor: [22, 101, 52], // Verde encabezado
            textColor: 255, 
            fontStyle: 'bold',
            halign: 'center'
        },
        columnStyles: {
            0: { cellWidth: 50 }, // Jugador más ancho
            3: { halign: 'center', fontStyle: 'bold' }, // Goles centrados
            4: { halign: 'center' },
            5: { halign: 'center' },
            6: { halign: 'center', textColor: [200, 150, 0] }, // TA amarilla oscura
            7: { halign: 'center', textColor: [200, 0, 0] }    // TR roja
        },
        styles: { 
            fontSize: 9, 
            cellPadding: 3,
            valign: 'middle'
        },
        alternateRowStyles: { 
            fillColor: [240, 253, 244] // Verde muy claro para filas alternas
        }
    });

    // --- PIE DE PÁGINA ---
    const pageCount = doc.internal.getNumberOfPages();
    for (let i = 1; i <= pageCount; i++) {
        doc.setPage(i);
        doc.setFontSize(8);
        doc.setTextColor(150);
        doc.text('Sistema de Gestión Deportiva - C.D. Momil', 14, doc.internal.pageSize.height - 10);
        doc.text(`Página ${i} de ${pageCount}`, doc.internal.pageSize.width - 25, doc.internal.pageSize.height - 10);
    }

    // Descargar el archivo
    const nombreArchivo = `Estadisticas_${nombreCategoria.replace(/[^a-zA-Z0-9]/g, '_')}.pdf`;
    doc.save(nombreArchivo);
}

// ==========================================
// 5. EVENT LISTENERS Y INICIALIZACIÓN
// ==========================================

// Listener para cambio en el filtro de categoría (Visualización en tabla)
if (filterCategoriaSelect) {
    filterCategoriaSelect.addEventListener('change', () => {
        const selectedCategoryId = filterCategoriaSelect.value === '' ? 0 : parseInt(filterCategoriaSelect.value);
        const currentSortOrder = sortOrderSelect ? sortOrderSelect.value : '';
        obtenerEstadisticas(selectedCategoryId, currentSortOrder);
    });
}

// Listener para cambio en el ordenamiento
if (sortOrderSelect) {
    sortOrderSelect.addEventListener('change', () => {
        const currentCategoryId = filterCategoriaSelect ? (filterCategoriaSelect.value === '' ? 0 : parseInt(filterCategoriaSelect.value)) : 0;
        const selectedSortOrder = sortOrderSelect.value;
        obtenerEstadisticas(currentCategoryId, selectedSortOrder);
    });
}

// INICIO: Cargar datos al abrir la página
document.addEventListener('DOMContentLoaded', () => {
    // 1. Cargar categorías (para filtros y PDF)
    obtenerCategoriasParaFiltro().then(() => {
        // 2. Cargar estadísticas generales
        obtenerEstadisticas(0, '');
    });
});