<?php
    // $id = isset($_GET['id']) ? $_GET['id'] : die('Debe ingresar el id de una empresa.');
?>


<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../app/css/icons/bootstrap-icons.css">
    <title>Envio RC</title>
</head>
<style>
    #load-spinner {
        position: absolute;
        display: grid;
        place-items: center;
        top: 0;
        left: 0;
        height: 300px;
        width: 100%;
        background-color: #aaaaaa6e;
    }
</style>

<body class="p-2">
    <div>
        <h5>Enviando resumenes</h5>
        <div class="mb-2">
            <nav class="navbar bg-primary">
                <div class="container-fluid">
                    <form class="d-flex" role="search">
                        <input class="form-control form-control-sm me-2" id="input-search" type="search"
                            placeholder="Search" aria-label="Search">
                        <button class="btn btn-light btn-sm" id="btn-sl" type="button">
                            <i class="bi bi-search"></i>
                        </button>
                    </form>
                </div>
            </nav>
            <div style="position: relative" id="cont-frame">
                <div id="load-spinner" class="d-none">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
                <iframe id="fr_envios" class="border border-primary w-100" src="" allow="clipboard-write" height="300px"
                    frameborder="0"></iframe>
            </div>
            <button class="btn btn-primary" onclick="insertCdr()">Insertar Cdr</button>
        </div>
        <textarea id="text-area" hidden rows="10"></textarea>

        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
        <script>

            var array = {};

            $('#btn-sl').on('click', function () {
                $('#load-spinner').removeClass('d-none');
                $("#fr_envios").attr("src", '../app/source/resumenes.php?id=' + $('#input-search').val());
            });

            var tiempoEspera = 60000; // 60 segundos

            var temporizador = setTimeout(function () {
                console.log('El tiempo de espera ha transcurrido.');
            }, tiempoEspera);

            $('#fr_envios').on('load', function () {
                clearTimeout(temporizador);
                $('#load-spinner').addClass('d-none');
                console.log('El iframe ha terminado de cargar.');

                var html_rc = $("#fr_envios").contents().find("body").text();

                if (html_rc.includes('XML Firmado:')) {
                    html_rc = html_rc.replaceAll('XML Firmado:', '\nXML Firmado:')
                }
                if (html_rc.includes('Ticket :')) {
                    html_rc = html_rc.replaceAll('Ticket :', '\nTicket :');
                }
                if (html_rc.includes('CDR en BASE64:')) {
                    html_rc = html_rc.replaceAll('CDR en BASE64:', '\nCDR en BASE64:');
                }

                $('#text-area').val(html_rc);

                html_rc = html_rc.split('\n');

                for (let i = 0; i < html_rc.length; i++) {
                    if (i == 0)
                        array.cod = (html_rc[0].match(/RC-\d{8}-\d{3}/))[0];
                    else {
                        if (html_rc[i].includes("XML Firmado: P"))
                            array.xml = html_rc[i].replace('XML Firmado: ', '');
                        if (html_rc[i].includes("CDR en BASE64: U"))
                            array.cdr = html_rc[i].replace('CDR en BASE64: ', '');
                        else
                            array.cdr = 'CDR';
                        if (html_rc[i].includes("Código:"))
                            array.status = html_rc[i + 1] == 0 ? 1 : 0;
                    }
                }
                console.log(array);
            });

            async function insertCdr() {
                try {
                    var parametros = {
                        idEmpresa: $('#input-search').val(),
                        codigo: array.cod,
                        serie: (array.cod).split('-')[2],
                        codXml: array.xml,
                        codCdr: array.cdr,
                        estado: array.status
                    };

                    const response = await $.ajax({
                        url: '../app/source/insert_cdr.php',
                        method: 'POST',
                        data: parametros
                    });
                    console.log(response);
                } catch (error) {
                    console.log(error);
                }
            }
        </script>
</body>

</html>