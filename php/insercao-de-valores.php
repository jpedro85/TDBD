<?php
require_once("custom/php/common.php");
if (checkCapability("insert_values")) {
    $myDB = connect();
    if (!mysqli_select_db($myDB, "bitnami_wordpress")) {//verifica conecao
        die("Connection failed:" . mysqli_connect_error());
    } else {//conecao feita
        if (!isset($_REQUEST['estado'])) {//nenhum estado inserido
            echo "<p>21</p>";
            echo "<h3 class='form_input_title'>Inserção de valores - criança - procurar</h3>";
            echo "<form method='post' action=" . $current_page . ">
        <input type='text' name='nome' placeholder='Nome criança'>
        <input type='text' name='data' placeholder='AAAA-MM-DD'>
        <input type='hidden' name='estado' value='escolher_crianca'>
        <hr>
        <input type='submit' name='submit' value='Procurar' >
        </form>";
        } else if (isset($_REQUEST['estado']) && $_REQUEST['estado'] == 'escolher_crianca') {//primeiro estado de escolher crianca
            echo "<h3 class='form_input_title'>Inserção de valores - criança - escolher</h3>";
            $nome = ($_REQUEST["nome"]);
            $data = ($_REQUEST["data"]);
            if (!empty($nome)) {//tem nome preenchido
                $query1 = 'SELECT child.name,child.birth_date,child.id FROM child WHERE child.name like "%' . $nome . '%"';
                if (!empty($data)) {// nome e data preenchido
                    $query1 .= '&& child.birth_date LIKE "%' . $data . '%"';
                }
                $result1 = mysqli_query($myDB, $query1);
                if (mysqli_num_rows($result1) > 0) {
                    while ($show = mysqli_fetch_assoc(($result1))) {
                        echo "<li><a href='insercao-de-valores?estado=escolher_item&crianca=" . $show['id'] . "'>[" . $show["name"] . "] (" . $show["birth_date"] . ")</a>";
                    }
                } else {
                    //adicionar echo de nao ter criancas
                }
            } else if (!empty($data) && empty($name)) {//data e nao tem nome preenchido
                $query1 = 'SELECT child.name,child.birth_date,child.id FROM child WHERE child.birth_date like "%' . $data . '%"';
                $result1 = mysqli_query($myDB, $query1);
                if (mysqli_num_rows($result1) > 0) {
                    while ($show = mysqli_fetch_assoc(($result1))) {
                        echo "<li><a href='insercao-de-valores?estado=escolher_item&crianca=" . $show['id'] . "'>[" . $show["name"] . "] (" . $show["birth_date"] . ")</a>";
                    }
                } else {
                    //adicionar echo de nao ter criancas
                }
            } else {
                //adicionar echo de nao ter criancas
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
            /*$form = sprintf("item_type_%d_item_%d", $_SESSION["item_type_id"], $_SESSION["item_id"]);
            $action = $current_page."?estado=validar&item=" .$_SESSION["item_id"];
            $query2 = "SELECT * from subitem WHERE subitem.item_id=" . $_SESSION["item_id"] . " AND state='active' ORDER BY form_field_order";
            $result2 = mysqli_query($myDB, $query2);
            $ID = 0;
            echo "<form method='post' name='$form' action='$action'>";
            while ($show = mysqli_fetch_assoc($result2)) {
                $add = $show["unit_type_id"];
                $input = $show["form_field_name"];
                $Input = "";
                switch ($show["value_type"]) {
                    case"text"://Caso seja TEXT
                        $Input .= "<span><strong>" . $show["name"] . "</strong></span>" . ($show["mandatory"] == 1 ? "<span>*</span>" : "") . "<br>";//Criacao da label
                        if ($show["form_field_type"] == "text") {
                            $Input .= "<input name='$input' type='text'  class='textInput'" . ($show["mandatory"] == 1 ? " id='$ID'" : "") . ">";
                        } else {
                            $Input .= "<textarea class='textArea' name='$input' rows='5' cols='50'" . ($show["mandatory"] == 1 ? " id='$ID'" : "") . "></textarea>";
                        }
                        break;
                    case"bool":
                        //caso booleano
                        $Input .= "<span><strong>" . $show["name"] . "</strong></span>" . ($show["mandatory"] == 1 ? "<span>*</span>" : "") . "<br>";
                        $Input .= "<input name='$input' type='radio' value='verdadeiro'><span>Verdadeiro</span>";
                        if ($add) {
                            $query4 = "SELECT name from subitem_unit_type WHERE id=" . $show["unit_type_id"];
                            $result4 = mysqli_fetch_assoc(mysqli_query($myDB, $query4));
                            $Input .= "<span> " . $result4["name"] . "</span>";
                        }
                        $Input .= "<br><input name='$input' type='radio' value='falso'><span class='textoLabels'>Falso</span>";
                        if ($add) {
                            $query4 = "SELECT name from subitem_unit_type WHERE id=" . $show["unit_type_id"];
                            $result4 = mysqli_fetch_assoc(mysqli_query($myDB, $query4));
                            $Input .= "<span> " . $result4["name"] . "</span>";
                        }
                        break;
                    case"double":
                    case"int":
                        //caso double e int
                        $Input .= "<span><strong>" . $show["name"] . "</strong></span>" . ($show["mandatory"] == 1 ? "<span>*</span>" : "") . "<br>";
                        $Input .= "<input name='$input' type='text' class='textInput'" . ($show["mandatory"] == 1 ? " id='$ID'" : "") . ">";
                        if ($add) {
                            $query4 = "SELECT name from subitem_unit_type WHERE id=" . $show["unit_type_id"];
                            $result4 = mysqli_fetch_assoc(mysqli_query($myDB, $query4));
                            $Input .= "<span> " . $result4["name"] . "</span>";
                        }
                        break;
                    case"enum":
                        $isSelectBox = $show["form_field_type"] == "selectbox";
                        $index = 0;
                        $query = "SELECT value from subitem_allowed_value WHERE subitem_id=" . $show["id"] . " AND state='active'";
                        $valoresPermitidos = mysqli_query($myDB, $query);
                        if (mysqli_num_rows($valoresPermitidos) > 0) {
                            $Input .= "<span><strong>" . $show["name"] . "</strong></span>" . ($show["mandatory"] == 1 ? "<span>*</span>" : "") . "<br>";
                            if ($isSelectBox) {
                                $Input .= "<select name='$input'" . ($show["mandatory"] == 1 ? " id='$ID'" : "") . " class='textInput textoLabels'>";
                                $Input .= "<option value='empty'>Selecione um valor</option>";
                            } else {
                                $Input .= "<input name='$input";
                                if ($show["form_field_type"] == "radio") {
                                    $Input .= "'";
                                    $Input .= " checked ";
                                } else {
                                    $Input .= "_$index'";
                                }
                            }
                            while ($valor = mysqli_fetch_assoc($valoresPermitidos)) {
                                if ($isSelectBox) {
                                    $Input .= "<option value='" . $valor["value"] . "'>" . $valor["value"] . "</option>";
                                } else {
                                    $Input .= " type='" . $show["form_field_type"] . "' value='" . $valor["value"] . "'" . ($show["mandatory"] == 1 ? " id='$ID'" : "") . "><span>" . $valor["value"] . "</span>";
                                    if ($add) {
                                        $query = "SELECT name from subitem_unit_type WHERE id=" . $show["unit_type_id"];
                                        $unidade = mysqli_fetch_assoc(mysqli_query($myDB, $query));
                                        $Input .= "<span> " . $unidade["name"] . "</span>";
                                    }
                                    $Input .= "<br>";
                                }
                                $index++;
                                if ($index < mysqli_num_rows($valoresPermitidos) && !$isSelectBox) {
                                    $Input .= "<input name='$input";
                                    if ($show["form_field_type"] == "checkbox") {
                                        $Input .= "_$index";
                                    }
                                    $Input .= "'";
                                }
                            }
                            if ($isSelectBox) {
                                $Input .= "</select>";
                                if ($add) {
                                    $query = "SELECT name from subitem_unit_type WHERE id=" . $show["unit_type_id"];
                                    $unidade = mysqli_fetch_assoc(mysqli_query($myDB, $query));
                                    $Input .= "<span> " . $unidade["name"] . "</span>";
                                }
                            }
                        }
                        break;
                }
                echo $Input . "<br>";
                if ($show["mandatory"] == 1) {
                    $ID++;
                }
            }
            echo "<input type='hidden' name='estado' value='validar'><hr>";
            echo "<input type='submit' name='submit' value='validar' >";
            echo "</form>";*/
        }elseif(isset($_REQUEST['estado']) && $_REQUEST['estado'] == 'validar'){
            echo "<h3 class='form_input_title'>Inserção de valores - validar</h3>";

        }elseif(isset($_REQUEST['estado']) && $_REQUEST['estado'] == 'inserir'){
            echo "<h3 class='form_input_title'>Inserção de valores - inserir</h3>";
        }
        else {
            //outro estado
        }
    }
} else {
    echo "<br>
          <div class='unsuccess'>
          <p id='obg_main'>Não tem<span id='obg'> autorização </span>para aceder á página<span id='obg'> Gestão de items </span></p>
          </div>";
}
?>