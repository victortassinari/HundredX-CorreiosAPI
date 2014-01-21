<?php
/*
 DESENVOLVIDO POR VICTOR TASSINARI MARTINS
 * victortassinarix@gmail.com
 * victortassinari@outlook.com
*/
class hundredXCorreiosAPI {

    private $codigoRastreio;

    public function getCodigoRastreio() {
        return $this->codigoRastreio;
    }

    public function setCodigoRastreio($codigoRastreio) {
        $this->codigoRastreio = $codigoRastreio;
    }

    #------- PEGA O CODIGO FONTE DA PÁGINA DE RASTREIO DOS COREIOS ----#
    private function getPaginaCorreios() {
        $url = "http://websro.correios.com.br/sro_bin/txect01$.QueryList?P_LINGUA=001&P_TIPO=001&P_COD_UNI=" . $this->getCodigoRastreio();
        $ch = curl_init();
        $timeout = 5;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        $dados = curl_exec($ch);
        curl_close($ch);

        return utf8_encode($dados);
    }

     #--------- ESTE METODO RETORNA UM ARRAY ASSOCIATIVO COM OS DADOS ---------------#
    public function getDados() {
    /* EXEMPLO DE RETORNO
    ["data"]=>
    string(17) " 16/01/2014 13:12"
    ["local"]=>
    string(7) "IEPE/SP"
    ["detalhes"]=>
    string(1) "-"
    ["status"]=>
    string(34) "Saiu para entrega ao destinatÃ¡rio"
     */
        $resultados = array();

        $dados = $this->getPaginaCorreios();
        
        #---- VERIFICO SE EXISTE A TABELA COM OS DADOS -------#
        if (!preg_match("/<\/TABLE>/s", $dados)) {
            $resultados["erro"] = "Não foi encontrado nenhuma informação.";
        } else {
            #--- PEGO O CONTEÚDO DA TABELA ---------------------#
            preg_match("/<\/tr>(.*)<\/TABLE>/s", $dados, $saida);
            
            #------ FAÇO O TRATAMENTO DOS DADOS, RETIRANDO AS TAGS HTML E SUBSTITUINDO POR PIPES -----#
            $strRes = $saida[0];
            $strRes = str_replace("<FONT COLOR=\"000000\">", "||", $strRes);
            $strRes = str_replace("<tr><td rowspan=2>", "", $strRes);
            $strRes = str_replace("<tr><td colspan=2>", "", $strRes);
            $strRes = str_replace("</td><td>", "||", $strRes);
            $strRes = str_replace("</td></tr>", "||", $strRes);
            $strRes = str_replace("||||", "||", $strRes);
            $strRes = str_replace("</font>", "", $strRes);
            $strRes = strip_tags($strRes);

            #----- EXPRESSÃO REGULAR PARA VALIDAR O FOMATO DE DATA E HORA DOS CORREIOS -------------------#
            $regDataHora = "/([0-2]{1}[0-9]{1}\/[0-1]{1}[0-9]{1}\/[0-9]{4} [0-2]{1}[0-9]{1}:[0-5][0-9])/s";
            #----- PEGO TODAS AS DATAS DA PÁGINA-------------------------------#
            preg_match($regDataHora, $strRes, $datas);
            
            #----- PEGO AS OUTRAS INFORMAÇÕES QUE ESTÃO NA TABELA --------------#
            preg_split($regDataHora, $strRes, -1, PREG_SPLIT_NO_EMPTY);
            
            #----- REMOVO AS QUEBRAS DE LINHA DAS INFORMAÇÕES ----#
            $strRes = \preg_replace('/\s/', ' ', $strRes);
            #----- CRIO UM ARRAY COM AS INFORMAÇÕES --------------#
            $dados = \explode("||", $strRes);

            #--- AQUI MONTO O ARRAY ASSOCIATIVO, CRIANDO ÍNDICES DE ARRAYS --#
            $temp = array();
            $qtde = 0; #QUANTIDADE DE DATAS, INFORMAÇOES DISPONÍVEIS.
            #-- PERCORRO O ARRAY DE DADOS ----------------#
            for ($i = 0; $i < count($dados); $i++) {
                #--- VERIFICO SE É UMA DATA ---------------#
                if (preg_match($regDataHora, $dados[$i])) {
                    $qtdeTMP = 0; #QUANTIDADE DE REGISTROS DA DATA ATUAL
                    $temp["data"] = trim($dados[$i]); #GUARDO A DATA
                    #-- PERCORRO AGORA OS PROXIMOS ÍNDICES DO ARRAY QUE CONTEM OUTRAS INFORMAÇÕES ---#
                    for ($j = $i + 1; $j < count($dados); $j++) {
                        #-- ENQUANTO O ITEM NÃO FOR DATA VOU GUARDANDO O INDICE ATUAL
                        if (!preg_match($regDataHora, $dados[$j])) {
                            if (trim($dados[$j])) {
                                #---AQUI VERIFICO EM QUAL COLUNA VOU ADICIONAR ----#
                                $coluna = "";
                                $valor = trim($dados[$j]);
                                if ($qtdeTMP == 0) {
                                    $coluna = "local";
                                } else if ($qtdeTMP == 1) {
                                    $coluna = "status";
                                } else if ($qtdeTMP == 2) {
                                    $coluna = "detalhes";
                                }
                                #--- GUARDO O VALOR NA COLUNA ENCONTRADA ---#
                                $temp[$coluna] = $valor;
                                $qtdeTMP++;
                            }
                        }
                        #--- SE NÃO HOUVER NENHUMA INFORMAÇÃO DE DETALHES, CRIO O ÍNDICE COM UM VALOR PADRAO --#
                        if (!key_exists("detalhes", $temp)) {
                            $temp["detalhes"] = "-";
                        }
                        #ADICIONA ESTA ARRAY DENTRO DA ARRAY PRINCIPAL
                        $resultados[$qtde] = $temp;
                        #-- SE O ITEM ATUAL FOR UMA DATA, SAIO DA REPETIÇÃO E PARA CRIAR UM NOVO ÍNDICE NO ARRAY
                        if (preg_match($regDataHora, $dados[$j])) {
                            $temp = array();
                            $qtde++;
                            break;
                        }
                    }
                }
            }
        }
        return $resultados;
    }

    public function getDadosJson() {
    /* EXEMPLO DE RETORNO
    [
    {
        "data": " 16\/01\/2014 13:12",
        "local": "IEPE\/SP",
        "detalhes": "-",
        "status": "Saiu para entrega ao destinat\u00e1rio"
    }
    ]
     */
        return json_encode($this->getDados(), JSON_PRETTY_PRINT);
    }

}
