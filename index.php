<html>
    <head>
        <meta charset="UTF-8">
        <title></title>
        <link href="http://getbootstrap.com/2.3.2/assets/css/bootstrap.css" rel="stylesheet" media="screen">
        <style>
            table{
                font-size: 11pt;
            }
        </style>
    </head>
    <body>
        <div class="well">
            <form class="form-search" name="frmCodigo" action="" method="post">
                <input type="text" name="txtCodigo" class="input-medium search-query" value="DG231880526BR">
                <button type="submit" class="btn">Search</button>
            </form>
        </div>
        <?php
        if (isset($_POST["txtCodigo"])) {
                include("correios.class.php");
                $api = new hundredXCorreiosAPI();
                $api->setCodigoRastreio($_POST["txtCodigo"]);
                $resultados = $api->getDados();

                if (!key_exists("erro", $resultados)) {
                    #--------------------------------------------------------------
                    ?>
                    <table class="table table-hover" style="width: 900px">
                        <thead>
                            <tr>
                                <td><b>Data</b></td>
                                <td><b>Local</b></td>
                                <td><b>Status</b></td>
                                <td><b>Detalhes</b></td>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            foreach ($resultados as $valor) {
                                ?>
                                <tr>
                                    <td><?= $valor["data"] ?></td>
                                    <td><?= $valor["local"] ?></td>

                                    <td><?= $valor["status"] ?></td>

                                    <td><?= $valor["detalhes"] ?></td>

                                </tr>
                                <?php
                            } 
                        } else {
                            echo "Código de rastreio inválido!";
                        }
                    }                
                ?>
            </tbody>
        </table>
    </body>
</html>
