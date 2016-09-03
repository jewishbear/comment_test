<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Comments</title>
    <script src="bootstrap.min.js"></script>
    <link rel="stylesheet" href="bootstrap.min.css">
    <link rel="stylesheet" href="addition.css">
</head>
<body>
<div class="row">
    <h1 align="center">Comments</h1>
</div>
<?php
function __autoload($class_name) {
    include $class_name . '.php';
}

$a=new CommentTracker();
$a->show(); //showing existing comments
?>


<div class="row">
    <div class="col-xs-1">

    </div>
    <div class="col-xs-6">
        <h1>Leave your comment here</h1>
    </div>
</div>
<div class="row">
    <div class="col-xs-6">
        <form class="form-horizontal comm-ans" action="add.php/" role="form" method="GET">
            <div class="form-group">
                <label for="comName" class="col-xs-2 control-label">Name:</label>
                <div class="col-xs-6">
                    <input type="text" class="form-control" id="comName" name="c_name">
                </div>
            </div>
            <div class="form-group">
                <label for="comText" class="col-xs-2 control-label">Text:</label>
                <div class="col-xs-6">
                    <textarea id="comText" class="form-control" rows="5" name="c_text"></textarea>
                </div>
            </div>
            <div class="form-group">
                <div class="col-xs-2"></div>
                <div class="col-xs-6">
                    <div align="right">
                        <input class="hidden" name="p_id" value=0>
                        <input class="hidden" name="c_level" value=-1>
                        <button type="submit" class="btn btn-default">Submit</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
</body>
</html>
