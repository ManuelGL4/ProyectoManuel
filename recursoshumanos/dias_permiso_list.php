<?php

/*
 * Copyright (C) 2007-2017 Laurent Destailleur  <eldy@users.sourceforge.net>
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
 * \file       dias_permiso_list.php
 * 		\ingroup    recursoshumanos
 * 		\brief      List page for dias_permiso
 */

// if (! defined('NOREQUIREDB'))              define('NOREQUIREDB', '1');				// Do not create database handler $db
// if (! defined('NOREQUIREUSER'))            define('NOREQUIREUSER', '1');				// Do not load object $user
// if (! defined('NOREQUIRESOC'))             define('NOREQUIRESOC', '1');				// Do not load object $mysoc
// if (! defined('NOREQUIRETRAN'))            define('NOREQUIRETRAN', '1');				// Do not load object $langs
// if (! defined('NOSCANGETFORINJECTION'))    define('NOSCANGETFORINJECTION', '1');		// Do not check injection attack on GET parameters
// if (! defined('NOSCANPOSTFORINJECTION'))   define('NOSCANPOSTFORINJECTION', '1');		// Do not check injection attack on POST parameters
// if (! defined('NOCSRFCHECK'))              define('NOCSRFCHECK', '1');				// Do not check CSRF attack (test on referer + on token if option MAIN_SECURITY_CSRF_WITH_TOKEN is on).
// if (! defined('NOTOKENRENEWAL'))           define('NOTOKENRENEWAL', '1');				// Do not roll the Anti CSRF token (used if MAIN_SECURITY_CSRF_WITH_TOKEN is on)
// if (! defined('NOSTYLECHECK'))             define('NOSTYLECHECK', '1');				// Do not check style html tag into posted data
// if (! defined('NOREQUIREMENU'))            define('NOREQUIREMENU', '1');				// If there is no need to load and show top and left menu
// if (! defined('NOREQUIREHTML'))            define('NOREQUIREHTML', '1');				// If we don't need to load the html.form.class.php
// if (! defined('NOREQUIREAJAX'))            define('NOREQUIREAJAX', '1');       	  	// Do not load ajax.lib.php library
// if (! defined("NOLOGIN"))                  define("NOLOGIN", '1');					// If this page is public (can be called outside logged session). This include the NOIPCHECK too.
// if (! defined('NOIPCHECK'))                define('NOIPCHECK', '1');					// Do not check IP defined into conf $dolibarr_main_restrict_ip
// if (! defined("MAIN_LANG_DEFAULT"))        define('MAIN_LANG_DEFAULT', 'auto');					// Force lang to a particular value
// if (! defined("MAIN_AUTHENTICATION_MODE")) define('MAIN_AUTHENTICATION_MODE', 'aloginmodule');	// Force authentication handler
// if (! defined("NOREDIRECTBYMAINTOLOGIN"))  define('NOREDIRECTBYMAINTOLOGIN', 1);		// The main.inc.php does not make a redirect if not logged, instead show simple error message
// if (! defined("FORCECSP"))                 define('FORCECSP', 'none');				// Disable all Content Security Policies
// if (! defined('CSRFCHECK_WITH_TOKEN'))     define('CSRFCHECK_WITH_TOKEN', '1');		// Force use of CSRF protection with tokens even for GET
// if (! defined('NOBROWSERNOTIF'))     		 define('NOBROWSERNOTIF', '1');				// Disable browser notification

// Load Dolibarr environment
$res = 0;
// Try main.inc.php into web root known defined into CONTEXT_DOCUMENT_ROOT (not always defined)
if (!$res && !empty($_SERVER['CONTEXT_DOCUMENT_ROOT'])) {
    $res = @include $_SERVER['CONTEXT_DOCUMENT_ROOT'] . '/main.inc.php';
}
// Try main.inc.php into web root detected using web root calculated from SCRIPT_FILENAME
$tmp = empty($_SERVER['SCRIPT_FILENAME']) ? '' : $_SERVER['SCRIPT_FILENAME'];
$tmp2 = realpath(__FILE__);
$i = strlen($tmp) - 1;
$j = strlen($tmp2) - 1;
while ($i > 0 && $j > 0 && isset($tmp[$i]) && isset($tmp2[$j]) && $tmp[$i] == $tmp2[$j]) {
    $i--;
    $j--;
}
if (!$res && $i > 0 && file_exists(substr($tmp, 0, ($i + 1)) . '/main.inc.php')) {
    $res = @include substr($tmp, 0, ($i + 1)) . '/main.inc.php';
}
if (!$res && $i > 0 && file_exists(dirname(substr($tmp, 0, ($i + 1))) . '/main.inc.php')) {
    $res = @include dirname(substr($tmp, 0, ($i + 1))) . '/main.inc.php';
}
// Try main.inc.php using relative path
if (!$res && file_exists('../main.inc.php')) {
    $res = @include '../main.inc.php';
}
if (!$res && file_exists('../../main.inc.php')) {
    $res = @include '../../main.inc.php';
}
if (!$res && file_exists('../../../main.inc.php')) {
    $res = @include '../../../main.inc.php';
}
if (!$res) {
    die('Include of main fails');
}

require_once DOL_DOCUMENT_ROOT . '/core/class/html.formcompany.class.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/date.lib.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/company.lib.php';

require_once DOL_DOCUMENT_ROOT . '/custom/recursoshumanos/app/DiaPermisoController.php';
// load recursoshumanos libraries
require_once __DIR__ . '/class/dias_permiso.class.php';

// for other modules
// dol_include_once('/othermodule/class/otherobject.class.php');

// Load translation files required by the page
$langs->loadLangs(array('recursoshumanos@recursoshumanos', 'other'));

$action = GETPOST('action', 'aZ09') ? GETPOST('action', 'aZ09') : 'view';  // The action 'add', 'create', 'edit', 'update', 'view', ...
$massaction = GETPOST('massaction', 'alpha');  // The bulk action (combo box choice into lists)
$show_files = GETPOST('show_files', 'int');  // Show files area generated by bulk actions ?
$confirm = GETPOST('confirm', 'alpha');  // Result of a confirmation
$cancel = GETPOST('cancel', 'alpha');  // We click on a Cancel button
$toselect = GETPOST('toselect', 'array');  // Array of ids of elements selected into a list
$contextpage = GETPOST('contextpage', 'aZ') ? GETPOST('contextpage', 'aZ') : 'dias_permisolist';  // To manage different context of search
$backtopage = GETPOST('backtopage', 'alpha');  // Go back to a dedicated page
$optioncss = GETPOST('optioncss', 'aZ');  // Option for the css output (always '' except when 'print')

$id = GETPOST('id', 'int');

// Load variable for pagination
$limit = GETPOST('limit', 'int') ? GETPOST('limit', 'int') : $conf->liste_limit;
$sortfield = GETPOST('sortfield', 'aZ09comma');
$sortorder = GETPOST('sortorder', 'aZ09comma');
if (empty($sortfield)) {
    $sortfield = 't.rowid';
}

if (empty($sortorder)) {
    $sortorder = 'DESC';
}
$page = GETPOSTISSET('pageplusone') ? (GETPOST('pageplusone') - 1) : GETPOST('page', 'int');
if (empty($page) || $page < 0 || GETPOST('button_search', 'alpha') || GETPOST('button_removefilter', 'alpha')) {
    // If $page is not defined, or '' or -1 or if we click on clear filters
    $page = 1;
}
$offset = $limit * $page;
$pageprev = $page - 1;
$pagenext = $page + 1;

// Initialize technical objects

$arrayfields = dol_sort_array($arrayfields, 'position');

$permissiontoread = $user->rights->recursoshumanos->dias_permiso->read;
$permissiontoadd = $user->rights->recursoshumanos->dias_permiso->write;
$permissiontodelete = $user->rights->recursoshumanos->dias_permiso->delete;

if (empty($conf->recursoshumanos->enabled)) {
    accessforbidden('Module not enabled');
}

if ($user->socid > 0)
    accessforbidden();

/*
 * Actions
 */

/*
 * View
 */
print '<style>
.overlay {
   position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.5); display: flex; align-items: center; justify-content: center;
}
   .form-delete{
background: white; padding: 20px; border-radius: 8px; width: 400px; text-align: center;
}
.edit{
background-color: #5bc0de !important; color: white !important; padding: 10px 20px !important; border: none !important; border-radius: 4px !important;
}
.back{
    background-color: #d9534f !important; color: white !important; padding: 10px 20px !important; border: none !important; border-radius: 4px !important;
    }
</style>';
$form = new Form($db);

$now = dol_now();

// $help_url="EN:Module_Dias_permiso|FR:Module_Dias_permiso_FR|ES:Módulo_Dias_permiso";
$help_url = '';
$title = $langs->trans('ListOf', $langs->transnoentitiesnoconv('dias de permiso'));
$morejs = array();
$morecss = array();

$controller = new DiaPermisoController($db);

if (isset($_GET['action']) && $_GET['action'] == 'delete') {
    $id = $_GET['id'];
    print '
    <div class="overlay">
        <form method="POST" action="' . $_SERVER['PHP_SELF'] . '?id=' . $id . '" name="formfilter" autocomplete="off" class="form-delete">
                    <span id="ui-id-1" class="ui-dialog-title">Borrar Registro</span>
                    <button type="button" class="ui-button ui-corner-all ui-widget ui-button-icon-only ui-dialog-titlebar-close" title="Close" onclick="window.history.back();" style="background: none; border: none; cursor: pointer;">
                        <span class="ui-button-icon ui-icon ui-icon-closethick"></span>
                    </button>
                    <br><br>
                    <p>¿Desea borrar este registro de Día de permiso?</p>

                    <div class="ui-dialog-buttonset modal-footer" >
                        <button type="submit" class="ui-button ui-corner-all ui-widget back" name="Borrar" >
                            Borrar
                        </button>
                        <button type="button" class="ui-button ui-corner-all ui-widget edit " onclick="window.history.back();">
                            Cancelar
                        </button>
                </div>
            </div>
        </form>
    </div>';
}

if (isset($_POST['Borrar'])) {
    $id = $_GET['id'];
    $controller->deleteRegistro($id);
}

llxHeader('', $title, $help_url, '', 0, 0, $morejs, $morecss, '', '');
if (isset($_GET['creado'])) {
    if ($_GET['creado'] == 'success') {
        setEventMessages(array('Solicitud de permiso realizada con éxito.'), array(), 'mesgs');
    } elseif ($_GET['creado'] == 'error') {
        setEventMessages(array('Error al intentar solicitar la fecha, por favor, inténtelo de nuevo.'), array(), 'errors');
    }
}
if (isset($_GET['editada'])) {
    if ($_GET['editada'] == 'success') {
        setEventMessages(array('Solicitud de permiso actualizada con éxito.'), array(), 'mesgs');
    } elseif ($_GET['editada'] == 'error') {
        setEventMessages(array('Error al intentar actualizar la fecha, por favor, inténtelo de nuevo.'), array(), 'errors');
    }
}

print '<form method="get" id="searchFormList" action="' . $_SERVER['PHP_SELF'] . '">' . "\n";
if ($optioncss != '') {
    print '<input type="hidden" name="optioncss" value="' . $optioncss . '">';
}
print '<input type="hidden" name="token" value="' . newToken() . '">';
print '<input type="hidden" name="formfilteraction" id="formfilteraction" value="list">';
print '<input type="hidden" name="action" value="list">';
print '<input type="hidden" name="sortfield" value="' . $sortfield . '">';
print '<input type="hidden" name="sortorder" value="' . $sortorder . '">';
print '<input type="hidden" name="page" value="' . $page . '">';
print '<input type="hidden" name="contextpage" value="' . $contextpage . '">';

$newcardbutton = dolGetButtonTitle($langs->trans('New'), '', 'fa fa-plus-circle', dol_buildpath('/recursoshumanos/dias_permiso_card.php', 1) . '?action=create&backtopage=' . urlencode($_SERVER['PHP_SELF']), '');

print_barre_liste($title, '', '', '', '', '', $massactionbutton, '', '', 'object_' . $object->picto, 0, $newcardbutton, '', '', 0, 0, 0);

print '<div class="div-table-responsive">';
print '<table class="tagtable nobottomiftotal liste' . ($moreforfilter ? ' listwithfilterbefore' : '') . '">' . "\n";
print_liste_field_titre('Código de solicitud', $PHP_SELF, 't.rowid', '', $param, '', $sortfield, $sortorder);

print_liste_field_titre('Descripción', $PHP_SELF, 't.label', '', $param, '', $sortfield, $sortorder);

print_liste_field_titre('Fecha inicio', $PHP_SELF, 't.date_solic', '', $param, '', $sortfield, $sortorder);

print_liste_field_titre('Fecha fin', $PHP_SELF, 't.date_solic_fin', '', $param, '', $sortfield, $sortorder);
print_liste_field_titre('Usuario de solicitud', $PHP_SELF, 't.fk_user_creat', '', $param, '', $sortfield, $sortorder);
print_liste_field_titre('Fecha creacion solicitud', $PHP_SELF, 't.date_creation', '', $param, '', $sortfield, $sortorder);
print_liste_field_titre('Usuario validador', $PHP_SELF, 't.fk_user_validador', '', $param, '', $sortfield, $sortorder);
print_liste_field_titre('Estado Peticion', $PHP_SELF, 't.status', '', $param, '', $sortfield, $sortorder);

print_liste_field_titre('Acciones', $PHP_SELF, '', '', $param, '', $sortfield, $sortorder);

$codigo = GETPOST('codigo', 'alpha');
$description = GETPOST('description', 'alpha');
$fecha_inicio = GETPOST('fecha_inicio', 'date');
$fecha_fin = GETPOST('fecha_fin', 'date');
$ls_userid = GETPOST('ls_userid', 'int');
$fecha_create = GETPOST('fecha_create', 'date');
$validador = GETPOST('validador', 'int');
$status = GETPOST('status', 'int');
$status = GETPOST('status', 'int');
if ($status === null || $status === '') {
    $status = -1;
}

print '<tr class="liste_titre">';

print '<td class="liste_titre" colspan="1">';
print '<input type="text" name="codigo" value="' . $codigo . '" />';
print '</td>';

print '<td class="liste_titre" colspan="1">';
print '<input type="text" name="description" value="' . $description . '" / style="width: 100%">';
print '</td>';

print '<td class="liste_titre center" colspan="1">';
print '<input type="date" name="fecha_inicio" value="' . $fecha_inicio . '" />';
print '</td>';

print '<td class="liste_titre" colspan="1">';
print '<input type="date" name="fecha_fin" value="' . $fecha_fin . '" />';
print '</td>';

print '<td class="liste_titre" colspan="1">';
print $form->select_dolusers($ls_userid, 'ls_userid', 1, '', 0);
print '</td>';

print '<td class="liste_titre" colspan="1">';
print '<input type="date" name="fecha_create" value="' . $fecha_create . '" />';
print '</td>';

print '<td class="liste_titre" colspan="1">';
print $form->select_dolusers($validador, 'validador', 1, '', 0);
print '</td>';

print '<td class="liste_titre" colspan="1">';
print '<select name="status">';
print '<option value="-1" ' . ($status == -1 ? 'selected' : '') . '>Todos</option>';
print '<option value="0" ' . ($status == 0 ? 'selected' : '') . '>Borrador</option>';
print '<option value="1" ' . ($status == 1 ? 'selected' : '') . '>Validado</option>';
print '<option value="9" ' . ($status == 9 ? 'selected' : '') . '>Rechazado</option>';
print '</select>';
print '</td>';

include DOL_DOCUMENT_ROOT . '/core/tpl/extrafields_list_search_input.tpl.php';

$parameters = array('arrayfields' => $arrayfields);
$reshook = $hookmanager->executeHooks('printFieldListOption', $parameters, $object);
print $hookmanager->resPrint;

print '<td class="liste_titre maxwidthsearch">';
$searchpicto = $form->showFilterButtons();
print $searchpicto;
print '</td>';
print '</tr>' . "\n";

$filters = [
    'codigo' => GETPOST('codigo', 'alpha'),
    'description' => GETPOST('description', 'alpha'),
    'fecha_inicio' => GETPOST('fecha_inicio', 'date'),
    'fecha_fin' => GETPOST('fecha_fin', 'date'),
    'ls_userid' => GETPOST('ls_userid', 'int'),
    'fecha_create' => GETPOST('fecha_create', 'date'),
    'validador' => GETPOST('validador', 'int'),
    'status' => GETPOST('status', 'int')
];

$limit = 10;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;

$totalRecords = $controller->getRegistros($user, $filters);
$totalPages = ceil($totalRecords / $limit);

$result = $controller->listDias($user, $sortfield, $sortorder, $page, $limit, $filters);

if ($totalPages > 1) {
    print '<div class="pagination" style="text-align: center;">';

    // "<<"
    if ($page > 1) {
        $firstPageUrl = $_SERVER['PHP_SELF'] . '?page=1';
        foreach ($_GET as $key => $value) {
            if ($key != 'page') {
                $firstPageUrl .= '&' . urlencode($key) . '=' . urlencode($value);
            }
        }
        print '<a href="' . $firstPageUrl . '" style="font-size: 1.2em; margin: 0 10px;">&laquo;</a>';
    }

    // <
    if ($page > 1) {
        $prevPageUrl = $_SERVER['PHP_SELF'] . '?page=' . ($page - 1);
        foreach ($_GET as $key => $value) {
            if ($key != 'page') {
                $prevPageUrl .= '&' . urlencode($key) . '=' . urlencode($value);
            }
        }
        print '<a href="' . $prevPageUrl . '" style="font-size: 1.2em; margin: 0 10px;">&lsaquo;</a>';
    }

    // paginas
    for ($i = 1; $i <= $totalPages; $i++) {
        if ($i == $page || ($i >= $page - 1 && $i <= $page + 1)) {
            $paginationUrl = $_SERVER['PHP_SELF'] . '?page=' . $i;
            foreach ($_GET as $key => $value) {
                if ($key != 'page') {
                    $paginationUrl .= '&' . urlencode($key) . '=' . urlencode($value);
                }
            }
            if ($i === $page) {
                print '<strong style="font-size: 1.2em; margin: 0 10px;">' . $i . '</strong>';
            } else {
                print '<a href="' . $paginationUrl . '" style="font-size: 1.2em; margin: 0 10px;">' . $i . '</a>';
            }
        } elseif ($i == 1 || $i == $totalPages) {
            $paginationUrl = $_SERVER['PHP_SELF'] . '?page=' . $i;
            foreach ($_GET as $key => $value) {
                if ($key != 'page') {
                    $paginationUrl .= '&' . urlencode($key) . '=' . urlencode($value);
                }
            }
            print '<a href="' . $paginationUrl . '" style="font-size: 1.2em; margin: 0 10px;">' . $i . '</a>';
        } elseif ($i == $page - 2 || $i == $page + 2) {
            print '<span style="font-size: 1.2em; margin: 0 10px;">...</span>';
        }
    }

    if ($page < $totalPages) {
        $nextPageUrl = $_SERVER['PHP_SELF'] . '?page=' . ($page + 1);
        foreach ($_GET as $key => $value) {
            if ($key != 'page') {
                $nextPageUrl .= '&' . urlencode($key) . '=' . urlencode($value);
            }
        }
        print '<a href="' . $nextPageUrl . '" style="font-size: 1.2em; margin: 0 10px;">&rsaquo;</a>';
    }

    if ($page < $totalPages) {
        $lastPageUrl = $_SERVER['PHP_SELF'] . '?page=' . $totalPages;
        foreach ($_GET as $key => $value) {
            if ($key != 'page') {
                $lastPageUrl .= '&' . urlencode($key) . '=' . urlencode($value);
            }
        }
        print '<a href="' . $lastPageUrl . '" style="font-size: 1.2em; margin: 0 10px;">&raquo;</a>';
    }

    print '</div>';
}

$statusLabels = [
    0 => ['label' => 'Borrador', 'color' => 'background-color: white;'],
    1 => ['label' => 'Validado', 'color' => 'background-color: green;'],
    9 => ['label' => 'Cancelado', 'color' => 'background-color: red;'],
];

if ($result) {
    $i = 0;
    $totalarray = array();
    $totalarray['nbfield'] = 0;

    foreach ($result as $obj) {
        $statusLabel = '';
        $statusClass = '';

        switch ($obj->status) {
            case 0:
                $statusLabel = 'Pendiente';
                $statusClass = 'badge badge-status0 badge-status';
                break;
            case 1:
                $statusLabel = 'Aprobada';
                $statusClass = 'badge  badge-status4 badge-status';
                break;
            case 9:
                $statusLabel = 'Rechazada';
                $statusClass = 'badge badge-status8 badge-status';
                break;
            default:
                $statusLabel = 'Desconocido';
                $statusClass = 'badge badge-status unknown';
                break;
        }
        $fecha_solic = date('d/m/Y H:i', strtotime($obj->date_solic));
        $fecha_solic_fin = date('d/m/Y H:i', strtotime($obj->date_solic_fin));
        $fecha_creation = date('d/m/Y H:i', strtotime($obj->date_creation));

        print '<tr class="oddeven">';
        print '<td class="center">' . $obj->peticion_id . '</td>';
        print '<td class="center">' . $obj->label . '</td>';
        print '<td class="center">' . $fecha_solic . '</td>';
        print '<td class="center">' . $fecha_solic_fin . '</td>';

        print '<td class="center">' . $obj->creator_firstname . ' ' . $obj->creator_lastname . '</td>';
        print '<td class="center">' . $fecha_creation . '</td>';

        print '<td class="center">' . $obj->modifier_firstname . ' ' . $obj->modifier_lastname . '</td>';
        print '<td class="center">';
        print '<span class="' . $statusClass . '">' . $statusLabel . '</span>';
        print '</td>';
        print '<td class="center">';

        print '<a class="fas  fa-eye" title="Editar Registro" href="dias_permiso_card.php?action=edit&id=' . $obj->peticion_id . '"></a>';
        print '<a class="fas  fa-trash"  title="Eliminar" href="' . $_SERVER['PHP_SELF'] . '?action=delete&id=' . $obj->peticion_id . '"></a>';
        print '</td>';

        print '</tr>' . "\n";

        $i++;
    }
} else {
    print '<tr><td colspan="10" class="center">No hay registros</td></tr>';
}

print '</table>' . "\n";

print '</form>' . "\n";

llxFooter();
$db->close();
