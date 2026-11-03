// Configuración genérica de la aplicación.
// Cambia aquí el nombre visible y los permisos de usuario.

window.APP_CONFIG = {
    nombre: "Plantas Solares",

    permisos: {
        // Usuarios que pueden pedir lecturas instantáneas.
        lecturaInstantanea: [1],

        // Usuarios que NO pueden ver la pestaña de alarmas.
        alarmasExcluidos: [6]
    }
};

function usuarioActual() {
    return sessionStorage.getItem("usuario");
}

function usuarioEnLista(lista) {
    var usuario = usuarioActual();
    if (usuario === null) {
        return false;
    }
    return (lista || []).map(String).indexOf(String(usuario)) !== -1;
}

function puedePedirLecturaInstantanea() {
    return usuarioEnLista(window.APP_CONFIG.permisos.lecturaInstantanea);
}

function puedeVerAlarmas() {
    return !usuarioEnLista(window.APP_CONFIG.permisos.alarmasExcluidos);
}

// Aplica el nombre configurado a los elementos marcados con data-app-nombre.
document.addEventListener("DOMContentLoaded", function () {
    var nombre = window.APP_CONFIG.nombre;
    document.title = nombre;

    var elementos = document.querySelectorAll("[data-app-nombre]");
    for (var i = 0; i < elementos.length; i++) {
        var el = elementos[i];
        if (el.tagName === "IMG") {
            el.alt = nombre;
        } else {
            el.textContent = nombre;
        }
    }
});

// Si el servidor invalida la sesión (401), vuelve a la pantalla de login.
if (window.jQuery) {
    jQuery(document).ajaxError(function (event, xhr) {
        if (xhr && xhr.status === 401) {
            try {
                sessionStorage.removeItem("usuario");
            } catch (e) {
                // sessionStorage puede no estar disponible
            }
            window.location = "index.html";
        }
    });
}
