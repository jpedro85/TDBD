<?php
require_once("custom/php/common.php");
echo "24<br>";

//estados = {"editar","ativar","desativar","apagar"}
//tipos = {"subitem","item","valor_permitido","unidade","resgisto"}
//id >=0;
// get_site_url().'/'.edicao-de-dados()

// componentes = resgistos ; items unidades subitems permitidos
//?component=""&estado=editar apagar etc

/*
 * <?php
try {
  $dbh = new PDO('odbc:SAMPLE', 'db2inst1', 'ibmdb2',
      array(PDO::ATTR_PERSISTENT => true));
  echo "Connected\n";
} catch (Exception $e) {
  die("Unable to connect: " . $e->getMessage());
}

try {
  $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  $dbh->beginTransaction();
  $dbh->exec("insert into staff (id, first, last) values (23, 'Joe', 'Bloggs')");
  $dbh->exec("insert into salarychange (id, amount, changedate)
      values (23, 50000, NOW())");
  $dbh->commit();

} catch (Exception $e) {
  $dbh->rollBack();
  echo "Failed: " . $e->getMessage();
}
?>
 *
 */
/*

mysqli_begin_transaction($mysqli);
mysqli_autocommit()
mysqli_rollback()
mysqli_savepoint()
User Contributed Notes 6 notes
mysqli_commit();
*/

//Global $mysqli;
//$mysqli= connect();
//$_SESSION["dado_alterado_bool"]=false;
mysqli_autocommit($mysqli,false);


//reset request
if ( isset($_SESSION["dado_alterado_last_estado"]) && ($_SESSION["dado_alterado_last_estado"]!=$_REQUEST["estado"] || $_SESSION["dado_alterado_last_tipo"]!=$_REQUEST["tipo"] || $_SESSION["dado_alterado_last_id"]!=$_REQUEST["id"]) )
    $_SESSION["dado_alterado_bool"]=false;

if( !$_SESSION["dado_alterado_bool"]  && isset( $_REQUEST["estado"] ) && isset( $_REQUEST["tipo"] ) && isset( $_REQUEST["id"] ) )
{

    if( $_REQUEST["estado"] == "editar" ){

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

    } else if( $_REQUEST["estado"] == "ativar" || $_REQUEST["estado"] == "desativar" ){

        if( $_REQUEST["tipo"] == "item" ){

            mysqli_begin_transaction($mysqli);
            if( mysqli_query($mysqli,'UPDATE item SET item.state="'.( $_REQUEST["estado"] == "ativar" ? "active" : "inactive" ).'" WHERE item.id='.$_REQUEST["id"]) )
                show_success_tipo("gestao-de-items",$_REQUEST["estado"] ,"item",$_REQUEST["id"],$mysqli);
            else
                rollback( $_REQUEST["estado"] ,$mysqli);

        } else if( $_REQUEST["tipo"] == "subitem" ) {
            teste();
        } else if( $_REQUEST["tipo"] == "valor_permitido" ) {

          /*  mysqli_begin_transaction($mysqli);
            if( mysqli_query($mysqli,'UPDATE item SET item.state="'.( $_REQUEST["estado"] == "ativar" ? "active" : "inactive" ).'" WHERE item.id='.$_REQUEST["id"]) )
                show_success_tipo("gestao-de-valores-permitidos",$_REQUEST["estado"] ,"subitem_alowed_value",$_REQUEST["id"],$mysqli);
            else
                rollback( $_REQUEST["estado"] ,$mysqli);*/
            teste();

        } else if( $_REQUEST["tipo"] == "unidade" ) {
            teste();
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

    if( $_SESSION["dado_alterado_bool"] )
    {
        echo "<div class='unsuccess'>
                 <p id='obg_main' > A operação ".$_SESSION["dado_alterado"]." no  id ". $_SESSION["dado_alterado_last_id"]." na  tabela ".$_SESSION["dado_alterado_last_tabela"]." <span id='obg'>já foi executada!</span>   </p>
             </div>                
             <a href=".$_SESSION["dado_alterado_page"]."'> <button class='continueButton' >Continuar</button> </a>";

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
            <p id='suc_main'> Operação <span id='suc' > $categoria </span> realizada com <span id='suc' >Sucesso</span> no tuplo com id igual $id na tabbela $tabela </p>
          </div>
          <a href=".$_SESSION["dado_alterado_page"]."> <button class='continueButton' >Continuar</button> </a>;";

    $_SESSION["dado_alterado_bool"]=true;
    $_SESSION["dado_alterado"]=$categoria;
    $_SESSION["dado_alterado_last_estado"]=$_REQUEST["estado"];
    $_SESSION["dado_alterado_last_tipo"]=$_REQUEST["tipo"];
    $_SESSION["dado_alterado_last_id"]=$_REQUEST["id"];
    $_SESSION["dado_alterado_last_tabela"]=$tabela;


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
    echo"  </ul>
          </div>";

    $_SESSION["dado_alterado"]=false;

    voltar_atras();
}

function teste(){
    echo $_REQUEST["estado"]."<br>";
    echo $_REQUEST["tipo"]."<br>";
    echo $_REQUEST["id"]."<br>";
}

?>