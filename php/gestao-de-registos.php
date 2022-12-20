<?php
require_once("custom/php/common.php");
reset_edicao_dados();

echo 1;
if (checkCapability("manage_records")) {
    if (!mysqli_select_db($dbLink, "bitnami_wordpress")) {
        die("Connection to DB failed: " . mysqli_connect_error());
    } else {
        if (array_key_exists("estado", $_REQUEST) && $_REQUEST["estado"] == "validar" && !$_SESSION["childAdded"]) {
            $actualDate = date("Y-m-d");
            $requiredFilled = true;
            $fields = "";
            echo '<h3 class="main_title"><b>Dados de registo - validação</b></h3>
                 <div>';
            if (empty($_REQUEST["childName"]) || is_numeric($_REQUEST["childName"])) {
                $fields .= "<li class = 'warning_list'><strong>Nome Completo da criança</strong></li>";
                $requiredFilled = false;
            }
            if (empty($_REQUEST["childBday"]) || !validateDate($_REQUEST["childBday"]) || $actualDate <= $_REQUEST["childBday"]) {
                $fields .= "<li class = 'warning_list'><strong>Data de Nascimento não válida</strong></li>";
                $requiredFilled = false;
            }
            if (empty($_REQUEST["tutorName"]) || is_numeric($_REQUEST["tutorName"])) {
                $fields .= "<li class = 'warning_list'><strong>Nome Completo do Encarregado de Educação</strong></li>";
                $requiredFilled = false;
            }
            if (empty($_REQUEST["tutorPhone"]) || strlen($_REQUEST["tutorPhone"]) != 9 || !preg_match('/^\d{9}$/', $_REQUEST["tutorPhone"])) {
                $fields .= "<li class = 'warning_list'><strong>Telefone do Encarregado de Educação</strong></li>";
                $requiredFilled = false;
            }
            if (!empty($_REQUEST["tutorEmail"]) && !filter_var($_REQUEST["tutorEmail"], FILTER_VALIDATE_EMAIL)) {
                $fields .= "<li class = 'warning_list'><strong>Endereço de e-mail do Encarregado de educação</strong></li>";
                $requiredFilled = false;
            }
            if (!$requiredFilled) {
                echo '<div class="unsuccess warnings">
                        <span> Os seguintes campos são <strong>obrigatorios e percisam de ser válidos:</strong></span><ul>' . $fields . '</ul>
                    </div>';
                voltar_atras();
            } else {//se nao ocorreu erros na introduçao de dados validar dados
                echo "<div class='success'>
                        <span id='suc_main'>Estamos prestes a inserir os dados abaixo na base de dados. Confirma que os dados estão correctos e pretende submeter os mesmos?</span>";
                //lista com os dados
                echo "<li class='warnings'><span id='suc'>Nome completo da criança: </span>" . $_REQUEST["childName"] . "</li>
                      <li class='warnings'><span id='suc'>Data de nascimento da criança: </span>" . $_REQUEST["childBday"] . "</li>
                      <li class='warnings'><span id='suc'>Nome completo do encarregado de educação: </span>" . $_REQUEST["tutorName"] . "</li>
                      <li class='warnings'><span id='suc'>Telefone do Encarregado de Educação: </span>" . $_REQUEST["tutorPhone"] . "</li>
                      <li class='warnings'><span id='suc'>Endereço de e-mail do Encarregado de educação: </span>" . ($_REQUEST["tutorEmail"] == null ? " NULL" : $_REQUEST["tutorEmail"]) . "</li>
                      </div>";
                echo "<form method='post' action='$current_page'>
                        <input type='hidden' name='childName' value='" . $_REQUEST["childName"] . "'>
                        <input type='hidden' name='childBday' value='" . $_REQUEST["childBday"] . "'>
                        <input type='hidden' name='tutorName' value='" . $_REQUEST["tutorName"] . "'>
                        <input type='hidden' name='tutorPhone' value='" . $_REQUEST["tutorPhone"] . "'>
                        <input type='hidden' name='tutorEmail' value='" . $_REQUEST["tutorEmail"] . "'>
                        <input type='hidden' name='estado' value='inserir'><br>
                        <button type='submit' class='continueButton'>Submeter</button>
                      </form>";

                $_SESSION["childAdded"] = false;
            }
        } elseif (array_key_exists("estado", $_REQUEST) && $_REQUEST["estado"] == "inserir") {
            echo "<h3 class='sub_title'>Dados de registo - inserção</h3>";
            if (!$_SESSION["childAdded"]) {
                $queryChildInsert = "INSERT INTO child(id, name, birth_date, tutor_name, tutor_phone, tutor_email) VALUES (NULL,'" . $_REQUEST["childName"] . "','" . $_REQUEST["childBday"] . "','" . $_REQUEST["tutorName"] . "','" . $_REQUEST["tutorPhone"] . "','" . $_REQUEST["tutorEmail"] . "')";
                if (!mysqli_query($dbLink, $queryChildInsert)) {
                    echo '<div class="unsuccess warnings"><span>Error: ' . $queryChildInsert . "<br>" . mysqli_error($dbLink) . '</span>';
                } else {//Senao houver erros a executar a querry os dados sao inseridos e aparece o butao continuar
                    echo "<div class='success'><p id='suc_main'>Inseriu os dados de registo com sucesso.<br>Clique em <span id='suc'>Continuar</span> para avançar.</p></div>
                      <a href='$current_page' ><button class='continueButton' >Continuar</button></a>";
                    $_SESSION["childAdded"] = true;
                }
            } else {
                echo "<div class='unsuccess warnings'><p id='obg_main'><span id='obg'>Erro:</span> Este subitem já foi criado e <span id='obg'>inserido na Base de Dados</span></p></div>
                              <a href='$current_page' ><button class='continueButton'>Continuar</button></a>";
            }
        } else {//estado inicial
            $queryChild = "SELECT id, name, birth_date, tutor_name, tutor_phone, tutor_email FROM child ORDER BY name ASC";
            $resultChild = mysqli_query($dbLink, $queryChild);
            if (mysqli_num_rows($resultChild) != 0) {
                echo "<div>
                     <table>
                     <tbody>
                        <tr class='tableHead'>
                           <th>Nome</th>
                           <th>Data de nascimento</th>
                           <th>Enc. de Educação</th>
                           <th>Telefone do Enc.</th>
                           <th>E-mail</th>
                           <th>Registos</th>
                        </tr>";
                $bckgType = 'row2_registo';
                while ($rowChild = mysqli_fetch_assoc($resultChild)) {
                    if ($bckgType == 'row1_registo') {
                        $bckgType = 'row2_registo';
                    } else {
                        $bckgType = 'row1_registo';
                    }
                    echo '<tr class="' . $bckgType . '">
                                <td>' . $rowChild["name"] . '</td>
                                <td>' . $rowChild["birth_date"] . '</td>
                                <td>' . $rowChild["tutor_name"] . '</td>
                                <td>' . $rowChild["tutor_phone"] . '</td>
                                <td>' . (($rowChild["tutor_email"] == null || $rowChild["tutor_email"] == "") ? "Não possui email na BD" : $rowChild["tutor_email"]) . '</td>';
                    $info = "";
                    $values = "";
                    $queryItem = "SELECT id,name FROM item ORDER BY name ASC";
                    $resultItem = mysqli_query($dbLink, $queryItem);
                    while ($rowItem = mysqli_fetch_assoc($resultItem)) {
                        $itemName = strtoupper($rowItem["name"]) . ":";
                        $done = false;
                        $queryDateProducer = "SELECT DISTINCT date, producer FROM value";
                        $resultDateProducer = mysqli_query($dbLink, $queryDateProducer);
                        $dateInfo = "";
                        while ($rowDateProducer = mysqli_fetch_assoc($resultDateProducer)) {
                            $querySubitem = "SELECT id,name,unit_type_id FROM subitem WHERE item_id=" . $rowItem["id"];
                            $resultSubitem = mysqli_query($dbLink, $querySubitem);
                            //$dateProducer = "<br>[editar][apagar] - <strong>" . $rowDateProducer["date"] . "</strong> (" . $rowDateProducer["producer"] . ") -";
                            $allDateInfo = "";
                            $relatedDate = false;
                            $counter_value = 1;
                            while ($rowSubitem = mysqli_fetch_assoc($resultSubitem)) {
                                $queryValue = "SELECT id,value FROM value WHERE child_id =" . $rowChild["id"] . " AND subitem_id=" . $rowSubitem["id"] . " AND date='" . $rowDateProducer["date"] . "' AND producer='" . $rowDateProducer["producer"] . "'";
                                $resultValue = mysqli_query($dbLink, $queryValue);
                                $resultFetchedUnit = mysqli_query($dbLink, "SELECT name FROM subitem_unit_type WHERE id=" . $rowSubitem["unit_type_id"]);
                                $resultFetchedUnit != null ? $fetchedUnit = mysqli_fetch_assoc($resultFetchedUnit) : $fetchedUnit = null;
                                $subitemName = "<strong>" . $rowSubitem["name"] . "</strong> (";
                                $counter = 1;
                                if (mysqli_num_rows($resultValue) != 0) {
                                    if (!$done) {
                                        //$info .= $itemName;
                                        //$allDateInfo.=$itemName;
                                        $done = true;
                                    }
                                    if (!$relatedDate) {
                                        //$allDateInfo .= " - <strong>" . $rowDateProducer["date"] . "</strong> (" . $rowDateProducer["producer"] . ") -";
                                        $relatedDate = true;
                                    }
                                    $allDateInfo .= $subitemName;
                                    while ($rowValue = mysqli_fetch_assoc($resultValue)) {
                                        if (mysqli_num_rows($resultValue) == $counter) {
                                            (!is_null($fetchedUnit) && array_key_exists("name", $fetchedUnit)) ? $allDateInfo .= $rowValue["value"] . " " . $fetchedUnit["name"] . "); " : $allDateInfo .= $rowValue["value"] . "); ";
                                            $values .= "&value" . $counter_value . "=" . $rowValue["id"];
                                            $counter_value++;
                                        } else if (!empty($rowValue["value"]) && $counter < mysqli_num_rows($resultValue)) {
                                            (!is_null($fetchedUnit) && array_key_exists("name", $fetchedUnit)) ? $allDateInfo .= $rowValue["value"] . " " . $fetchedUnit["name"] . "," : $allDateInfo .= $rowValue["value"] . ",";
                                            $counter++;
                                            $counter_value++;
                                        } else {
                                            $counter++;
                                        }
                                    }
                                }
                            }
                            $dateProducer = "<br> 
                                                    <a href='" . $edicao_de_dados_page . "?estado=editar&tipo=resgisto$values" . "'>[Editar]</a> 
                                                    <a href=" . $edicao_de_dados_page . "?estado=apagar&tipo=resgisto$values" . "'>[Apagar]</a>";
                            if ($relatedDate) $dateInfo .= $dateProducer . " - <strong>" . $rowDateProducer["date"] . "</strong> (" . $rowDateProducer["producer"] . ") -" . $allDateInfo;
                            if ($relatedDate) echo "<br>" . $itemName . " " . $dateProducer . " - <strong>" . $rowDateProducer["date"] . "</strong> (" . $rowDateProducer["producer"] . ") -";
                            //if ($relatedDate) echo " || ".$dateInfo;
                        }
                        if ($done) $info .= $itemName . $dateInfo . "<br>";
                    }
                    echo "<td>$info</td></tr>";
                }
                //campos de Introduçao de valores
                echo '</tbody></table></div>
                    <body>
                    <h3 class="sub_title"><b>Gestão de Registos - introdução</b></h3>
                    <h4>Introduza os dados pessoais básicos da criança:</h4>
                    <form method="post" action="' . $current_page . '">
                        <h4 class="form_input_title">Nome completo da criança</h4>
                        <input type="text" id="childName" name="childName"><br>
                        <h4 class="form_input_title">Data de nascimento</h4>
                        <input type="text" id="childBday" name="childBday" placeholder="AAAA-MM-DD">
                        <h4 class="form_input_title">Nome completo do encarregado de educação</h4>
                        <input type="text" id="tutorName" name="tutorName">
                        <h4 class="form_input_title">Telefone do encarregado de educação</h4>
                        <input type="text" id="tutorPhone" name="tutorPhone" placeholder="123456789" maxlength="9" minlength="9">
                        <!--o minimo que o utlizador consegue escrever para ser valido sao 9 algarismos e o maximo que se pode escrever sao 9 algarismos-->
                        <h4 class="form_input_title">Endereço de e-mail do tutor</h4>
                        <input type="text" id="tutorEmail" name="tutorEmail"><br>
                        <input type="hidden" name="estado" value="validar"><br>
                        <button type="submit" class="continueButton">Submeter</button>';
                $_SESSION["childAdded"] = false;
            } else {
                echo "<div class='unsuccess warnings'>
                        <span><b>Não há crianças</b></span>
                      </div>";
            }
        }
    }
} else {
    echo "<div class='unsuccess warnings'>
            <span><b>Não tem autorização para aceder a esta página</b></span>
          </div>";
}
?>
