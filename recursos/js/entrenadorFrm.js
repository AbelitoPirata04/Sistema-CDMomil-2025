// ============================
// ARCHIVO: recursos/js/entrenadorFrm.js (VERSIÓN CORREGIDA INTERACTIVIDAD)
// ============================

let entrenadores = [];
let entrenadorEditando = null;

// ============================
// DOM ELEMENTS
// ============================
// Menú y Sidebar
const menuToggle = document.getElementById('menuToggle');
const sidebar = document.getElementById('sidebar');
const overlay = document.getElementById('overlay');
const profileToggle = document.getElementById('profileToggle');
const profileDropdown = document.getElementById('profileDropdown');

// Tabla y Filtros
const tablaEntrenadores = document.getElementById('tablaEntrenadores');
const totalEntrenadores = document.getElementById('totalEntrenadores');
const searchInput = document.getElementById('searchInput');
const filterEspecialidad = document.getElementById('filterEspecialidad');

// Modales
const modalEntrenador = document.getElementById('modalEntrenador');
const modalCategoria = document.getElementById('modalCategoria');
const modalTitle = document.getElementById('modalTitle');

// Formularios
const formEntrenador = document.getElementById('formEntrenador');
const formCategoria = document.getElementById('formCategoria');

// Botones
const btnNuevoEntrenador = document.getElementById('btnNuevoEntrenador');
const btnCancelar = document.getElementById('btnCancelar');
const btnCerrarModal = document.getElementById('btnCerrarModal'); // Botón X
const btnCancelarCategoria = document.getElementById('btnCancelarCategoria');
const btnCerrarCat = document.getElementById('btnCerrarCat'); // Botón X Cat

// Inputs
const inputUsuario = document.getElementById('usuario');
const inputPass = document.getElementById('contrasena');
const msgPass = document.getElementById('msgPass');

// ============================
// LOGICA DE INTERFAZ (MENÚ Y PERFIL)
// ============================

// 1. Mostrar/Ocultar Sidebar (Menú Lateral)
function toggleSidebar() {
    sidebar.classList.toggle('-translate-x-full');
    overlay.classList.toggle('hidden');
}

if(menuToggle) menuToggle.addEventListener('click', toggleSidebar);
if(overlay) overlay.addEventListener('click', toggleSidebar);

// 2. Mostrar/Ocultar Dropdown Perfil
if(profileToggle) {
    profileToggle.addEventListener('click', (e) => {
        e.stopPropagation(); // Evita que el click se propague al document
        profileDropdown.classList.toggle('hidden');
    });
}

// 3. Cerrar Dropdown al hacer clic fuera
document.addEventListener('click', (e) => {
    if (profileDropdown && !profileDropdown.classList.contains('hidden')) {
        if (!profileToggle.contains(e.target) && !profileDropdown.contains(e.target)) {
            profileDropdown.classList.add('hidden');
        }
    }
});

// 4. Cerrar Sidebar al presionar ESC
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        if (!sidebar.classList.contains('-translate-x-full')) toggleSidebar();
        if (!modalEntrenador.classList.contains('hidden')) cerrarModal();
        if (!modalCategoria.classList.contains('hidden')) cerrarModalCategoria();
    }
});

// ============================
// LÓGICA DE DATOS (API)
// ============================

async function obtenerEntrenadores() {
    try {
        const res = await fetch('../api/entrenadorAPI.php');
        const data = await res.json();
        entrenadores = data;
        renderizarTabla(entrenadores);
        actualizarContador();
    } catch (e) {
        console.error(e);
        tablaEntrenadores.innerHTML = '<tr><td colspan="8" class="p-8 text-center text-red-500">Error de conexión</td></tr>';
    }
}

async function obtenerCategorias() {
    try {
        const res = await fetch('../api/categoriaAPI.php');
        const categorias = await res.json();
        const select = document.getElementById('categoria');
        select.innerHTML = '<option value="">Seleccionar...</option>';
        categorias.forEach(cat => {
            const opt = document.createElement('option');
            opt.value = cat.id_categoria;
            opt.textContent = `${cat.nombre} (${cat.edad_minima}-${cat.edad_maxima} años)`;
            if (cat.id_entrenador) {
                opt.textContent += " (Ocupada)";
                // opt.disabled = true; // Descomentar si quieres bloquear
            }
            select.appendChild(opt);
        });
    } catch (e) { console.error(e); }
}

function renderizarTabla(datos) {
    tablaEntrenadores.innerHTML = '';
    if (!datos.length) {
        tablaEntrenadores.innerHTML = '<tr><td colspan="8" class="p-8 text-center text-gray-500">No hay registros.</td></tr>';
        return;
    }

    datos.forEach(e => {
        const tr = document.createElement('tr');
        tr.className = 'hover:bg-gray-50 border-b';
        tr.innerHTML = `
            <td class="px-6 py-4">
                <div class="font-bold text-gray-900">${e.nombre} ${e.apellido}</div>
                <div class="text-xs text-gray-500">ID: ${e.cedula}</div>
            </td>
            <td class="px-6 py-4 text-sm font-mono text-gray-700">${e.usuario || '-'}</td>
            <td class="px-6 py-4 text-sm">
                <div>${e.telefono || ''}</div>
                <div class="text-xs text-gray-500">${e.correo || ''}</div>
            </td>
            <td class="px-6 py-4"><span class="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full">${e.especialidad || 'N/A'}</span></td>
            <td class="px-6 py-4 text-sm">${e.experiencia_años || 0} años</td>
            <td class="px-6 py-4 text-sm">${e.fecha_contratacion || ''}</td>
            <td class="px-6 py-4"><span class="bg-gray-100 text-gray-800 text-xs px-2 py-1 rounded-full">${e.categoria || 'Sin asignar'}</span></td>
            <td class="px-6 py-4 text-center">
                <button class="btn-editar text-blue-600 hover:text-blue-800 font-bold mr-2">Editar</button>
                <button class="btn-asignar text-green-600 hover:text-green-800 font-bold mr-2">Asignar</button>
                <button class="btn-eliminar text-red-600 hover:text-red-800 font-bold">Eliminar</button>
            </td>
        `;
        
        tr.querySelector('.btn-editar').onclick = () => editarEntrenador(e.id || e.cedula);
        tr.querySelector('.btn-asignar').onclick = () => abrirModalAsignar(e);
        tr.querySelector('.btn-eliminar').onclick = () => eliminarEntrenador(e.id || e.cedula, e.nombre);
        
        tablaEntrenadores.appendChild(tr);
    });
}

function actualizarContador() {
    totalEntrenadores.textContent = entrenadores.length;
}

// ============================
// LÓGICA DE MODALES (CRUD)
// ============================

function abrirModalNuevo() {
    entrenadorEditando = null;
    modalTitle.textContent = 'Nuevo Entrenador';
    formEntrenador.reset();
    
    // Contraseña Obligatoria
    inputPass.required = true;
    inputPass.placeholder = "••••••";
    msgPass.textContent = "Obligatorio para nuevos";
    
    modalEntrenador.classList.remove('hidden');
    modalEntrenador.classList.add('flex');
}

function editarEntrenador(id) {
    const e = entrenadores.find(ent => (ent.id || ent.cedula) == id);
    if (!e) return;
    
    entrenadorEditando = id;
    modalTitle.textContent = 'Editar Entrenador';
    
    document.getElementById('cedula').value = e.cedula;
    document.getElementById('nombre').value = e.nombre;
    document.getElementById('apellido').value = e.apellido;
    document.getElementById('telefono').value = e.telefono;
    document.getElementById('correo').value = e.correo;
    document.getElementById('especialidad').value = e.especialidad;
    document.getElementById('experiencia_años').value = e.experiencia_años;
    document.getElementById('fecha_contratacion').value = e.fecha_contratacion;
    document.getElementById('usuario').value = e.usuario || '';
    
    // Contraseña Opcional
    inputPass.required = false;
    inputPass.value = "";
    inputPass.placeholder = "(Sin cambios)";
    msgPass.textContent = "Dejar vacío para mantener la actual";
    
    modalEntrenador.classList.remove('hidden');
    modalEntrenador.classList.add('flex');
}

function cerrarModal() {
    modalEntrenador.classList.add('hidden');
    modalEntrenador.classList.remove('flex');
    formEntrenador.reset();
}

// Modal Categoría
function abrirModalAsignar(e) {
    document.getElementById('entrenadorSeleccionado').textContent = `${e.nombre} ${e.apellido}`;
    formCategoria.dataset.entrenadorId = e.id || e.cedula;
    obtenerCategorias(); // Refrescar
    modalCategoria.classList.remove('hidden');
    modalCategoria.classList.add('flex');
}

function cerrarModalCategoria() {
    modalCategoria.classList.add('hidden');
    modalCategoria.classList.remove('flex');
}

// ============================
// SUBMITS Y ACCIONES
// ============================

formEntrenador.addEventListener('submit', async (e) => {
    e.preventDefault();
    const data = Object.fromEntries(new FormData(formEntrenador));
    const url = '../api/entrenadorAPI.php';
    const method = entrenadorEditando ? 'PUT' : 'POST';
    
    if (entrenadorEditando) data.id = entrenadorEditando;

    try {
        const res = await fetch(url, {
            method: method,
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        const result = await res.json();
        
        if (result.success) {
            mostrarNotificacion(result.message);
            cerrarModal();
            obtenerEntrenadores();
        } else {
            mostrarNotificacion(result.message, 'error');
        }
    } catch (err) { console.error(err); }
});

formCategoria.addEventListener('submit', async (e) => {
    e.preventDefault();
    const cat = document.getElementById('categoria').value;
    const id = formCategoria.dataset.entrenadorId;
    
    try {
        const res = await fetch('../api/entrenadorAPI.php', {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: id, categoria: cat })
        });
        const result = await res.json();
        if(result.success) {
            mostrarNotificacion("Asignado correctamente");
            cerrarModalCategoria();
            obtenerEntrenadores();
        } else {
            mostrarNotificacion(result.message, 'error');
        }
    } catch(err) { console.error(err); }
});

function eliminarEntrenador(id, nombre) {
    if (!confirm(`¿Eliminar a ${nombre}?`)) return;
    
    fetch(`../api/entrenadorAPI.php?id=${id}`, { method: 'DELETE' })
    .then(r => r.json())
    .then(d => {
        if(d.success) { 
            mostrarNotificacion("Eliminado"); 
            obtenerEntrenadores(); 
        } else { 
            mostrarNotificacion("Error", "error"); 
        }
    });
}

function mostrarNotificacion(msg, tipo = 'success') {
    const div = document.createElement('div');
    const color = tipo === 'success' ? 'bg-green-600' : 'bg-red-600';
    div.className = `fixed top-4 right-4 p-4 rounded text-white shadow-lg z-50 ${color}`;
    div.textContent = msg;
    document.body.appendChild(div);
    setTimeout(() => div.remove(), 3000);
}

// Filtros
if(searchInput) searchInput.oninput = filtrar;
if(filterEspecialidad) filterEspecialidad.onchange = filtrar;

function filtrar() {
    const text = searchInput.value.toLowerCase();
    const esp = filterEspecialidad.value;
    const filtrados = entrenadores.filter(e => {
        const matchName = (e.nombre + " " + e.apellido).toLowerCase().includes(text);
        const matchEsp = esp === "" || e.especialidad === esp;
        return matchName && matchEsp;
    });
    renderizarTabla(filtrados);
}

// Listeners Botones
if(btnNuevoEntrenador) btnNuevoEntrenador.onclick = abrirModalNuevo;
if(btnCancelar) btnCancelar.onclick = cerrarModal;
if(btnCerrarModal) btnCerrarModal.onclick = cerrarModal; // Botón X
if(btnCancelarCategoria) btnCancelarCategoria.onclick = cerrarModalCategoria;
if(btnCerrarCat) btnCerrarCat.onclick = cerrarModalCategoria; // Botón X Cat

// INIT
document.addEventListener('DOMContentLoaded', () => {
    obtenerEntrenadores();
});