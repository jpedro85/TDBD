<?php
require_once("custom/php/common.php");

$dbLink = connect();
if (checkCapability("manage_subitems")) {
    if (!mysqli_select_db($dbLink, "bitnami_wordpress")) {
        die("Connection to DB failed: " . mysqli_connect_error());
    } else {
        if (array_key_exists('estado', get_defined_vars()) && $_REQUEST["estado"] == "inserir") {

        } else {//estado inicial
            $queryChild = "SELECT id, name, birth_date, tutor_name, tutor_phone, tutor_email FROM child ORDER BY name ASC";
            $resultChild = mysqli_query($dbLink, $queryChild);
            if (mysqli_num_rows($resultChild) != 0) {
                echo "<div>
                     <table>
                     <tbody>
                        <tr class='tableHead' width='50%'>
                           <th>Nome</th>
                           <th>Data de nascimento</th>
                           <th>Enc. de Educação</th>
                           <th>Telefone do Enc.</th>
                           <th>E-mail</th>
                           <th>Registos</th>
                        </tr>";
                $bckgType = 'row2_registo';
                while ($rowChild = mysqli_fetch_assoc($resultChild)) {
                    if ($bckgType == 'row1_registo') {
                        $bckgType = 'row2_registo';
                    } else {
                        $bckgType = 'row1_registo';
                    }
                    echo '<tr class="'.$bckgType.'">
                                <td>' . $rowChild["name"] . '</td>
                                <td>' . $rowChild["birth_date"] . '</td>
                                <td>' . $rowChild["tutor_name"] . '</td>
                                <td>' . $rowChild["tutor_phone"] . '</td>
                                <td>' . $rowChild["tutor_email"] . '</td>';
                    $info = "";
                    $queryItem = "SELECT id,name FROM item ORDER BY name ASC";
                    $resultItem = mysqli_query($dbLink, $queryItem);
                    while ($rowItem = mysqli_fetch_assoc($resultItem)) {
                        $querySubitem = "SELECT id,name FROM subitem WHERE item_id=" . $rowItem["id"] ;
                        $resultSubitem = mysqli_query($dbLink, $querySubitem);
                        $itemName = strtoupper($rowItem["name"]) . ": ";
                        $done = false;
                        while ($rowSubitem = mysqli_fetch_assoc($resultSubitem)) {
                            $queryValue = "SELECT value FROM value WHERE child_id =" . $rowChild["id"] . " AND subitem_id=" . $rowSubitem["id"];
                            $resultValue = mysqli_query($dbLink, $queryValue);

                            $subitemName = "<strong>" . $rowSubitem["name"] . "</strong> (";
                            $counter = 1;
                            if (mysqli_num_rows($resultValue) != 0) {
                                if (!$done) {
                                    $info .= $itemName ;
                                    $done = true;
                                }
                                $info.= $subitemName;
                                while ($rowValue = mysqli_fetch_assoc($resultValue)) {
                                    if (mysqli_num_rows($resultValue) == $counter) {
                                        $info .= $rowValue["value"] . "); ";
                                    } else if (!empty($rowValue["value"]) && $counter < mysqli_num_rows($resultValue)) {
                                        $info .= $rowValue["value"] . ",";
                                        $counter++;
                                    } else {
                                        $counter++;
                                    }
                                }
                            }
                        }
                        if($done) $info .= "<br>";
                    }
                    echo "<td>$info</td></tr>";
                }
                echo '</tbody></table></div>';
                //campos de Introduçao de valores
            } else {
                echo "<div class='unsuccess warnings'>
                        <span><b>Não há crianças</b></span>
                      </div>";
            }
        }
    }
} else {
    echo "<div class='unsuccess warnings'>
            <span><b>Não tem autorização para aceder a esta página</b></span>
          </div>";
}
?>
