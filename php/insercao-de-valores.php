<?php
require_once("custom/php/common.php");
if (checkCapability("insert_values")) {
    $myDB = connect();
    if (!mysqli_select_db($myDB, "bitnami_wordpress")) {//verifica conecao com a base de dados
        die("Connection failed:" . mysqli_connect_error());
    } else {//conecao feita com a base de dados
        if (!isset($_REQUEST['estado'])) {//nenhum estado inserido
            echo "<h3 class='form_input_title'>Inserção de valores - criança - procurar</h3>";
            echo "<form method='post' action=" . $current_page . ">
        <input type='text' name='nome' placeholder='Nome criança'>
        <input type='text' name='data' placeholder='AAAA-MM-DD'>
        <input type='hidden' name='estado' value='escolher_crianca'>
        <hr>
        <button type='submit' class='continueButton'>Procurar</button>
        </form>";
        } else if (isset($_REQUEST['estado']) && $_REQUEST['estado'] == 'escolher_crianca') {//primeiro estado de escolher crianca
            echo "<h3 class='form_input_title'>Inserção de valores - criança - escolher</h3>";
            $nome = ($_REQUEST["nome"]);
            $data = ($_REQUEST["data"]);
            if (!empty($nome)) {//tem nome preenchido
                $query1 = 'SELECT child.name,child.birth_date,child.id FROM child WHERE child.name like "%' . $nome . '%"';
                if (!empty($data)) {// nome e data preenchido
                    if (validateDate($data)) {
                        $query1 .= '&& child.birth_date LIKE "%' . $data . '%"';
                    } else {
                        echo "<div class='unsuccess'><p id='obg_main'><span id='obg'>A Data Não foi inserira de forma correta</span></p></div><br>";
                        echo "<p><strong>Foi procurado apenas pelos nomes</strong></p>";
                    }
                }
                $result1 = mysqli_query($myDB, $query1);
                if (mysqli_num_rows($result1) > 0) {
                    while ($show = mysqli_fetch_assoc(($result1))) {
                        echo "<li><a href='insercao-de-valores?estado=escolher_item&crianca=" . $show['id'] . "'>[" . $show["name"] . "] (" . $show["birth_date"] . ")</a>";
                    }
                } else {
                    echo "<div class='unsuccess'><p id='obg_main'><span id='obg'>Não foi encontrada nenhuma crianca com essas características</span></p></div>";
                }
                echo "<br>";
                voltar_atras();
            } else if (!empty($data) && empty($name)) {//data e nao tem nome preenchido
                if (validateDate($data)) {
                    $query1 = 'SELECT child.name,child.birth_date,child.id FROM child WHERE child.birth_date like "%' . $data . '%"';
                    $result1 = mysqli_query($myDB, $query1);
                    if (mysqli_num_rows($result1) > 0) {
                        while ($show = mysqli_fetch_assoc(($result1))) {
                            echo "<li><a href='insercao-de-valores?estado=escolher_item&crianca=" . $show['id'] . "'>[" . $show["name"] . "] (" . $show["birth_date"] . ")</a>";
                        }
                    } else {
                        echo "<div class='unsuccess'><p id='obg_main'><span id='obg'>Não foi encontrada nenhuma crianca com essas características</span></p></div>";
                    }
                } else {
                    echo "<div class='unsuccess'><p id='obg_main'><span id='obg'>A Data Não foi inserira de forma correta</span></p></div>";
                }
                echo "<br>";
                voltar_atras();
            } else {
                echo "<div class='unsuccess'><p id='obg_main'><span id='obg'>Não foi inserido nenhum valor</span></p></div>";
                voltar_atras();
            }
        } else if (isset($_REQUEST['estado']) && $_REQUEST['estado'] == 'escolher_item') {//segundo estado de escolher o item
            echo "<h3 class='form_input_title'>Inserção de valores - criança - escolher item</h3>";
            $_SESSION['child_id'] = $_REQUEST["crianca"];
            echo "<ul>";
            $query1 = "SELECT item_type.name,item_type.id FROM item_type ORDER BY id";
            $result1 = mysqli_query($myDB, $query1);
            while ($show = mysqli_fetch_assoc($result1)) {
                $query2 = "SELECT item.name,id FROM item WHERE item_type_id=" . $show["id"];
                $items = mysqli_query($myDB, $query2);
                if (mysqli_num_rows($items) > 0 && mysqli_num_rows(mysqli_query($myDB, "SELECT subitem.id FROM subitem WHERE state='active' AND item_id IN (SELECT item.id FROM item WHERE item_type_id=" . $show["id"] . ")")) > 0) {
                    echo "<li>" . $show["name"] . "</li><ul>";
                    while ($item = mysqli_fetch_assoc($items)) {
                        if (mysqli_num_rows(mysqli_query($myDB, "SELECT id FROM subitem WHERE state='active' AND item_id=" . $item["id"])) > 0) {
                            echo "<li><a href='insercao-de-valores?estado=introducao&item=" . $item["id"] . "'>[" . $item["name"] . "]</a></li>";
                        }
                    }
                    echo "</ul>";
                }
            }
            echo "</ul>";
        } else if (isset($_REQUEST['estado']) && $_REQUEST['estado'] == 'introducao') {//terceiro estado de introduzir
            $_SESSION["item_id"] = $_REQUEST["item"];
            $query1 = "SELECT item.name,item.item_type_id from item WHERE item.id=" . $_SESSION["item_id"];
            $result1 = mysqli_fetch_assoc(mysqli_query($myDB, $query1));
            $_SESSION["item_name"] = $result1["name"];
            $_SESSION["item_type_id"] = $result1["item_type_id"];
            echo "<h3 class='form_input_title'>Inserção de valores - " . $_SESSION["item_name"] . "</h3>";
            $form = sprintf("item_type_%d_item_%d", $_SESSION["item_type_id"], $_SESSION["item_id"]);
            $action = $current_page . "?estado=validar&item=" . $_SESSION["item_id"];
            $query2 = "SELECT * from subitem WHERE item_id=" . $_SESSION["item_id"] . " AND state='active' ORDER BY form_field_order";
            $result2 = mysqli_query($myDB, $query2);
            $contador = 0;
            echo "<form method='post' action=" . $current_page . ">";
            while ($subItem = mysqli_fetch_assoc($result2)) {
                $obrigatorio = $subItem["mandatory"];
                $addUnidades = $subItem["unit_type_id"] != null;
                $nomedocampo = "campo" . $contador;
                $nomedocampotipo = $nomedocampo . "tipo";
                $nomedocampomandatorio = $nomedocampo . "mandatorio";
                $campoid = $nomedocampo . "id";
                switch ($subItem["value_type"]) {
                    case "text":
                        echo "<h3 class='form_input_title'>" . $subItem["name"] . ($obrigatorio == 1 ? "*</h3>" : "</h3>");
                        if ($subItem["form_field_type"] == "text") {//text text
                            if ($addUnidades) {
                                $query5 = "SELECT name from subitem_unit_type WHERE id=" . $subItem["unit_type_id"];
                                $unidades = mysqli_fetch_assoc(mysqli_query($myDB, $query5));
                                echo "<input name='$nomedocampo' type='text' placeholder='" . $unidades["name"] . "'>";//com unidades no placeholder
                                echo "<input type='hidden' name='$nomedocampotipo' value='texto'>";
                                echo "<input type='hidden' name='$nomedocampomandatorio' value='" . ($obrigatorio == 1) . "'>";
                                echo "<input type='hidden' name='$campoid' value='" . $subItem["id"] . "'>";
                                echo "<input type='hidden' name='$campoid" . "Nome" . "' value='" . $subItem["name"] . "'>";
                                $contador++;
                            } else {
                                echo "<input name='$nomedocampo' type='text' placeholder='sem unidades'>";//sem unidades no placeholder
                                echo "<input type='hidden' name='$nomedocampotipo' value='texto'>";
                                echo "<input type='hidden' name='$nomedocampomandatorio' value='" . ($obrigatorio == 1) . "'>";
                                echo "<input type='hidden' name='$campoid' value='" . $subItem["id"] . "'>";
                                echo "<input type='hidden' name='$campoid" . "Nome" . "' value='" . $subItem["name"] . "'>";
                                $contador++;
                            }
                        } else {//text textbox
                            if ($addUnidades) {
                                $query5 = "SELECT name from subitem_unit_type WHERE id=" . $subItem["unit_type_id"];
                                $unidades = mysqli_fetch_assoc(mysqli_query($myDB, $query5));
                                echo "<textarea class='textArea' name='$nomedocampo' rows='5' cols'50' placeholder='" . $unidades["name"] . "'></textarea>";//com unidades no placeholder
                                echo "<input type='hidden' name='$nomedocampotipo' value='texto'>";
                                echo "<input type='hidden' name='$nomedocampomandatorio' value='" . ($obrigatorio == 1) . "'>";
                                echo "<input type='hidden' name='$campoid' value='" . $subItem["id"] . "'>";
                                echo "<input type='hidden' name='$campoid" . "Nome" . "' value='" . $subItem["name"] . "'>";
                                $contador++;
                            } else {
                                echo "<textarea class='textArea' name='$nomedocampo' rows='5' cols='50' placeholder='sem unidades'></textarea>";//sem unidades no placeholder
                                echo "<input type='hidden' name='$nomedocampotipo' value='texto'>";
                                echo "<input type='hidden' name='$nomedocampomandatorio' value='" . ($obrigatorio == 1) . "'>";
                                echo "<input type='hidden' name='$campoid' value='" . $subItem["id"] . "'>";
                                echo "<input type='hidden' name='$campoid" . "Nome" . "' value='" . $subItem["name"] . "'>";
                                $contador++;
                            }
                        }
                        break;
                    case "bool"://bool
                        echo "<h3 class='form_input_title'>" . $subItem["name"] . ($obrigatorio == 1 ? "*</h3>" : "</h3>");
                        echo "<input name='$nomedocampo' id='rbool1' type='radio' value='1'><label for='rbool1'>Verdadeiro</label><br>";//bool verdadeiro
                        echo "<input name='$nomedocampo' id='rbool2' type='radio' value='0'><label for='rbool2'>Falso</label>";//bool falso
                        echo "<input type='hidden' name='$nomedocampotipo' value='radiobool'>";
                        echo "<input type='hidden' name='$nomedocampomandatorio' value='" . ($obrigatorio == 1) . "'>";
                        echo "<input type='hidden' name='$campoid' value='" . $subItem["id"] . "'>";
                        echo "<input type='hidden' name='$campoid" . "Nome" . "' value='" . $subItem["name"] . "'>";
                        $contador++;
                        break;
                    case "double":
                    case "int":
                        echo "<h3 class='form_input_title'>" . $subItem["name"] . ($obrigatorio == 1 ? "*</h3>" : "</h3>");
                        if ($subItem["form_field_type"] == "text") {//int/double text
                            if ($addUnidades) {
                                $query5 = "SELECT name from subitem_unit_type WHERE id=" . $subItem["unit_type_id"];
                                $unidades = mysqli_fetch_assoc(mysqli_query($myDB, $query5));
                                echo "<input name='$nomedocampo' type='text' placeholder='" . $unidades["name"] . "'>";//com unidades no placeholder
                                echo "<input type='hidden' name='$nomedocampotipo' value='numerico'>";
                                echo "<input type='hidden' name='$nomedocampomandatorio' value='" . ($obrigatorio == 1) . "'>";
                                echo "<input type='hidden' name='$campoid' value='" . $subItem["id"] . "'>";
                                echo "<input type='hidden' name='$campoid" . "Nome" . "' value='" . $subItem["name"] . "'>";
                                $contador++;
                            } else {
                                echo "<input name='$nomedocampo' type='text' placeholder='sem unidades'>";//sem unidades no placeholder
                                echo "<input type='hidden' name='$nomedocampotipo' value='numerico'>";
                                echo "<input type='hidden' name='$nomedocampomandatorio' value='" . ($obrigatorio == 1) . "'>";
                                echo "<input type='hidden' name='$campoid' value='" . $subItem["id"] . "'>";
                                echo "<input type='hidden' name='$campoid" . "Nome" . "' value='" . $subItem["name"] . "'>";
                                $contador++;
                            }
                        }
                        break;
                    case "enum":
                        $query3 = "SELECT value from subitem_allowed_value WHERE subitem_id=" . $subItem["id"] . " AND state='active'";
                        $result3 = mysqli_query($myDB, $query3);
                        if (mysqli_num_rows($result3) > 0) {
                            echo "<h3 class='form_input_title'>" . $subItem["name"] . ($obrigatorio == 1 ? "*</h3>" : "</h3>");
                            if ($subItem["form_field_type"] == "selectbox") {//enum selectbox
                                echo "<select name='$nomedocampo'>";
                                echo "<option value='empty'>Selecione um valor</option>";
                                while ($valor = mysqli_fetch_assoc($result3)) {
                                    echo "<option value='" . $valor["value"] . "'>" . $valor["value"] . "</option>";
                                    echo "<br>";
                                }
                                echo "</select>";
                                echo "<input type='hidden' name='$nomedocampotipo' value='selectbox'>";
                                echo "<input type='hidden' name='$nomedocampomandatorio' value='" . ($obrigatorio == 1) . "'>";
                                echo "<input type='hidden' name='$campoid' value='" . $subItem["id"] . "'>";
                                echo "<input type='hidden' name='$campoid" . "Nome" . "' value='" . $subItem["name"] . "'>";
                                $contador++;
                            } elseif ($subItem["form_field_type"] == "checkbox") {//enum checkbox
                                $contador2 = 0;
                                while ($valor = mysqli_fetch_assoc($result3)) {
                                    $nomedocampov2 = $nomedocampo . "_" . $contador2;
                                    echo "<input type='checkbox' name='$nomedocampov2' value='" . $valor["value"] . "'>";
                                    echo "<label>" . $valor["value"] . "</label><br>";
                                    $contador2++;
                                }
                                echo "<input type='hidden' name='$nomedocampotipo' value='checkbox'>";
                                echo "<input type='hidden' name='$nomedocampomandatorio' value='" . ($obrigatorio == 1) . "'>";
                                echo "<input type='hidden' name='$campoid' value='" . $subItem["id"] . "'>";
                                echo "<input type='hidden' name='$campoid" . "Nome" . "' value='" . $subItem["name"] . "'>";
                                echo "<input type='hidden' name='$campoid" . "Numero" . "' value='$contador2'>";
                                $contador++;
                            } else {//enum radio
                                $id = 0;
                                while ($valor = mysqli_fetch_assoc($result3)) {
                                    $nomeId = $nomedocampo . $id;
                                    echo "<input name='$nomedocampo' type='radio' value='" . $valor["value"] . "' >";
                                    echo "<label>" . $valor["value"] . "</label>";
                                    echo "<br>";
                                    $id++;
                                }
                                echo "<input type='hidden' name='$nomedocampotipo' value='radioenum'>";
                                echo "<input type='hidden' name='$nomedocampomandatorio' value='" . ($obrigatorio == 1) . "'>";
                                echo "<input type='hidden' name='$campoid' value='" . $subItem["id"] . "'>";
                                echo "<input type='hidden' name='$campoid" . "Nome" . "' value='" . $subItem["name"] . "'>";
                                $contador++;
                            }
                        }
                        break;
                }
            }
            echo "<input type='hidden' name='estado' value='validar'><hr>";
            echo "<button type='submit' class='continueButton'>Validar</button>";
            echo "</form>";
        } elseif (isset($_REQUEST['estado']) && $_REQUEST['estado'] == 'validar') {//quarto estado validar
            echo "<h3 class='form_input_title'>Inserção de valores - " . $_SESSION["item_name"] . " - validar</h3>";
            $contador = 0;
            $valido = true;
            $list = "<ul>";
            $listcorreta = "<ul>";
            $action = $current_page . "?estado=inserir&item=" . $_SESSION["item_id"];
            echo "<form method='post' action='$action'>";
            while (isset($_REQUEST["campo$contador" . "id"])) {
                $nomedocampo = "campo" . $contador;
                $nomedocampotipo = $nomedocampo . "tipo";
                $nomedocampomandatorio = $nomedocampo . "mandatorio";
                echo "<input type='hidden' name='" . "campo$contador" . "id" . "' value='" . $_REQUEST["campo$contador" . "id"] . "'>";
                echo "<input type='hidden' name='$nomedocampotipo' value='" . $_REQUEST[$nomedocampotipo] . "'>";
                echo "<input type='hidden' name='$nomedocampomandatorio' value='" . $_REQUEST[$nomedocampomandatorio] . "'>";
                switch ($_REQUEST[$nomedocampotipo]) {
                    case "texto":
                        if ($_REQUEST[$nomedocampomandatorio] == 1 && empty($_REQUEST[$nomedocampo])) {
                            $valido = false;
                            $list .= "<li class='warning_list'>" . $_REQUEST["campo$contador" . "idNome"] . "</li>";
                        } else {
                            $listcorreta .= "<li class='warning_list'>" . $_REQUEST["campo$contador" . "idNome"] . ":" . $_REQUEST[$nomedocampo] . "</li>";
                            echo "<input type='hidden' name='$nomedocampo' value='" . $_REQUEST[$nomedocampo] . "'>";
                        }
                        break;
                    case "radiobool":
                        if ($_REQUEST[$nomedocampomandatorio] == 1 && !isset($_REQUEST[$nomedocampo])) {//radiobool
                            $valido = false;
                            $list .= "<li class='warning_list'>" . $_REQUEST["campo$contador" . "idNome"] . "</li>";
                        } else {
                            $listcorreta .= "<li class='warning_list'>" . $_REQUEST["campo$contador" . "idNome"] . ":" . $_REQUEST[$nomedocampo] . "</li>";
                            echo "<input type='hidden' name='$nomedocampo' value='" . $_REQUEST[$nomedocampo] . "'>";
                        }
                        break;
                    case "numerico":
                        if ($_REQUEST[$nomedocampomandatorio] == 1 && (empty($_REQUEST[$nomedocampo]) || !is_numeric($_REQUEST[$nomedocampo]))) {
                            $valido = false;
                            $list .= "<li class='warning_list'>" . $_REQUEST["campo$contador" . "idNome"] . "</li>";
                        } else {
                            $listcorreta .= "<li class='warning_list'>" . $_REQUEST["campo$contador" . "idNome"] . ":" . $_REQUEST[$nomedocampo] . "</li>";
                            echo "<input type='hidden' name='$nomedocampo' value='" . $_REQUEST[$nomedocampo] . "'>";
                        }
                        break;
                    case "selectbox":
                        if (($_REQUEST[$nomedocampomandatorio] == 1) && ($_REQUEST[$nomedocampo] == "empty")) {
                            $valido = false;
                            $list .= "<li class='warning_list'>" . $_REQUEST["campo$contador" . "idNome"] . "</li>";
                        } else {
                            $listcorreta .= "<li class='warning_list'>" . $_REQUEST["campo$contador" . "idNome"] . ":" . $_REQUEST[$nomedocampo] . "</li>";
                            echo "<input type='hidden' name='$nomedocampo' value='" . $_REQUEST[$nomedocampo] . "'>";
                        }
                        break;
                    case "checkbox":
                        if ($_REQUEST[$nomedocampomandatorio] == 1) {
                            $selecionado = false;
                            echo "<input type='hidden' name='" . "campo$contador" . "idNumero" . "' value='" . $_REQUEST["campo$contador" . "idNumero"] . "'>";
                            for ($contador2 = 0; $contador2 < $_REQUEST["campo$contador" . "idNumero"]; $contador2++) {
                                if (isset($_REQUEST[$nomedocampo . "_" . $contador2])) {
                                    $selecionado = true;
                                    $listcorreta .= "<li class='warning_list'>" . $_REQUEST["campo$contador" . "idNome"] . ":" . $_REQUEST[$nomedocampo . "_" . $contador2] . "</li>";
                                    echo "<input type='hidden' name='".$nomedocampo . "_" . $contador2."' value='" . $_REQUEST[$nomedocampo . "_" . $contador2] . "'>";
                                }
                            }
                            if (!$selecionado) {
                                $valido = false;
                                $list .= "<li class='warning_list'>" . $_REQUEST["campo$contador" . "idNome"] . "</li>";
                            }
                        }
                        break;
                    case "radioenum":
                        if ($_REQUEST[$nomedocampomandatorio] == 1 && !isset($_REQUEST[$nomedocampo])) {//radioenum
                            $valido = false;
                            $list .= "<li class='warning_list'>" . $_REQUEST["campo$contador" . "idNome"] . "</li>";
                        } else if (isset($_REQUEST[$nomedocampo])) {
                            $listcorreta .= "<li class='warning_list'>" . $_REQUEST["campo$contador" . "idNome"] . ":" . $_REQUEST[$nomedocampo] . "</li>";
                            echo "<input type='hidden' name='$nomedocampo' value='" . $_REQUEST[$nomedocampo] . "'>";
                        }
                        break;
                }
                $contador++;
            }
            if (!$valido) {
                echo "<div class='unsuccess'><p id='obg_main'>Os Campos seguintes <span id='obg'>sao obrigatorios</span> ou <span id='obg'>nao</span> estao preenchidos <span id='obg'>corretamente:</span></p>";
                echo $list;
                echo "</ul><br></div>";
                voltar_atraz();
            } else {
                echo "<div class='success'><p id='suc_main'>A Validacao<span id='suc'> foi</span> concluida <span id='suc'></span> com <span id='suc'>Sucesso:</span></p>";
                echo $listcorreta;
                echo "</ul><br></div><br>";
                echo "<div class='question'><p id='obg_main'>deseja continuar?</p></div>";
                echo "<button type='submit' class='continueButton'>Submeter</button>";
            }
            echo "</form>";
        } elseif (isset($_REQUEST['estado']) && $_REQUEST['estado'] == 'inserir') {//quinto estado inserir
            echo "<h3 class='form_input_title'>Inserção de valores - " . $_SESSION["item_name"] . " - inserir</h3>";
            $contador = 0;
            $queryErrors = false;
            while (isset($_REQUEST["campo$contador" . "id"])) {
                $nomedocampo = "campo" . $contador;
                if(isset($_REQUEST["campo$contador"."tipo"])&& $_REQUEST["campo$contador"."tipo"]=="checkbox"){
                    $contador3=0;
                    while(isset($_REQUEST["campo$contador"."_".$contador3])){
                        $query = 'INSERT INTO `value` (child_id,subitem_id,value,date,time,producer) VALUES (' . $_SESSION['child_id'] . ',' . $_REQUEST["campo$contador" . "id"] . ',"' . $_REQUEST["campo$contador"."_".$contador3] . '","' . date("Y-m-d") . '","' . date("H:i:s") . '", "' . WP_get_current_user()->user_login . '")';
                        if (!mysqli_query($myDB, $query)) {
                            echo '<div class="unsuccess warnings"><span>Erro: ' . $query . '<br>' . mysqli_error($myDB) . '</span></div>';
                            $queryErrors = true;
                        }
                        $contador3++;
                    }
                }else{
                    $query = 'INSERT INTO `value` (child_id,subitem_id,value,date,time,producer) VALUES (' . $_SESSION['child_id'] . ',' . $_REQUEST["campo$contador" . "id"] . ',"' . $_REQUEST[$nomedocampo] . '","' . date("Y-m-d") . '","' . date("H:i:s") . '", "' . WP_get_current_user()->user_login . '")';
                    if (!mysqli_query($myDB, $query)) {
                        echo '<div class="unsuccess warnings"><span>Erro: ' . $query . '<br>' . mysqli_error($myDB) . '</span></div>';
                        $queryErrors = true;
                    }
                }
            $contador++;
            }
            if (!$queryErrors) {
                echo "<div class='success'><p id='suc_main'>Inseriu os dados de registo com sucesso.<br>Clique em <span id='suc'>Continuar</span> para avançar.</p></div>
                      <a href='$current_page' ><button class='continueButton' >Voltar</button></a>
                      <a href='$current_page.?estado=escolher_item&crianca=" . $_SESSION["child_id"] . "' ><button class='continueButton' >Escolher item</button></a>";
            }
        } else {
            echo "<h3 class='form_input_title'>Outro estado</h3>";
        }
    }
} else {
    echo "<br>
          <div class='unsuccess'>
          <p id='obg_main'>Não tem<span id='obg'> autorização </span>para aceder á página<span id='obg'> Inserção de valores </span></p>
          </div>";
}
?>