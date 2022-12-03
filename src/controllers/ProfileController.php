<?php
namespace src\controllers;

use \core\Controller;
use \src\handlers\UserHandler;
use \src\handlers\PostHandler;

class ProfileController extends Controller {

    private $loggedUser;

    public function __construct(){
        $this->loggedUser=UserHandler::checkLogin();
        if($this->loggedUser===false){
            $this->redirect('/login');
        }
    }

    public function index($args = []) {
        $page = intval(filter_input(INPUT_GET, 'page'));

        //detectando usuário logado
        $id = $this->loggedUser->id;

        if(!empty($args['id'])){
            $id = $args['id'];
        }

        //pegando informações do usuário
        $user = UserHandler::getUser($id, true);

        if(!$user){
            $this->redirect('/');
        }

        $dateFrom = new \DateTime($user->birthdate);
        $dateTo = new \DateTime('today');
        $user->ageYears = $dateFrom->diff($dateTo)->y;

        //pegando feed do usuário
        $feed = PostHandler::getUserFeed($id, $page, $this->loggedUser->id);

        //verificando se EU sigo o usuario
        $isFollowing=false;
        if($user->id != $this->loggedUser->id){
            $isFollowing = UserHandler::isFollowing($this->loggedUser->id, $user->id);
        }


     $this->render('profile', [
        'loggedUser'=>$this->loggedUser,
        'user' => $user,
        'feed' => $feed,
        'isFollowing' =>$isFollowing
     ]);
    }

    public function follow($atts){
        $to = intval($atts['id']);

        $exists = UserHandler::idExists($to);

        if($exists){

            if(UserHandler::isFollowing($this->loggedUser->id, $to)){
                UserHandler::unfollow($this->loggedUser->id, $to);
            }
            else{
                UserHandler::follow($this->loggedUser->id, $to);
            }
        }

        $this->redirect('/perfil/'.$to);
    }

    public function friends($args = []){
        $id = $this->loggedUser->id;

        if(!empty($args['id'])){
            $id = $args['id'];
        }

        $user = UserHandler::getUser($id, true);

        if(!$user){
            $this->redirect('/');
        }

        $dateFrom = new \DateTime($user->birthdate);
        $dateTo = new \DateTime('today');
        $user->ageYears = $dateFrom->diff($dateTo)->y;

        $isFollowing=false;
        if($user->id != $this->loggedUser->id){
            $isFollowing = UserHandler::isFollowing($this->loggedUser->id, $user->id);
        }

        $this->render('profile_friends', [
            'loggedUser'=>$this->loggedUser,
            'user' => $user,
            'isFollowing' => $isFollowing
         ]);
    }

    public function photos($args = []){
        $id = $this->loggedUser->id;

        if(!empty($args['id'])){
            $id = $args['id'];
        }

        $user = UserHandler::getUser($id, true);

        if(!$user){
            $this->redirect('/');
        }

        $dateFrom = new \DateTime($user->birthdate);
        $dateTo = new \DateTime('today');
        $user->ageYears = $dateFrom->diff($dateTo)->y;

        //verificar se EU sigo o usuário
        $isFollowing=false;
        if($user->id != $this->loggedUser->id){
            $isFollowing = UserHandler::isFollowing($this->loggedUser->id, $user->id);
        }

        $this->render('profile_photos', [
            'loggedUser'=>$this->loggedUser,
            'user' => $user,
            'isFollowing' => $isFollowing
         ]);
    }
}