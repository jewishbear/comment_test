<?php
class CommentTracker
{
    private $name;
    private $pass;
    private $host;
    private $db;
    private $my;
    private $del;  // there's no big need to have there vars
    private $stop; // but it's working now and they are involved
    
    function __construct(){ //default params
        $this->name='root';
        $this->pass='';
        $this->host='localhost';
        $this->db='comment_db';
        $this->del=0;
        $this->stop=0;
    }

    function connect(){ //connect to db
        $this->my=new mysqli($this->host, $this->name, $this->pass, $this->db); //connect
        if($this->my->connect_errno > 0){
            die('Unable to connect to database [' . $this->my->connect_error . ']'); //or not?
        }
    }

    function disconnect(){ //close connection
        $this->my->close(); //just like this. if we connect in separate method, so we must disconnect in separate method too
    }
    
    function generate_comment($c_id, $c_pid, $c_name, $c_text, $c_level ){ //shitty things here
        $c_margin=40*$c_level+20; //base margin is 20. increases by 40 each level 
        echo
        '<div class="row comm" style="margin-left:'.$c_margin.'px">'. //level-based margin
                '
                <div class="col-xs-1">
                    <p align="right"><b>'.$c_name.':</b></p>' . //putting name
                '</div>
                <div class="col-xs-4">
                    '.$c_text. ''. //putting text
                    '<div >
                        <details>
                            <summary>Answer</summary>
                                <form class="form-horizontal comm-ans" action="add.php/" role="form" method="GET">
                                    <div class="form-group">
                                        <label for="comName_'.$c_id.'" class="col-xs-2 control-label">Name:</label>
                                        <div class="col-xs-4">
                                            <input type="text" class="form-control" id="comName_'.$c_id.'" name="c_name">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="comText_'.$c_id.'" class="col-xs-2 control-label">Text:</label>
                                        <div class="col-xs-6">
                                            <textarea id="comText_'.$c_id.'" class="form-control" rows="5" name="c_text"></textarea>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="col-xs-2"></div>
                                        <div class="col-xs-6">
                                            <div align="right">
                                                <input class="hidden" name="p_id" value="'.$c_id.'">'.       //that's how we can get ID of comment in future (for PARENT_ID)
                                                '<input class="hidden" name="c_level" value="'.$c_level.'">'.//that's how we can get LEVEL of comment in future
                                                '<button type="submit" class="btn btn-default">Submit</button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                        </details>
                    </div>
                 </div>
                 <div class="col-xs-1">
                    <form class="form-inline comm-del" action="delete.php" method="GET">
                            <input class="hidden" name="c_del" value="'.$c_id.'">'. //comment ID for deletion
                            '<button class="btn btn-danger" type="submit">Delete</button>
                    </form>
                 </div>
         </div>';
    }
    
    function show(){ //show list of comments
        $this->connect(); //open connection

        $query="SELECT * FROM comments WHERE LEVEL=0"; //lets start from level 0
        try{
            $q=$this->my->query($query);
            while($row=$q->fetch_assoc()){
                $this->generate_comment($row['ID'], $row['PARENT_ID'], $row['NAME'], $row['TEXT'], $row['LEVEL']); //making some html
                $this->do_magic($row['ID']); //look bellow for this
            }
        }catch (mysqli_sql_exception $e){
            echo '$e';
        }
        $this->disconnect(); //close connection
    }
    
    function do_magic($id){ //recursion warning
        $query="SELECT * FROM comments WHERE PARENT_ID=".$id; //we are looking for children of base comment
        $q=$this->my->query($query);
        while($row=$q->fetch_assoc()){ //so if there is some we generate them and repeat
            $this->generate_comment($row['ID'], $row['PARENT_ID'], $row['NAME'], $row['TEXT'], $row['LEVEL']);
            $this->do_magic($row['ID']);
        }
    }
    
    function add($c_name, $c_text, $p_id, $c_level){ //add comment
        $this->connect(); //open connection
        $query="INSERT INTO comments (PARENT_ID, NAME, TEXT, LEVEL) VALUES (?, ?, ?, ?)"; //lets place some values
        try{ //try try try   do we need all of these???                                   // LEVEL means hmm level of comment, 0 is base, 1 is child of base and so on
            $q=$this->my->prepare($query);
            if($q){
                try{
                    $q->bind_param('issi',$p_id, $c_name, $c_text, $c_level); //so bind them
                    $q->execute();                                            //and go
                }catch (mysqli_sql_exception $e){
                    echo $e;
                }
            }else{
                echo 'query failed';
            }
        }catch (mysqli_sql_exception $e){
            echo $e;
        }
        $this->disconnect(); //close connection
    }

    
    function delete($c_id){ //delete comment
        $this->connect(); //open connection

        $this->stop=$c_id; //store top comment id

        $query="SELECT ID FROM comments WHERE PARENT_ID=".$this->stop; 
        $q=$this->my->query($query);
        if(!empty($q)){                                                 // there was issue with top comment not deleted
            $query = "DELETE FROM comments WHERE ID=" .$this->stop;     // this little block fixed it
            $this->my->query($query);
        }

        $this->find_last($c_id); // search & destroy

        $this->del=0;
        $this->stop=0;

        $this->disconnect(); //close connection
    }



    function find_last($id){
        $query="SELECT ID FROM comments WHERE PARENT_ID=".$id; // looking for parents
        $q=$this->my->query($query);                           // while there are some
        while($row=$q->fetch_assoc()){
            $this->del=$row['ID'];

            $this->find_last($this->del); //we need to deeper
        }
        if(!$q->fetch_assoc()){             //looks like he is child-free
            $this->del_magic($this->del);   //time to kill
        }
    }

    function del_magic($id){
        if($id) {
            if ($id == $this->stop) {                           //if he is last and oldest
                $query = "DELETE FROM comments WHERE ID=".$id;  //kill him witch special honors
                $this->my->query($query);
            } else {
                $query = "SELECT PARENT_ID FROM comments WHERE ID=".$id;
                try {
                    $q = $this->my->query($query);
                    $row = $q->fetch_assoc();
                    $next_id = $row['PARENT_ID'];
                    $query = "DELETE FROM comments WHERE ID=".$id; 
                    $this->my->query($query);                      //kill
                    $this->del_magic($next_id);                    //continue massacre
                } catch (mysqli_sql_exception $e) {
                    echo $e;
                }
            }
        }
    }

    function edit(){ //edit comment (optional feature)
        $this->connect(); //open connection

        $this->disconnect(); //close connection
    }
}