<?php

namespace src\controllers;

use \core\Controller;
use \src\handlers\UserHandler;

class LoginController extends Controller
{

    public function signin()
    {

        $flash = '';

        if (!empty($_SESSION['flash'])) {
            $flash = $_SESSION['flash'];
            $_SESSION['flash'] = '';
        }
        $this->render('signin', [
            'flash' => $flash
        ]);
    }

    public function signinAction()
    {
        $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
        $password = filter_input(INPUT_POST, 'password');

        if ($email && $password) {
            $token = UserHandler::verifyLogin($email, $password);
            if ($token) {
                $_SESSION['token'] = $token;
                $this->redirect('/');
            } else {
                $_SESSION['flash'] = 'Email e/ou senha inválidos';
                $this->redirect('/login');
            }
        } else {
            $_SESSION['flash'] = 'Insira o e-mail e/ou senha';
            $this->redirect('/login');
        }
    }

    public function signup()
    {
        $flash = '';

        if (!empty($_SESSION['flash'])) {
            $flash = $_SESSION['flash'];
            $_SESSION['flash'] = '';
        }
        $this->render('signup', [
            'flash' => $flash
        ]);
    }

    public function signupAction()
    {
        $name = filter_input(INPUT_POST, 'name');
        $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
        $password = filter_input(INPUT_POST, 'password');
        $birthdate = filter_input(INPUT_POST, 'birthdate');

        if ($name && $email && $password && $birthdate) {
            //quebrando os valores da data e jogando para array 
            $birthdate = explode('/', $birthdate);

            //se não tiver 3 elementos..
            if(count($birthdate)!=3){
                $_SESSION['flash'] = 'Data de nascimento inválida';
                $this->redirect('/cadastro');
            }

                //dd/mm/aaaa to aaaa-mm-dd
                $birthdate = $birthdate[2].'-'.$birthdate[1].'-'.$birthdate[0];

                //retorna um timestamp se for um formato de data americano, se não retorna false
                if(strtotime($birthdate)===false){
                    $_SESSION['flash'] = 'Data de nascimento inválida';
                    $this->redirect('/cadastro');
                }
                
                if(UserHandler::emailExists($email)===false){
                    $token = UserHandler::addUser($name, $email, $password, $birthdate);
                    $_SESSION['token'] = $token;
                    $this->redirect('/');
                }
                else{
                    $_SESSION['flash'] = 'E-mail já cadastrado';
                    $this->redirect('/cadastro');
                }
            
        } 
        else {
            $_SESSION['flash'] = 'Preencha os dados de cadastro corretamente';
            $this->redirect('/cadastro');
        }
    }

    public function logout(){
        $_SESSION['token'] = '';
        $this->redirect('/login');
    }
}
