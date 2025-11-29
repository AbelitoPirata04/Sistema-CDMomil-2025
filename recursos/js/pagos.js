// ==========================================
// ARCHIVO: recursos/js/pagos.js (VERSIÓN FINAL)
// ==========================================

console.log("Cargando script de pagos...");

const API_PAGOS = '../api/pagoAPI.php';
const API_JUGADORES = '../api/jugadorAPI.php';
const API_CATEGORIAS = '../api/categoriaAPI.php';

let allPagos = [];
let allJugadores = [];
let allCategorias = [];

// Referencias DOM
const modalPago = document.getElementById('modalPago');
const formPago = document.getElementById('formPago');
const modalTitle = document.getElementById('modalTitle');
const tablaPagos = document.getElementById('tablaPagos');

// Botones
const btnNuevoPago = document.getElementById('btnNuevoPago');
const btnCancelar = document.getElementById('btnCancelar');
const btnCerrarX = document.getElementById('btnCerrarX');
const btnGenerarInformePagos = document.getElementById('btnGenerarInformePagos');

// Inputs Modal
const selectFiltroCategoria = document.getElementById('filtroCategoriaModal');
const selectJugador = document.getElementById('idJugador');
const inputIdPago = document.getElementById('idPago');

// --- CARGA INICIAL ---
document.addEventListener('DOMContentLoaded', () => {
    console.log("DOM Cargado. Iniciando peticiones...");
    inicializarDatos();
    configurarEventos();
});

async function inicializarDatos() {
    try {
        // Cargar datos en paralelo
        await Promise.all([cargarCategorias(), cargarJugadores(), cargarPagos()]);
        console.log("Todos los datos cargados correctamente.");
    } catch (error) {
        console.error("Error crítico inicializando:", error);
        Swal.fire("Error", "No se pudieron cargar los datos del sistema.", "error");
    }
}

// --- API FUNCTIONS ---

async function cargarCategorias() {
    try {
        const res = await fetch(API_CATEGORIAS);
        const data = await res.json();
        allCategorias = data;
        
        // Llenar el select del filtro en el modal
        if (selectFiltroCategoria) {
            selectFiltroCategoria.innerHTML = '<option value="">Todas las categorías</option>';
            allCategorias.forEach(cat => {
                const opt = document.createElement('option');
                opt.value = cat.id_categoria;
                opt.textContent = cat.nombre;
                selectFiltroCategoria.appendChild(opt);
            });
        }
    } catch (e) {
        console.error("Error cargando categorías:", e);
    }
}

async function cargarJugadores() {
    try {
        const res = await fetch(API_JUGADORES);
        const data = await res.json();
        allJugadores = data;
        renderizarOpcionesJugadores(allJugadores);
    } catch (e) {
        console.error("Error cargando jugadores:", e);
    }
}

async function cargarPagos() {
    try {
        const res = await fetch(API_PAGOS);
        const data = await res.json();
        allPagos = data;
        renderizarTablaPagos(allPagos);
    } catch (e) {
        console.error("Error cargando pagos:", e);
        tablaPagos.innerHTML = '<tr><td colspan="6" class="p-4 text-center text-red-500">Error cargando datos</td></tr>';
    }
}

// --- RENDERIZADO ---

function renderizarTablaPagos(pagos) {
    tablaPagos.innerHTML = '';

    if (!pagos || pagos.length === 0) {
        tablaPagos.innerHTML = '<tr><td colspan="6" class="p-8 text-center text-gray-500">No hay pagos registrados.</td></tr>';
        return;
    }

    pagos.forEach(pago => {
        const tr = document.createElement('tr');
        tr.className = 'hover:bg-gray-50 border-b border-gray-100 transition-colors';

        // Formato de moneda y estado
        const monto = parseFloat(pago.monto).toLocaleString('es-CO', { style: 'currency', currency: 'COP', minimumFractionDigits: 0 });
        const estadoClass = pago.estado === 'Pagado' 
            ? 'bg-green-100 text-green-800' 
            : 'bg-yellow-100 text-yellow-800';

        // Crear celdas
        tr.innerHTML = `
            <td class="px-6 py-4">
                <div class="text-sm font-bold text-gray-900">${pago.jugador_nombre || 'Desconocido'} ${pago.jugador_apellido || ''}</div>
                <div class="text-xs text-gray-500">${pago.categoria_nombre || 'Sin categoría'}</div>
            </td>
            <td class="px-6 py-4 text-sm font-mono text-gray-900">${monto}</td>
            <td class="px-6 py-4 text-sm text-gray-700">${pago.mesPagado}</td>
            <td class="px-6 py-4 text-sm text-gray-500">${pago.fechaPago}</td>
            <td class="px-6 py-4">
                <span class="px-2 py-1 text-xs font-semibold rounded-full ${estadoClass}">${pago.estado}</span>
            </td>
            <td class="px-6 py-4 text-center">
                <div class="flex justify-center items-center space-x-2">
                    <button class="btn-editar bg-blue-100 hover:bg-blue-200 text-blue-700 p-2 rounded-lg transition" title="Editar">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                    </button>
                    <button class="btn-eliminar bg-red-100 hover:bg-red-200 text-red-700 p-2 rounded-lg transition" title="Eliminar">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </button>
                </div>
            </td>
        `;

        // Asignar eventos a los botones directamente
        tr.querySelector('.btn-editar').addEventListener('click', () => abrirModal('editar', pago));
        tr.querySelector('.btn-eliminar').addEventListener('click', () => confirmarEliminacion(pago.idPago));

        tablaPagos.appendChild(tr);
    });
}

function renderizarOpcionesJugadores(lista) {
    selectJugador.innerHTML = '<option value="">Seleccionar jugador...</option>';
    lista.forEach(j => {
        const opt = document.createElement('option');
        opt.value = j.id_jugador;
        opt.textContent = `${j.nombre} ${j.apellido}`;
        selectJugador.appendChild(opt);
    });
}

// Filtro en el modal
if (selectFiltroCategoria) {
    selectFiltroCategoria.addEventListener('change', (e) => {
        const idCat = e.target.value;
        if (!idCat) {
            renderizarOpcionesJugadores(allJugadores);
        } else {
            // Filtrar usando coincidencia flexible (==) por si string vs int
            const filtrados = allJugadores.filter(j => j.id_categoria == idCat);
            renderizarOpcionesJugadores(filtrados);
        }
    });
}

// --- MODAL Y EVENTOS ---

function configurarEventos() {
    // Abrir Modal Crear
    if(btnNuevoPago) btnNuevoPago.onclick = () => abrirModal('crear');
    
    // Cerrar Modal
    const cerrar = () => {
        modalPago.classList.add('hidden');
        modalPago.classList.remove('flex');
    };
    if(btnCancelar) btnCancelar.onclick = cerrar;
    if(btnCerrarX) btnCerrarX.onclick = cerrar;
    
    // Guardar
    if(formPago) {
        formPago.onsubmit = async (e) => {
            e.preventDefault();
            await guardarPago();
        };
    }

    // PDF
    if(btnGenerarInformePagos) {
        btnGenerarInformePagos.onclick = iniciarGeneracionPDF;
    }

    // Sidebar
    const menuToggle = document.getElementById('menuToggle');
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('overlay');
    
    const toggleSidebar = () => {
        sidebar.classList.toggle('-translate-x-full');
        overlay.classList.toggle('hidden');
    };

    if(menuToggle) menuToggle.onclick = toggleSidebar;
    if(overlay) overlay.onclick = toggleSidebar;
}

function abrirModal(modo, data = null) {
    formPago.reset();
    modalPago.classList.remove('hidden');
    modalPago.classList.add('flex');

    // Resetear filtro a "Todas"
    if(selectFiltroCategoria) selectFiltroCategoria.value = "";
    renderizarOpcionesJugadores(allJugadores);

    if (modo === 'editar' && data) {
        modalTitle.textContent = 'Editar Pago';
        document.getElementById('idPago').value = data.idPago;
        document.getElementById('monto').value = data.monto;
        document.getElementById('fechaPago').value = data.fechaPago;
        document.getElementById('mesPagado').value = data.mesPagado;
        document.getElementById('estado').value = data.estado;
        
        // Seleccionar el jugador
        setTimeout(() => { selectJugador.value = data.idJugador; }, 50);
    } else {
        modalTitle.textContent = 'Registrar Nuevo Pago';
        document.getElementById('idPago').value = '';
        document.getElementById('fechaPago').value = new Date().toISOString().split('T')[0];
    }
}

// --- GUARDAR / ELIMINAR ---

async function guardarPago() {
    const formData = new FormData(formPago);
    const data = Object.fromEntries(formData.entries());
    
    // Validar
    if(!data.idJugador || !data.monto) {
        return Swal.fire("Atención", "Faltan datos obligatorios", "warning");
    }

    const method = data.idPago ? 'PUT' : 'POST';

    try {
        const res = await fetch(API_PAGOS, {
            method: method,
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        const result = await res.json();

        if (result.success) {
            Swal.fire("Éxito", result.message, "success");
            modalPago.classList.add('hidden');
            modalPago.classList.remove('flex');
            cargarPagos();
        } else {
            throw new Error(result.message);
        }
    } catch (e) {
        Swal.fire("Error", e.message, "error");
    }
}

async function confirmarEliminacion(idPago) {
    const result = await Swal.fire({
        title: '¿Eliminar pago?',
        text: "Esta acción no se puede deshacer.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        confirmButtonText: 'Sí, eliminar'
    });

    if (result.isConfirmed) {
        try {
            const res = await fetch(API_PAGOS, {
                method: 'DELETE',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ idPago: idPago })
            });
            const data = await res.json();
            
            if (data.success) {
                Swal.fire('Eliminado', data.message, 'success');
                cargarPagos();
            } else {
                throw new Error(data.message);
            }
        } catch (err) {
            Swal.fire('Error', err.message, 'error');
        }
    }
}

// --- PDF ---

async function iniciarGeneracionPDF() {
    // Verificar librerías
    if (typeof window.jspdf === 'undefined') {
        return Swal.fire("Error", "Librería PDF no cargada", "error");
    }

    // Opciones para SweetAlert
    const options = { '0': 'Todas las categorías' };
    allCategorias.forEach(c => options[c.id_categoria] = c.nombre);

    const { value: idCat } = await Swal.fire({
        title: 'Informe PDF',
        text: 'Selecciona una categoría:',
        input: 'select',
        inputOptions: options,
        showCancelButton: true,
        confirmButtonText: 'Descargar',
        confirmButtonColor: '#166534'
    });

    if (idCat) generarPDF(idCat);
}

function generarPDF(idCat) {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();
    
    let datosPDF = allPagos;
    let nombreCat = "General";

    // Filtrar si no es "Todas"
    if (idCat !== '0') {
        const cat = allCategorias.find(c => c.id_categoria == idCat);
        nombreCat = cat ? cat.nombre : 'Desconocida';
        
        datosPDF = allPagos.filter(p => {
            const j = allJugadores.find(jug => jug.id_jugador == p.idJugador);
            return j && j.id_categoria == idCat;
        });
    }

    if (datosPDF.length === 0) {
        return Swal.fire("Info", "No hay pagos en esta categoría", "info");
    }

    // Encabezado
    doc.setFontSize(18);
    doc.setTextColor(22, 101, 52); // Verde
    doc.text("Reporte de Pagos - C.D. Momil", 14, 20);
    doc.setFontSize(12);
    doc.setTextColor(100);
    doc.text(`Categoría: ${nombreCat} | Fecha: ${new Date().toLocaleDateString()}`, 14, 28);

    // Cuerpo
    const body = datosPDF.map(p => [
        `${p.jugador_nombre} ${p.jugador_apellido}`,
        p.mesPagado,
        p.fechaPago,
        p.estado,
        `$${parseFloat(p.monto).toLocaleString('es-CO')}`
    ]);

    doc.autoTable({
        head: [['Jugador', 'Mes', 'Fecha', 'Estado', 'Monto']],
        body: body,
        startY: 35,
        theme: 'striped',
        headStyles: { fillColor: [22, 101, 52] },
        columnStyles: { 4: { halign: 'right', fontStyle: 'bold' } }
    });

    // Total
    const total = datosPDF.reduce((sum, p) => sum + parseFloat(p.monto), 0);
    doc.text(`Total Recaudado: $${total.toLocaleString('es-CO')}`, 14, doc.lastAutoTable.finalY + 10);

    doc.save(`Pagos_${nombreCat}.pdf`);
}