<?php
require_once("custom/php/common.php");

$dbLink = connect();
if (checkCapability("manage_subitems")) {
    if (!mysqli_select_db($dbLink, "bitnami_wordpress")) {
        die("Connection to DB failed: " . mysqli_connect_error());
    } else {
        if (array_key_exists("estado", $_REQUEST) && $_REQUEST["estado"] == "inserir" && !$_SESSION["subitemAdded"]) {
            $requiredFilled = true;
            $fields = "";
            echo '<h3 class="main_title"><b>Gestão de subitens - inserção</b></h3>';
            echo '<div>';
            if (empty($_REQUEST["subName"]) || is_numeric($_REQUEST["subName"])) {
                $fields .= "<li class = 'warning_list'><strong>Nome do subitem</strong></li>";
                $requiredFilled = false;
            }
            if (empty($_REQUEST["subType"])) {
                $fields .= "<li class = 'warning_list'><strong>Tipo do valor</strong></li>";
                $requiredFilled = false;
            }
            if ($_REQUEST["ItemName"] === "Selecione Um item") {
                $fields .= "<li class = 'warning_list'><strong>Item</strong></li>";
                $requiredFilled = false;
            }
            if (empty($_REQUEST["formType"])) {
                $fields .= "<li class = 'warning_list'><strong>Tipo do campo no formulario</strong></li>";
                $requiredFilled = false;
            }
            if (empty($_REQUEST["formOrder"]) || !is_numeric($_REQUEST["formOrder"]) || $_REQUEST["formOrder"] < 0) {
                $fields .= "<li class = 'warning_list'><strong>Ordem do campo no formulario</strong></li>";
                $requiredFilled = false;
            }
            if (empty($_REQUEST["mandatory"])) {
                $fields .= "<li class = 'warning_list'><strong>Obrigatório</strong></li>";
                $requiredFilled = false;
            }
            echo '</div>';
            if (!$requiredFilled) {
                echo '<div class="unsuccess warnings">
                        <span> Os seguintes campos são <strong>obrigatorios e percisam de ser validos:</strong></span><ul>' . $fields . '</ul>
                    </div>';
                voltar();
            } else {//como nao houve erros procede a parte de inserçao de dados
                $itemName = str_replace("_", " ", $_REQUEST["ItemName"]);
                $subitemName = trim($_REQUEST["subName"]);
                $subitemName = stripslashes($_REQUEST["subName"]);
                $subitemName = htmlspecialchars($_REQUEST["subName"]);
                $subitemName = str_replace("_", " ", $_REQUEST["subName"]);
                $subType = $_REQUEST["subType"];
                $formType = $_REQUEST["formType"];
                $subUnitType = str_replace("_", " ", $_REQUEST["subUnitType"]);
                $formOrder = trim($_REQUEST["formOrder"]);
                $mandatory = $_REQUEST["mandatory"];

                $queryIdItem = "SELECT id FROM item WHERE name='$itemName'";
                $resultInsert = mysqli_query($dbLink, $queryIdItem);
                $fetchedItem = mysqli_fetch_assoc($resultInsert);
                $queryIdSubUnit = "SELECT id,name FROM subitem_unit_type WHERE name='$subUnitType'";
                $resultInsert = mysqli_query($dbLink, $queryIdSubUnit);
                $fetchedUnit = mysqli_fetch_assoc($resultInsert);

                $transaction = "START TRANSACTION;";
                $queryErrors = false;
                if (!mysqli_query($dbLink, $transaction)) {
                    echo '<div class="unsuccess warnings"><span>Error: ' . $transaction . "<br>" . mysqli_error($dbLink) . '</span></div>';
                    $queryErrors = true;
                }
                $queryInsert = "INSERT INTO subitem(id, name, item_id, value_type, form_field_name, form_field_type, unit_type_id, form_field_order, mandatory, state) VALUES (NULL,'$subitemName'," . $fetchedItem["id"] . " ,'$subType','','$formType'," . ($fetchedUnit == null ? 'NULL' : $fetchedUnit["id"]) . ",'$formOrder'," . ($mandatory == "Sim" ? 1 : 0) . ",'active')";
                if (!mysqli_query($dbLink, $queryInsert)) {
                    echo '<div class="unsuccess warnings"><span>Error: ' . $queryInsert . "<br>" . mysqli_error($dbLink) . '</span>';
                    $queryErrors = true;
                }
                if (!$queryErrors) {//senao ocorreu erros nas querries ate agr procede-se a criar o form_field_name
                    $subitemId = mysqli_insert_id($dbLink);//id do subitem inserido atras
                    $removeAccent = Transliterator::createFromRules(':: NFD; :: [:Nonspacing Mark:] Remove; :: NFC;', Transliterator::FORWARD);//NFD decompoe a letra do acento , [:Nonspacing Mark:] (Unicode Characters) é basicamente onde o acento fica depois de ser decomposto e a seguir é removido, NFC volta a juntar a letra decomposta que ira ficar sem acento
                    $itemNameAccentless = $removeAccent->transliterate($itemName);
                    $firstLetters = substr($itemNameAccentless, 0, 3);
                    $subitemName_ASCI = preg_replace('/[^a-z0-9_ ]/i', '', $subitemName);
                    $subitemNameSpaceless = str_replace(" ", "_", $subitemName_ASCI);
                    $formFieldName = $subitemNameSpaceless . "-" . $subitemId . "-" . $subitemNameSpaceless;
                    $queryFieldName = "UPDATE subitem SET form_field_name='$formFieldName' WHERE id=" . $subitemId;
                    if (!mysqli_query($dbLink, $queryFieldName)) {
                        echo '<div class="unsuccess warnings"><span>Erro: ' . $queryFieldName . '<br>' . mysqli_error($dbLink) . '</span></div>';
                        $queryErrors = true;
                    }
                }
                echo '</div>';
                if (!$queryErrors) {
                    $transaction = "COMMIT;";
                    if (!mysqli_query($dbLink, $transaction)) {
                        $queryErrors = true;
                    }
                } else {
                    $transaction = "ROLLBACK;";
                    if (!mysqli_query($dbLink, $transaction)) {
                        $queryErrors = true;
                    }
                }
                if (!$queryErrors) {//senao ocorreu erros ate agora mostrar pagina final
                    echo "<div class='success'>
                              <p id='suc_main'>Inseriu os dados de novo subitem com sucesso.<br>
                                    Clique em <span id='suc'>Continuar</span> para avançar<br>  
                              </p>
                          </div>
                          <table style='text-align: center' width: 100%>
                            <tbody>
                                <tr class='tableHead'>
                                    <th>item</th>
                                    <th>id</th>
                                    <th>subitem</th>
                                    <th>tipo de valor</th>
                                    <th>nome do campo do formulário</th>
                                    <th>tipo do campo no formulário</th>
                                    <th>tipo de unidade</th>
                                    <th>ordem do campo no formulário</th>         
                                    <th>obrigatório</th>
                                    <th>estado</th>
                                </tr>
                                <tr class='row1'>
                                    <td>$itemNameAccentless</td>
                                    <td>$subitemId</td>
                                    <td>$subitemName</td>
                                    <td>$subType</td>
                                    <td>$formFieldName</td>
                                    <td>$formType</td>
                                    <td>" . ($fetchedUnit == null ? '-' : $fetchedUnit["name"]) . "</td>
                                    <td>$formOrder</td>
                                    <td>$mandatory</td>
                                    <td>active</td>
                                </tr>
                            </tbody>
                          </table><br><br>
                          <a href='$current_page' >Continuar</a>";
                    $_SESSION["subitemAdded"] = true;
                }
            }
        } elseif ($_SESSION["subitemAdded"]) {
            echo "<div class='unsuccess warnings'><ul id='obg_main'><span>Erro: Este subitem já foi criado e inserido na Base de Dados</span></ul></div>
                   <a href='$current_page' ><button class='continueButton'>Continuar</button></a>";
        } else {//estado inicial
            echo '<table class="tabela" style="text-align: center; width: 100%;">
                    <tbody>
                        <tr class="tableHead">
                            <th>item</th>
                            <th>id</th>
                            <th>subitem</th>
                            <th>tipo de valor</th>
                            <th>nome do campo do formulário</th>
                            <th>tipo do campo no formulário</th>
                            <th>tipo de unidade</th>
                            <th>ordem do campo no formulário</th>         
                            <th>obrigatório</th>
                            <th>estado</th>
                            <th>ação</th>
                        </tr>';
            $queryItem = "Select id,name From item ORDER BY name ASC";
            $resultItem = mysqli_query($dbLink, $queryItem);
            $bckgType = "row2";
            $bckgType2 = 'row2';
            while ($rowItem = mysqli_fetch_assoc($resultItem)) {
                $querySubitem = "SELECT id,name,value_type,form_field_name,form_field_type,unit_type_id,form_field_order, mandatory, state FROM subitem WHERE item_id=" . $rowItem["id"] . " ORDER BY form_field_order";//buscar os subtiens relacionados ao item atual
                $resultSubitem = mysqli_query($dbLink, $querySubitem);//a tabela com da querry com os dados relacionados tendo um certo numero de linhas que vai quantas rows este item vai ter
                $numRowsSubItem = mysqli_num_rows($resultSubitem);//quantidade de linhas que a tabela tem
                $bckgType = switchBackground($bckgType);
                echo '<tr>';
                if ($numRowsSubItem != 0) {
                    echo '<td class="' . $bckgType . '" rowspan ="' . $numRowsSubItem . '">' . $rowItem["name"] . '</td>';
                    while ($rowSubitem = mysqli_fetch_assoc($resultSubitem)) {
                        $bckgType2 = switchBackground($bckgType2);
                        $querySubitemUnit = "Select id,name FROM subitem_unit_type WHERE id=" . $rowSubitem["unit_type_id"];
                        $resultSubitemUnit = mysqli_query($dbLink, $querySubitemUnit);
                        if (isset($rowSubitem["unit_type_id"])) {//se o unit type id for nulo ira prencher a celula com o dado referente com
                            while ($rowSubitemUnit = mysqli_fetch_assoc($resultSubitemUnit)) {
                                echo '<td class="' . $bckgType2 . '">' . $rowSubitem["id"] . '</td>
                            <td class="' . $bckgType2 . '">' . $rowSubitem["name"] . '</td>
                            <td class="' . $bckgType2 . '">' . $rowSubitem["value_type"] . '</td>
                            <td class="' . $bckgType2 . '">' . $rowSubitem["form_field_name"] . '</td>
                            <td class="' . $bckgType2 . '">' . $rowSubitem["form_field_type"] . '</td>
                            <td class="' . $bckgType2 . '">' . $rowSubitemUnit["name"] . '</td>
                            <td class="' . $bckgType2 . '">' . $rowSubitem["form_field_order"] . '</td>
                            <td class="' . $bckgType2 . '">' . $rowSubitem["mandatory"] . '</td>
                            <td class="' . $bckgType2 . '">' . $rowSubitem["state"] . '</td>
                            <td class="' . $bckgType2 . '"><a href="' . $current_page . '?estado=editar&item=' . $rowItem["id"] . '">[editar]</a><br>
                                <a href="' . $current_page . '?estado=desativar&item=' . $rowItem["id"] . '">[desativar]</a><br>
                                <a href="' . $current_page . '?estado=apagar&item=' . $rowItem["id"] . '">[apagar]</a></td>
                            </tr>';
                            }
                        } else {
                            echo '<td class="' . $bckgType2 . '">' . $rowSubitem["id"] . '</td>
                          <td class="' . $bckgType2 . '">' . $rowSubitem["name"] . '</td>
                          <td class="' . $bckgType2 . '">' . $rowSubitem["value_type"] . '</td>
                          <td class="' . $bckgType2 . '">' . $rowSubitem["form_field_name"] . '</td>
                          <td class="' . $bckgType2 . '">' . $rowSubitem["form_field_type"] . '</td>
                          <td class="' . $bckgType2 . '">-</td>
                          <td class="' . $bckgType2 . '">' . $rowSubitem["form_field_order"] . '</td>
                          <td class="' . $bckgType2 . '">' . $rowSubitem["mandatory"] . '</td>
                          <td class="' . $bckgType2 . '">' . $rowSubitem["state"] . '</td>
                          <td class="' . $bckgType2 . '"><a href="' . $current_page . '?estado=editar&item=' . $rowItem["id"] . '">[editar]</a><br>
                              <a href="' . $current_page . '?estado=desativar&item=' . $rowItem["id"] . '">[desativar]</a><br>
                              <a href="' . $current_page . '?estado=apagar&item=' . $rowItem["id"] . '">[apagar]</a></td>
                          </tr>';
                        }
                    }
                } else {
                    echo '<td class="' . $bckgType . '">' . $rowItem["name"] . '</td>
                          <td class="' . $bckgType . '" colspan="' . mysqli_num_fields($resultSubitem) + 1 . '"> Não há subitens especificados </td>';
                    echo '</tr>';
                }
            }

            echo '</tbody></table>
                  <body>
                    <h3 class="sub_title"><b>Gestão de subitems - introdução</b></h3>
                    <form method="post" action="' . $current_page . '">
                        <h4 class="form_input_title">Nome do subitem</h4>
                        <input type="text" id="subName" name="subName"><br>
                        <h4 class="form_input_title">Tipo de valor</h4>';
            $_SESSION["subitemAdded"] = false;
            $valType = get_enum_values($dbLink, "subitem", "value_type");
            $checked = true;
            foreach ($valType as $type) {//radio buttons do tipo de valor --subType--
                $input = '<input';
                if ($checked) {
                    $input .= ' checked';
                    $checked = false;
                }
                $input .= ' type="radio" name="subType" value="' . $type . '"><label>' . $type . '</label><br>';
                echo $input;
            }
            echo '<h4 class="form_input_title">Selecione Um Item</h4>';//Selectbox do Item --Itemname--
            if (mysqli_num_rows($resultItem) > 0) {
                echo '<select  name="ItemName" id="ItemName" >
                                <option>Selecione Um item</option>';
                $resultItem = mysqli_query($dbLink, $queryItem);
                while ($row = mysqli_fetch_assoc($resultItem)) {
                    $option = str_replace(" ", "_", $row["name"]);
                    echo '<option value="' . $option . '">' . $row["name"] . '</option>';
                }
                echo '</select><br>';
            } else {
                echo 'Não existem Itens.<br>';
            }
            $formFieldType = get_enum_values($dbLink, "subitem", "form_field_type"); //radio - Tipo do campo do formulário(Obrigatorio) --formType--
            echo '<h4 class="form_input_title">Tipo do campo no formulario</h4>';
            $checked = true;
            foreach ($formFieldType as $formTypes) {
                $input = '<input';
                if ($checked) {
                    $input .= ' checked';
                    $checked = false;
                }
                $input .= ' type="radio" name="formType" value="' . $formTypes . '"><label>' . $formTypes . '</label><br>';
                echo $input;
            }
            echo '<h4 class="form_input_title">Tipo de Undidade</h4>';//selectbox - Tipo de unidade --subUnitType--
            $querySubUnits = "SELECT name FROM subitem_unit_type";
            $resultSubUnits = mysqli_query($dbLink, $querySubUnits);
            if (mysqli_num_rows($resultSubUnits) > 0) {
                echo '<select  name="subUnitType" id="subUnitType" >
                                <option></option>';
                while ($row = mysqli_fetch_assoc($resultSubUnits)) {
                    $option = str_replace(" ", "_", $row["name"]);
                    echo '<option value="' . $option . '">' . $row["name"] . '</option>';
                }
                echo '</select><br>';
            } else {
                echo 'Não existem tipos de Unidade.<br>';
            }
            echo ' 
            <h4 class="form_input_title">Ordem do campo no formulario</h4>
            <input type="text" id="formOrder" name="formOrder"><br>
            <h4 class="form_input_title">Obrigatório</h4>
            <input type="radio" name="mandatory" checked value="Sim"><label>Sim</label><br>
            <input type="radio" name="mandatory" value="Nao"><label>Nao</label><br>
            <input type="hidden" name="estado" value="inserir"><br>
            <input type="submit" value="Inserir Subitem">
        </form>
      </body>';
        }
    }
} else {
    echo "<div class='unsuccess warnings'>
            <span><b>Não tem autorização para aceder a esta página</b></span>
          </div>";
}
?>