<?php
function block_mobile_access($message = null) {
    if (!$message) {
        $message = "<h1 style='color:red; text-align:center; padding:20px; margin-top:100px; border:1px solid red;'>Mobile phones are NOT ALLOWED in the exam.</h1>";
    }

    if (preg_match('/Mobi|Android|iPhone|iPad/i', $_SERVER['HTTP_USER_AGENT'])) {
        die($message);
    }
}

// Call the function at the top of your page

?>