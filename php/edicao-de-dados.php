<?php
require_once("custom/php/common.php");
echo "47";
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
                                <input type='text' placeholder='Nome' name='nome' value=" . $item["name"] . ">
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
                                <input type='hidden' name='estado' value=" . $_REQUEST["estado"] . ">
                                <input type='hidden' name='tipo' value=" . $_REQUEST["tipo"] . ">
                                <input type='hidden' name='id' value=" . $_REQUEST["id"] . ">
                                <input type='hidden' name='update' value='true'>
                                <hr>
                                <button type='submit' class='continueButton'>Alterar</button>
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

                        echo "<h3 class='sub_title'>Edição de dados- Alterar valores</h3>//--nome--
                             <p class='form_input_title'>Nome do subitem</p>
                             <form method='post' action=" . $current_page . '?estado=' . $_REQUEST["estado"] . '&tipo=' . $_REQUEST["tipo"] . '&id=' . $_REQUEST["id"] . ">
                                <input type='text' placeholder='Nome' name='nome' value=" . $subitem["name"] . "> //--nome -> subitem.name
                                <p class='form_input_title'>Tipo de valor</p>
                                <ul>";

                        $valType = get_enum_values($dbLink, "subitem", "value_type");//--subType -> campo value_type
                        foreach ($valType as $type) {//radio buttons do tipo de valor
                            echo '<li><input ' . ($subitem["value_type"] == $type ? "checked" : "") . ' type="radio" name="subType" value="' . $type . '"><label>' . $type . '</label></li>';
                        }
                        echo "</ul>";

                        if ($resultItem = mysqli_query($dbLink, "Select item.id,item.name From item ORDER BY name")){

                            if( mysqli_num_rows($resultItem) != 0 ){

                                //Selectbox
                                echo "<p class='form_input_title'>Item</p> // --itemName -> campo item_id
                                      <select name='ItemName' >
                                      <option>Selecione Um item</option>";
                                while ($item = mysqli_fetch_assoc($resultItem)) {
                                    $option = str_replace(" ", "_", $item["name"]);
                                    echo '<option value="'.$item["id"].'" '.( $subitem["item_id"]==$item["id"] ? "selected ":"").'>' . $item["name"] . '</option>';
                                }
                                echo '</select><br>';

                            } else
                                show_error_geral("gestao-de-subitems","Nenhum item encontrado.");
                        } else
                            show_error_geral("gestao-de-subitems","A procura de items falhou.");

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
                                        echo '<option value=' . $unit["id"] .' '.( $subitem["unit_type_id"]==$unit["id"] ? "selected ":"").'>' . $unit["name"] . '</option>';
                                }
                                echo '</select><br>';

                            } else
                                show_error_geral("gestao-de-subitems","Nenhum tipo de unidade encontrado.");
                        } else
                            show_error_geral("gestao-de-subitems","A procura de tipos de unidade falhou.");

                        echo'<h4 class="form_input_title">Ordem do campo no formulario</h4>
                            <input type="text" id="formOrder" name="formOrder" placeholder="0" value="'.$subitem["form_field_order"].'"><br>// --formorder -> campo form_fiel_order
                            <p class="form_input_title">Obrigatório</p>
                            <input type="radio" name="mandatory" value="Sim" '.($subitem["mandatory"]==1 ? "checked" : "").'><label>Sim</label><br> // --mandatory -> campo obrigatório
                            <input type="radio" name="mandatory" value="Nao" '.($subitem["mandatory"]==0 ? "checked" : "").'><label>Nao</label><br>
                            <input type="hidden" name="update" value="inserir"><br>
                            <hr>
                            <button type="submit" class="continueButton">Alterar</button>
                        </form>';

                        //aqui


                    } else
                        show_error_geral("gestao-de-subitems","Não existe nenhum subitem com o id igual a ".$_REQUEST["id"].".");

                } else
                    show_error_geral("gestao-de-subitems","A procura do subitem falhou.");

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
                if (empty($_REQUEST["ItemName"]) && is_numeric($_REQUEST["ItemName"])) {
                    $list["ItemName"] = "Item";
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
                if (empty($_REQUEST["mandatory"])) {
                    $list["mandatory"] = "Obrigatório";
                    $valid_form = false;
                }

                if ($valid_form){

                    mysqli_begin_transaction($dbLink);
                    if( mysqli_query($dbLink,'UPDATE subitem SET subitem.name='.$_REQUEST["nome"].' , sunitem.item_id="'.$_REQUEST["ItemName"].'" , subitem.value_type="'.$_REQUEST["subType"].'",subitem.form_field_type="'.$_REQUEST["formType"].'", subitem.unit_type_id="'.$_REQUEST["subUnitType"].'", subitem.form_field_order'.$_REQUEST["formOrder"].' , subitem.mandatory= '.$_REQUEST["mandatory"].' WHERE subitem.id='.$_REQUEST["id"]  ) )
                        show_success_tipo("gestao-de-subitems",$_REQUEST["estado"] ,"item",$_REQUEST["id"],$dbLink);
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
                                <input type='text' placeholder='Nome' name='value' value=" . $valor_permitido["value"] . ">
                                <input type='hidden' name='update' value='true'>
                                <hr>
                                <button type='submit' class='continueButton'>Alterar</button>
                             </form>";

                    } else
                        show_error_geral("gestao-de-valores-permitidos","Não existe nenhum valor permitido com o id igual a ".$_REQUEST["id"].".");

                } else
                    show_error_geral("gestao-de-valores-permitidos","A execução da query falhou.");

            } else {

                mysqli_begin_transaction($dbLink);
                if( mysqli_query($dbLink,'UPDATE subitem_allowed_value SET subitem_allowed_value.value="'.$_REQUEST["value"].'" WHERE subitem_allowed_value.id='.$_REQUEST["id"]  ) )
                    show_success_tipo("gestao-de-valores-permitidos",$_REQUEST["estado"] ,"subitem allowed value",$_REQUEST["id"],$dbLink);
                else
                    rollback( $_REQUEST["estado"] ,$dbLink);

            }

        } else if( $_REQUEST["tipo"] == "unidade" ) {
            teste();
        } else if( $_REQUEST["tipo"] == "resgisto" ) {
            teste();
        } else {
            show_error_tipo();
        }

    } else if( $_REQUEST["estado"] == "ativar" || $_REQUEST["estado"] == "desativar" ){

        if( $_REQUEST["tipo"] == "item" ){

            mysqli_begin_transaction($dbLink);
            if( mysqli_query($dbLink,'UPDATE item SET item.state="'.( $_REQUEST["estado"] == "ativar" ? "active" : "inactive" ).'" WHERE item.id='.$_REQUEST["id"]) )
                show_success_tipo("gestao-de-items",$_REQUEST["estado"] ,"Item",$_REQUEST["id"],$dbLink);
            else
                rollback( $_REQUEST["estado"] ,$dbLink);

        } else if( $_REQUEST["tipo"] == "subitem" ) {

            mysqli_begin_transaction($dbLink);
            if( mysqli_query($dbLink,'UPDATE subitem SET subitem.state="'.( $_REQUEST["estado"] == "ativar" ? "active" : "inactive" ).'" WHERE subitem.id='.$_REQUEST["id"]) )
                show_success_tipo("gestao-de-subitens",$_REQUEST["estado"] ,"Subitem",$_REQUEST["id"],$dbLink);
            else
                rollback( $_REQUEST["estado"] ,$dbLink);

        } else if( $_REQUEST["tipo"] == "valor_permitido" ) {

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
            teste();
        } else if( $_REQUEST["tipo"] == "subitem" ) {
            teste();
        } else if( $_REQUEST["tipo"] == "valor_permitido" ) {
            teste();
        } else if( $_REQUEST["tipo"] == "unidade" ) {
            teste();
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
            <p id='obg_main'> <span id='obg' >Erro</span> ao carregar a página. <span id='obg' >O valor de 'tipo' tem de ser:</span> </p>
            <ul>
                <li class='warning_list' >item</li>
                <li class='warning_list' >subitem</li>
                <li class='warning_list' >valor_permitido</li>
                <li class='warning_list' >unidade</li>
                <li class='warning_list' >resgisto</li>
            </ul>
          </div>";
    voltar_atras();
}

function show_success_tipo($page,$categoria,$tabela,$id,$link) {
    mysqli_commit($link);
    $_SESSION["dado_alterado_page"]=get_site_url()."/$page";

    echo "<div class='success'>
            <p id='suc_main'> Operação <span id='suc' > $categoria </span> realizada com <span id='suc' >Sucesso</span> no tuplo com id <span id='suc' > $id </span> na tabbela <span id='suc' > $tabela </span>.</p>
          </div>
          <a href=".$_SESSION["dado_alterado_page"]."> <button class='continueButton' >Continuar</button> </a>";

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
    echo "<div class='unsuccess warning_list' > 
                    <p id='obg_main' > O(s) campo(s) seguinte(s) é(são) <span id='obg'> Obrigatório(s): </span>  </p>
                    <ul>";
    foreach ($list as $item) {
        echo "<li class='warning_list'>$item</li>";
    }

    echo"<br></ul></div>";
    voltar_atras();
}

function rollback($categoria,$link){
    mysqli_rollback($link);
    echo "<div class='unsuccess'>
            <p id='obg_main'> Operação <span id='obg' > $categoria não realizada:</span> </p>
            <ul>";
    if( !isset($_REQUEST["id"]) )
        echo "<li class='warning_list' >Id não definido</li>";
    else if( !is_numeric($_REQUEST["id"]) || $_REQUEST["id"]<0)
        echo "<li class='warning_list' >Id é inválido</li>";
    else
        echo "<li class='warning_list' >Erro ao executar a query</li>";
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