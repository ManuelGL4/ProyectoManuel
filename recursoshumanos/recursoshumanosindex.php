<?php
/* Copyright (C) 2001-2005 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2015 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2005-2012 Regis Houssin        <regis.houssin@inodbox.com>
 * Copyright (C) 2015      Jean-François Ferry	<jfefe@aternatik.fr>
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
 *	\file       recursoshumanos/recursoshumanosindex.php
 *	\ingroup    recursoshumanos
 *	\brief      Home page of recursoshumanos top menu
 */

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

require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';

// Load translation files required by the page
$langs->loadLangs(array("recursoshumanos@recursoshumanos"));

$action = GETPOST('action', 'aZ09');


// Security check
// if (! $user->rights->recursoshumanos->myobject->read) {
// 	accessforbidden();
// }
$socid = GETPOST('socid', 'int');
if (isset($user->socid) && $user->socid > 0) {
	$action = '';
	$socid = $user->socid;
}

$max = 5;
$now = dol_now();


/*
 * Actions
 */

// None


/*
 * View
 */

$form = new Form($db);
$formfile = new FormFile($db);

llxHeader("", $langs->trans("Recursos Humanos"));

print '<style>
	.contenedorTi {
		display: flex;
		justify-content: center;
	}
	.titulo {
		font-size: 80px;
	}
	.contenedorCont {
		display: flex;
		justify-content: center;
	}
	.contenido {
		margin-top: 50px;
		font-size: 20px;
		padding-left: 8%;
		padding-right: 8%;
	}
	.contenedorImg {
		display: flex;
		justify-content: center;
	}
	.img {
		margin-top: 60px;
		width: 800px;
	}
	@media screen and (max-width: 800px) {
		.titulo {
			font-size: 60px;
		}
		.contenido {
			font-size: 15px;
		}
        .img {
            width: 500px;
            height: auto;
        }
	}
	@media screen and (max-width: 505px) {
		.titulo {
			font-size: 40px;
		}
		.contenido {
			font-size: 15px;
		}
        .img {
            width: 300px;
            height: auto;
        }
	}
</style>';

print load_fiche_titre($langs->trans("Recursos Humanos"), '', 'object_informacion_formacion.png@recursoshumanos');

print '<div class="fichecenter">';

print '<div class="contenedorTi">';
print '<span class="titulo">¡Bienvenido, '.$user->firstname.'!</span>';
print '</div>';

print '<div class="contenedorCont">';
print '<span class="contenido">Este es el módulo de recursos humanos. Desde aquí podrás responder encuestas, ver noticias de interés, ver tutoriales de uso de Dolibarr o atender tareas o asuntos que te soliciten. Empieza a navegar desde las opciones del menú lateral izquierdo.</span>';
print '</div>';

print '<div class="contenedorImg">';
print '<img src="img/banner.jpg" class="img">';
print '</div>';

print '<div class="fichethirdleft">';
print '</div>';

print '<div class="fichetwothirdright">';

print '<div class="ficheaddleft">';


$NBMAX = $conf->global->MAIN_SIZE_SHORTLIST_LIMIT;
$max = $conf->global->MAIN_SIZE_SHORTLIST_LIMIT;

print '</div></div></div>';

// End of page
llxFooter();
$db->close();
