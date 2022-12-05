<?php

namespace src\controllers;

use \core\Controller;
use \src\handlers\UserHandler;

class ConfigController extends Controller
{

    private $loggedUser;

    public function __construct()
    {
        $this->loggedUser = UserHandler::checkLogin();
        if ($this->loggedUser === false) {
            $this->redirect('/login');
        }
    }

    public function index()
    {

        $flash = '';
        $flashsuccess = '';

        if (!empty($_SESSION['flash'])) {
            $flash = $_SESSION['flash'];
            $_SESSION['flash'] = '';
        }

        if (!empty($_SESSION['flashsuccess'])) {
            $flashsuccess = $_SESSION['flashsuccess'];
            $_SESSION['flashsuccess'] = '';
        }

        $user = UserHandler::getUser($this->loggedUser->id);

        $this->render('config', [
            'loggedUser' => $this->loggedUser,
            'flash' => $flash,
            'flashsuccess' => $flashsuccess,
            'user' => $user
        ]);
    }

    public function configAction()
    {

        $user = UserHandler::getUser($this->loggedUser->id);

        $name = filter_input(INPUT_POST, 'name');
        $birthdate = filter_input(INPUT_POST, 'birthdate');
        //$email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
        $city = filter_input(INPUT_POST, 'city');
        $work = filter_input(INPUT_POST, 'work');
        //$newpassword = filter_input(INPUT_POST, 'newpassword');
        //$confirmpassword = filter_input(INPUT_POST, 'confirmpassword');


        if ($birthdate && $name && $city && $work) {

            //quebrando os valores da data e jogando para array 
            $birthdate = explode('/', $birthdate);

            //se não tiver 3 elementos..
            if (count($birthdate) != 3) {
                $_SESSION['flash'] = 'Data de nascimento inválida';
                $this->redirect('/config');
            }

            //dd/mm/aaaa to aaaa-mm-dd
            $birthdate = $birthdate[2] . '-' . $birthdate[1] . '-' . $birthdate[0];

            //retorna um timestamp se for um formato de data americano, se não retorna false
            if (strtotime($birthdate) === false) {
                $_SESSION['flash'] = 'Data de nascimento inválida';
                $this->redirect('/config');
            }

            $updateFields = [];

            //avatar
            if(isset($_FILES['avatar'])&& !empty($_FILES['avatar']['tmp_name'])){
                $newAvatar = $_FILES['avatar'];

                if(in_array($newAvatar['type'], ['image/jpeg', 'image/jpg', 'image/png'])){
                    $avatarName = $this->cutImage($newAvatar, 200, 200, 'media/avatars');
                    $updateFields['avatar'] = $avatarName;
                }
            }
            else{
                $updateFields['avatar']=UserHandler::getAvatar($this->loggedUser->id);
            }


            //cover
            if(isset($_FILES['cover']) && !empty($_FILES['cover']['tmp_name'])){
                $newCover = $_FILES['cover'];

                if(in_array($newCover['type'], ['image/jpeg', 'image/jpg', 'image/png'])){
                    $coverName = $this->cutImage($newCover, 850, 310, 'media/covers');
                    $updateFields['cover'] = $coverName;

                }
            }
            else{
                $updateFields['cover']=UserHandler::getCover($this->loggedUser->id);
            }
            

            UserHandler::updateFiles($updateFields, $this->loggedUser->id);
            UserHandler::updateUser($birthdate, $name, $city, $work, $this->loggedUser->id);
        } else {
            $_SESSION['flash'] = 'Preencha os dados corretamente';
            $this->redirect('/config');
        }
        $_SESSION['flashsuccess'] = 'Dados atualizados com sucesso!';
        $this->redirect('/config');
    }

    private function cutImage($file, $w, $h, $folder){
        list($widthOrig, $heightOrig) = getimagesize($file['tmp_name']);
        $ratio = $widthOrig/$heightOrig;

        $newWidth = $w;
        $newHeight = $newWidth/$ratio;

        if($newHeight<$h){
            $newHeight = $h;
            $newWidth = $newHeight*$ratio;
        }

        $x = $w - $newWidth;
        $y = $h - $newHeight;
        $x = $x < 0 ? $x / 2 : $x;
        $y = $y < 0 ? $y / 2 : $y;

        $finalImage = imagecreatetruecolor($w, $h);
        switch($file['type']){
            case 'image/jpeg':
            case 'image/jpg':
                $image = imagecreatefromjpeg($file['tmp_name']);
            break;
            case 'image/png':
                $image = imagecreatefromjpeg($file['tmp_name']);
            break;
        }

        imagecopyresampled(
            $finalImage, $image,
            $x, $y, 0, 0,
            $newWidth, $newHeight, $widthOrig, $heightOrig
        );

        $fileName = md5(time().rand(0,9999)).'.jpg';

        imagejpeg($finalImage, $folder.'/'.$fileName);

        return $fileName;

    }


    public function changePassword()
    {
        $flash = '';
        $flashsuccess = '';

        if (!empty($_SESSION['flash'])) {
            $flash = $_SESSION['flash'];
            $_SESSION['flash'] = '';
        }

        if (!empty($_SESSION['flashsuccess'])) {
            $flashsuccess = $_SESSION['flashsuccess'];
            $_SESSION['flashsuccess'] = '';
        }

        $this->render('changepassword', [
            'loggedUser' => $this->loggedUser,
            'flash' => $flash,
            'flashsuccess' => $flashsuccess,
        ]);
    }

    public function changePasswordAction()
    {

        $newpassword = filter_input(INPUT_POST, 'newpassword');
        $confirmpassword = filter_input(INPUT_POST, 'confirmpassword');

        if ($newpassword && $confirmpassword) {
            if ($newpassword == $confirmpassword) {
                UserHandler::updatePassword($newpassword, $this->loggedUser->id);
            } else {
                $_SESSION['flash'] = 'Senha de confirmação não bate';
                $this->redirect('/config/alterarsenha');
            }
        }
        $_SESSION['flashsuccess'] = 'Dados atualizados com sucesso!';
        $this->redirect('/config');
    }
}
