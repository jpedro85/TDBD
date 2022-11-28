<?php
require_once("custom/php/common.php");
if (checkCapability("insert values")) {
    if (!isset($_POST['estado'])) {
        $myDB = connect();
        echo "<p>16</p>";
        echo "<h3 class='form_input_title'>Inserção de valores - criança - procurar</h3>";
        echo
            "<form method='post' action=" . $current_page . ">
        <input type='text' name='nome' placeholder='Nome criança'>
        <input type='text' name='data' placeholder='AAAA-MM-DD'>
        <input type='hidden' name='estado' value='escolher_crianca'>
        <hr>
        <input type='submit' name='submit' value='Procurar' >
        </form>";
    } else if (isset($_POST['estado']) && $_POST['estado'] == 'escolher_crianca') {
        echo "<h3 class='form_input_title'>Gestão de unidades - inserção</h3>";
        $myDB = connect();
        if (!empty($_POST['nome']) && !empty($_POST['data'])) {
            //procura nome e data
            $name = $_POST['nome'];
            $data = $_POST['data'];
            $procurarnomeedata= 'SELECT child.name,child.birth_date FROM child WHERE child.name LIKE "%'.$name.'%" && child.birth_date LIKE "%'.$data.'%"';
            $resultadonomeedata=mysqli_query($myDB, $procurarnomeedata);
            while ($show = mysqli_fetch_assoc($resultadonomeedata)) {
                echo $show["name"] . $show["birth_date"] . "<br>";
            }
            echo "<a href=" . $current_page . ">Continuar</a>";
        } elseif (!empty($_POST['nome']) && empty($_POST['data'])) {
            //apenas nome
            $nome = $_POST['nome'];
            $procurarnome = 'SELECT child.name,child.birth_date FROM child WHERE child.name like "%' . $nome . '%"';
            $resultadonome = mysqli_query($myDB, $procurarnome);
            while ($show = mysqli_fetch_assoc($resultadonome)) {
                echo $show["name"] . $show["birth_date"] . "<br>";
            }
            echo "<a href=" . $current_page . ">Continuar</a>";
        } elseif (empty($_POST['nome']) && !empty($_POST['data'])) {
            //apenas data
            $data = $_POST['data'];
            $procurardata = 'SELECT child.name,child.birth_date FROM child WHERE child.birth_date like "%' . $data . '%"';
            $resultadodata = mysqli_query($myDB, $procurardata);
            while ($show = mysqli_fetch_assoc($resultadodata)) {
                echo $show["name"] . $show["birth_date"] . "<br>";
            }
            echo "<a href=" . $current_page . ">Continuar</a>";
        } elseif (empty($_POST['nome']) && empty($_POST['data'])) {
            echo "<br>
          <div class='unsuccess'>
          <p id='obg_main'><span id='obg'>Nenhum campo foi preenchido</span></p>
          <ul><a href=" . $current_page . ">Continuar</a></ul>
          </div>";
        }
    } else {
        echo "<h3>Outro estado</h3>";
    }

} else {
    echo "<br>
          <div class='unsuccess'>
          <p id='obg_main'>Não tem<span id='obg'> autorização </span>para aceder á página<span id='obg'> Inserção de valores </span></p>
          </div>";
}
?>