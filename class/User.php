<?php
    class User{
        public $id;
        public $name;
        public $email;
        public $posts;
        
        /* V1
        */
        /* Get
         */
        //Buscar usuários
        public function getUsers(){
            try{
                $sql = "SELECT * FROM users";
                $db = new db();
                $db = $db->connect();

                $stmt = $db->query($sql);
                $FetchUsers = $stmt->fetchAll(PDO::FETCH_OBJ);
                //$db = null;

                return array_map(array($this, 'users'), $FetchUsers);

            }catch (PDOException $e){
                echo '{"error":{"text":'.$e->getMessage().'}}';
            }
        }

        //Buscar usuário
        public function getUser($id){
            try{
                $sql = "SELECT * FROM users WHERE id=$id";
                $db = new db();
                $db = $db->connect();

                $stmt = $db->query($sql);
                $FetchUser = $stmt->fetchAll(PDO::FETCH_OBJ);
                //$db = null;

                return array_map(array($this, 'users'), $FetchUser);

            }catch (PDOException $e){
                echo '{"error":{"text":'.$e->getMessage().'}}';
            }
        }


        /* V2
        */
        /* Get
         */
        //Buscar usuários v2
        public function getUsers2($limit,$currentPage){
            try{
                //Valores metadata
                $metadata = new Metadata();
                $metadata->count = $this->countUsers();
                $metadata->limit = $limit;   //por página
                $metadata->totalPages = ceil($metadata->count / $metadata->limit);

                //Verifica se valor da página informada existe ou já ultrapassou o limite
                if($currentPage > $metadata->totalPages){
                    $currentPage = $metadata->totalPages;
                }elseif($currentPage < 0){
                    $currentPage = 1;
                }
                $metadata->offset = $limit*($currentPage-1);
                $metadata->currentPage = $currentPage;


                //Buscar usuários no DB
                $sql = "SELECT * FROM users LIMIT ".$metadata->limit." OFFSET ".$metadata->offset;
                $db = new db();
                $db = $db->connect();

                $stmt = $db->query($sql);
                $FetchUsers = $stmt->fetchAll(PDO::FETCH_OBJ);
                //$db = null;

                $metadata->size = count($FetchUsers);


                //Atribuir dados ao objeto
                $usersV2 = new apiData();
                $usersV2->meta = $metadata;
                $usersV2->data = array_map(array($this, 'users'), $FetchUsers);

                return $usersV2;

            }catch (PDOException $e){
                echo '{"error":{"text":'.$e->getMessage().'}}';
            }
        }

        //Buscar usuário v2
        public function getUser2($id){
            try{
                $sql = "SELECT * FROM users WHERE id=$id";
                $db = new db();
                $db = $db->connect();

                $stmt = $db->query($sql);
                $FetchUser = $stmt->fetchAll(PDO::FETCH_OBJ);
                //$db = null;
                
                if(Count($FetchUser) > 0){
                    return array(1, array_map(array($this, 'users'), $FetchUser));
                }else{
                    return 0;
                }

            }catch (PDOException $e){
                echo '{"error":{"text":'.$e->getMessage().'}}';
            }
        }

        /* Post
         */
        //Cadastrar usuário v2
        public function postUsers2($data){
            try{
                //Insere usuário no DB
                $sql = "INSERT INTO users (name,email) VALUES (:name,:email)";
                $db = new db();
                $db = $db->connect();

                $stmt = $db->prepare($sql);

                foreach($data as $key => $value){
                    $stmt->bindValue(':'.$key, $value);
                }

                $stmt->execute();
                $lastID = $db->lastInsertId();
                //$db = null;

                return $lastID;

            }catch(PDOException $e){
                echo '{"error": {"text": '.$e->getMessage().'}';
            }
        }

        /* Put
         */
        //Alterar usuário v2
        //public function putUsers2($id,$name,$email){
        public function putUsers2($id,$data){
            try{
                $user = $this->getUser2($id);

                //PUT - Se existe no DB
                if(isset($user[1][0]->id)){

                    //Altera usuário no DB
                    $sql = "UPDATE users SET name = :name, email = :email WHERE id = ".$id;
                    $db = new db();
                    $db = $db->connect();

                    $stmt = $db->prepare($sql);

                    foreach($data as $key => $value){
                        $stmt->bindValue(':'.$key, $value);
                    }

                    $stmt->execute();
                    
                    if($stmt->rowCount() > 0){
                        return array(1);
                    }else{
                        $sql = "SELECT * FROM users WHERE id = ".$id;
                        $stmt = $db->prepare($sql);
                        $stmt->execute();

                        if($stmt->rowCount() > 0){
                            //Nenhuma alteração, mas mostra o usuário encontrado
                            return array(1);
                        }else{
                            //Nenhuma alteração, porque usuário não existe no DB
                            return array(0);
                        }

                    }
                
                    //$db = null;
                }
                //POST - Se não existe no DB
                else{
                    $newUser = $this->postUsers2($name,$email);
                    return array(2,$newUser);
                }



            }catch(PDOException $e){
                if(isset($e->errorInfo[1]) && $e->errorInfo[1] == 1064){
                    $statusError = 422;
                    $error = array("errors" => $this->errorTryCatch($statusError,"Erro de sintaxe no seu SQL",""));
                    return array(0,$error,$statusError);
                }else{
                    echo '{"error": {"text": '.$e->getMessage().'}';
                }
            }
        }

        /* Patch
         */
        //Alterar usuário v2
        public function patchUsers2($id,$name,$email){
            try{
                //Constuir sql conforme dados informados
                if (!is_null($name)){
                    $clausula = 'name = :name';

                    if (!is_null($email)){
                        $clausula .= ', email = :email';
                    }
                }else{
                    if (!is_null($email)){
                        $clausula = 'email = :email';
                    }
                }

                //Altera usuário no DB
                $sql = "UPDATE users SET ".$clausula." WHERE id = ".$id;

                $db = new db();
                $db = $db->connect();

                $stmt = $db->prepare($sql);

                //Referenciar parâmetros conforme dados informados
                if (!is_null($name)){
                    $stmt->bindParam(':name', $name);

                    if (!is_null($email)){
                        $stmt->bindParam(':email',  $email);
                    }
                }else{
                    if (!is_null($email)){
                        $stmt->bindParam(':email',  $email);
                    }
                }

                $stmt->execute();

                if($stmt->rowCount() > 0){
                    return 1;
                }else{
                    $sql = "SELECT * FROM users WHERE id = ".$id;
                    $stmt = $db->prepare($sql);
                    $stmt->execute();

                    if($stmt->rowCount() > 0){
                        //Nenhuma alteração, mas mostra o usuário encontrado
                        return 1;
                    }else{
                        //Nenhuma alteração, porque usuário não existe no DB
                        return 0;
                    }

                }

                //$db = null;

            }catch(PDOException $e){
                echo '{"error": {"text": '.$e->getMessage().'}';
            }
        }

        /* Delete
         */
        //Deletar usuário v2
        public function deleteUser2($id){
            try{
                $db = new db();
                $db = $db->connect();

                $sqlPosts = "DELETE FROM posts WHERE id_user = $id";
                $stmt = $db->query($sqlPosts);

                $sql = "DELETE FROM users WHERE id = $id";
                $stmt = $db->query($sql);
                //$db = null;
                
                if($stmt->rowCount()>0){
                    return array(1);
                }else{
                    return array(0, $this->noRecordsFound(),400);
                }

            }catch (PDOException $e){
                if(isset($e->errorInfo[1]) && $e->errorInfo[1] == 1451){
                    $statusError = 422;
                    $error = array("errors" => $this->errorTryCatch($statusError,"Restrição de chave estrangeira","Não é possível excluir ou atualizar um registro 'pai' que tem um 'filho'"));
                    return array(0,$error,$statusError);
                }else{
                    $error = array("errors" => (object) array('detail' => $e->getMessage()));
                    return array(0,$error,500);
                }
            }
        }



        /* Functions
         */
        //Atribuir dados ao usuário
        public function users($item){
            $user = new User();
            $user->id=$item->id;
            $user->name=$item->name;
            $user->email=$item->email;

            $post = new Post();
            $user->posts=$post->getPosts($item->id);

            return $user;
        }

        //Atribuir dados ao post
        public function posts($item){
            $post = new Post();
            $post->id=$item->id;
            $post->title=$item->title;
            $post->text=$item->text;

            return $post;
        }

        //Contador de registros
        public function countUsers(){
            $sql = "SELECT COUNT(*) AS count FROM users";
            $db = new db();
            $db = $db->connect();

            $stmt = $db->query($sql);
            $FetchSize = $stmt->fetch(PDO::FETCH_ASSOC);
            //$db = null;


            return intval($FetchSize['count']);
        }
        
        //Valida se parâmetro foi enviado
        public function paramNotInformed($field){
            return (object) array(  
                'status' => '400',
                //'source' => (object) array('pointer' => $field),
                'title' => "Parâmetro não informado", 
                'detail' => "O parâmetro '".$field."' é obrigatório");
        }
        
        //Valida se valor do parâmetro não é nulo
        public function paramIsNull($field){
            return (object) array(  
                'status' => '400',
                //'source' => (object) array('pointer' => $field),
                'title' => "Parâmetro null", 
                'detail' => "O valor do parâmetro '".$field."' deve ser informado");
        }

        //Valida email
        public function validaEmail($email){
            if(filter_var($email, FILTER_VALIDATE_EMAIL)){
                return array(true);
            }else{
                $fetch = (object) array(  
                    'status' => '422',
                    //'source' => (object) array('pointer' => "email"),
                    'title' => "Parâmetro inválido", 
                    'detail' => "O 'email' informado não está em um formato válido");
                return array(false,$fetch);
            }
        }
        
        //Constrói objeto error
        public function errorTryCatch($status,$title,$detail){
            return (object) array(
                'status' => $status,
                'title' => $title,
                'detail' => $detail);
        }

        //Nenhum registro encontrado
        public function noRecordsFound(){
            return array('errors' => (object) array(
                'status' => '400',
                'title' => 'Nenhum registro encontrado',
                'detail' => 'Verifique se o id informado está correto'));
        }

    }
?>