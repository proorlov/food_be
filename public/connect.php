<?php
/**
 * Created by PhpStorm.
 * User: mekenzh
 * Date: 26.02.16
 * Time: 23:44
 */
$db = mysqli_connect('codepr.ru','food','foodSECRET') or die('no_connect');
mysqli_select_db($db, 'food') or die('no_select');
mysqli_query($db, "SET CHARACTER SET 'utf8'");
