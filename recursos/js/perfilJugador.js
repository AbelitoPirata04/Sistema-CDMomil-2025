// Se ejecuta cuando todo el HTML está cargado
document.addEventListener('DOMContentLoaded', () => {
    // 1. Obtener el ID del jugador que guardamos en el div
    const idJugador = document.getElementById('datos-jugador').dataset.id;
    
    if (idJugador) {
        // 2. Llamar a la función que carga los datos
        cargarDatosJugador(idJugador);
    } else {
        console.error('No se pudo encontrar el ID del jugador.');
        alert('Error al cargar el perfil. Intenta iniciar sesión de nuevo.');
    }
});

// ===============================================================
// FUNCIÓN 'RELLENAR' MEJORADA
// ===============================================================
function rellenar(id, valor, tipo = 'texto') {
    const elemento = document.getElementById(id);
    if (elemento) {
        // Quitar la clase 'loading-placeholder'
        elemento.classList.remove('loading-placeholder');
        
        // --- INICIO DE LA MODIFICACIÓN ---
        
        // Comprobar si el valor es 'falsy' (null, undefined, "", 0)
        if (!valor && valor !== 0) { 
            // Si es 'falsy' Y ADEMÁS no es el número 0
            
            if (tipo === 'dorsal') {
                elemento.innerHTML = '<span>?</span>'; // Signo de interrogación para dorsal
            } else {
                elemento.textContent = 'N/A'; // N/A para texto
            }
            
        } else {
            // Si el valor SÍ existe (incluyendo el número 0)
            
            if (tipo === 'dorsal') {
                elemento.innerHTML = `<span>${valor}</span>`;
            } else {
                elemento.textContent = valor; // Mostrar el valor (ej: "0")
            }
        }
        // --- FIN DE LA MODIFICACIÓN ---
    }
}

// 3. Función principal que habla con la API
// 3. Función principal que habla con la API
async function cargarDatosJugador(id) {
    try {
        const url = `../api/jugadorAPI.php?id=${id}`;
        const response = await fetch(url);
        
        if (!response.ok) {
            throw new Error(`Error ${response.status}: No se pudo cargar la información.`);
        }
        
        const data = await response.json();
        
        // 4. Rellenar todos los campos con los datos recibidos
        
        // Cabecera
        rellenar('perfil-dorsal', data.dorsal, 'dorsal');
        rellenar('perfil-nombre-completo', data.nombre_completo);
        rellenar('perfil-posicion', data.posicion);
        rellenar('perfil-categoria', data.categoria_nombre);
        
        // Estadísticas (Ahora mostrarán "0" correctamente)
        rellenar('stats-partidos', data.partidos_jugados);
        rellenar('stats-goles', data.goles_totales);
        rellenar('stats-minutos', data.minutos_jugados);
        rellenar('stats-amarillas', data.tarjetas_amarillas);
        rellenar('stats-rojas', data.tarjetas_rojas);
        
        // Información Personal (Mostrarán "N/A" si están vacíos)
        rellenar('info-cedula', data.cedula);
        rellenar('info-telefono', data.telefono);
        rellenar('info-nacimiento', data.fecha_nacimiento);
        rellenar('info-edad', data.edad ? `${data.edad} años` : 'N/A');
        
        // --- LÍNEA QUE FALTABA ---
        rellenar('info-genero', data.genero);
        // --- FIN DE LA LÍNEA ---
        
        rellenar('info-fisico', (data.altura && data.altura > 0 && data.peso && data.peso > 0) ? `${data.altura}m / ${data.peso}kg` : 'N/A');
        rellenar('info-ingreso', data.fecha_ingreso);
        rellenar('info-usuario', data.usuario);

    } catch (error) {
        console.error('Error fatal al cargar datos:', error);
        document.getElementById('perfil-nombre-completo').textContent = 'Error al cargar perfil';
        document.getElementById('perfil-nombre-completo').classList.remove('loading-placeholder');
    }
}