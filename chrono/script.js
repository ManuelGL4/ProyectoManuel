var timers = {};
var startTimes = {};
var endTimes = {};
var totalElapsedTimes = {};
var startDates = {};
var endDates = {};

var apiKey; // Variable global para la API key
var userId; // Variable global para el User ID
function obtenerRegistrosActivos(usuarioId) {
    return $.ajax({
        url: `http://localhost/khonos-ORTRAT/api/index.php/chronoapi/obtenerRegistrosActivos/${usuarioId}`,
        method: 'GET',
        contentType: 'application/json',
        headers: {
            'Content-Type': 'application/json',
            'DOLAPIKEY': '9b1ea5dfacefebb40fb1591891764236f4241041'
        }
    });
}


window.onload = function() {
    apiKey = localStorage.getItem("apiKey");
    const hiddenUserIdInput = document.querySelector('input[name="usuario_id"]');
    if (hiddenUserIdInput) {
        userId = hiddenUserIdInput.value; // Capturar el valor del usuario
        localStorage.setItem("userId", userId); // Guardar en localStorage si es necesario
    } else {
        console.error("No se encontró el input oculto con el userId.");
        return;
    }
    console.log("userId", userId);
    // Llamada a la API para obtener el estado del temporizador
    $.ajax({
        url: `http://localhost/khonos-ORTRAT/api/index.php/chronoapi/obtenerregistrosactivos?fk_userid=${userId}`,
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'DOLAPIKEY': '9b1ea5dfacefebb40fb1591891764236f4241041'
        },
        success: function(response) {
            if (response && response.length > 0) {
                const activeTask = response[0];
        
                const taskId = activeTask.fk_task;
                const startTime = new Date(activeTask.date_time_event);
                startTimes[taskId] = startTime;
                timers[taskId] = true;
                totalElapsedTimes[taskId] = activeTask.elapsedTime || 0;  // Si `elapsedTime` existe, usalo, sino, usa 0.
        
                document.getElementById("start-time-" + taskId).innerText = formatDate(startTimes[taskId]);
                document.getElementById("time-" + taskId).innerHTML = formatTime(totalElapsedTimes[taskId]);
        
                updateTimerUI();
            }
            console.log("Tarea activa:", response[0]); 
        },
        
        error: function(xhr, status, error) {
            console.error('Error al obtener el estado del temporizador:', error);
        }
    });
};




function updateTimerUI() {
    apiKey = localStorage.getItem("apiKey");
    userId = localStorage.getItem("userId");

    for (let taskId in timers) {
        const taskIcon = document.getElementById("icon-" + taskId);
        const resetIcon = document.getElementById("reset-" + taskId);
        if (timers[taskId]) {
            taskIcon.innerHTML = "<img src='img/detener.png' alt='Detener' style='height: 40px;' onclick='stopTimer(" + taskId + ", \"" + apiKey + "\", \"" + userId + "\")'>";
            resetIcon.style.display = "inline-block";
        } else {
            taskIcon.innerHTML = "<img src='img/notstarted.png' alt='Iniciar' style='height: 40px;' onclick='startTimer(" + taskId + ", \"" + apiKey + "\", \"" + userId + "\")'>";
            resetIcon.style.display = "none";
        }
    }
}

function formatTime(totalSeconds) {
    const hours = Math.floor(totalSeconds / 3600);
    const minutes = Math.floor((totalSeconds % 3600) / 60);
    const seconds = totalSeconds % 60;
    return hours + "h " + minutes + "m " + seconds + "s";
}

function formatDate(date) {
    return date.toLocaleString();
}

function updateCurrentTime() {
    const now = new Date();
    const hours = now.getHours().toString().padStart(2, "0");
    const minutes = now.getMinutes().toString().padStart(2, "0");
    const seconds = now.getSeconds().toString().padStart(2, "0");
    document.getElementById("current-time").innerText = "Hora actual: " + hours + ":" + minutes + ":" + seconds;
}

function updateTotalTime() {
    let totalSeconds = 0;

    for (let taskId in totalElapsedTimes) {
        totalSeconds += totalElapsedTimes[taskId] || 0;
    }

    for (let taskId in timers) {
        if (timers[taskId]) {
            const now = new Date().getTime();
            const elapsedTime = now - startTimes[taskId].getTime();
            totalSeconds += Math.floor(elapsedTime / 1000);
        }
    }

    const hours = Math.floor(totalSeconds / 3600);
    const minutes = Math.floor((totalSeconds % 3600) / 60);
    const seconds = totalSeconds % 60;

    document.getElementById("total-time").innerText = "Tiempo total: " + hours + "h " + minutes + "m " + seconds + "s";
}

setInterval(function() {
    updateCurrentTime();
    updateTotalTime();
    updateActiveTimers();
}, 1000);

function updateActiveTimers() {
    for (let taskId in timers) {
        if (timers[taskId]) {
            const now = new Date().getTime();
            const elapsedTime = Math.floor((now - startTimes[taskId].getTime()) / 1000);
            const currentTotal = totalElapsedTimes[taskId] + elapsedTime;
            document.getElementById("time-" + taskId).innerHTML = formatTime(currentTotal);
        }
    }
}

function startTimer(taskId, apiKeyInput, userIdInput) {
    // Detener el temporizador existente si está corriendo
    for (let existingTaskId in timers) {
        if (timers[existingTaskId]) {
            stopTimer(existingTaskId);
        }
    }

    // Asegúrate de que la tarea no esté ya iniciada
    if (timers[taskId]) {
        return;
    }

    // Inicializa el tiempo total transcurrido para la tarea si no existe
    if (!totalElapsedTimes[taskId]) {
        totalElapsedTimes[taskId] = 0;
    }

    // Establecer la hora de inicio
    startTimes[taskId] = new Date();
    startDates[taskId] = new Date();
    document.getElementById("start-time-" + taskId).innerText = formatDate(startDates[taskId]);

    // Iniciar el temporizador
    timers[taskId] = setInterval(function() {
        const now = new Date().getTime();
        const elapsedTime = Math.floor((now - startTimes[taskId].getTime()) / 1000);
        totalElapsedTimes[taskId] = (totalElapsedTimes[taskId] || 0) + elapsedTime;

        // Actualizar la UI con el tiempo transcurrido en la tarea
        document.getElementById("time-" + taskId).innerHTML = formatTime(totalElapsedTimes[taskId]);

        // Restablecer la hora de inicio para una medición más precisa
        startTimes[taskId] = new Date();
    }, 1000);

    // Hacer la solicitud POST para iniciar la tarea en el servidor
    const startDateForTask = formatDateForMySQL(startTimes[taskId]);
    const noteInput = document.querySelector(`input.notes[data-task-id="${taskId}"]`);
    const note = noteInput ? noteInput.value : "";
    const projectIdInput = document.querySelector(`input[data-task-id="${taskId}"]`);
    const projectId = projectIdInput ? projectIdInput.value : "";

    const taskData = {
        date_time_event: startDateForTask,
        // event_location_ref: "Prueba", DESCOMENTAR PARA EL FUTURO
        event_type: 2, // Evento de entrada
        note: note,
        fk_userid: userIdInput,
        fk_task: taskId,
        fk_project: projectId,
    };

    // Enviar la solicitud POST para iniciar la tarea
    $.ajax({
        url: 'http://localhost/khonos-ORTRAT/api/index.php/chronoapi/chrono',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify(taskData),
        headers: {
            'Content-Type': 'application/json',
            'DOLAPIKEY': '9b1ea5dfacefebb40fb1591891764236f4241041'
        },
        success: function(response) {
            console.log("Temporizador iniciado y guardado con éxito:", response);
             const currentUrl = window.location.href.split('?')[0];
             window.location.href = `${currentUrl}?iniciado=1`;
        },
        error: function(xhr, status, error) {
            console.error('Error al guardar el inicio del temporizador:', error);
        }
    });

    // Actualizar la UI después de iniciar el temporizador
    updateTimerUI();
}

function stopTimer(taskId) {
    if (timers[taskId]) {
        clearInterval(timers[taskId]);
        timers[taskId] = null;

        const now = new Date();
        const sessionElapsedTime = Math.floor((now.getTime() - startTimes[taskId].getTime()) / 1000);
        totalElapsedTimes[taskId] += sessionElapsedTime;
        endTimes[taskId] = new Date();
        endDates[taskId] = new Date();

        const endDateForTask = formatDateForMySQL(now);
        const projectIdInput = document.querySelector(`input[data-task-id="${taskId}"]`);
        const projectId = projectIdInput ? projectIdInput.value : "";
        const noteInput = document.querySelector(`input.notes[data-task-id="${taskId}"]`);
        const note = noteInput ? noteInput.value : "";

        const taskData = {
            event_type: 3, // Evento de salida
            date_time_event: endDateForTask,
            // event_location_ref: "Prueba", DESCOMENTAR PARA EL FUTURO
            note: note,
            fk_userid: userId,
            fk_task: taskId,
            fk_project: projectId,
        };

        // Enviar la solicitud POST para detener la tarea
        $.ajax({
            url: 'http://localhost/khonos-ORTRAT/api/index.php/chronoapi/chrono',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(taskData),
            headers: {
                'Content-Type': 'application/json',
                'DOLAPIKEY': '9b1ea5dfacefebb40fb1591891764236f4241041'
            },
            success: function(response) {
                console.log("Temporizador detenido y guardado con éxito:", response);
                const currentUrl = window.location.href.split('?')[0];
                window.location.href = `${currentUrl}?parado=1`;
            },
            error: function(xhr, status, error) {
                console.error('Error al guardar la detención del temporizador:', error);
            }
        });

        // Actualizar la UI después de detener el temporizador
        updateTimerUI();
    }
}


function resetAllTimers() {
    for (let taskId in timers) {
        stopTimer(taskId);
        document.getElementById("time-" + taskId).innerHTML = "no iniciado";
        document.getElementById("start-time-" + taskId).innerHTML = "no iniciado";
        document.getElementById("reset-" + taskId).style.display = "none";
        totalElapsedTimes[taskId] = 0;
        startDates[taskId] = null;
    }
    timers = {};
    localStorage.clear(); // Limpiar localStorage al resetear todos los temporizadores

    updateTotalTime();
    location.reload();

}

function formatDateForMySQL(date) {
    const utcYear = date.getUTCFullYear();
    const utcMonth = String(date.getUTCMonth() + 1).padStart(2, '0');
    const utcDay = String(date.getUTCDate()).padStart(2, '0');
    const utcHours = String(date.getUTCHours()).padStart(2, '0');
    const utcMinutes = String(date.getUTCMinutes()).padStart(2, '0');
    const utcSeconds = String(date.getUTCSeconds()).padStart(2, '0');

    return `${utcYear}-${utcMonth}-${utcDay} ${utcHours}:${utcMinutes}:${utcSeconds}`;
}




document.addEventListener("DOMContentLoaded", function() {
    document.getElementById("resetButton").onclick = function() {
        document.getElementById("confirmModal").style.display = "block";
    };

    document.getElementById("confirmReset").onclick = function() {
        resetAllTimers();
        document.getElementById("confirmModal").style.display = "none";
    };

    document.getElementById("cancelReset").onclick = function() {
        document.getElementById("confirmModal").style.display = "none";
    };
});


