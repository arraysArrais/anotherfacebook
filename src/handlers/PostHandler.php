<?php

namespace src\handlers;

use \src\models\Post;
use \src\models\Posts_like;
use \src\models\Post_comment;
use \src\models\User;
use \src\models\User_Relation;

class PostHandler
{
    public static function addPost($idUser, $type, $body)
    {
        $body = trim($body);
        if (!empty($idUser) && !empty($body)) {
            Post::insert([
                'id_user' => $idUser,
                'type' => $type,
                'body' => $body,
                'created_at' => date('Y-m-d H:i:s'),
            ])->execute();
        }
    }

    public static function _postListToObject($postList, $loggedUserId)
    {

        $posts = [];
        foreach ($postList as $postItem) {
            $newPost = new Post();
            $newPost->id = $postItem['id'];
            $newPost->type = $postItem['type'];
            $newPost->created_at = $postItem['created_at'];
            $newPost->body = $postItem['body'];
            $newPost->mine = false;

            if ($postItem['id_user'] == $loggedUserId) {
                $newPost->mine = true;
            }
            // 4. preencher as infos adicionais no post
            $newUser = User::select()->where('id', $postItem['id_user'])->one();
            $newPost->user = new User();
            $newPost->user->id = $newUser['id'];
            $newPost->user->name = $newUser['name'];
            $newPost->user->avatar = $newUser['avatar'];

            // TODO:4.1 preencher informações de LIKE
            $likes = Posts_like::select()->where('id_post', $postItem['id'])->get();
            $newPost->likeCount = count($likes);
            $newPost->liked = self::isLiked($postItem['id'], $loggedUserId);

            // TODO:4.2 preencher informações de COMMENTS
            $newPost->comments = Post_Comment::select()->where('id_post', $postItem['id'])
            ->get();

            foreach($newPost->comments as $key => $comment){
                $newPost->comments[$key]['user'] = User::select()->where('id', $comment['id_user'])->one();
            }

            $posts[] = $newPost;
        }

        return $posts;
    }

    public static function isLiked($id, $loggedUserId){
            $myLike=Posts_like::select()
            ->where('id_post', $id)
            ->where('id_user', $loggedUserId)
            ->get();

            if(count($myLike)>0){
                return true;
            } 
            else{
                return false;
            }
    }

    public static function deleteLike($id, $loggedUserId){
        Posts_like::delete()
        ->where('id_post', $id)
        ->where('id_user', $loggedUserId)
        ->execute();
    }

    public static function addLike($id, $loggedUserId){
        Posts_like::insert([
            'id_post' => $id, 
            'id_user'=> $loggedUserId,
            'created_at' => date('Y-m-d H:i:s')
            ])
        ->execute();
    }

    public static function addComment($id, $txt, $loggedUserId){
            Post_comment::insert([
            'id_post' => $id,
            'body' => $txt,
            'id_user' => $loggedUserId,
            'created_at' => date('Y-m-d H:i:s')
            ])->execute();  
    }



    public static function getUserFeed($idUser, $page, $loggedUserId)
    {

        $perPage = 2;


        $postList = Post::select()
            ->where('id_user', $idUser)
            ->orderBy('created_at', 'desc')
            ->page($page, $perPage)
            ->get();


        //total de posts
        $total = Post::select()
            ->where('id_user', $idUser)
            ->count();

        $pageCount = ceil($total / $perPage);


        // 3. transformar o resultado em objetos dos Models
        $posts = self::_postListToObject($postList, $loggedUserId);

        // 5. retornar o resultado
        return [
            'posts' => $posts,
            'pageCount' => $pageCount,
            'currentPage' => $page
        ];
    }

    public static function getHomeFeed($idUser, $page)
    {
        $perPage = 2;

        // 1. pegar lista de usuários que EU sigo.

        //todos os regitros de relação onde 'user_from' possui o meu id (ou seja, relações onde eu sigo alguém)
        $userList = User_Relation::select()->where('user_from', $idUser)->get();
        $users = [];

        //jogando para $users todos os ids das pessoas que EU sigo
        foreach ($userList as $userItem) {
            $users[] = $userItem['user_to'];
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
        $posts = self::_postListToObject($postList, $idUser);

        // 5. retornar o resultado
        return [
            'posts' => $posts,
            'pageCount' => $pageCount,
            'currentPage' => $page
        ];
    }

    public static function getPhotosFrom($idUser)
    {
        $photosData = Post::select()
            ->where('id_user', $idUser)
            ->where('type', 'photo')
            ->get();

        $photos = [];

        foreach ($photosData as $photo) {
            $newPost = new Post();
            $newPost->id = $photo['id'];
            $newPost->type = $photo['type'];
            $newPost->created_at = $photo['created_at'];
            $newPost->body = $photo['body'];

            $photos[] = $newPost;
        }
        return $photos;
    }

    public static function delete($id, $loggedUserId){
        //1. verificar se o post existe (e se é seu (user logado))
        $post = Post::select()
                ->where('id', $id)
                ->where('id_user', $loggedUserId)
                ->get();


        if(count($post)>0){
            $post=$post[0];

            //2. deletar likes e commnets
            Posts_like::delete()->where('id_post', $id)->execute();
            Post_comment::delete()->where('id_post', $id)->execute();
            

            //3. se for type == photo, deletar o arquivo também
            if($post['type'] == 'photo'){
                $img = __DIR__.'/../../public/media/uploads/'.$post['body'];
                if(file_exists($img)){
                    unlink($img);
                }
            }
            //4. deletar o post
            Post::delete()->where('id', $id)->execute();

        }
        
        
    }
}
