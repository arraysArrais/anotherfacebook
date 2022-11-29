<?php

namespace src\handlers;

use \src\models\Post;
use \src\models\User;
use \src\models\User_Relation;

class PostHandler{
    public static function addPost($idUser, $type, $body){
        $body = trim($body);
        if(!empty($idUser) && !empty($body)){
            Post::insert([
                'id_user' => $idUser,
                'type' => $type,
                'body' => $body,
                'created_at' => date('Y-m-d H:i:s'),
            ])->execute();
        }

    }

    public static function getHomeFeed($idUser, $page){
        $perPage = 2;

        // 1. pegar lista de usuários que EU sigo.

        //todos os regitros de relação onde 'user_from' possui o meu id (ou seja, relações onde eu sigo alguém)
        $userList = User_Relation::select()->where('user_from', $idUser)->get();
        $users = [];

        //jogando para $users todos os ids das pessoas que EU sigo
        foreach($userList as $userItem){
            $user[] = $userItem['user_to'];
        }

        $users[] = $idUser;

        // 2. pegar os posts dessa galera, ordenado pela data
        $postList = Post::select()
        ->where('id_user', 'in', $users)
        ->orderBy('created_at', 'desc')
        ->page($page, $perPage)
        ->get();


        //total de posts
        $total = Post::select()
        ->where('id_user', 'in', $users)
        ->count();
        $pageCount = ceil($total / $perPage);


        // 3. transformar o resultado em objetos dos Models
        $posts = [];
        foreach($postList as $postItem){
            $newPost = new Post();
            $newPost->id=$postItem['id'];
            $newPost->type=$postItem['type'];
            $newPost->created_at=$postItem['created_at'];
            $newPost->body = $postItem['body'];
            $newPost->mine = false;

            if($postItem['id_user']==$idUser){
                $newPost->mine = true;
            }

            // 4. preencher as infos adicionais no post
            $newUser = User::select()->where('id', $postItem['id_user'])->one();
            $newPost->user = new User();
            $newPost->user->id = $newUser['id'];
            $newPost->user->name = $newUser['name'];
            $newPost->user->avatar = $newUser['avatar'];

            // TODO:4.1 preencher informações de LIKE
            $newPost->likeCount = 0;
            $newPost->liked = false;

            // TODO:4.2 preencher informações de COMMENTS
            $newPost->comments = [];

            $posts[] = $newPost;
        }
        
        // 5. retornar o resultado
        return [
            'posts' => $posts,
            'pageCount' => $pageCount,
            'currentPage' => $page
        ];


    }

}