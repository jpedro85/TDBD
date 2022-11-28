<?php
//valores premitivos
require_once("custom/php/common.php");
//echo "132 <br>";
//links
//verificação cliente side
//transactions
if( checkCapability("manage_allowed_values") ) {

    if (!isset($_REQUEST["estado"])) {

        $mysqli = connect();
        $rowType = "row2";
        $rowType2 = "row2";
        $rowType3 = "row2";
        $result_item = "";

        echo"<table>
                <tr>
                    <td class='tableHead'>Item</td>
                    <td class='tableHead'>Subitem id</td>
                    <td class='tableHead'>Subitem name</td>
                    <td class='tableHead'>Valores permitidos id</td>
                    <td class='tableHead'>Valores permitidos</td>
                    <td class='tableHead'>Estado</td>
                    <td class='tableHead'>Ação</td>
                </tr>";

        $query_items ='SELECT item.id , item.name FROM item ORDER BY item.name;';
        $result_item = mysqli_query($mysqli,$query_items);

        //percorrer os items que tem sub items com enum
        while($item = mysqli_fetch_assoc( $result_item ) ) {

            //todos
            $colunas ='subitem.id AS sub_id, subitem.name , subitem.item_id ,subitem.value_type, subitem.mandatory , subitem.state AS sub_state , subitem_allowed_value.id AS value_id , subitem_allowed_value.value ,subitem_allowed_value.state';
            $query_Subitems = 'SELECT '.$colunas.' FROM subitem LEFT OUTER JOIN subitem_allowed_value ON subitem.id = subitem_allowed_value.subitem_id WHERE subitem.value_type="enum" AND subitem.item_id ='.$item["id"];
            $result_Subitems = mysqli_query($mysqli,$query_Subitems);

            //Agrupados (devolve os subitems)
            $result_Subitems_group = mysqli_query($mysqli,$query_Subitems.' GROUP BY subitem.name ');

            //aletrar tipode coluna
            $rowType = switchBackground($rowType);

            echo "<tr>";
            echo "<td class=$rowType rowspan=".($result_Subitems->num_rows == 0 ? 1 : $result_Subitems->num_rows).">". $item["name"] . "</td>";

            if ($result_Subitems_group->num_rows != 0) {

                //percorrer todos os subitems
                while ($subItem = mysqli_fetch_assoc($result_Subitems_group) ) {
                    //tem sempre um linha emobara com nulls
                    $result_SubSubitems = mysqli_query($mysqli,$query_Subitems.' AND subitem.name ="'.$subItem["name"].'"');

                    //aletrar tipode coluna
                    $rowType2 = switchBackground($rowType2);
                    $rowType3 = 'row2';

                    echo "<td class=$rowType2 rowspan=$result_SubSubitems->num_rows>". $subItem["sub_id"] ."</td>";
                    $ref = $current_page.'?estado=introducao&subitem='.$subItem["sub_id"];
                    echo "<td class=$rowType2 rowspan=$result_SubSubitems->num_rows><a href=$ref >[".$subItem["name"]."]</a></td>";

                    if($result_SubSubitems->num_rows != 0){

                        //percorrer todos os valores do subitem
                        while($subSubItem_value = mysqli_fetch_assoc($result_SubSubitems) )
                        {
                            //aletrar tipode coluna
                            $rowType3 = switchBackground($rowType3);

                            if( isset($subSubItem_value["value_id"]) || isset($subSubItem_value["value"]) || isset($subSubItem_value["state"])  ) {

                                echo "<td class=$rowType3>" . $subSubItem_value["value_id"] . "</td>";
                                echo "<td class=$rowType3>" . $subSubItem_value["value"] . "</td>";
                                echo "<td class=$rowType3>" . $subSubItem_value["state"] . "</td>";
                                echo "<td class=$rowType3>nada</td>";
                                echo "</tr>";

                            } else {

                                echo "<td colspan=4 class=$rowType3>Não existem valoes permitidos relacionados com este subitem</td>";
                                echo"</tr>";

                            }
                        }

                    } else {

                        //aletrar tipode coluna
                        $rowType3 = switchBackground($rowType3);
                        echo "<td colspan=4 class=$rowType3>Não existem valoes permitidos relacionados com este subitem</td>";
                        echo"</tr>";

                    }

                    echo"</tr>";

                }

                echo"</tr>";

            } else {
                //aletrar tipode coluna
                $rowType2 = switchBackground($rowType2);
                //aletrar tipode coluna
                $rowType3 = switchBackground($rowType3);
                echo"<td colspan=6 class=$rowType2 >Não há subitems especificados cujo tipo de valor seja ENUM</td>";
                echo"</tr>";
            }

        }

        echo"</table>";

    }else if ($_REQUEST["estado"] == 'introducao'){

        $_SESSION["subitem_id"] = $_REQUEST["subitem"];

        echo"<h3 class='sub_title'>Gestão de valores permitidos - introdução</h3>";
        echo"<form method='post' action=$current_page>
                <input type='text' name='nome' placeholder='Valor Permitido'>
                <input type='hidden' name='estado' value='inserir'>
                <hr>
                <input type='submit' name='submit' value='Inserir valor permitido'>
            </form>";

        $_SESSION["SubAllowedValue_added"]=false;

    } else if ($_REQUEST["estado"] == 'inserir'){

        echo"<h3 class='sub_title'>Gestão de valores permitidos - inserção</h3>";

        if( isset($_REQUEST["nome"]) && $_REQUEST["nome"]!="" && !$_SESSION["SubAllowedValue_added"] )
        {
            $mysqli = connect();
            $wuery_inert = 'INSERT INTO subitem_allowed_value(subitem_id,value,state) VALUES ('.$_SESSION["subitem_id"].',"'.$_REQUEST["nome"].'","active")';

            if (mysqli_query($mysqli, $wuery_inert)) {
                //Inserção com Sucesso mostrat sucesso

                echo "<div class='success'>
                <p id='suc_main'> A linha seguinte foi inserida com <span id='suc'> Successo </span> á tabela <span id='suc'> Subitem Allowed Value </span> </p>  
                </div>
                <table>
                    <tr class='tableHead'>
                        <td>id</td>
                        <td>Subitem id</td>
                        <td>Value</td>
                        <td>Estado</td>
                    </tr> 
                    <tr class='row2'>
                        <td>".mysqli_insert_id($mysqli)."</td>
                        <td>" . $_SESSION["subitem_id"] . "</td>
                        <td>" . $_REQUEST["nome"] . "</td>
                        <td>Active</td>
                    </tr> 
                </table>
                <a id='continuar' href=$current_page>Continuar</a>";

                $_SESSION["SubAllowedValue_added"]=true;

            } else {


                //mostrar o Insucesso
                echo "<div class='unsuccess'> 
                <p id='obg_main' > A inserção  <span id='obg'> Falhou: </span>  </p>";
                voltar_atras();
                echo "</div>";

            }

        } else {

            if($_SESSION["SubAllowedValue_added"])
            {

                echo "<div class='unsuccess'>
                         <p id='obg_main' > A inserção <span id='obg'>Já foi executada!</span></p>
                         <a id='continuar' href=$current_page>Continuar</a>
                         </div>";

            } else {

                echo "<div class='unsuccess' > 
                    <p id='obg_main' > O campo seguinte é <span id='obg'> Obrigatório: </span>  </p>
                    <ul>
                        <li class='warnig_list'>Nome</li>
                    <br>";
                voltar_atras();
                echo "</ul></div>";

            }
        }

    } else {
        echo "<p>OUTRO ESTADO</p>";
    }

} else {
    echo "<br>
          <div class='unsuccess'>
          <p id='obg_main'> Não tem  <span id='obg'>autorização </span> para aceder á página <span id='obg'> Gestão de valores permitidos </span></p>
          </div>";
}
?>