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
require_once('funciones_generar.php');

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

$NBMAX = $conf->global->MAIN_SIZE_SHORTLIST_LIMIT;
$max = $conf->global->MAIN_SIZE_SHORTLIST_LIMIT;

$fecha = new DateTime();
$fecha->modify('first day of this month');
$primer_dia = $fecha->format('Y-m-d');

//MsgId
$today = date('ymd') . "01";
$msgId = $today . date('YmdHis');

//CreDtTm
$hora_atom = date(DATE_ATOM);
$creDtTm = substr($hora_atom, 0, -6);

//OBTENEMOS LOS DATOS DE LA ORGANIZACIÓN
//NOMBRE
$consulta = "SELECT c.value, b.iban_prefix FROM ".MAIN_DB_PREFIX."const c, ".MAIN_DB_PREFIX."bank_account b
    WHERE  name = 'MAIN_INFO_SOCIETE_NOM'";
$resultadoNom = $db->query($consulta);
$Org = $db->fetch_object($resultadoNom);

$nombreOrg = $Org->value;
$bicOrg = $Org->iban_prefix;
$bicOrg2 = calculoBic($bicOrg);

//CALLE
$consulta = "SELECT c.value, b.iban_prefix FROM ".MAIN_DB_PREFIX."const c, ".MAIN_DB_PREFIX."bank_account b
    WHERE  name = 'MAIN_INFO_SOCIETE_ADDRESS'";
$resultadoAdd = $db->query($consulta);
$datos = $db->fetch_object($resultadoAdd);

$direccion = $datos->value;

//CP
$consulta = "SELECT c.value, b.iban_prefix FROM ".MAIN_DB_PREFIX."const c, ".MAIN_DB_PREFIX."bank_account b
    WHERE  name = 'MAIN_INFO_SOCIETE_ZIP'";
$resultadoZip= $db->query($consulta);
$datos = $db->fetch_object($resultadoZip);

$zip = $datos->value;

//CIUDAD
$consulta = "SELECT c.value, b.iban_prefix FROM ".MAIN_DB_PREFIX."const c, ".MAIN_DB_PREFIX."bank_account b
    WHERE  name = 'MAIN_INFO_SOCIETE_TOWN'";
$resultadoTown= $db->query($consulta);
$datos = $db->fetch_object($resultadoTown);

$ciudad = $datos->value;

//PAIS
$consulta = "SELECT c.value, b.iban_prefix FROM ".MAIN_DB_PREFIX."const c, ".MAIN_DB_PREFIX."bank_account b
    WHERE  name = 'MAIN_INFO_SOCIETE_COUNTRY'";
$resultadoCountry= $db->query($consulta);
$datos = $db->fetch_object($resultadoCountry);

$pais = $datos->value;

preg_match('#\:(.*?)\:#', $pais, $match);
$pais = $match[1];

//SIREN
$consulta = "SELECT c.value, b.iban_prefix FROM ".MAIN_DB_PREFIX."const c, ".MAIN_DB_PREFIX."bank_account b
    WHERE  name = 'MAIN_INFO_SIREN'";
$resultadoSiren= $db->query($consulta);
$datos = $db->fetch_object($resultadoSiren);

$siren = $datos->value;
$num = idAcreditador($siren);

//VALORES FINALES
$nombreOrg; //nombre
$bicOrg;    //IBAN
$bicOrg2;   //bic
$direccion; //calle
$zip;   //cp
$ciudad;    //ciudad
$direccionFinal = $direccion.", ".$zip.", ".$ciudad;
$pais;  //pais
$siren; //cif
$num;   //idcif

$cif = 'ES33100'.$siren;
$cod_seg = $siren.''.date('YmdHis')."001";
$ano = date('Y');
$aux = 0;
$cont = 0;
$total = 0;

$fecha_actual = date("Y-m-d");
$fecha_ven= date("Y-m-d",strtotime($fecha_actual."+ 2 days"));

//CONSEGUIR EL ARRAY DE COBRADORES

//RECORRER ARRAY DE COBRADORES
$cobradores = obtenerCobradores($db);

for ($i = 0; $i < count($cobradores); $i++) {

    $total+= $cobradores[$i]->__get('cantidad');

    ++$cont;

}

$total=number_format($total,2,'.','');

if (isset($_POST['xml'])) {

    $xml = new XMLWriter();
    $xml->openMemory();
    $xml->setIndent(true);
    $xml->setIndentString('	'); 
    $xml->startDocument('1.0', 'UTF-8');
    $xml->startElement("Document"); 
    $xml->writeAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
    $xml->writeAttribute('xmlns', 'urn:iso:std:iso:20022:tech:xsd:pain.008.001.02');
    $xml->startElement("CstmrDrctDbtInitn"); 
        
        $xml->startElement("GrpHdr");
            $xml->writeElement("MsgId", $msgId);
            $xml->writeElement("CreDtTm", $creDtTm);
            $xml->writeElement("NbOfTxs", $cont);
            $xml->writeElement("CtrlSum", $total);
            $xml->startElement("InitgPty");
                $xml->writeElement("Nm", $nombreOrg);
                $xml->startElement("Id");
                    $xml->startElement("OrgId");
                        $xml->startElement("Othr");
                            $xml->writeElement("Id", $cif);
                            $xml->startElement("SchmeNm");
                                $xml->writeElement("Prtry", "SEPA");
                            $xml->endElement();
                            $xml->writeElement("Issr", "ISO");
                        $xml->endElement();
                    $xml->endElement();
                $xml->endElement(); 
            $xml->endElement(); 
    $xml->endElement(); 

    $xml->startElement("PmtInf");
            $xml->writeElement("PmtInfId", $cod_seg);
            $xml->writeElement("PmtMtd", "DD");
            $xml->writeElement("BtchBookg", "false");
            $xml->writeElement("NbOfTxs", $cont);
            $xml->writeElement("CtrlSum",  $total);
            $xml->startElement("PmtTpInf");
                $xml->startElement("SvcLvl");
                    $xml->writeElement("Cd", "SEPA");
                $xml->endElement();
                $xml->startElement("LclInstrm");
                    $xml->writeElement("Cd", "CORE");
                $xml->endElement();
                $xml->writeElement("SeqTp", "RCUR");
            $xml->endElement(); 
            $xml->writeElement("ReqdColltnDt", $fecha_ven);
            $xml->startElement("Cdtr");
                $xml->writeElement("Nm", $nombreOrg);
                $xml->startElement("PstlAdr");
                    $xml->writeElement("Ctry", $pais);
                    $xml->writeElement("AdrLine", $direccionFinal);
                $xml->endElement();
            $xml->endElement();    
            $xml->startElement("CdtrAcct");
                $xml->startElement("Id");
                    $xml->writeElement("IBAN", $bicOrg);
                $xml->endElement(); 
            $xml->endElement(); 
            $xml->startElement("CdtrAgt");
                $xml->startElement("FinInstnId");
                    $xml->writeElement("BIC", $bicOrg2);
                $xml->endElement(); 
            $xml->endElement(); 
            $xml->writeElement("ChrgBr", "SLEV");

            $xml->startElement("CdtrSchmeId");
                $xml->startElement("Id");
                    $xml->startElement("PrvtId");
                        $xml->startElement("Othr");
                            $xml->writeElement("Id", $cif);
                            $xml->startElement("SchmeNm");
                                $xml->writeElement("Prtry", "SEPA");
                            $xml->endElement(); 
                        $xml->endElement(); 
                    $xml->endElement(); 
                $xml->endElement(); 
            $xml->endElement(); 
        
            for ($i = 0; $i < count($cobradores); $i++) {
                $nombreCobrador = $cobradores[$i]->__get('nombreApell');
                if($nombreCobrador==', '){
                    $nombreCobrador = $nombreOrg;
                }

                $cantidad = number_format($cobradores[$i]->__get('cantidad'),2,'.','');

                $bic = calculoBic($cobradores[$i]->__get('iban'));
                $xml->startElement("DrctDbtTxInf");
                    $xml->startElement("PmtId");
                        $xml->writeElement("EndToEndId", "45409".++$aux);
                    $xml->endElement(); 
                    $xml->startElement("InstdAmt");
                        $xml->writeAttribute('Ccy', 'EUR');
                        $xml->text($cantidad);
                    $xml->endElement();
                    $xml->startElement("DrctDbtTx");
                        $xml->startElement("MndtRltdInf");
                            $xml->writeElement("MndtId", $cobradores[$i]->__get("fk_object"));
                            $xml->writeElement("DtOfSgntr", "2009-10-31");
                            $xml->writeElement("AmdmntInd", "false");
                        $xml->endElement(); 
                    $xml->endElement();

                    $xml->startElement("DbtrAgt");
                        $xml->startElement("FinInstnId");
                            $xml->writeElement("BIC", $bic);
                        $xml->endElement(); 
                    $xml->endElement(); 

                    $xml->startElement("Dbtr");
                        $xml->writeElement("Nm",$nombreCobrador);
                        $xml->startElement("PstlAdr");
                            $xml->writeElement("Ctry", $cobradores[$i]->__get('pais'));
                            $xml->writeElement("AdrLine", $cobradores[$i]->__get('direccion'));                   
                        $xml->endElement(); 

                        $xml->startElement("Id");
                            $xml->startElement("PrvtId"); 
                                $xml->startElement("Othr"); 
                                    $xml->writeElement("Id", $cobradores[$i]->__get('dni'));                   
                                    $xml->startElement("SchmeNm"); 
                                        $xml->writeElement("Prtry", "SEPA");                   
                                    $xml->endElement();
                                    $xml->writeElement("Issr", "ISO");                   
                                $xml->endElement();                 
                            $xml->endElement();                  
                        $xml->endElement();                 

                    $xml->endElement(); 

                    $xml->startElement("DbtrAcct");
                            $xml->startElement("Id");
                                $xml->writeElement("IBAN", $cobradores[$i]->__get('iban'));                   
                            $xml->endElement(); 
                        $xml->endElement(); 
                    $xml->startElement("RmtInf");
                            $xml->writeElement("Ustrd", "ABONO NÓMINA");  
                    $xml->endElement(); 
                $xml->endElement(); 
            }

    $xml->endElement();
    $xml->endElement();
    $xml->endElement();

    $content = $xml->outputMemory();
    ob_end_clean();
    ob_start();
    header('Content-Type: application/xml; charset=UTF-8');
    header('Content-Encoding: UTF-8');
    header("Content-Disposition: attachment;filename=sepa.xml");
    header('Expires: Jue, 21 Oct 2017 07:28:00 GMT');
    header('Pragma: cache');
    header('Cache-Control: private');
    echo $content;

} else {

    $input = "MsgId;CreDtTm;NbOfTxs;CtrlSum;Nm;Id;Prtry;Issr";
    $input .= "\r\n";
    $nombreOrg = str_replace(" ", "", $nombreOrg);
    $nombreOrg = str_replace(",", "", $nombreOrg);
    $input .= $msgId . ";" . $creDtTm . ";" . $cont . ";" . $total . ";" . $nombreOrg . ";" . $cif . ";SEPA;ISO" ;

    $input .= "\r\n";
    $input .= "\r\n";

    $input .= "PmtInfId;PmtMtd;BtchBookg;NbOfTxs;CtrlSum;SvcLvl-Cd;LclInstrm-Cd;SeqTp;ReqdColltnDt;Nm;Ctry;AdrLine;IBAN;BIC;ChrgBr;Id;Prtry";
    $input .= "\r\n";
    $direccionFinal = str_replace(" ", "", $direccionFinal);
    $direccionFinal = str_replace(",", "-", $direccionFinal);
    $input .= $cod_seg . ";DD;false;" . $cont . ";" . $total . ";SEPA;CORE;RCUR;" . $fecha_ven . ";" . $nombreOrg . ";" . $pais . ";" . $direccionFinal . ";" . $bicOrg . ";" . $bicOrg2 . ";SLEV;" . $cif . ";SEPA" ;

    $input .= "\r\n";
    $input .= "\r\n";

    $input .= "EndToEndId;InstdAmt(EUR);MndtId;DtOfSgntr;AmdmntInd;BIC;Nm;Ctry;AdrLine;DNI;Prtry;Issr;IBAN;Ustrd";

    for ($i = 0; $i < count($cobradores); $i++) {
        $input .= "\r\n";
        $nombreCobrador = $cobradores[$i]->__get('nombreApell');
        if($nombreCobrador==', '){
            $nombreCobrador = $nombreOrg;
        }

        $bic = calculoBic($cobradores[$i]->__get('iban'));

        $cantidad = number_format($cobradores[$i]->__get('cantidad'),2,'.','');

        $nombreCobrador = str_replace(" ", "", $nombreCobrador);
        $nombreCobrador = str_replace(",", "-", $nombreCobrador);
        $direccionCobrador = str_replace(",", "-", $cobradores[$i]->__get('direccion'));

        $input .= "45409".++$aux . ";" . $cantidad . ";" . $cobradores[$i]->__get("fk_object") . ";2009-10-31;false;" . $bic . ";" . $nombreCobrador . ";" . $cobradores[$i]->__get('pais') . ";" . $direccionCobrador . ";" . $cobradores[$i]->__get('dni') . ";SEPA;ISO;" . $cobradores[$i]->__get('iban') . ";ABONO NÓMINA";
    }

    $filesize = strlen($input);
    $filename = "abono_nominas.csv";
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Length: '.$filesize);
    header('Content-Disposition: attachment; filename="'.$filename.'"');
    echo $input;

}

?>