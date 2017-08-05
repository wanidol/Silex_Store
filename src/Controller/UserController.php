<?php
/**
 * Created by PhpStorm.
 * User: PC-Wanidol
 * Date: 03/08/2017
 * Time: 20:59
 */


namespace App\Controller;

use App\Model\UserModel;

use Silex\Application;
use Silex\Api\ControllerProviderInterface;   // modif version 2.0

use Symfony\Component\HttpFoundation\Request;   // pour utiliser request

class UserController implements ControllerProviderInterface {

    private $userModel;

    public function index(Application $app) {
        return $this->connexionUser($app);
    }
//
//    public function register(Application $app) {
//
//        return $app["twig"]->render('register.html.twig');
//    }

    public function connexionUser(Application $app)
    {
        return $app["twig"]->render('login.html.twig');
    }

    public function show(Application $app)
    {
        $this->usermodel = new UserModel($app);
        $users = $this->usermodel->show();
        return $app["twig"]->render('backOff\showuser.html.twig',['data'=>$users]);
    }

    public function registerUser(Application $app)
    {
        return $app["twig"]->render('register.html.twig');
    }



    public function validFormRegisterUser(Application $app, Request $req)
    {

        $app['session']->clear();
        $password = $req->get('motdepasse');
        $confiemPass = $req->get('confirmpassword');
        $donnees['username']=$req->get('username');
        $donnees['email']=$req->get('email');

        if ($password == $confiemPass)
        {
            $donnees['motdepasse']= $password;

        }else{

            $erreurs['motdepasse'] = 'Les mots de passe ne correspondent pas';
        }

        if ((! preg_match("/^[A-Za-z ]{2,}/",$donnees['username']))) $erreurs['username']='username composé de 2 lettres minimum';

        if(! empty($erreurs))
        {
//          $erreurs['register']='L\'enregistrement ne réussit pas';
            return $app["twig"]->render('register.html.twig',['donnees'=>$donnees,'erreurs'=>$erreurs]);
        }else{

            $this->userModel = new UserModel($app);
            $this->userModel->addUser($donnees);
            $data=$this->userModel->verif_login_mdp_Utilisateur($donnees['username'],$donnees['motdepasse']);
            if($data != NULL)
            {
                $app['session']->set('roles', $data['roles']);  //dans twig {{ app.session.get('roles') }}
                $app['session']->set('username', $data['username']);
                $app['session']->set('logged', 1);
                $app['session']->set('user_id', $data['id']);
                return $app->redirect($app["url_generator"]->generate("accueil"));
            }
            else
            {
                $erreurs['register']='L\'enregistrement ne réussit pas';
//                $app['session']->set('erreur','L\'enregistrement ne réussit pas');
                return $app["twig"]->render('register.html.twig');
            }

        }

    }

    public function validFormConnexionUser(Application $app, Request $req)
    {

        $app['session']->clear();
        $donnees['login']=$req->get('login');
        $donnees['password']=$req->get('password');

        $this->userModel = new UserModel($app);
        $data=$this->userModel->verif_login_mdp_Utilisateur($donnees['login'],$donnees['password']);

        if($data != NULL)
        {
            $app['session']->set('roles', $data['roles']);  //dans twig {{ app.session.get('roles') }}
            $app['session']->set('username', $data['username']);
            $app['session']->set('logged', 1);
            $app['session']->set('user_id', $data['id']);
            return $app->redirect($app["url_generator"]->generate("accueil"));
        }
        else
        {
            $app['session']->set('erreur','mot de passe ou login incorrect');
            return $app["twig"]->render('login.html.twig');
        }
    }
    public function deconnexionSession(Application $app)
    {
        $app['session']->clear();
        $app['session']->getFlashBag()->add('msg', 'vous êtes déconnecté');
        return $app->redirect($app["url_generator"]->generate("accueil"));
    }

    public function coordonnees(Application $app)
    {
        $this->usermodel = new UserModel($app);
        $user_id =$app['session']->get('user_id');
//        var_dump($user_id);die();
        $users = $this->usermodel->coord($user_id);
        return $app["twig"]->render('backOff\showcoord.html.twig',['data'=>$users]);
    }

    public function connect(Application $app) {
        $controllers = $app['controllers_factory'];
        $controllers->match('/', 'App\Controller\UserController::index')->bind('user.index');
        $controllers->get('/login', 'App\Controller\UserController::connexionUser')->bind('user.login');
        $controllers->post('/login', 'App\Controller\UserController::validFormConnexionUser')->bind('user.validFormlogin');
        $controllers->get('/logout', 'App\Controller\UserController::deconnexionSession')->bind('user.logout');

        $controllers->post('/register', 'App\Controller\UserController::validFormRegisterUser')->bind('user.validFormRegister');
        $controllers->get('/register', 'App\Controller\UserController::registerUser')->bind('user.register');

        $controllers->get('/show', 'App\Controller\UserController::show')->bind('user.show');
        $controllers->get('/coord', 'App\Controller\UserController::coordonnees')->bind('user.coord');

        return $controllers;
    }
}