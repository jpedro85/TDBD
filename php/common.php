<?php
/*
 * ligação wordpress à BD.
 * $result = mysqli_query($link,$query);
 * require_once("custom/php/common.php");
 * ligação wordpress à BD.
 */
Global $dbLink;
$dbLink= connect();
//mysqli_autocommit(false);

global $current_page;
$current_page = get_site_url().'/'.basename(get_permalink());

Global $edicao_de_dados_page;
$edicao_de_dados_page = get_site_url().'/edicao-de-dados';

function reset_edicao_dados(){
    if(isset($_SESSION["dado_alterado_bool"])){
        $_SESSION["dado_alterado_bool"]=false;
    }
}

function connect() {
    $link = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    if ($link->connect_error) {
        die('Connection failed: ' . $link->connect_error);
        echo "Not Connected\n";
    }

    return $link;
}

function voltar_atras(){

    echo "<script type='text/javascript'>document.write(\"<a href='javascript:history.back()'> <button class='continueButton' >Voltar Atrás</button> </a>\");</script>
    <noscript>
    <a href='" . $_SERVER['HTTP_REFERER'] . "</a>
    </noscript>";

}

function reset_my_session() {
    if (session_status() != PHP_SESSION_NONE) {
        session_destroy();
    }
}

function get_enum_values($connection, $table, $column )
{
    $query = " SHOW COLUMNS FROM `$table` LIKE '$column' ";
    $result = mysqli_query($connection, $query );
    $row = mysqli_fetch_array($result , MYSQLI_NUM );
    #extract the values
    #the values are enclosed in single quotes
    #and separated by commas
    $regex = "/'(.*?)'/";
    preg_match_all( $regex , $row[1], $enum_array );
    $enum_fields = $enum_array[1];
    return( $enum_fields );
}

function checkCapability($capability)
{
    return is_user_logged_in() && current_user_can($capability);
}

function switchBackground($background)
{
    if ($background == 'row1') {
        $background = 'row2';
    } else {
        $background = 'row1';
    }
    return $background;
}

?>


