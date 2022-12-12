<?php
require_once("custom/php/common.php");
//gestao de itens
echo "189 <br>";
//check de capabilitie

reset_edicao_dados();
if( checkCapability("manage_items") ) {

    if (!isset($_REQUEST["estado"])) {

       // $mysqli = connect();
        $result_item_type = mysqli_query($dbLink, "SELECT item_type.* FROM item_type");
        //variaveis para as cores das linhas da tablela
        $type = 'row2';
        $type2 = 'row2';

        //Construção da tabela
        echo "<table>
            <tr class='tableHead'> 
                <td>Tipo de Item</td>
                <td>id</td>
                <td>Nome do Item</td>
                <td>Estado</td>
                <td>Ação </b> </td>.                    
            </tr>";

        if ( mysqli_num_rows($result_item_type) != 0) {

            //loop por todos os tipos de items
            while ($array = mysqli_fetch_assoc($result_item_type)) {

                //alernar a cor da linha
                $type = switchBackground($type);

                //query para conseguir todos os items de cada tipo
                $query = mysqli_query($dbLink, "SELECT * FROM item WHERE item.item_type_id=" . $array["id"] . " ORDER BY name");

                //primeira coluna
                echo "<tr><td class=$type rowspan=".( mysqli_num_rows($query) == 0 ? 1: mysqli_num_rows($query)) .">" . $array["name"] . "</td>";//col1

                //se existe items daquele tipo
                if (mysqli_num_rows($query) != 0) {
                    //loop pelos items
                    while ($row2 = mysqli_fetch_assoc($query)) {

                        $type2 = switchBackground($type2);

                        echo "<td class=$type2>" . $row2["id"] . "</td>" .
                            "<td class=$type2>" . $row2["name"] . "</td>" .
                            "<td class=$type2>" . $row2["state"] . "</td>";

                        //if ($array["name"] == "dado_de_crianca") {

                            switch ($row2["state"]) {
                                case "active":
                                    echo"<td class=$type2>
                                            <a href=".$edicao_de_dados_page.'?estado=editar&tipo=item&id='.$row2["id"].">[Editar]</a>  
                                            <a href=".$edicao_de_dados_page.'?estado=desativar&tipo=item&id='.$row2["id"].">[Desativar]</a>  
                                            <a href=".$edicao_de_dados_page.'?estado=editar&tipo=item&id='.$row2["id"].">[Apagar]</a>  
                                        </td>";
                                    break;
                                case "inactive":
                                    echo "<td class=$type2>
                                            <a href=".$edicao_de_dados_page.'?estado=editar&tipo=item&id='.$row2["id"].">[Editar]</a>  
                                            <a href=".$edicao_de_dados_page.'?estado=ativar&tipo=item&id='.$row2["id"].">[Ativar]</a>  
                                            <a href=".$edicao_de_dados_page.'?estado=apagar&tipo=item&id='.$row2["id"].">[Apagar]</a>
                                        </td>";
                                    break;
                                default:
                                    echo "<td class=$type2>
                                            <a href=".$edicao_de_dados_page.'?estado=editar&tipo=item&id='.$row2["id"].">[Editar]</a>  
                                            <a href=".$edicao_de_dados_page.'?estado=apagar&tipo=item&id='.$row2["id"].">[Apagar]</a>
                                        </td>";
                                    break;
                            }
                            echo "</tr>";

                    }

                } else {
                    //rowspan e texto "sem items relacionados"
                    $type2 = switchBackground($type2);
                    echo "<td class=$type2  colspan=4>Sem items relacionados</td></tr>";
                }
            }
            echo "</table>";

            //parte de inserção de evalores
            echo "<h3 class='sub_title'>Gestão de itens - introdução</h3>
            <p class='form_input_title'>Nome do item</p>
            <form method='post' action=" . $current_page . ">
            <input type='text' placeholder='Nome' name='nome'>
            <p class='form_input_title'>Tipo</p>";

                //query para os obter os nomes dos tipos
                $result_item_type = mysqli_query($dbLink, "SELECT name,item_type.id FROM item_type");
                //criar a lista de radiobuttons tipos de items

                echo "<ul>";
                while ($row = mysqli_fetch_assoc($result_item_type)) {
                    echo "<li>
                        <input type='radio' id='radio_type_'" . $row["name"] . " name='radio_type' value=" . $row["id"] . "> 
                        <label for='radio_type_'" . $row["name"] . ">" . $row["name"] . "</label>
                      <br>
                      </li>";
                }
                echo "</ul>";

                //criar a lista de radiobuttons dos estados
                echo "<p class='form_input_title'>Estado</p>
            <ul>
                <li> 
                    <input type='radio' id='rad_state_active' name='rad_state' value='Active'>
                    <label for='rad_state_active'>Active</label><br> 
                </li>   
                <li>
                    <input type='radio' id='rad_state_inactive' name='rad_state' value='Inactive'>  
                    <label for='rad_state_inactive'>Inactive</label><br>
                </li>
            </ul>
            <input type='hidden' name='estado' value='inserir'>
            <hr>
            <button type='submit' class='continueButton'>Inserir Item</button>
            <!--<input type='submit' name='submit' value='Inserir Item' > -->
            </form>";

            $_SESSION["Item_added"]=false;

        //caso não exista items
        } else {

            echo "<tr>
                    <td colspan=5 class='row1'>Não há tipos de items</td>
                  </tr>
                  </table>";
        }

//se ja existir um submit mostra o resultado de tentar adicionar
} else if ($_REQUEST["estado"] == 'inserir') {

       // $mysqli = connect();
        echo "<h3 class='sub_title'>Gestão de itens - Inserção</h3>";

        //verificar o campo nome se está vazio
        $valid_form = true;
        if ( empty($_REQUEST["nome"]) ) {
            $valid_form = false;
            $list["nome"] = 'Nome';
        }
        //verificar o campo state se foi escolhido
        if (!isset($_REQUEST["rad_state"])){
            $valid_form = false;
            $list["state"] = 'Estado';
        }
        //verificar o campo tipo se foi escolhido
        if (!isset($_REQUEST["radio_type"])){
            $valid_form = false;
            $list["type"] = 'Tipo';
        }

        //se todos preenchidos
        if ($valid_form && !$_SESSION["Item_added"] ) {

            //Inserção
            if (mysqli_query($dbLink, 'INSERT INTO item(name,state,item_type_id) VALUES ("' . $_REQUEST["nome"] . '","' . $_REQUEST["rad_state"] . '",' . $_REQUEST["radio_type"] . ')')) {

                //mostrar o Sucesso
                echo "<div class='success'>
                <p id='suc_main'> A linha seguinte foi inserida com <span id='suc'> Successo </span> á tabela <span id='suc'> Item </span> </p>  
                </div>
                <table>
                    <tr class='tableHead'>
                        <td>id</td>
                        <td>Nome</td>
                        <td>Item Type id</td>
                        <td>Estado</td>
                    </tr> 
                    <tr class='row1'>
                        <td>".mysqli_insert_id($dbLink)."</td>
                        <td>" . $_REQUEST["nome"] . "</td>
                        <td>" . $_REQUEST["radio_type"] . "</td>
                        <td>" . $_REQUEST["rad_state"] . "</td>
                    </tr> 
                </table>
                <a href=$current_page> <button class='continueButton' >Continuar</button> </a>";

                $_SESSION["Item_added"]=true;

            } else {
                //mostrar o Insucesso
                echo "<div class='unsuccess'> 
                        <p id='obg_main' > A inserção  <span id='obg'> Falhou: </span>  </p>
                      </div>";
                voltar_atras();
            }

        } else { //caso o formolário esteja inclompleto

             if( $_SESSION["Item_added"] ){

                 echo "<div class='unsuccess'>
                         <p id='obg_main' > A inserção <span id='obg'>Já foi executada!</span></p>
                         </div>
                         <a href=$current_page> <button class='continueButton' >Continuar</button> </a>";

             }else{//erro de campo

                echo "<div class='unsuccess warning_list' > 
                    <p id='obg_main' > O(s) campo(s) seguinte(s) é(são) <span id='obg'> Obrigatório(s): </span>  </p>
                    <ul>";
                foreach ($list as $item) {
                    echo "<li class='warning_list'>$item</li>";
                }

                echo"<br></ul></div>";
                voltar_atras();
             }
        }

    } else {
        echo $_REQUEST["estado"];
    }
}else {
    echo "<br>
          <div class='unsuccess'>
          <p id='obg_main'>Não tem<span id='obg'> autorização </span>para aceder á página<span id='obg'> Gestão de items </span></p>
          </div>";
}
?>

