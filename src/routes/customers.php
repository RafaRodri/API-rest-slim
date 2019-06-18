<?php
require_once'../class/user.php';
require_once'../class/post.php';
require_once'../class/metadata.php';
require_once'../class/apiData.php';
require_once'../class/apiError.php';

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app = new \Slim\App([
    'settings' => [
        'displayErrorDetails' => true
    ],
]);

/*GET
 */
// V1- Busca todos os usuários
$app->get('/v1/users',function(Request $request, Response $response){
    $user = new User();
    $users = $user->getUindex.php/v2/userssers();

    echo json_encode($users);
});

// V1- Busca usuário específico
$app->get('/v1/user/{id}',function(Request $request, Response $response, array $args){
    $id = $args['id'];
    $user = new User();
    $users = $user->getUser($id);

    echo json_encode($users);
})->setName('findUser');

// V1- Busca posts de usuário específico
$app->get('/v1/user/{id}/posts',function(Request $request, Response $response, array $args){
    $id = $args['id'];
    $post = new Post();
    $posts = $post->getPosts($id);

    echo json_encode($posts);
});

// V2- Busca todos os usuários
$app->get('/v2/users',function(Request $request, Response $response, array $args){
    // Aceita parâmetros:
    // limit - quantidade de registros a serem retornados
    // page - página a ser retornada
    $params = $request->getQueryParams();
    
    if(isset($params['limit']) && is_numeric($params['limit'])) {
        $limit = intval($params['limit']);
    }else{
        $limit = 5;
    }
    if(isset($params['page']) && is_numeric($params['page'])) {
        $currentPage = intval($params['page']);
    }else{
        $currentPage = 1;
    }

    $user = new User();
    $users = $user->getUsers2($limit,$currentPage);

    echo json_encode($users);
});

// V2- Busca usuário específico
$app->get('/v2/user/{id}',function(Request $request, Response $response, array $args){
    $id = $args['id'];
    $user = new User();
    $users = $user->getUser2($id);
    
    if($users[0] == 1){
        echo json_encode($users[1]);
    }else{
        echo json_encode($user->noRecordsFound());
        return $response->withStatus(400);
    }
})->setName('findUser');

// V2- Busca posts de usuário específico
$app->get('/v2/user/{id}/posts',function(Request $request, Response $response, array $args){
    $id = $args['id'];
    $post = new Post();
    $posts = $post->getPosts2($id);
    
    if($posts[0] == 1){
        echo json_encode($posts[1]);
    }else{
        echo json_encode($posts[1]);
        return $response->withStatus(400);
    }
});


/*POST
 */
// V2- Insere usuário
$app->post('/v2/user', function(Request $request, Response $response){
    $data = $request->getParsedBody();  //ou $request->getBody() que é um array simples

    $error = new apiError();
    $error->errors = array();
    
    $user = new User();
    $validaEmail = $user->validaEmail($data['email']);
    
    /*Valida campos
     */
    if(!isset($data['name'])){
        array_push($error->errors, $user->paramNotInformed("name"));
        if(empty($statusError)){$statusError = 400;}

    }elseif(empty($data['name'])){
        array_push($error->errors, $user->paramIsNull("name"));
        if(empty($statusError)){$statusError = 400;}}
    if(!isset($data['email'])){
        array_push($error->errors, $user->paramNotInformed("email"));
        if(empty($statusError)){$statusError = 400;}

    }elseif(empty($data['email'])){
        array_push($error->errors, $user->paramIsNull("email"));
        if(empty($statusError)){$statusError = 400;}

    }elseif(!$validaEmail[0]){
        array_push($error->errors, $validaEmail[1]);
        if(empty($statusError)){$statusError = 422;}}

    if(empty($error->errors)){
        $user = $user->postUsers2($data);


        $dataUser = new User();
        $newUser = $dataUser->getUser($user);

        echo json_encode($newUser);

        $pathRoot = 'http://restful:8080';
        $pathFindUser = $this->router->pathFor('findUser', ['id' => '']);

        return $response
            ->withAddedHeader('Location', $pathRoot.$pathFindUser.$user)
            ->withStatus(201);
    }else{
        echo json_encode($error);
        return $response->withStatus($statusError);
    }

});

// V2- Insere post
$app->post('/v2/user/{id}/post',function(Request $request, Response $response, array $args){
    $idUser = $args['id'];
    $data = $request->getParsedBody();
    
    $post = new Post();
    $posts = $post->postPosts2($idUser,$data['title'],$data['text']);
    
    if($posts[0] == 1){
        $dataUser = new User();
        $user = $dataUser->getUser($idUser);

        echo json_encode($user);
    }elseif($posts[0] == 0){
        echo json_encode($posts[1]);
        return $response->withStatus($posts[2]);
    }
});


/*PUT
 */
// V2- Alterar usuário
$app->put('/v2/user/{id}',function(Request $request, Response $response, array $args){
    $id = $args['id'];
    $data = $request->getParsedBody();
    
    $error = new apiError();
    $error->errors = array();

    $user = new User();
    $validaEmail = $user->validaEmail($data['email']);
    
    /*Valida campos
     */
    if(!isset($data['name'])){
        array_push($error->errors, $user->paramNotInformed("name"));
        if(empty($statusError)){$statusError = 400;}

    }elseif(empty($data['name'])){
        array_push($error->errors, $user->paramIsNull("name"));
        if(empty($statusError)){$statusError = 400;}}
    if(!isset($data['email'])){
        array_push($error->errors, $user->paramNotInformed("email"));
        if(empty($statusError)){$statusError = 400;}

    }elseif(empty($data['email'])){
        array_push($error->errors, $user->paramIsNull("email"));
        if(empty($statusError)){$statusError = 400;}

    }elseif(!$validaEmail[0]){
        array_push($error->errors, $validaEmail[1]);
        if(empty($statusError)){$statusError = 422;}}


    if(empty($error->errors)){
        //$users = $user->putUsers2($id,$data['name'],$data['email']);
        $users = $user->putUsers2($id,$data);
        

        if($users[0] == 1){
            echo json_encode($user->getUser($id));
        }elseif($users[0] == 2){
            echo json_encode($user->getUser($users[1]));
            

            $pathRoot = 'http://restful:8080';
            $pathFindUser = $this->router->pathFor('findUser', ['id' => '']);

            return $response
                ->withAddedHeader('Location', $pathRoot.$pathFindUser.$users[1])
                ->withStatus(201);
        }
        else{
            echo json_encode($users[1]);
            return $response->withStatus($users[2]);
        }
    }else{
        echo json_encode($error);
        return $response->withStatus($statusError);
    }

});


/*PATCH
 */
// V2- Alterar "partes" do usuário
$app->patch('/v2/user/{id}',function(Request $request, Response $response, array $args){
    $id = $args['id'];
    $data = $request->getParsedBody();
    
    $error = new apiError();
    $error->errors = array();

    
    if(isset($data['name'])){
        $name = $data['name'];
    }else{
        $name = null;
    }
    if(isset($data['email'])){
        $email = $data['email'];
    }else{
        $email = null;
    }
    
    //Entrar somente se algum parâmetro tiver sido passado
    if(!empty($name) || !empty($email)){
        $user = new User();
        
        //Se o nome tiver sido passado
        if(isset($nome) && !is_null($nome)){
            if(!isset($data['name'])){
                array_push($error->errors, $user->paramNotInformed("name"));
                if(empty($statusError)){$statusError = 400;}

            }elseif(empty($data['name'])){
                array_push($error->errors, $user->paramIsNull("name"));
                if(empty($statusError)){$statusError = 400;}}
        }
        //Se o email tiver sido passado
        if(isset($email) && !is_null($email)){
            $validaEmail = $user->validaEmail($email);

            if(!isset($data['email'])){
                array_push($error->errors, $user->paramNotInformed("email"));
                if(empty($statusError)){$statusError = 400;}

            }elseif(empty($data['email'])){
                array_push($error->errors, $user->paramIsNull("email"));
                if(empty($statusError)){$statusError = 400;}

            }elseif(!$validaEmail[0]){
                array_push($error->errors, $validaEmail[1]);
                if(empty($statusError)){$statusError = 422;}}
        }

        if(empty($error->errors)){
            $users = $user->patchUsers2($id,$name,$email);

            if($users == 1){
                echo json_encode($user->getUser($id));
            }else{
                echo json_encode($user->noRecordsFound());
                return $response->withStatus(400);
            }
        }else{
            echo json_encode($error);
            return $response->withStatus($statusError);
        }
    }
    
    
});


/*DELETE
 */
// V2- Deletar usuário
$app->delete('/v2/user/{id}',function(Request $request, Response $response, array $args){
    $id = $args['id'];

    $user = new User();
    $users = $user->deleteUser2($id);

    if($users[0] == 1){
        return $response
            ->withAddedHeader('Entity', $id)
            ->withStatus(204);
    }elseif($users[0] == 0){
        echo json_encode($users[1]);
        return $response
            ->withStatus($users[2]);
    }
});
