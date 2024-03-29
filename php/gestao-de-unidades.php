<?php
require_once("custom/php/common.php");
reset_edicao_dados();
if (checkCapability("manage_unit_types")) {
    if (!isset($_REQUEST['estado'])) {
        $myDB = connect();
        $query = "SELECT id,name FROM subitem_unit_type ORDER BY name ASC";
        $result = mysqli_query($myDB, $query);
        $subitem = "";
        $cor = "row2";
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
            echo '<td class="'.$cor.'">
                    <a href="' . $edicao_de_dados_page . '?estado=editar&tipo=unidade&id=' . $row["id"] . '">[Editar]</a>
                    <a href="' . $edicao_de_dados_page . '?estado=apagar&tipo=unidade&id=' . $row["id"] . '">[Apagar]</a>
                    </td>
                    </tr>';
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
        <button type='submit' class='continueButton'>Inserir Item</button>
        </form>";
    } else if (isset($_REQUEST['estado']) && $_REQUEST['estado'] == 'inserir') {
        $myDB = connect();
        if (!empty($_REQUEST['nome'])) {
            $name = $_REQUEST['nome'];
            $inserir = "INSERT INTO subitem_unit_type (name) VALUES ('$name')";
            mysqli_query($myDB, $inserir);
            echo "<h3 class='form_input_title'>Gestão de unidades - inserção</h3>";
            echo "<div class='success'>";
            if ($inserir) {
                echo "<p id='suc_main'>Foi submetido um valor</p>";
            } else {
                echo "<p id='suc_main'>nao foi submetido um valor</p>";

            }
            echo "</div>";
            echo "<a href=" . $current_page . "><button class='continueButton'>Continuar</button></a>";
        } else {
            echo "<br>
          <div class='unsuccess'>
          <p id='obg_main'><span id='obg'>Todos os campos teem de ser preenchidos</span></p>
          <a href=" . $current_page . "><button class='continueButton'>Continuar</button></a>
          </div>";
        }
    } else {
        echo "<h3 class='form_input_title'>Outro estado</h3>";
    }
} else {
    echo "<br>
          <div class='unsuccess'>
          <p id='obg_main'>Não tem<span id='obg'> autorização </span>para aceder á página<span id='obg'> Gestão de Unidades </span></p>
          </div>";
}
?>