<?php
function __autoload($class_name) {
    include $class_name . '.php'; 
}

if(isset($_GET['c_text']) && isset($_GET['c_name']) && isset($_GET['p_id']) && isset($_GET['c_level']) && !empty($_GET['c_text'])){ //yes. here we are using GET method. why? because $_POST was empty for no reason, so it didn't work at all
    $a=new CommentTracker();
    $_GET['c_level']=$_GET['c_level']+1;
    if (!empty($_GET['c_name'])) { //check if visitor haven't signed
        try {
            $a->add($_GET['c_name'], $_GET['c_text'], $_GET['p_id'], $_GET['c_level']); //sending data to adding method
            header('Location: /comment_test/index.php'); //go back (no ajax?)
        } catch (mysqli_sql_exception $e) {
            echo $e;
        }
    } else {
        try {
            $a->add("anonymous", $_GET['c_text'], $_GET['p_id'], $_GET['c_level']); //now he is anonymous
            header('Location: /comment_test/index.php'); //go back (no ajax?)
        } catch (mysqli_sql_exception $e) {
            echo $e;
        }
    }

}else{
    echo "Something went wrong! Your comment wasn't added.";
}

