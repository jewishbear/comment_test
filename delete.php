<?php
function __autoload($class_name) {
    include $class_name . '.php';
}

if(isset($_GET['c_del'])){
    $a=new CommentTracker();
    $a->delete($_GET['c_del']);
    header('Location: /comment_test/index.php'); //go back (no ajax?)
}