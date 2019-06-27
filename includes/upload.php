<?php
/**
 * @copyright  2017 Aleksandr Pyshnov
 * @link       http://pyshnov.ru
 * @author     Aleksandr Pyshnov <aleksandr@pyshnov.ru>
 */

// TODO Нужно переделать, данный пример взят из сети

// Required: anonymous function reference number as explained above.
$funcNum = $_GET['CKEditorFuncNum'] ;

// Check the $_FILES array and save the file. Assign the correct path to a variable ($url).
$url = '/uploads/' . $_FILES['upload']['name'];
// Usually you will only assign something here if the file could not be uploaded.
$message = 'Файл загружен';

$tmp_name = $_FILES['upload']['tmp_name'];

move_uploaded_file($tmp_name, dirname(dirname(__DIR__)) . $url);

echo "<script type='text/javascript'>window.parent.CKEDITOR.tools.callFunction($funcNum, '$url', '$message');</script>";