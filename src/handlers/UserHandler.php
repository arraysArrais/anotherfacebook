<?php

namespace src\handlers;

use \src\models\User;
use \src\models\User_Relation;
use \src\handlers\PostHandler;

class UserHandler
{

    public static function checkLogin()
    {
        //se $_SESSION['token'] não estiver vazio..
        if (!empty($_SESSION['token'])) {
            //atribui o valor de $_SESSION['token'] a $token
            $token = $_SESSION['token'];

            //busca na tabela users do banco resultados onde a coluna 'token' (do banco) é igual ao valor de $token
            $data = User::select()->where('token', $token)->one();

            //se $data tiver algum valor..
            if (count($data) > 0) {

                $loggedUser = new User();

                //atribuindo as propriedades de $loggedUser (instancia de User)
                //os valores resgatados pela query do banco
                $loggedUser->id = $data['id'];
                $loggedUser->name = $data['name'];
                $loggedUser->avatar = $data['avatar'];

                return $loggedUser;
            }
        }

        return false;
    }

    public static function verifyLogin($email, $password)
    {

        //buscando usuário pelo e-mail e armazenando a linha da consulta em $user
        $user = User::select()->where('email', $email)->one();

        //se user existir..
        if ($user) {
            //batendo a senha digitada através do form com o hash salvo no banco
            if (password_verify($password, $user['password'])) {

                //gera um token
                $token = md5(time() . rand(0, 9999) . time());

                //guardando o token no banco 
                User::update()
                    ->set('token', $token)
                    ->where('email', $email)
                    ->execute();

                return $token;
            }
        }

        //se o e-mail não for válido retorna false
        return false;
    }

    public static function idExists($id)
    {
        $id = User::select()->where('id', $id)->one();
        return $id ? true : false;
    }

    public static function emailExists($email)
    {
        $userEmail = User::select()->where('email', $email)->one();
        /*if(count($userEmail)>0){
            return true;
        }
        else{
            return false;
        }*/

        return $userEmail ? true : false;
    }

    public static function getUser($id, $full = false)
    {
        $data = User::select()->where('id', $id)->one();

        if ($data) {
            $user = new User;
            $user->id = $data['id'];
            $user->name = $data['name'];
            $user->email = $data['email'];
            $user->birthdate = $data['birthdate'];
            $user->city = $data['city'];
            $user->work = $data['work'];
            $user->avatar = $data['avatar'];
            $user->cover = $data['cover'];

            if($full){
                $user->followers = [];
                $user->following = [];
                $user->photos = [];

                // followers
                //todos os registros de relação onde EU estou sendo seguido
                $followers = User_Relation::select()->where('user_to', $id)->get();
                foreach($followers as $follower){
                    
                    //buscando informações das pessoas que me seguem
                    $userData = User::select()->where('id', $follower['user_from'])->one();
                    $newUser = new User();
                    $newUser->id = $userData['id'];
                    $newUser->name = $userData['name'];
                    $newUser->avatar = $userData['avatar'];

                    $user->followers[] = $newUser;
                    

                }

                // following
                //todos os registros de relação onde EU sigo alguém
                $following = User_Relation::select()->where('user_from', $id)->get();
                foreach($following as $value){

                    //buscando informações das pessoas que EU sigo
                    $userData = User::select()->where('id', $value['user_to'])->one();
                    $newUser = new User();
                    $newUser->id = $userData['id'];
                    $newUser->name = $userData['name'];
                    $newUser->avatar = $userData['avatar'];

                    $user->following[] = $newUser;
                    

                }

                // photos
                $photos = PostHandler::getPhotosFrom($id);
                $user->photos = $photos;

            }

            return $user;
        }

        return false;
    }

    public static function addUser($name, $email, $password, $birthdate)
    {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $token = md5(time() . rand(0, 9999) . time());
        User::insert([
            'name' => $name,
            'email' => $email,
            'password' => $hash,
            'birthdate' => $birthdate,
            'token' => $token
        ])->execute();

        return $token;
    }

    public static function isFollowing($from, $to){
        $data = User_Relation::select()
        ->where('user_from', $from)
        ->where('user_to', $to)
        ->one();

        if($data){
            return true;
        }
        return false;

    }

    public static function follow($from, $to){
        User_Relation::insert([
            'user_from' => $from, 
            'user_to' => $to
        ])->execute();
    }

    public static function unfollow($from, $to){
        User_Relation::delete()
        ->where('user_from', $from)
        ->where('user_to', $to)
        ->execute();
    }

    public static function searchUser($term){
        $users = [];
        $data = User::select()->where('name', 'like', '%'.$term.'%')->get();

        if($data){
            foreach ($data as $user){
                $newUser = new User();
                $newUser->id = $user['id'];
                $newUser->name = $user['name'];
                $newUser->avatar = $user['avatar'];

                $users[] = $newUser;
            }
        }

        return $users;
    }

    public static function updateUser($birthdate, $name, $city, $work, $id){
        User::update()
        /*->set('email', $email)*/
        ->set('birthdate', $birthdate)
        ->set('name', $name)
        ->set('city', $city)
        ->set('work', $work)
        ->where('id', $id)
        ->execute();

        /*if(!empty($newpassword)){
            $hash = password_hash($newpassword, PASSWORD_DEFAULT);
            User::update()
            ->set('password', $hash)
            ->where('id', $id)
            ->execute();
        }*/


    }

    public static function updatePassword($newpassword, $id){
        if(!empty($newpassword)){
            $hash = password_hash($newpassword, PASSWORD_DEFAULT);
            User::update()
            ->set('password', $hash)
            ->where('id', $id)
            ->execute();
        }
    }

    public static function updateFiles($fields, $id){
        User::update()
        ->set('cover', $fields['cover'])
        ->where('id', $id)
        ->execute();

        User::update()
        ->set('avatar', $fields['avatar'])
        ->where('id', $id)
        ->execute();
    }

    public static function getAvatar($id){
        $data = User::select()
                ->where('id', $id)
                ->one();

        return $data['avatar'];
    }

    public static function getCover($id){
        $data = User::select()
                ->where('id', $id)
                ->one();

        return $data['cover'];
    }
}
