<?php

    $form = new Form($db);
    $notes_user = $user->id;
    $tuser = new User($db);

    require_once('cobrador.php');

    function obtenerCobradores($db) {
        
        $consulta = "SELECT u.rowid, ue.dni, u.firstname, u.address, u.lastname, u.zip, u.town, c.code, ue.salario_liquido, ue.salario_bruto, ue.coste_empresa, ue.iban FROM ".MAIN_DB_PREFIX."user u INNER JOIN ".MAIN_DB_PREFIX."user_extrafields ue ON u.rowid = ue.fk_object INNER JOIN ".MAIN_DB_PREFIX."c_country c ON c.rowid = u.fk_country";

        $resultado = $db->query($consulta);
        $num_cobradores = $db->num_rows($resultado);
        
        $listaCobradores = array();
        
        $i = 0;
        while ($i < $num_cobradores) {

            $datos = $db->fetch_object($resultado);

            $Cobrador = new Cobrador();
        
            $Cobrador->__set("fk_object", $datos->rowid);
            $Cobrador->__set("dni", $datos->dni);
            $nombre = $datos->lastname . ", " . $datos->firstname;
            $Cobrador->__set("nombreApell", $nombre);
            $direccion = $datos->address.", ".$datos->zip . ", " . $datos->town;//$fila['address'] . " " . 
            $Cobrador->__set("direccion", $direccion);
            
            $Cobrador->__set("pais", $datos->code);
            $Cobrador->__set("cantidad", $datos->salario_liquido);
            $Cobrador->__set("iban", $datos->iban);
            $Cobrador->__set("salario_bruto", $datos->salario_bruto);
            $Cobrador->__set("salario_liquido", $datos->salario_liquido);
            $Cobrador->__set("coste_empresa", $datos->coste_empresa);

            $listaCobradores[] = $Cobrador;

            $i++;
            
        }

        return $listaCobradores;
    }

    function idAcreditador($cif) {

        $letra = substr($cif,0, 1); 
        $num = substr($cif,1); 

        $cal = array(
            'A' => '10',
            'B' => '11',
            'C' => '12',
            'D' => '13',
            'E' => '14',
            'F' => '15',
            'G' => '16',
            'H' => '17',
            'I' => '18',
            'J' => '19',
            'K' => '20',
            'L' => '21',
            'M' => '22',
            'N' => '23',
            'O' => '24',
            'P' => '25',
            'Q' => '26',
            'R' => '27',
            'S' => '28',
            'T' => '29',
            'U' => '30',
            'V' => '31',
            'W' => '32',
            'X' => '33',
            'Y' => '34',
            'Z' => '35',
        );

        foreach($cal as $clave=>$valor)
        {
            if($clave == $letra){
                $total=$valor.''.$num;
                $resto= fmod($total, 97);
                $fin= 98-$resto;
                return $fin;
            }
        }

    }

    function calculoBic($entidad) {
        
        $entidad = substr($entidad,4, 4); 

        $bic = array(
            '0003' => 'BDEPESM1XXX',
            '0030' => 'ESPCESMMXXX',
            '0031' => 'ETCHES2GXXX',
            '0036' => 'SABNESMMXXX',
            '0046' => 'GALEES2GXXX',
            '0049' => 'BSCHESMMXXX',
            '0057' => 'BVADESMMXXX',
            '0058' => 'BNPAESMMXXX',
            '0059' => 'MADRESMMXXX',
            '0061' => 'BMARES2MXXX',
            '0065' => 'BARCESMMXXX',
            '0073' => 'OPENESMMXXX',
            '0075' => 'POPUESMMXXX',
            '0078' => 'BAPUES22XXX',
            '0081' => 'BSABESBBXXX',
            '0083' => 'RENBESMMXXX',
            '0086' => 'NORTESMMXXX',
            '0094' => 'BVALESMMXXX',
            '0122' => 'CITIES2XXXX',
            '0125' => 'BAOFESM1XXX',
            '0128' => 'BKBKESMMXXX',
            '0130' => 'CGDIESMMXXX',
            '0133' => 'MIKBESB1XXX',
            '0136' => 'AREBESMMXXX',
            '0138' => 'BKOAES22XXX',
            '0149' => 'BNPAESMSXXX',
            '0167' => 'GEBAESMMXXX',
            '0182' => 'BBVAESMMXXX',
            '0184' => 'BEDFESM1XXX',
            '0186' => 'BFIVESBBXXX',
            '0188' => 'ALCLESMMXXX',
            '0190' => 'BBPIESMMXXX',
            '0196' => 'WELAESMMXXX',
            '0198' => 'BCOEESMMXXX',
            '0200' => 'PRVBESB1XXX',
            '0211' => 'PROAESMMXXX',
            '0216' => 'POHIESMMXXX',
            '0219' => 'BMCEESMMXXX',
            '0220' => 'FIOFESM1XXX',
            '0224' => 'SCFBESMMXXX',
            '0227' => 'UNOEESM1XXX',
            '0229' => 'POPLESMMXXX',
            '0231' => 'DSBLESMMXXX',
            '0232' => 'INVLESMMXXX',
            '0233' => 'POPIESMMXXX',
            '0234' => 'CCOCESMMXXX',
            '0235' => 'PICHESMMXXX',
            '0236' => 'LOYIESMMXXX',
            '0237' => 'CSURES2CXXX',
            '0238' => 'PSTRESMMXXX',
            '0239' => 'EVOBESMMXXX',
            '0487' => 'GBMNESMMXXX',
            '1459' => 'PRABESMMXXX',
            '1460' => 'CRESESMMXXX',
            '1465' => 'INGDESMMXXX',
            '1474' => 'CITIESMXXXX',
            '1475' => 'CCSEESM1XXX',
            '1490' => 'SELFESMMXXX',
            '1491' => 'TRIOESMMXXX',
            '1524' => 'UBIBESMMXXX',
            '1525' => 'BCDMESMMXXX',
            '1534' => 'KBLXESMMXXX',
            '1544' => 'BACAESMMXXX',
            '2000' => 'CECAESMMXXX',
            '2013' => 'CESCESBBXXX',
            '2031' => 'CECAESMM031',
            '2038' => 'CAHMESMMXXX',
            '2043' => 'CECAESMM043',
            '2045' => 'CECAESMM045',
            '2048' => 'CECAESMM048',
            '2051' => 'CECAESMM051',
            '2056' => 'CECAESMM056',
            '2066' => 'CECAESMM066',
            '2080' => 'CAGLESMMXXX',
            '2085' => 'CAZRES2ZXXX',
            '2086' => 'CECAESMM086',
            '2095' => 'BASKES2BXXX',
            '2096' => 'CSPAES2LXXX',
            '2099' => 'CECAESMM099',
            '2100' => 'CAIXESBBXXX',
            '2103' => 'UCJAES2MXXX',
            '2104' => 'CSSOES2SXXX',
            '2105' => 'CECAESMM105',
            '3001' => 'BCOEESMM001',
            '3005' => 'BCOEESMM005',
            '3007' => 'BCOEESMM007',
            '3008' => 'BCOEESMM008',
            '3009' => 'BCOEESMM009',
            '3016' => 'BCOEESMM016',
            '3017' => 'BCOEESMM017',
            '3018' => 'BCOEESMM018',
            '3020' => 'BCOEESMM020',
            '3023' => 'BCOEESMM023',
            '3025' => 'CDENESBBXXX',
            '3029' => 'CCRIES2A029',
            '3035' => 'CLPEES2MXXX',
            '3045' => 'CCRIES2A045',
            '3058' => 'CCRIES2AXXX',
            '3059' => 'BCOEESMM059',
            '3060' => 'BCOEESMM060',
            '3063' => 'BCOEESMM063',
            '3067' => 'BCOEESMM067',
            '3070' => 'BCOEESMM070',
            '3076' => 'BCOEESMM076',
            '3080' => 'BCOEESMM080',
            '3081' => 'BCOEESMM081',
            '3084' => 'CVRVES2BXXX',
            '3085' => 'BCOEESMM085',
            '3089' => 'BCOEESMM089',
            '3095' => 'CCRIES2A095',
            '3096' => 'BCOEESMM096',
            '3098' => 'BCOEESMM098',
            '3102' => 'BCOEESMM102',
            '3104' => 'BCOEESMM104',
            '3105' => 'CCRIES2A105',
            '3110' => 'BCOEESMM110',
            '3111' => 'BCOEESMM111',
            '3112' => 'CCRIES2A112',
            '3113' => 'BCOEESMM113',
            '3115' => 'BCOEESMM115',
            '3116' => 'BCOEESMM116',
            '3117' => 'BCOEESMM117',
            '3118' => 'CCRIES2A118',
            '3119' => 'CCRIES2A119',
            '3121' => 'CCRIES2A121',
            '3123' => 'CCRIES2A123',
            '3127' => 'BCOEESMM127',
            '3130' => 'BCOEESMM130',
            '3134' => 'BCOEESMM134',
            '3135' => 'CCRIES2A135',
            '3138' => 'BCOEESMM138',
            '3140' => 'BCOEESMM140',
            '3144' => 'BCOEESMM144',
            '3146' => 'CCCVESM1XXX',
            '3150' => 'BCOEESMM150',
            '3152' => 'CCRIES2A152',
            '3157' => 'CCRIES2A157',
            '3159' => 'BCOEESMM159',
            '3160' => 'CCRIES2A160',
            '3162' => 'BCOEESMM162',
            '3165' => 'CCRIES2A165',
            '3166' => 'BCOEESMM166',
            '3174' => 'BCOEESMM174',
            '3179' => 'CCRIES2A179',
            '3183' => 'CASDESBBXXX',
            '3186' => 'CCRIES2A186',
            '3187' => 'BCOEESMM187',
            '3190' => 'BCOEESMM190',
            '3191' => 'BCOEESMM191',
            '9000' => 'ESPBESMMXXX'
        );

        foreach($bic as $clave=>$valor)
        {
            if($clave == $entidad){
                return $valor;
            }
        }
        return '0000';
        
    }

?>