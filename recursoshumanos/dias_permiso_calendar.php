<?php
/* Copyright (C) 2007-2017 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) ---Put here your own copyright and developer email---
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

/**
 *   	\file       dias_permiso_list.php
 *		\ingroup    recursoshumanos
 *		\brief      List page for dias_permiso
 */

//if (! defined('NOREQUIREDB'))              define('NOREQUIREDB', '1');				// Do not create database handler $db
//if (! defined('NOREQUIREUSER'))            define('NOREQUIREUSER', '1');				// Do not load object $user
//if (! defined('NOREQUIRESOC'))             define('NOREQUIRESOC', '1');				// Do not load object $mysoc
//if (! defined('NOREQUIRETRAN'))            define('NOREQUIRETRAN', '1');				// Do not load object $langs
//if (! defined('NOSCANGETFORINJECTION'))    define('NOSCANGETFORINJECTION', '1');		// Do not check injection attack on GET parameters
//if (! defined('NOSCANPOSTFORINJECTION'))   define('NOSCANPOSTFORINJECTION', '1');		// Do not check injection attack on POST parameters
//if (! defined('NOCSRFCHECK'))              define('NOCSRFCHECK', '1');				// Do not check CSRF attack (test on referer + on token if option MAIN_SECURITY_CSRF_WITH_TOKEN is on).
//if (! defined('NOTOKENRENEWAL'))           define('NOTOKENRENEWAL', '1');				// Do not roll the Anti CSRF token (used if MAIN_SECURITY_CSRF_WITH_TOKEN is on)
//if (! defined('NOSTYLECHECK'))             define('NOSTYLECHECK', '1');				// Do not check style html tag into posted data
//if (! defined('NOREQUIREMENU'))            define('NOREQUIREMENU', '1');				// If there is no need to load and show top and left menu
//if (! defined('NOREQUIREHTML'))            define('NOREQUIREHTML', '1');				// If we don't need to load the html.form.class.php
//if (! defined('NOREQUIREAJAX'))            define('NOREQUIREAJAX', '1');       	  	// Do not load ajax.lib.php library
//if (! defined("NOLOGIN"))                  define("NOLOGIN", '1');					// If this page is public (can be called outside logged session). This include the NOIPCHECK too.
//if (! defined('NOIPCHECK'))                define('NOIPCHECK', '1');					// Do not check IP defined into conf $dolibarr_main_restrict_ip
//if (! defined("MAIN_LANG_DEFAULT"))        define('MAIN_LANG_DEFAULT', 'auto');					// Force lang to a particular value
//if (! defined("MAIN_AUTHENTICATION_MODE")) define('MAIN_AUTHENTICATION_MODE', 'aloginmodule');	// Force authentication handler
//if (! defined("NOREDIRECTBYMAINTOLOGIN"))  define('NOREDIRECTBYMAINTOLOGIN', 1);		// The main.inc.php does not make a redirect if not logged, instead show simple error message
//if (! defined("FORCECSP"))                 define('FORCECSP', 'none');				// Disable all Content Security Policies
//if (! defined('CSRFCHECK_WITH_TOKEN'))     define('CSRFCHECK_WITH_TOKEN', '1');		// Force use of CSRF protection with tokens even for GET
//if (! defined('NOBROWSERNOTIF'))     		 define('NOBROWSERNOTIF', '1');				// Disable browser notification

// Load Dolibarr environment
$res = 0;
// Try main.inc.php into web root known defined into CONTEXT_DOCUMENT_ROOT (not always defined)
if (!$res && !empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) {
	$res = @include $_SERVER["CONTEXT_DOCUMENT_ROOT"]."/main.inc.php";
}
// Try main.inc.php into web root detected using web root calculated from SCRIPT_FILENAME
$tmp = empty($_SERVER['SCRIPT_FILENAME']) ? '' : $_SERVER['SCRIPT_FILENAME']; $tmp2 = realpath(__FILE__); $i = strlen($tmp) - 1; $j = strlen($tmp2) - 1;
while ($i > 0 && $j > 0 && isset($tmp[$i]) && isset($tmp2[$j]) && $tmp[$i] == $tmp2[$j]) {
	$i--; $j--;
}
if (!$res && $i > 0 && file_exists(substr($tmp, 0, ($i + 1))."/main.inc.php")) {
	$res = @include substr($tmp, 0, ($i + 1))."/main.inc.php";
}
if (!$res && $i > 0 && file_exists(dirname(substr($tmp, 0, ($i + 1)))."/main.inc.php")) {
	$res = @include dirname(substr($tmp, 0, ($i + 1)))."/main.inc.php";
}
// Try main.inc.php using relative path
if (!$res && file_exists("../main.inc.php")) {
	$res = @include "../main.inc.php";
}
if (!$res && file_exists("../../main.inc.php")) {
	$res = @include "../../main.inc.php";
}
if (!$res && file_exists("../../../main.inc.php")) {
	$res = @include "../../../main.inc.php";
}
if (!$res) {
	die("Include of main fails");
}

require_once DOL_DOCUMENT_ROOT.'/core/class/html.formcompany.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/date.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/company.lib.php';

// load recursoshumanos libraries
require_once __DIR__.'/class/dias_permiso.class.php';

// for other modules
//dol_include_once('/othermodule/class/otherobject.class.php');

// Load translation files required by the page
$langs->loadLangs(array("recursoshumanos@recursoshumanos", "other"));

$action     = GETPOST('action', 'aZ09') ?GETPOST('action', 'aZ09') : 'view'; // The action 'add', 'create', 'edit', 'update', 'view', ...
$massaction = GETPOST('massaction', 'alpha'); // The bulk action (combo box choice into lists)
$show_files = GETPOST('show_files', 'int'); // Show files area generated by bulk actions ?
$confirm    = GETPOST('confirm', 'alpha'); // Result of a confirmation
$cancel     = GETPOST('cancel', 'alpha'); // We click on a Cancel button
$toselect   = GETPOST('toselect', 'array'); // Array of ids of elements selected into a list
$contextpage = GETPOST('contextpage', 'aZ') ? GETPOST('contextpage', 'aZ') : 'dias_permisolist'; // To manage different context of search
$backtopage = GETPOST('backtopage', 'alpha'); // Go back to a dedicated page
$optioncss = GETPOST('optioncss', 'aZ'); // Option for the css output (always '' except when 'print')

$id = GETPOST('id', 'int');

// Load variable for pagination
$limit = GETPOST('limit', 'int') ? GETPOST('limit', 'int') : $conf->liste_limit;
$sortfield = GETPOST('sortfield', 'aZ09comma');
$sortorder = GETPOST('sortorder', 'aZ09comma');
$page = GETPOSTISSET('pageplusone') ? (GETPOST('pageplusone') - 1) : GETPOST("page", 'int');
if (empty($page) || $page < 0 || GETPOST('button_search', 'alpha') || GETPOST('button_removefilter', 'alpha')) {
	// If $page is not defined, or '' or -1 or if we click on clear filters
	$page = 0;
}
$offset = $limit * $page;
$pageprev = $page - 1;
$pagenext = $page + 1;

// Initialize technical objects
$object = new Dias_permiso($db);

$object->fields = dol_sort_array($object->fields, 'position');
$arrayfields = dol_sort_array($arrayfields, 'position');

$permissiontoread = $user->rights->recursoshumanos->dias_permiso->read;
$permissiontoadd = $user->rights->recursoshumanos->dias_permiso->write;
$permissiontodelete = $user->rights->recursoshumanos->dias_permiso->delete;

if (empty($conf->recursoshumanos->enabled)) {
	accessforbidden('Module not enabled');
}

if ($user->socid > 0) accessforbidden();




/*
 * Actions
 */




/*
 * View
 */

$form = new Form($db);

$now = dol_now();

//$help_url="EN:Module_Dias_permiso|FR:Module_Dias_permiso_FR|ES:Módulo_Dias_permiso";
$help_url = '';
$title = $langs->trans('ListOf', $langs->transnoentitiesnoconv("dias de permiso"));
$morejs = array();
$morecss = array();




$query = "
    SELECT t.rowid AS peticion_id, 
           t.label, 
           t.date_solic, 
           t.date_solic_fin, 
           u1.firstname AS creator_firstname, 
           u1.lastname AS creator_lastname, 
           u2.firstname AS modifier_firstname, 
           u2.lastname AS modifier_lastname, 
           t.date_creation, 
           t.status 
    FROM khns_recursoshumanos_dias_permiso AS t
    LEFT JOIN khns_user AS u1 ON t.fk_user_creat = u1.rowid
    LEFT JOIN khns_user AS u2 ON t.fk_user_validador = u2.rowid
    WHERE 1 = 1"; 
	
	if ($user->admin) {
		// Si es administrador, no se añade ninguna condición
	} else {
		// Si no es administrador, se añade la condición para filtrar por fk_user_solicitador
		$query .= " AND t.fk_user_solicitador = " . intval($user->id);
	}

	if (isset($_POST['codigo']) && !empty($_POST['codigo'])) {
		$query .= " AND t.rowid = " . intval($_POST['codigo']); 
	}

// Ejecutar la consulta
$resql = $db->query($query);







// Output page
// --------------------------------------------------------------------

llxHeader('', $title, $help_url, '', 0, 0, $morejs, $morecss, '', '');
// Consulta para obtener los días de permiso
$queryPermisos = "
    SELECT t.rowid AS id, 
           t.status,
           t.label AS title, 
           t.date_solic AS start, 
           t.date_solic_fin AS end, 
           u1.firstname AS creator_firstname, 
           u1.lastname AS creator_lastname, 
           u2.firstname AS validator_firstname, 
           u2.lastname AS validator_lastname
    FROM khns_recursoshumanos_dias_permiso AS t
    LEFT JOIN khns_user AS u1 ON t.fk_user_creat = u1.rowid
    LEFT JOIN khns_user AS u2 ON t.fk_user_validador = u2.rowid
    WHERE 1 = 1";

// Filtrar por usuario si no es administrador
if (!$user->admin) {
    $queryPermisos .= " AND t.fk_user_creat = " . intval($user->id);
}

// Consulta para obtener todos los usuarios
$queryUsuarios = "
    SELECT CONCAT(u.firstname, ' ', u.lastname) AS user_name, u.rowid AS id_usuario
    FROM khns_user AS u";

// Ejecutar las consultas
$resqlPermisos = $db->query($queryPermisos);
$resqlUsuarios = $db->query($queryUsuarios);

// Crear un arreglo para los eventos y el índice de usuarios
$events = [];
$userIndex = []; // Para rastrear el índice del usuario en el eje Y

// Procesar usuarios
if ($resqlUsuarios) {
    while ($objUsuario = $db->fetch_object($resqlUsuarios)) {
        $userName = $objUsuario->user_name;
        if (!isset($userIndex[$userName])) {
            $userIndex[$userName] = count($userIndex); // Asignar un índice único
        }

        // Agregar al array de eventos con valores predeterminados
        $events[] = [
            'id' => null, // Sin permiso asociado
            'name' => null, // Sin título
            'start' => null, // Sin fecha de inicio
            'end' => null, // Sin fecha de fin
            'description' => $userName, // Solo el nombre del usuario
            'validator' => null, // Sin validador
            'y' => $userIndex[$userName],
            'status' => null // Sin estado
        ];
    }
}

// Procesar permisos
if ($resqlPermisos) {
    while ($obj = $db->fetch_object($resqlPermisos)) {
        $userName = $obj->creator_firstname . ' ' . $obj->creator_lastname;

        // Si el usuario no está ya en el índice, agregarlo
        if (!isset($userIndex[$userName])) {
            $userIndex[$userName] = count($userIndex);
        }

        // Agregar el evento con los datos del permiso
        $events[] = [
            'id' => $obj->id,
            'name' => $obj->title,
            'start' => strtotime($obj->start) * 1000,
            'end' => strtotime($obj->end) * 1000,
            'description' => $userName,
            'validator' => $obj->validator_firstname . ' ' . $obj->validator_lastname,
            'y' => $userIndex[$userName],
            'status' => $obj->status
        ];
    }
}


// Salida para el lado del cliente
?>
<!DOCTYPE html>
<html lang="es">
<head>
<style>
.button-container {
    display: flex;
    justify-content: center; /* Centrar horizontalmente */
    align-items: center; /* Centrar verticalmente */
    gap: 10px; /* Espacio entre los botones */
    margin-top: 20px; /* Separación desde otros elementos */
}


#legend {
    display: flex;
    justify-content: center; /* Centra los elementos en línea */
    gap: 20px; /* Espaciado entre los elementos */
    margin-top: 10px; /* Espaciado entre la leyenda y el gráfico */
}

.legend-color {
    display: inline-block;
    width: 20px;
    height: 10px;
}

.approved {
    background-color: #28a745 ;
}

.denied {
    background-color: #dc3545;
}

.draft {
    background-color: #ffc107;
}

</style>
    
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Gantt de Días de Permiso</title>
<!-- Highcharts Gantt -->
<script src="https://code.highcharts.com/gantt/highcharts-gantt.js"></script>
<script src="https://code.highcharts.com/modules/accessibility.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

</head>
<body>

<!-- Selector de fechas -->
<div style="text-align: center; margin-top: 20px;">
    <label for="startDate">Fecha de Inicio: </label>
    <input type="date" id="startDate" name="startDate">
    <label for="endDate">Fecha de Fin: </label>
    <input type="date" id="endDate" name="endDate">
    <button id="applyDateFilter" class="butAction">Aplicar Filtro</button>
</div>

<div id="container">
    <!-- Gráfico Gantt -->
    <div id="chart-container"></div>
</div>

<!-- Leyenda debajo del gráfico con colores -->
<div id="legend">
    <span><span class="legend-color approved"></span> Aprobado</span>
    <span><span class="legend-color denied"></span> Denegado</span>
    <span><span class="legend-color draft"></span> Borrador</span>
</div>

<div class="button-container">
    <button class="butAction" id="exportExcel">Exportar a Excel</button>
    <button class="butAction" id="exportIcal">Exportar a aplicación de calendario</button>
</div>

<script>
// Datos enviados desde PHP
const events = <?php echo json_encode($events); ?>;

// Extraer los nombres únicos de los usuarios para etiquetar el eje Y
const userNames = [...new Set(events.map(event => event.description))];

const coloredEvents = events.map(event => {
    let color;
    switch (event.status) {
        case '1':
            color = '#28a745'; // Aprobado: Verde
            break;
        case '9':
            color = '#dc3545'; // Denegado: Rojo
            break;
        case '0':
            color = '#ffc107'; // Borrador: 
            break;
        default:
            color = '#343a40 '; // Estado desconocido: Gris
    }
    return { ...event, color };
});
function getNextMonthEvents() {
    const today = new Date();
    const oneMonthLater = new Date(today.getFullYear(), today.getMonth() + 1, today.getDate()); // Mismo día del próximo mes

    return coloredEvents.filter(event => {
        const eventStart = new Date(event.start);
        return eventStart >= today && eventStart <= oneMonthLater;
    });
}

// Función para filtrar eventos por fechas seleccionadas
function filterEventsByDate() {
    const startDate = document.getElementById('startDate').value;
    const endDate = document.getElementById('endDate').value;

    let filteredEvents = [...coloredEvents];

    if (startDate) {
        filteredEvents = filteredEvents.filter(event => new Date(event.start).toISOString().split('T')[0] >= startDate);
    }

    if (endDate) {
        filteredEvents = filteredEvents.filter(event => new Date(event.end).toISOString().split('T')[0] <= endDate);
    }

    return filteredEvents;
}
console.log(getNextMonthEvents());

// Configuración global para cambiar el idioma a español
Highcharts.setOptions({
    lang: {
        months: [
            'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 
            'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
        ],
        weekdays: [
            'Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'
        ],
        shortMonths: [
            'Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 
            'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'
        ],
        rangeSelectorFrom: 'Desde',
        rangeSelectorTo: 'Hasta',
        loading: 'Cargando...',
        noData: 'No hay datos que mostrar',
        week: 'Semana'
    }
});
let day = 1000 * 60 * 60 * 24;

// Función para actualizar el gráfico Gantt
function updateChart(filteredEvents) {
    Highcharts.ganttChart('container', {
        xAxis:[{

}],
 
        title: {
            text: 'Calendario días de permiso'
        },
        subtitle: {
            text: '', // Mantener vacío si se desea usar solo la leyenda personalizada
        },
        yAxis: {
            categories: userNames, // Nombres de usuarios en el eje Y
            title: {
                text: 'Usuarios'
            },
            uniqueNames: true
        },
        xAxis: [{
    type: 'datetime', // Tipo de eje datetime
    tickInterval: 24 * 3600 * 1000, // Un día completo en milisegundos
    dateTimeLabelFormats: {
        day: '%d/%m', // Formato DIA/MES
        month: '%d/%m', // Formato DIA/MES
        year: '%d/%m', // Formato DIA/MES
        week: 'Semana %W' // Formato para mostrar "Semana 45", "Semana 46", etc.
    },
    labels: {
        style: {
            fontSize: '12px' // Tamaño de la fuente reducido
        },
    },
    min: Date.now(),
    max: new Date().setMonth(new Date().getMonth() + 1), 
    tickPixelInterval: 60, 
    gridLineWidth: 1, 
    tickWidth: 1,
}]
,
        
        tooltip: {
            pointFormat: '<b>{point.description}</b><br>{point.name}<br>Inicio: {point.start:%d/%m %Y %H:%M}<br>Fin: {point.end:%d/%m %Y %H:%M}<br>Estado : {point.status}'
        },
        series: [{
            name: '',
            data: filteredEvents,
            dataLabels: {
                enabled: true,
                format: '{point.name}', // Muestra la descripción del evento
                style: {
                    color: '#343a40 ', // Color morado para la descripción
                    fontWeight: 'bold', // Negrita
                    textOutline: 'none', // Quita el contorno de texto
                    fontSize: '12px', // Ajusta el tamaño de la fuente
                }
            }
        }]
    });
}

const defaultEvents = getNextMonthEvents();
updateChart(defaultEvents);
// Filtrar eventos cuando el usuario aplica el filtro
document.getElementById('applyDateFilter').addEventListener('click', () => {
    const filteredEvents = filterEventsByDate();
    updateChart(filteredEvents);
});
function exportToExcel() {
    const filteredEvents = filterEventsByDate(); // Usa los eventos filtrados
    const excelData = filteredEvents.map(event => {
        let estado;
        switch (event.status) {
            case '1':
                estado = 'Aprobado';
                break;
            case '9':
                estado = 'Denegado';
                break;
            case '0':
                estado = 'Borrador';
                break;
            default:
                estado = 'Desconocido';
        }

        return {
            "Usuario que solicita el día": event.description,
            "Descripcion solicitud": event.name,
            "Fecha inicio": new Date(event.start).toLocaleString('es-ES', { dateStyle: 'short', timeStyle: 'short' }),
            "Fecha fin": new Date(event.end).toLocaleString('es-ES', { dateStyle: 'short', timeStyle: 'short' }),
            "Usuario validador": event.validator || 'N/A',
            "Estado": estado
        };
    });

    const worksheet = XLSX.utils.json_to_sheet(excelData);
    const workbook = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(workbook, worksheet, "Registros");
    XLSX.writeFile(workbook, "Registros_Permisos.xlsx");
}


document.getElementById('exportExcel').addEventListener('click', exportToExcel);

function exportToIcal() {
    let icalData = "BEGIN:VCALENDAR\nVERSION:2.0\nCALSCALE:GREGORIAN\n";

    const filteredEvents = filterEventsByDate(); // Usa los eventos filtrados

    filteredEvents.forEach(event => {
        const start = new Date(event.start).toISOString().replace(/[-:]/g, '').split('.')[0];
        const end = new Date(event.end).toISOString().replace(/[-:]/g, '').split('.')[0];

        icalData += `
BEGIN:VEVENT
SUMMARY:${event.name}
DESCRIPTION:${event.description}
DTSTART:${start}
DTEND:${end}
STATUS:CONFIRMED
UID:${event.id}@example.com
END:VEVENT
`;
    });

    icalData += "END:VCALENDAR";

    const blob = new Blob([icalData], { type: 'text/calendar' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = 'events.ics';
    link.click();
}


document.getElementById('exportIcal').addEventListener('click', exportToIcal);

</script>

</body>
</html>
