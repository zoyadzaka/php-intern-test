<?php
$kolom = 7;

for ($i = 0; $i < $kolom; $i++) {
    for($j = 0; $j < $kolom; $j++) {
        if ($i == $j || $i + $j == $kolom - 1) {
            echo "X";
        } else {
            echo "0";
        }
    }
    echo PHP_EOL;
}
?>