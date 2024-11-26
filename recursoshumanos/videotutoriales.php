<?php
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




/*
 * View
 */

$form = new Form($db);
$formfile = new FormFile($db);

//Título de la página
llxHeader("", $langs->trans("Tutoriales"));

//Estilos
print '<style>
	.imgTuto {
		border: 1px solid black !important;
		margin-bottom: 50px;
		filter: drop-shadow(8px 8px 8px rgba(0,0,0,0.8));
	}
	.contenido {
		margin-bottom: 20px;
	}
	.titulo {
		font-size: 25px;
		font-weight: bold;
		margin-top: 120px;
		margin-bottom: 50px;
	}
	@media screen and (max-width: 1750px) {
		.seis {
			width: 100%;
		}
	}
	@media screen and (max-width: 1450px) {
		.dos {
			width: 100%;
		}
	}
	@media screen and (max-width: 1400px) {
		.tres {
			width: 100%;
		}
	}
	@media screen and (max-width: 980px) {
		.siete {
			width: 100%;
		}
	}
	@media screen and (max-width: 800px) {
		.cinco {
			width: 100%;
		}
	}
	@media screen and (max-width: 1685px) {
		.doscuatro {
			width: 100%;
		}
	}
	@media screen and (max-width: 1345px) {
		.dosdos {
			width: 100%;
		}
	}
	@media screen and (max-width: 980px) {
		.doscinco {
			width: 100%;
		}
	}
	.nueva {
		display: flex;
		justify-content: center;
	}
	.tutorial {
		margin-top: 80px;
		width: 800px;
	}
	@media screen and (max-width: 800px) {
		.tutorial {
			width: 500px;
		}
	}
	@media screen and (max-width: 500px) {
		.tutorial {
			width: 300px;
		}
	}
	.aviso {
		font-style: italic;
		color: grey;
	}
	.paso {
		font-weight: bold;
		font-size: 16px;
		color: rgb(38,60,92);
	}
	li {
		font-weight: bold;
	}
</style>';

//Cabecera de la página
print load_fiche_titre($langs->trans("Tutoriales"), '', 'object_informacion_formacion.png@recursoshumanos');

print '<div class="aviso" style="font-style:italic;color:grey;margin-bottom:20px;margin-left:10px">';
print '<span>Aquí tienes a tu alcance diversos tutoriales que puedenayudarte a entender mejor Dolibarr y su funcionamiento</span>';
print '</div>';

print '<div class="fichecenter">';
print '<div class="fichethirdleft">';

print '<ol>
	<li><a href="videotutoriales.php?action=show1">Creación de módulos personalizados (custom) en Dolibarr</a></li>
	<li><a href="videotutoriales.php?action=show2">Creación de campos extra en módulos de Dolibarr</a></li>
</ol>';

print '</div>';

print '<div class="fichethirdright">';

print '</div>';

print '</div>';

//Si no hemos clickado en ningún tutorial, se muestra la imagen inicial
if ($action != "show1" && $action != "show2") {
	print '<div class="fichecenter nueva">';
	print '<img src="img/tutorial.jpg" class="tutorial">';
	print '</div>';
}

//Si hemos clickado en el primer tutorial, se muestra
if ($action == 'show1') {

	print '<div class="fichecenter">';

	print '<div class="contenido">';

	//Contenido del tutorial, con pasos e imágenes
	print '<div class="titulo"><span>TUTORIAL 1: Creación de módulos personalizados (custom) en Dolibarr</span></div>';
	print '<div class="contenido"><span><span class="paso">Paso 1:</span> Primero tendremos que irnos al menú superior, en la parte superior derecha, y le daremos al icono que aparece rodeado en la imagen, llamado Módulo Builder.</span></div>';
	print '<img src="img/tuto/show1.png" class="imgTuto">';
	print '<div class="contenido"><span><span class="paso">Paso 2:</span> Le damos a "Nuevo Módulo", introducimos el nombre que queramos ponerle a nuestro módulo y le damos al botón "Crear".</span></div>';
	print '<img src="img/tuto/show2.png" class="imgTuto dos">';
	print '<div class="contenido"><span><span class="paso">Paso 3:</span> Nuestro módulo nuevo aparecerá en la lista con el resto. Tendremos que activarlo como aparece en pantalla.</span></div>';
	print '<img src="img/tuto/show3.png" class="imgTuto tres">';
	print '<div class="contenido"><span><span class="paso">Paso 4:</span> Justo debajo nos saldrán todas las pestañas de opciones de nuestro módulo. La más importante en este caso será la de "Objects", que nos permitirá crear los objetos que necesitemos para nuestro módulo.</span></div>';
	print '<div class="contenido"><span><span class="paso">Paso 5:</span> Haremos  click en ella, le daremos a "Nuevo objeto", introducimos el nombre que queramos, marcamos el botón de "La referencia del objeto debe generarse automáticamente", y le damos al botón "Generar" que viene justo debajo.</span></div>';
	print '<img src="img/tuto/show4.png" class="imgTuto cinco">';
	print '<div class="contenido"><span><span class="paso">Paso 6:</span> Al hacer eso, nos creará automáticamente todos los archivos necesarios, incluída la tabla, son su fichero .sql.</span></div>';
	print '<img src="img/tuto/show5.png" class="imgTuto seis">';
	print '<div class="contenido"><span><span class="paso">Paso 7:</span> En ese archivo .sql viene definida toda la tabla, que tendrá unos cuantos campos por defecto. Solo tendremos que modificar dicho archivo e introducir los campos que queramos, con todos sus atributos.</span></div>';
	print '<img src="img/tuto/show7.png" class="imgTuto siete">';
	print '<div class="contenido"><span><span class="paso">Paso 8:</span> Ahora solo tenemos que destruir la tabla anterior (que debe estar vacía), darle al botón de "Forzar actualización de archivos", y volver a iniciar el módulo (como viene en el paso 3).</span></div>';
	print '<img src="img/tuto/show6.png" class="imgTuto seis">';
	print '<div class="contenido"><span><span class="paso">Paso 9:</span> Si quisiéramos eliminar el objeto, solo tendríamos que darle a "Zona peligrosa", introducir el nombre del mismo y darle a "Eliminar".</span></div>';
	print '<img src="img/tuto/show8.png" class="imgTuto siete">';

	print '</div>';

	print '</div>';

//Si hemos clickado en el segundo tutorial, se muestra
} else if ($action == 'show2') {

	print '<div class="fichecenter">';

	print '<div class="contenido">';

	//Contenido del tutorial, con pasos e imágenes
	print '<div class="titulo"><span>TUTORIAL 2: Creación de campos extra en módulos de Dolibarr</span></div>';
	print '<div class="contenido"><span><span class="paso">Paso 1:</span> Tendremos que ir a "Inicio"->"Configuración" y hacer click en "Módulos".</span></div>';
	print '<img src="img/tuto/show9.png" class="imgTuto">';
	print '<div class="contenido"><span><span class="paso">Paso 2:</span> Nos aparecerá un listado con todos los módulos disponibles en Dolibarr, con filtros de búsqueda. Buscamos el nuestro y le damos a la rueda de configuración.</span></div>';
	print '<img src="img/tuto/show10.png" class="imgTuto dosdos">';
	print '<div class="contenido"><span><span class="paso">Paso 3:</span> Aparecerá toda la información relacionada con el módulo en cuestión. En la cabecera, aparecerán varias pestañas, y algunas de ellas relacionadas con todos los tipos de campos externos que se pueden añadir, que son las que nos interesan.</span></div>';
	print '<img src="img/tuto/show11.png" class="imgTuto dosdos">';
	print '<div class="contenido"><span><span class="paso">Paso 4:</span> Veremos un listado con todos los campos extras ya introducidos, así como un botón de "Nuevo campo". Haremos click en él.</span></div>';
	print '<img src="img/tuto/show12.png" class="imgTuto doscuatro">';
	print '<div class="contenido"><span><span class="paso">Paso 5:</span> Veremos un formulario con todos los datos a rellenar para nuestro nuevo campo.</span></div>';
	print '<img src="img/tuto/show13.png" class="imgTuto doscinco">';
	print '<div class="contenido"><span><span class="paso">Paso 6:</span> Una vez los hayamos rellenado y le hayamos dado a "Grabar", nos aparecerá nuestro campo en el listado, y ya estará creado. Hay que recordar que ese campo, en nuestra BBDD, aparecerá en la tabla "nombremodulo_extrafields", y no en la tabla original.</span></div>';
	print '<img src="img/tuto/show14.png" class="imgTuto doscuatro">';

	print '</div>';

	print '</div>';

}

// End of page
llxFooter();
$db->close();