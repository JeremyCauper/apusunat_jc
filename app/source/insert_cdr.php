<?php
    require '../config/conEmpresa.php';

    extract($_POST);

    $db = new DatabaseEmpresa();
    $con = $db->conectar($idEmpresa);

    if (!$con) {
        die(json_encode(['success' => false, 'message' => 'La base de datos no respondió.']));
    }
    $data = [];

    if ($estado == '0') {
        $consulta = $con->query("SELECT * FROM nube_cdr_sunat WHERE cod_recibo = '$codigo'");
        $num_filas = $consulta->rowCount();
        if ($num_filas == 0) {
            $insertCdrRc = $con->prepare("INSERT INTO nube_cdr_sunat (ruc_company, numero_identificacion, cod_recibo, serie, correlativo, tipo_recibo, file_xml, file_zip, fecha_registro, result,  xml_firmado, respuesta, codigo) SELECT b.ruc_company, '00000000', a.cod_recibo, 'RC', :serie, 'RC', CONCAT(b.ruc_company,'-',a.cod_recibo,'.xml'), CONCAT('R-',b.ruc_company,'-',a.cod_recibo,'.zip'), a.fecha_emision, :codCdr, :codXml, CONCAT('El Comprobante ',a.cod_recibo,', ha sido aceptada'), 0 FROM nube_resumen a INNER JOIN nube_recibos b ON a.serie_correlativo = b.cod_venta WHERE a.cod_recibo = :codigo LIMIT 1");
            $resultCdrRc = $insertCdrRc->execute(array('codigo' => $codigo, 'serie' => $serie, 'codCdr' => $codCdr, 'codXml' => $codXml));

            if (!$resultCdrRc) {
                die(json_encode(['success' => false, 'message' => 'La ejecución al intentar insertar el cdr del resumen "'.$codigo.'" falló.']));
            }
            $data['fillCdrRc'] = $insertCdrRc->rowCount();
        }
    }

    $updateRes = $con->prepare("UPDATE nube_resumen SET estatus_envio =1 WHERE cod_recibo = '$codigo'");
    $resultEstRes = $updateRes->execute();
    if (!$resultEstRes) {
        die(json_encode(['success' => false, 'message' => 'La ejecución al intentar actualizar el estatdo del resumen '.$codigo.'" falló.']));
    }
    $data['fillRes'] = $updateRes->rowCount();


    $consulta = $con->query("SELECT b.cod_recibo FROM nube_resumen a INNER JOIN nube_cdr_sunat b ON a.serie_correlativo = b.cod_recibo WHERE a.cod_recibo = '$codigo'");
    $num_filas = $consulta->rowCount();
    if ($num_filas == 0) {
        $insertCdrRec = $con->prepare("INSERT INTO nube_cdr_sunat (ruc_company, numero_identificacion, cod_recibo, serie, correlativo, tipo_recibo, file_xml, file_zip, fecha_registro, result,  xml_firmado, respuesta, codigo) select b.ruc_company, b.numero_identificacion, a.serie_correlativo, SUBSTR(a.serie_correlativo,1,4), SUBSTR(a.serie_correlativo,6,8), a.tipo_recibo, CONCAT(b.ruc_company,'-',a.serie_correlativo,'.xml'), CONCAT('R-',b.ruc_company,'-',a.serie_correlativo,'.zip'), b.fecha_registro, b.result,  b.xml_firmado, CONCAT('La Boleta de Venta numero ',a.serie_correlativo,', ha sido aceptado'), b.codigo from nube_resumen a INNER JOIN nube_cdr_sunat b on a.cod_recibo = b.cod_recibo where a.cod_recibo = :codigo");
        $resultCdrRec = $insertCdrRec->execute(array('codigo' => $codigo));
        if (!$resultCdrRec) {
            die(json_encode(['success' => false, 'message' => 'La ejecución al intentar insertar los cdr de los recibos falló.']));
        }
        $data['fillCdrRec'] = $insertCdrRec->rowCount();
    }

    $updateRec = $con->prepare("UPDATE nube_recibos a INNER JOIN nube_cdr_sunat b ON a.cod_venta = b.cod_recibo AND a.tipo_recibo = b.tipo_recibo SET a.estatus_sunat =1 WHERE a.estatus_sunat =0 and a.tipo_recibo in (03,07)");
    $resultEstRec = $updateRec->execute();
    if (!$resultEstRec) {
        die(json_encode(['success' => false, 'message' => 'La ejecución al intentar actualizar el estatdo de los comprobantes del resumen '.$codigo.'" falló.']));
    }
    $data['fillRec'] = $updateRec->rowCount();

    die(json_encode(['success' => true, 'message' => 'Se ejecutaron las consultas con exito.', 'data' => $data]));