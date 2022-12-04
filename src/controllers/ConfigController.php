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

            UserHandler::updateUser($birthdate, $name, $city, $work, $this->loggedUser->id);
        } else {
            $_SESSION['flash'] = 'Preencha os dados corretamente';
            $this->redirect('/config');
        }
        $_SESSION['flashsuccess'] = 'Dados atualizados com sucesso!';
        $this->redirect('/config');
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
