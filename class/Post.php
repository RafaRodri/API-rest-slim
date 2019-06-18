<?php
    class Post{
        public $id;
        public $title;
        public $text;

        /* V1
        */
        /* Get
         */
        //Buscar posts
        public function getPosts($id){
            $sql = "SELECT * FROM posts WHERE id_user = $id";
            $db = new db();
            $db = $db->connect();

            $stmt = $db->query($sql);
            $FetchPosts = $stmt->fetchAll(PDO::FETCH_OBJ);
            //$db = null;

            $user = new User();

            return array_map(array($user, 'posts'), $FetchPosts);
        }

        /* V2
        */
        /* Get
         */
        //Buscar posts
        public function getPosts2($id){
            $sql = "SELECT * FROM posts WHERE id_user = $id";
            $db = new db();
            $db = $db->connect();

            $stmt = $db->query($sql);
            $FetchPosts = $stmt->fetchAll(PDO::FETCH_OBJ);
            //$db = null;
            
            $user = new User();
            if(Count($FetchPosts) > 0){
                return array(1,array_map(array($user, 'posts'), $FetchPosts));
            }else{
                return array(0,$user->noRecordsFound());
            }
        }

        /* Post
         */
        //Cadastrar post vs2
        public function postPosts2($idUser,$title,$text){
            try{
                //Insere post no DB
                $sql = "INSERT INTO posts (id_user,title,text) VALUES (:id,:title,:text)";
                $db = new db();
                $db = $db->connect();

                $stmt = $db->prepare($sql);

                $stmt->bindParam(':id', $idUser);
                $stmt->bindParam(':title', $title);
                $stmt->bindParam(':text',  $text);

                $stmt->execute();
                //$db = null;


                if($stmt->rowCount()>0){
                    return array(1);
                }

            }catch(PDOException $e){
                if(isset($e->errorInfo[1]) && $e->errorInfo[1] == 1452){
                    $statusError = 422;
                    $user = new User();
                    $error = array("errors" => $user->errorTryCatch($statusError,"Restrição de chave estrangeira","Não é possível adicionar ou atualizar um registro 'filho' de um 'pai' que não existe"));
                    return array(0,$error,$statusError);
                }else{
                    echo '{"error":{"text":'.$e->getMessage().'}}';
                }
            }
        }

    }
?>