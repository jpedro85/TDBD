<?php
require_once("custom/php/common.php");
echo "60<br>";
//verificações dos campos
//estados = {"editar","ativar","desativar","apagar"}
//tipos = {"subitem","item","valor_permitido","unidade","resgisto"}
//id >=0;
// get_site_url().'/'.edicao-de-dados()

// componentes = resgistos ; items unidades subitems permitidos
//?component=""&estado=editar apagar etc

mysqli_autocommit($dbLink,false);

//reset request
//if ( isset($_SESSION["dado_alterado_last_estado"]) && isset($_REQUEST["estado"]) && ( ( $_SESSION["dado_alterado_last_estado"]!=$_REQUEST["estado"] ) || ( $_SESSION["dado_alterado_last_tipo"]!=$_REQUEST["tipo"] ) || ( $_SESSION["dado_alterado_last_id"]!=$_REQUEST["id"])  ) )
  //  $_SESSION["dado_alterado_bool"]=false;

if( isset($_SESSION["dado_alterado_bool"]) && !$_SESSION["dado_alterado_bool"]  && isset( $_REQUEST["estado"] ) && isset( $_REQUEST["tipo"] ) && isset( $_REQUEST["id"] ) )
{

    if( $_REQUEST["estado"] == "editar" ){

        if( $_REQUEST["tipo"] == "item" ){

            if(!isset($_REQUEST["update"]) ){

                if($resul = mysqli_query($dbLink,'SELECT item.id,item.name,item.state,item.item_type_id FROM item WHERE item.id='.$_REQUEST["id"]) ) {

                    if(mysqli_num_rows($resul) != 0){

                        $item = mysqli_fetch_assoc($resul);

                        echo "<h3 class='sub_title'>Edição de dados- Alterar valores</h3>
                             <p class='form_input_title'>Nome do item</p>
                             <form method='post' action=" . $current_page . '?estado=' . $_REQUEST["estado"] . '&tipo=' . $_REQUEST["tipo"] . '&id=' . $_REQUEST["id"] . ">
                                <input type='text' placeholder='Nome' name='nome' value='" . $item["name"] . "'>
                                <p class='form_input_title'>Tipo</p>
                                <ul>";

                        $result_item_type = mysqli_query($dbLink, "SELECT name,id FROM item_type");
                        while ($row = mysqli_fetch_assoc($result_item_type)) {
                            echo "<li>
                                <input ".( $row["id"]==$item["item_type_id"] ? 'checked': '')." type='radio' id='radio_type_'" . $row["name"] . " name='radio_type' value=" . $row["id"] . "> 
                                <label for='radio_type_'" . $row["name"] . ">" . $row["name"] . "</label>
                                <br>
                            </li>";
                        }

                        echo "   </ul>
                                <p class='form_input_title'>Estado</p>
                                <ul>
                                    <li> 
                                        <input type='radio' id='rad_state_active' name='rad_state' value='Active' ".( $item["state"]=='active'? 'checked': '') .">
                                        <label for='rad_state_active'>Active</label><br> 
                                    </li>   
                                    <li>
                                        <input type='radio' id='rad_state_inactive' name='rad_state' value='Inactive' ".( $item["state"]=='inactive'? 'checked': '') ." >  
                                        <label for='rad_state_inactive'>Inactive</label><br>
                                    </li>
                                </ul>
                                <input type='hidden' name='estado' value='" . $_REQUEST["estado"] . "'>
                                <input type='hidden' name='tipo' value='" . $_REQUEST["tipo"] . "'>
                                <input type='hidden' name='id' value=" . $_REQUEST["id"] . ">
                                <input type='hidden' name='update' value='true'>
                                <hr>
                                <button type='submit' class='continueButton'>Alterar</button>
                                <a href='".get_site_url().'/gestao-de-items'."' ><button type='button' class='continueButton'>Cancelar</button></a>
                            </form>";

                    } else
                        show_error_geral("gestao-de-items","Não existe nenhum item com o id igual a ".$_REQUEST["id"].".");

                } else
                    show_error_geral("gestao-de-items","A execução da query falhou.");

            } else {

                //verificar o campo nome se está vazio
                $valid_form = true;
                if ( empty($_REQUEST["nome"]) ){
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

                if($valid_form) {

                    mysqli_begin_transaction($dbLink);
                    if (mysqli_query($dbLink, 'UPDATE item SET item.item_type_id=' . $_REQUEST["radio_type"] . ' ,item.name="' . $_REQUEST["nome"] . '" ,item.state="' . $_REQUEST["rad_state"] . '" WHERE item.id=' . $_REQUEST["id"]))
                        show_success_tipo("gestao-de-items", $_REQUEST["estado"], "item", $_REQUEST["id"], $dbLink);
                    else
                        rollback($_REQUEST["estado"], $dbLink);

                } else
                    show_campos_errados($list);

            }

        } else if( $_REQUEST["tipo"] == "subitem" ) {

            if(!isset($_REQUEST["update"]) ){

                if($resul = mysqli_query($dbLink,'SELECT * FROM subitem WHERE subitem.id='.$_REQUEST["id"]) ) {

                    if(mysqli_num_rows($resul) != 0) {

                        $subitem = mysqli_fetch_assoc($resul);

                        echo "<h3 class='sub_title'>Edição de dados- Alterar valores</h3> <!--nome-->
                             <p class='form_input_title'>Nome do subitem</p>
                             <form method='post' action=" . $current_page . '?estado=' . $_REQUEST["estado"] . '&tipo=' . $_REQUEST["tipo"] . '&id=' . $_REQUEST["id"] . ">
                                <input type='text' placeholder='Nome' name='nome' value='" . $subitem["name"] . "'> <!--nome -> subitem.name-->
                                <p class='form_input_title'>Tipo de valor</p>
                                <ul>";

                        $valType = get_enum_values($dbLink, "subitem", "value_type"); //--subType -> campo value_type
                        foreach ($valType as $type) {//radio buttons do tipo de valor
                            echo '<li><input ' . ($subitem["value_type"] == $type ? "checked" : "") . ' type="radio" name="subType" value="' . $type . '"><label>' . $type . '</label></li>';
                        }
                        echo "</ul>";

                        if ($resultItem = mysqli_query($dbLink, "Select item.id,item.name From item ORDER BY name")){

                            if( mysqli_num_rows($resultItem) != 0 ){

                                //Selectbox
                                echo "<p class='form_input_title'>Item</p> <!-- --item_id -> campo item_id -->
                                      <select name='item_id' >";
                                while ($item = mysqli_fetch_assoc($resultItem)) {
                                    $option = str_replace(" ", "_", $item["name"]);
                                    echo '<option value="'.$item["id"].'" '.( $subitem["item_id"]==$item["id"] ? "selected ":"").'>' . $item["name"] . '</option>';
                                }
                                echo '</select><br>';

                            } else
                                show_error_geral("gestao-de-subitens","Nenhum item encontrado.");
                        } else
                            show_error_geral("gestao-de-subitens","A procura de items falhou.");

                        echo "<p class='form_input_title'>Tipo do campo no formolário</p>";// --formType -> form_field_type
                        $formFieldType = get_enum_values($dbLink, "subitem", "form_field_type");
                        foreach ($formFieldType as $formType) {
                            echo '<li><input type="radio" name="formType" value="' . $formType . '" '.( $subitem["form_field_type"]==$formType ? "checked": "").'><label>' . $formType . '</label></li>';
                        }

                        echo "<p class='form_input_title'>Tipo de unidade</p>";
                        if ($resultUnit_name = mysqli_query($dbLink, "SELECT * FROM subitem_unit_type")) {

                           if(mysqli_num_rows($resultUnit_name) != 0){

                                echo '<select  name="subUnitType" id="subUnitType" >'; // --subUnitType -> campo unit_type_id
                                while ($unit = mysqli_fetch_assoc($resultUnit_name)) {
                                        echo '<option value="' . $unit["id"] .'" '.( $subitem["unit_type_id"]==$unit["id"] ? "selected ":"").' >' . $unit["name"] . '</option>';
                                }
                                echo '</select><br>';

                            } else
                                show_error_geral("gestao-de-subitens","Nenhum tipo de unidade encontrado.");
                        } else
                            show_error_geral("gestao-de-subitens","A procura de tipos de unidade falhou.");

                        echo'<h4 class="form_input_title">Ordem do campo no formulario</h4>
                            <input type="text" id="formOrder" name="formOrder" placeholder="0" value="'.$subitem["form_field_order"].'" ><br><!--formorder -> campo form_fiel_order -->
                            <p class="form_input_title">Obrigatório</p>
                            <input type="radio" name="mandatory" value=1 '.($subitem["mandatory"]==1 ? "checked" : "").'><label>Sim</label><br> <!-- --mandatory -> campo obrigatório -->
                            <input type="radio" name="mandatory" value=0 '.($subitem["mandatory"]==0 ? "checked" : "").'><label>Nao</label><br>
                            <input type="hidden" name="update" value="inserir"><br>
                            <hr>
                            <button type="submit" class="continueButton">Alterar</button>
                            <a href="'.get_site_url()."/gestao-de-subitens".'" ><button type="button" class="continueButton">Cancelar</button></a>
                        </form>';

                        //aqui


                    } else
                        show_error_geral("gestao-de-subitens","Não existe nenhum subitem com o id igual a ".$_REQUEST["id"].".");

                } else
                    show_error_geral("gestao-de-subitens","A procura do subitem falhou.");

            } else {

                $valid_form = true;
                if (empty($_REQUEST["nome"]) ) {
                    $list["nome"] = "Nome do subitem";
                    $valid_form = false;
                }
                if (empty($_REQUEST["subType"])) {
                    $list["subType"] .= "Tipo do valor";
                    $valid_form = false;
                }
                if (empty($_REQUEST["item_id"]) && is_numeric($_REQUEST["item_id"])) {
                    $list["item_id"] = "Item";
                    $valid_form = false;
                }
                if (empty($_REQUEST["formType"])) {
                    $list["formType"] = "Tipo do campo no formulario";
                    $valid_form = false;
                }
                if (empty($_REQUEST["subUnitType"])) {
                    $list["subUnitType"] = "Tipo de unidade";
                    $valid_form = false;
                }
                if (empty($_REQUEST["formOrder"]) || !filter_var($_REQUEST["formOrder"], FILTER_VALIDATE_INT) || $_REQUEST["formOrder"] < 0) {
                    $list["formOrder"] = "Ordem do campo no formulario";
                    $valid_form = false;
                }
                if (!isset($_REQUEST["mandatory"]) ) {
                    $list["mandatory"] = "Obrigatório";
                    $valid_form = false;
                }

                if ($valid_form){

                    mysqli_begin_transaction($dbLink);
                    if( mysqli_query($dbLink,'UPDATE subitem SET subitem.name="'.$_REQUEST["nome"] .'" , subitem.item_id='.$_REQUEST["item_id"].', subitem.value_type="'.$_REQUEST["subType"].'",subitem.form_field_type="'.$_REQUEST["formType"].'", subitem.unit_type_id='.$_REQUEST["subUnitType"].', subitem.form_field_order='.$_REQUEST["formOrder"].' , subitem.mandatory= '.$_REQUEST["mandatory"].' WHERE subitem.id='.$_REQUEST["id"]  ) )
                        show_success_tipo("gestao-de-subitens",$_REQUEST["estado"] ,"item",$_REQUEST["id"],$dbLink);
                    else
                        rollback( $_REQUEST["estado"] ,$dbLink);

                } else
                    show_campos_errados($list);

            }

        } else if( $_REQUEST["tipo"] == "valor_permitido" ) {

            if(!isset($_REQUEST["update"]) ){

                if($resul = mysqli_query($dbLink,'SELECT subitem_allowed_value.id,subitem_allowed_value.value FROM subitem_allowed_value WHERE subitem_allowed_value.id='.$_REQUEST["id"]) ) {

                    if(mysqli_num_rows($resul) != 0){

                        $valor_permitido = mysqli_fetch_assoc($resul);

                        echo"<h3 class='sub_title'>Edição de dados- Alterar valores</h3>
                             <p class='form_input_title'>Nome do valor permitido</p>
                             <form method='post' action=" . $current_page . '?estado=' . $_REQUEST["estado"] . '&tipo=' . $_REQUEST["tipo"] . '&id=' . $_REQUEST["id"] . ">
                                <input type='text' placeholder='Nome' name='value' value='" . $valor_permitido["value"] . "'>
                                <input type='hidden' name='update' value='true'>
                                <hr>
                                <button type='submit' class='continueButton'>Alterar</button>
                                <a href='".get_site_url().'/gestao-de-valores-permitidos'."' ><button type='button' class='continueButton'>Cancelar</button></a>
                             </form>";

                    } else
                        show_error_geral("gestao-de-valores-permitidos","Não existe nenhum valor permitido com o id igual a ".$_REQUEST["id"].".");

                } else
                    show_error_geral("gestao-de-valores-permitidos","A execução da query falhou.");

            } else {

                if( !empty($_REQUEST["value"]) ) {

                    mysqli_begin_transaction($dbLink);
                    if (mysqli_query($dbLink, 'UPDATE subitem_allowed_value SET subitem_allowed_value.value="' . $_REQUEST["value"] . '" WHERE subitem_allowed_value.id=' . $_REQUEST["id"]))
                        show_success_tipo("gestao-de-valores-permitidos", $_REQUEST["estado"], "subitem allowed value", $_REQUEST["id"], $dbLink);
                    else
                        rollback($_REQUEST["estado"], $dbLink);

                } else {

                    $list["value"] = "Nome do valor permitido";
                    show_campos_errados($list);
                }
            }

        } else if( $_REQUEST["tipo"] == "unidade" ) {

            if(!isset($_REQUEST["update"]) ){

                if($resul = mysqli_query($dbLink,'SELECT * FROM subitem_unit_type WHERE subitem_unit_type.id='.$_REQUEST["id"]) ) {

                    if(mysqli_num_rows($resul) != 0){

                        $tipo_unidade = mysqli_fetch_assoc($resul);

                        echo"<h3 class='sub_title'>Edição de dados- Alterar valores</h3>
                             <p class='form_input_title'>Nome da unidade</p>
                             <form method='post' action=" . $current_page . '?estado=' . $_REQUEST["estado"] . '&tipo=' . $_REQUEST["tipo"] . '&id=' . $_REQUEST["id"] . ">
                                <input type='text' placeholder='Nome da unidade' name='value' value='" . $tipo_unidade["name"] . "'>
                                <input type='hidden' name='update' value='true'>
                                <hr>
                                <button type='submit' class='continueButton'>Alterar</button>
                                <a href='".get_site_url().'/gestor-de-unidades'."' ><button type='button' class='continueButton'>Cancelar</button></a>
                             </form>";

                    } else
                        show_error_geral("gestor-de-unidades","Não existe nenhum tipo de unidade com o id igual a ".$_REQUEST["id"].".");

                } else
                    show_error_geral("gestor-de-unidades","A execução da query falhou.");

            } else {

                if( !empty($_REQUEST["value"]) ) {

                    mysqli_begin_transaction($dbLink);
                    if (mysqli_query($dbLink, 'UPDATE subitem_unit_type SET subitem_unit_type.name="' . $_REQUEST["value"] . '" WHERE subitem_unit_type.id=' . $_REQUEST["id"]))
                        show_success_tipo("gestor-de-unidades", $_REQUEST["estado"], "subitem unit type", $_REQUEST["id"], $dbLink);
                    else
                        rollback($_REQUEST["estado"], $dbLink);

                } else {

                    $list["value"] = "Nome da unidade";
                    show_campos_errados($list);
                }
            }

        } else if( $_REQUEST["tipo"] == "resgisto" ) {
            teste();
        } else {
            show_error_tipo();
        }

    } else if( $_REQUEST["estado"] == "ativar" || $_REQUEST["estado"] == "desativar" ) {

        if ($_REQUEST["tipo"] == "item") {

            mysqli_begin_transaction($dbLink);
            if (mysqli_query($dbLink, 'UPDATE item SET item.state="' . ($_REQUEST["estado"] == "ativar" ? "active" : "inactive") . '" WHERE item.id=' . $_REQUEST["id"]))
                show_success_tipo("gestao-de-items", $_REQUEST["estado"], "Item", $_REQUEST["id"], $dbLink);
            else
                rollback($_REQUEST["estado"], $dbLink);

        } else if ($_REQUEST["tipo"] == "subitem") {

            mysqli_begin_transaction($dbLink);
            if (mysqli_query($dbLink, 'UPDATE subitem SET subitem.state="' . ($_REQUEST["estado"] == "ativar" ? "active" : "inactive") . '" WHERE subitem.id=' . $_REQUEST["id"]))
                show_success_tipo("gestao-de-subitens", $_REQUEST["estado"], "Subitem", $_REQUEST["id"], $dbLink);
            else
                rollback($_REQUEST["estado"], $dbLink);

        } else if ($_REQUEST["tipo"] == "valor_permitido") {

            mysqli_begin_transaction($dbLink);
            if (mysqli_query($dbLink, 'UPDATE subitem_allowed_value SET subitem_allowed_value.state="' . ($_REQUEST["estado"] == "ativar" ? "active" : "inactive") . '" WHERE subitem_allowed_value.id=' . $_REQUEST["id"]))
                show_success_tipo("gestao-de-valores-permitidos", $_REQUEST["estado"], "Subitem Allowed Value", $_REQUEST["id"], $dbLink);
            else
                rollback($_REQUEST["estado"], $dbLink);

        } else if ($_REQUEST["tipo"] == "unidade" || $_REQUEST["tipo"] == "resgisto") {

            echo "<div class='unsuccess'>
                 <p id='obg_main' > A operação <span id='obg'>  ".$_REQUEST["estado"]."</span> não está disponível para a gestão de <span id='obg'> ".$_REQUEST["tipo"]."s </span>  </p>
             </div>                
             <a href=".$_SESSION["dado_alterado_page"]."'> <button class='continueButton' >Continuar</button> </a>";

        } else {
            show_error_tipo();
        }

    } else if( $_REQUEST["estado"] == "apagar" ){

        if( $_REQUEST["tipo"] == "item" ){

            if(!isset($_REQUEST["update"]) ){

                echo "<div class='question' > 
                        <p id='obg_main'> <span id='obg'> Na operaão serão tambem apagados: </span> </p>
                        <ul>
                            <li class='warning_list' >Os valores assossiados a subitems assossiados ao item.</li>
                            <li class='warning_list' >Os valores permitidos assossiados a subitems assossiados ao item.</li>
                            <li class='warning_list' >Os subitems assossiados ao item.</li>
                        </ul>
                        <p id='obg_main'> Deseja continuar com a opreração <span id='obg'>apagar</span> o item com o <span id='obg'>id</span> ".$_REQUEST["id"]." ? </p>
                        </div>
                     <form method='post' action=" . $current_page . '?estado=' . $_REQUEST["estado"] . '&tipo=' . $_REQUEST["tipo"] . '&id=' . $_REQUEST["id"] . ">
                        <input type='hidden' name='update' value='true'>
                        <button type='submit' class='continueButton'>Apagar</button>
                        <a href='".get_site_url().'/gestao-de-itens'."' ><button type='button' class='continueButton'>Cancelar</button></a>
                     </form>";

            } else {

                if($resul_subitems=mysqli_query($dbLink,'SELECT subitem.name,subitem.id FROM subitem WHERE subitem.item_id='.$_REQUEST["id"] ) ){

                    $fail = false;
                    mysqli_begin_transaction($dbLink);

                    while ($subitem = mysqli_fetch_assoc($resul_subitems)) {

                        if (mysqli_query($dbLink, 'DELETE FROM value WHERE value.subitem_id=' . $subitem["id"])) {

                            if (mysqli_query($dbLink, 'DELETE FROM subitem_allowed_value WHERE subitem_allowed_value.subitem_id=' . $subitem["id"])) {

                                if (!mysqli_query($dbLink, 'DELETE FROM subitem WHERE subitem.id=' . $subitem["id"])) {

                                    rollback($_REQUEST["estado"], $dbLink, "para elinimar o subitem com o id " . $subitem["id"]);
                                    $fail = true;
                                    break;
                                }
                            } else {
                                rollback($_REQUEST["estado"], $dbLink, "para apagar o(s) valore(s) premitido(s) onde o subitem id é " . $subitem["id"]);
                                $fail = true;
                                break;
                            }
                        } else {
                            rollback($_REQUEST["estado"], $dbLink, "para apagar valores onde subitem id é " . $subitem["id"]);
                            $fail = true;
                            break;
                        }
                    }

                    if(!$fail && mysqli_query($dbLink, 'DELETE FROM item WHERE item.id='.$_REQUEST["id"]) ){

                        if($subitem) {
                            show_success_geral("Foram apagados todos os valores assossiados a subitems assossiados ao item com id igual a  " . $_REQUEST["id"]);
                            show_success_geral("Foram apagados todos os valores permitidos assossiados a subitems assossiados ao item com id igual a " . $_REQUEST["id"]);
                            show_success_geral("Foram apagados todos os subitems assossiados ao item com id igual a " . $_REQUEST["id"]);
                        }
                        show_success_tipo("gestao-de-items", $_REQUEST["estado"], "item", $_REQUEST["id"], $dbLink);

                    } else
                        rollback($_REQUEST["estado"], $dbLink, "ao apagar o item onde o id é ".$subitem["id"] );


                } else
                    show_error_geral("gestao-de-itens","Não foi possível procurar todos os subtiems onde o item id é ".$_REQUEST["id"]);


            }

        } else if( $_REQUEST["tipo"] == "subitem" ) {

            if(!isset($_REQUEST["update"]) ){

                echo "<div class='question' > 
                        <p id='obg_main'> <span id='obg'> Na operaão serão tambem apagados: </span> </p>
                        <ul>
                            <li class='warning_list' >Os valores assossiados ao subitem assossiados ao subitem.</li>
                            <li class='warning_list' >Os valores permitidos assossiados ao subitem.</li>
                        </ul>
                        <p id='obg_main'> Deseja continuar com a opreração <span id='obg'>apagar</span> o subitem com o <span id='obg'>id</span>> ".$_REQUEST["id"]." ? </p>
                        </div>
                     <form method='post' action=" . $current_page . '?estado=' . $_REQUEST["estado"] . '&tipo=' . $_REQUEST["tipo"] . '&id=' . $_REQUEST["id"] . ">
                        <input type='hidden' name='update' value='true'>
                        <button type='submit' class='continueButton'>Apagar</button>
                        <a href='".get_site_url().'/gestao-de-subitens'."' ><button type='button' class='continueButton'>Cancelar</button></a>
                     </form>";

            } else {

                mysqli_begin_transaction($dbLink);
                if(mysqli_query($dbLink, 'DELETE FROM value WHERE value.subitem_id='.$_REQUEST["id"]) ) {

                    show_success_geral("Foram apagados todos os valores assossiados ao subitem com id igual a ".$_REQUEST["id"]);

                    if (mysqli_query($dbLink, 'DELETE FROM subitem_allowed_value WHERE subitem_allowed_value.subitem_id=' . $_REQUEST["id"])) {

                        show_success_geral("Foram apagados todos os valores permitidos assossiados ao subitem com id igual a ".$_REQUEST["id"]);

                        if (mysqli_query($dbLink, 'DELETE FROM subitem WHERE subitem.id=' . $_REQUEST["id"]))
                            show_success_tipo("gestor-de-unidades", $_REQUEST["estado"], "item", $_REQUEST["id"], $dbLink);
                        else
                            rollback($_REQUEST["estado"], $dbLink , "elinimar o subitem");
                    }else
                        rollback($_REQUEST["estado"], $dbLink , "ao pagar o(s) valore(s) premitido(s) onde o subitem id é ".$_REQUEST["id"] );
                }else
                    rollback($_REQUEST["estado"], $dbLink, "apagar valores onde subitem id é ".$_REQUEST["id"] );
            }

        } else if( $_REQUEST["tipo"] == "valor_permitido" ) {

            if(!isset($_REQUEST["update"]) ){

                echo "<div class='question' > 
                        <p id='obg_main'> Deseja continuar com a opreração <span id='obg'>apagar</span> o valor permitido com o <span id='obg'>id</span>> ".$_REQUEST["id"]." ? </p>
                        </div>
                     <form method='post' action=" . $current_page . '?estado=' . $_REQUEST["estado"] . '&tipo=' . $_REQUEST["tipo"] . '&id=' . $_REQUEST["id"] . ">
                        <input type='hidden' name='update' value='true'>
                        <button type='submit' class='continueButton'>Apagar</button>
                        <a href='".get_site_url().'/gestao-de-valores-permitidos'."' ><button type='button' class='continueButton'>Cancelar</button></a>
                     </form>";

            } else {

                mysqli_begin_transaction($dbLink);
               if (mysqli_query($dbLink, 'DELETE FROM subitem_allowed_value WHERE subitem_allowed_value.id=' . $_REQUEST["id"]))
                   show_success_tipo("gestao-de-valores-permitidos", $_REQUEST["estado"], "item", $_REQUEST["id"], $dbLink);
               else
                   rollback($_REQUEST["estado"], $dbLink , "elinimar item");

            }

        } else if( $_REQUEST["tipo"] == "unidade" ) {

            if(!isset($_REQUEST["update"]) ){

                echo "<div class='question' > 
                        <p id='obg_main'> <span id='obg'> Na operaão serão alterados: </span> </p>
                        <ul>
                            <li class='warning_list' >Os subitems com o unyt type id igual ".$_REQUEST["id"].".</li>
                        </ul>
                        <p id='obg_main'> Deseja continuar com a opreração <span id='obg'>apagar</span> o tipo de unidade com o <span id='obg'>id</span>> ".$_REQUEST["id"]." ? </p>
                        </div>
                     <form method='post' action=" . $current_page . '?estado=' . $_REQUEST["estado"] . '&tipo=' . $_REQUEST["tipo"] . '&id=' . $_REQUEST["id"] . ">
                        <input type='hidden' name='update' value='true'>
                        <button type='submit' class='continueButton'>Apagar</button>
                        <a href='".get_site_url().'/gestor-de-unidades'."' ><button type='button' class='continueButton'>Cancelar</button></a>
                     </form>";

            } else {

                mysqli_begin_transaction($dbLink);
                if(mysqli_query($dbLink, 'UPDATE subitem SET subitem.unit_type_id=NULL WHERE subitem.unit_type_id='.$_REQUEST["id"]) ) {

                    show_success_geral("A operação editar foi concluida no(s) tuplo(s) com o campo item id igual a ".$_REQUEST["id"]);

                    if (mysqli_query($dbLink, 'DELETE FROM subitem_unit_type WHERE subitem_unit_type.id=' . $_REQUEST["id"]))
                        show_success_tipo("gestor-de-unidades", $_REQUEST["estado"], "item", $_REQUEST["id"], $dbLink);
                    else
                        rollback($_REQUEST["estado"], $dbLink , "elinimar item");

                }else
                    rollback($_REQUEST["estado"], $dbLink, "editar subitem item id");
            }

        } else if( $_REQUEST["tipo"] == "resgisto" ) {
            teste();
        } else {
            show_error_tipo();
        }

    } else {

        echo '<div class="unsuccess">
            <p id="obg_main"> <span id="obg" >Erro</span> ao carregar a página. <span id="obg" >O valor do "estado" tem de ser:</span> </p>
            <ul class="warning_list">
                <li class="warning_list" >editar</li>
                <li class="warning_list" >ativar</li>
                <li class="warning_list" >desativar</li>
                <li class="warning_list" >apagar</li>
            </ul>
          </div>';
        voltar_atras();

    }

} else {

    if( isset($_SESSION["dado_alterado_bool"]) && $_SESSION["dado_alterado_bool"] && isset($_REQUEST["estado"]) )
    {
        echo '<div class="unsuccess">
                 <p id="obg_main" > A operação '.$_SESSION["dado_alterado"].' no  id <span id="obg"> '. $_SESSION["dado_alterado_last_id"].' </span> na  tabela <span id="obg"> '.$_SESSION["dado_alterado_last_tabela"].' já foi executada!</span>  </p>
             </div> 
             <a href="'.$_SESSION["dado_alterado_page"].'"> <button class="continueButton" >Continuar</button> </a> ';

    } else {

        echo '<div class="unsuccess"> 
            <p id="obg_main"> <span id="obg" >Erro</span> ao carregar a página.</p>
            <ul>';

        if (!isset($_REQUEST["estado"]))
            echo '<li class="warning_list" >A variavel <span id="obg" >"estado"</span> não foi defenida</li>';
        if (!isset($_REQUEST["tipo"]))
            echo '<li class="warning_list" >A variavel <span id="obg" >"tipo"</span> não foi defenida</li>';
        if (!isset($_REQUEST["id"]))
            echo '<li class="warning_list" >A variavel <span id="obg" >"id"</span> não foi defenida</li>';

        echo '   <br>
            </ul>
          </div>';
        voltar_atras();
    }

}

function show_error_tipo() {
    echo "<div class='unsuccess'>
            <p id='obg_main'> <span id='obg' >Erro</span> ao carregar a página. <span id='obg' >O valor de '".$_REQUEST["tipo"]."' tem de ser:</span> </p>
            <ul>
                <li class='warning_list' >item</li>
                <li class='warning_list' >subitem</li>
                <li class='warning_list' >valor_permitido</li>
                <li class='warning_list' >unidade</li>
                <li class='warning_list' >resgisto</li>
            </ul>
            <br>
          </div>";
    voltar_atras();
}

function show_success_geral($message){

    echo "<div class='success'>
            <p id='suc_main'> $message </p>
          </div>";

}

function show_success_tipo( $page,$categoria,$tabela,$id,$link,$mesage="no tuplo com id",$button=true ) {
    mysqli_commit($link);
    $_SESSION["dado_alterado_page"]=get_site_url()."/$page";

    echo "<div class='success'>
            <p id='suc_main'> Operação <span id='suc' > $categoria </span> realizada com <span id='suc' >Sucesso</span> $mesage <span id='suc' > $id </span> na tabbela <span id='suc' > $tabela </span>.</p>
          </div>".
          ($button ? "<a href=".$_SESSION["dado_alterado_page"]."> <button class='continueButton' >Continuar</button> </a>" : '' ) ;

    $_SESSION["dado_alterado_bool"]=true;
    $_SESSION["dado_alterado"]=$categoria;
    $_SESSION["dado_alterado_last_estado"]=$_REQUEST["estado"];
    $_SESSION["dado_alterado_last_tipo"]=$_REQUEST["tipo"];
    $_SESSION["dado_alterado_last_id"]=$_REQUEST["id"];
    $_SESSION["dado_alterado_last_tabela"]=$tabela;

}

function show_error_geral($page,$message){
    $_SESSION["dado_alterado_page"]=get_site_url()."/$page";
    echo "<div class='success'>
            <p id='suc_main'> <span id='suc' > $message </span>. </p>
          </div>
          <a href=".$_SESSION["dado_alterado_page"]."> <button class='continueButton' >Continuar</button> </a>";
}

function show_campos_errados($list){
    echo "<div class='unsuccess' > 
                    <p id='obg_main' > O(s) campo(s) seguinte(s) é(são) <span id='obg'> Obrigatório(s): </span>  </p>
                    <ul>";
    foreach ($list as $item) {
        echo "<li class='warning_list'>$item</li>";
    }

    echo"<br></ul></div>";
    voltar_atras();
}

function rollback($categoria,$link,$message=""){
    mysqli_rollback($link);
    echo "<div class='unsuccess'>
            <p id='obg_main'> Operação <span id='obg' > $categoria não realizada:</span> </p>
            <ul>";
    if( !isset($_REQUEST["id"]) )
        echo "<li class='warning_list' >Id não definido</li>";
    else if( !is_numeric($_REQUEST["id"]) || $_REQUEST["id"]<0)
        echo "<li class='warning_list' >Id é inválido</li>";
    else
        echo "<li class='warning_list' >Erro ao executar a query $message</li>";
    echo"   <br>
            </ul>
          </div>";

    $_SESSION["dado_alterado_bool"]=false;

    voltar_atras();
}

function teste(){
    echo $_REQUEST["estado"]."<br>";
    echo $_REQUEST["tipo"]."<br>";
    echo $_REQUEST["id"]."<br>";
}

?>