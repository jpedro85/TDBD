<?php
require_once("custom/php/common.php");
if (checkCapability("manage_unit_types")) {
    if (!isset($_REQUEST['estado'])) {
        $myDB = connect();
        $query = "SELECT id,name FROM subitem_unit_type ORDER BY name ASC";
        $result = mysqli_query($myDB, $query);
        $subitem = "";
        $cor = "row2";
        echo "<p>6</p>";
        echo '<table >
                  <tbody>
                        <tr class="tableHead">
                            <th>ID</th>
                            <th>Unidade</th>
                            <th>SubItem</th>
                            <th>Ação</th>
                        </tr>
                        <tr>';
        while ($row = mysqli_fetch_assoc($result)) {
            $cor = switchBackground($cor);
            $subitem = "";
            echo "<td class=$cor>" . $row['id'] . '</td>';
            echo "<td class=$cor>" . $row['name'] . '</td>';
            $querysubitem = "SELECT subitem.name,subitem.item_id FROM subitem WHERE unit_type_id=" . $row["id"];
            $resultsubitem = mysqli_query($myDB, $querysubitem);
            $contador=0;
            $linhas=mysqli_num_rows($resultsubitem);
            while ($rowsubitem = mysqli_fetch_assoc($resultsubitem)) {
                $queryitem = "SELECT item.name FROM item WHERE item.id=" . $rowsubitem["item_id"];
                $resultitem = mysqli_query($myDB, $queryitem);
                while ($rowitem = mysqli_fetch_assoc($resultitem)) {
                    $contador+=1;
                    if($contador==$linhas){
                        $subitem .= " " . $rowsubitem["name"] . "(" . $rowitem["name"] . ")";
                    }else{
                        $subitem .= " " . $rowsubitem["name"] . "(" . $rowitem["name"] . "), ";
                    }
                }
            }
            if ($subitem == "") {
                echo "<td class=$cor>Não há tipos de unidades</td>";
            } else {
                echo "<td class=$cor>" . $subitem . '</td>';
            }
            echo "<td class=$cor>[Apagar]</td></tr>";
        }
        echo '</tr>
            </tbody>
        </table>';
        echo "<h3 class='form_input_title'>Gestão de unidades - introdução</h3>";
        echo
            "<form method='post' action=" . $current_page . ">
        <input type='text' name='nome' placeholder='Unidades'>
        <input type='hidden' name='estado' value='inserir'>
        <hr>
        <input type='submit' name='submit' value='Inserir Item' >
        </form>";
    } else if (isset($_REQUEST['estado']) && $_REQUEST['estado'] == 'inserir') {
        $myDB = connect();
        if (!empty($_REQUEST['nome'])) {
            $name = $_REQUEST['nome'];
            $inserir = "INSERT INTO subitem_unit_type (name) VALUES ('$name')";
            mysqli_query($myDB, $inserir);
            echo "<div class='success'>";
            if ($inserir) {
                echo "<h3 class='form_input_title'>Gestão de unidades - inserção</h3>";
                echo "<p id='suc_main'>Foi submetido um valor</p>";
                echo "<ul><a href=" . $current_page . ">Continuar</a></ul>";
            } else {
                echo "<h3 class='form_input_title'>Gestão de unidades - inserção</h3>";
                echo "<p id='suc_main'>nao foi submetido um valor</p>";
                echo "<ul><a href=" . $current_page . ">Continuar</a></ul>";
            }
            echo "</div>";
        } else {
            echo "<br>
          <div class='unsuccess'>
          <p id='obg_main'><span id='obg'>Todos os campos teem de ser preenchidos</span></p>
          <ul><a href=" . $current_page . ">Continuar</a></ul>
          </div>";
        }
    } else {
        echo "<h3 class='form_input_title'>Outro estado</h3>";
    }
} else {
    echo "<br>
          <div class='unsuccess'>
          <p id='obg_main'>Não tem<span id='obg'> autorização </span>para aceder á página<span id='obg'> Gestão de items </span></p>
          </div>";
}
?>