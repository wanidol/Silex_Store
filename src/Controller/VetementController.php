<?php
namespace App\Controller;

use Silex\Application;
use Silex\Api\ControllerProviderInterface;   // modif version 2.0

use Symfony\Component\HttpFoundation\Request;   // pour utiliser request

use App\Model\VetementModel;
use App\Model\TypeModel;

use App\Helper\Helper_Date;


use Symfony\Component\Validator\Constraints as Assert;   // pour utiliser la validation
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Security;

class VetementController implements ControllerProviderInterface
{
    private $vetementModel;
    private $typeModel;

    public function initModel(Application $app)
    {  //  ne fonctionne pas dans le const
        $this->vetementModel = new VetementModel($app);
        $this->typeModel = new TypeModel($app);
    }

    public function index(Application $app)
    {
        return $this->show($app);
    }

    public function show(Application $app)
    {
        //Calling Helper
        $vdate = new Helper_Date();

        $this->vetementModel = new VetementModel($app);
        $total = $this->vetementModel->GetTotalRecords();
        $vetement = $this->vetementModel->getAllVetements();

        $max = (int)$total['total'];

        for ($i = 0;$i < $max; $i++){
            $temp = $vetement[$i]['dateAchat'];
            $vetement[$i]['dateAchat'] = $vdate->convertUsToFr($temp); //from yyyy/mm/dd to dd/mm/yyyy
        }

        return $app["twig"]->render('show.html.twig', ['data' => $vetement]);
    }

    public function edit(Application $app, $id) {

        $this->typeModel = new TypeModel($app);
        $types = $this->typeModel->getAllType();

        $this->vetementModel = new VetementModel($app);
        $donnees = $this->vetementModel->getVetementByID($id);

        return $app["twig"]->render('backOffice/Vetement/edit.html.twig',['types'=>$types,'donnees'=>$donnees]);
    }

    public function add(Application $app) {
        $this->typeModel = new TypeModel($app);
        $types = $this->typeModel->getAllType();
        return $app["twig"]->render('backOffice/vetement/1add.html.twig',['types'=>$types]);
    }

    public function delete(Application $app, $id) {
        $this->typeModel = new TypeModel($app);
        $types = $this->typeModel->getAllType();
        $this->vetementModel = new VetementModel($app);
        $donnees = $this->vetementModel->getVetementByID($id);
        return $app["twig"]->render('backOffice/Vetement/delete.html.twig',['types'=>$types,'donnees'=>$donnees]);
    }

    public function validFormDelete(Application $app, Request $req) {
        $id=$app->escape($req->get('id'));
        if (is_numeric($id)) {
            $this->vetementModel = new VetementModel($app);
            $this->vetementModel->deleteVetement($id);
            return $app->redirect($app["url_generator"]->generate("vetement.index"));
        }
        else
            return $app->abort(404, 'error Pb id form Delete');
    }

    public function validFormAdd(Application $app, Request $req) {

        //Calling helper for validate date and convert from dd/mm/yyyy to yyyy/mm/dd
        $date = new Helper_Date();

        if (isset($_POST['descriptif']) && isset($_POST['type_id']) and isset($_POST['descriptif']) and isset($_POST['photo'])) {
            $donnees = [
                'descriptif' => htmlspecialchars($_POST['descriptif']),                    // echapper les entrées
                'type_id' => htmlspecialchars($req->get('type_id')),  //$app['request']-> ne focntionne plus
                'prixDeBase' => htmlspecialchars($req->get('prixDeBase')),
                'taille' => htmlspecialchars($req->get('taille')),
                'photo' => $app->escape($req->get('photo')),  //$req->query->get('photo')-> ne focntionne plus
                'dateAchat'=> htmlspecialchars($req->get('dateAchat'))
            ];
            if ((! preg_match("/^[A-Za-z ]{2,}/",$donnees['descriptif']))) $erreurs['descriptif']='nom composé de 2 lettres minimum';
            if(! is_numeric($donnees['type_id']))$erreurs['type_id']='veuillez saisir une valeur';
            if(! is_numeric($donnees['prixDeBase']))$erreurs['prixDeBase']='saisir une valeur numérique';
            if(! is_numeric($donnees['taille']))$erreurs['taille']='saisir une valeur numérique';
            if(! preg_match("/[A-Za-z0-9]{2,}.(jpeg|jpg|png)/",$donnees['photo'])) $erreurs['photo']='nom de fichier incorrect (extension jpeg , jpg ou png)';

            if(! $date->isValidDate($donnees['dateAchat'])) {

                $erreurs['dateAchat']='Date Invalide';
            }
            else
            {
                $donnees['dateAchat'] = $date->convertFRtoUS($donnees['dateAchat']);
            }

            if(! empty($erreurs))
            {
                $this->typeModel = new TypeModel($app);
                $types = $this->typeModel->getAllType();
                return $app["twig"]->render('backOffice/Vetement/1add.html.twig',['donnees'=>$donnees,'erreurs'=>$erreurs,'types'=>$types]);
            }
            else
            {
                $this->VetementModel = new VetementModel($app);
                $this->VetementModel->insertVetement($donnees);
                return $app->redirect($app["url_generator"]->generate("vetement.index"));
            }

        }
        else
            return $app->abort(404, 'error Pb data form Add');
    }

    /**
     * @param Application $app
     * @param Request $req
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|void
     */
    public function validFormEdit(Application $app, Request $req) {
        // var_dump($app['request']->attributes);
        $date = new Helper_Date();
        if (isset($_POST['descriptif']) && isset($_POST['type_id']) and isset($_POST['descriptif']) and isset($_POST['photo']) and isset($_POST['id_vetement'])) {
            $donnees = [
                'descriptif' => htmlspecialchars($_POST['descriptif']),                    // echapper les entrées
                'type_id' => htmlspecialchars($req->get('type_id')),  //$app['request']-> ne focntionne plus
                'prixDeBase' => htmlspecialchars($req->get('prixDeBase')),
                'taille' => htmlspecialchars($req->get('taille')),
                'photo' => $app->escape($req->get('photo')),  //$req->query->get('photo')-> ne focntionne plus
//                'dateAchat' => htmlspecialchars($_POST['dateAchat']),
                'dateAchat'=> htmlspecialchars($req->get('dateAchat')),
                'id_vetement' => $app->escape($req->get('id_vetement'))//$req->query->get('photo')
            ];



            if ((! preg_match("/^[A-Za-z ]{2,}/",$donnees['descriptif']))) $erreurs['descriptif']='nom composé de 2 lettres minimum';
            if(! is_numeric($donnees['type_id']))$erreurs['type_id']='veuillez saisir une valeur';
            if(! is_numeric($donnees['prixDeBase']))$erreurs['prixDeBase']='saisir une valeur numérique';
            if(! is_numeric($donnees['taille']))$erreurs['taille']='saisir une valeur numérique';
            if(! preg_match("/[A-Za-z0-9]{2,}.(jpeg|jpg|png)/",$donnees['photo'])) $erreurs['photo']='nom de fichier incorrect (extension jpeg , jpg ou png)';

            if(! is_numeric($donnees['id_vetement']))$erreurs['id_vetement']='saisir une valeur numérique';

            if(! $date->isValidDate($donnees['dateAchat'])) {

                $erreurs['dateAchat']='Date Invalide';
            }
            else
            {
                $donnees['dateAchat'] = $date->convertFRtoUS($donnees['dateAchat']);
            }

//                var_dump($donnees['dateAchat']);die();
            $contraintes = new Assert\Collection(
                [
                    'id_vetement' => [new Assert\NotBlank(),new Assert\Type('digit')],
                    'type_id' => [new Assert\NotBlank(),new Assert\Type('digit')],
                    'descriptif' => [
                        new Assert\NotBlank(['message'=>'saisir une valeur']),
                        new Assert\Length(['min'=>2, 'minMessage'=>"Le nom doit faire au moins {{ limit }} caractères."])
                    ],
                    //http://symfony.com/doc/master/reference/constraints/Regex.html
                    'photo' => [
                        new Assert\Length(array('min' => 5)),
                        new Assert\Regex([ 'pattern' => '/[A-Za-z0-9]{2,}.(jpeg|jpg|png)/',
                            'match'   => true,
                            'message' => 'nom de fichier incorrect (extension jpeg , jpg ou png)' ]),
                    ],
                    'prixDeBase' => new Assert\Type(array(
                        'type'    => 'numeric',
                        'message' => 'La valeur {{ value }} n\'est pas valide, le type est {{ type }}.',
                    )),

                    'taille' => new Assert\Type(array(
                        'type'    => 'numeric',
                        'message' => 'La valeur {{ value }} n\'est pas valide, le type est {{ type }}.',
                    )),
                        'dateAchat' => new Assert\Type('string')
//                        'dateAchat' =>[ new Assert\Date(['message'=>'saisir une date'])]
                ]);

            $errors = $app['validator']->validate($donnees,$contraintes);  // ce n'est pas validateValue


            if ((count($errors) > 0)||(! empty($erreurs))) {
//            if (count($errors) > 0) {

                $this->typeModel = new TypeModel($app);
                $types = $this->typeModel->getAllType();
                return $app["twig"]->render('backOffice/Vetement/edit.html.twig',['donnees'=>$donnees,'errors'=>$errors,'erreurs'=>$erreurs,'types'=>$types]);
            }
            else
            {
                $this->VetementModel = new VetementModel($app);
                $this->VetementModel->updateVetement($donnees);
                return $app->redirect($app["url_generator"]->generate("vetement.index"));
            }

        }
        else
            return $app->abort(404, 'error Pb id form edit');

    }

    public function showCategories(Application $app){
        $this->typeModel = new TypeModel($app);
        $types = $this->typeModel->getAllType();
        return $app["twig"]->render('frontOffice/Vetement/showByType.html.twig',['types'=>$types]);
    }

    public function showbyCategories(Application $app,Request $req) {
        $vdate = new Helper_Date();
        $type_id = htmlspecialchars($req->get('id_Type'));
        $this->vetementModel = new VetementModel($app);
        $total = $this->vetementModel->getTotalByTypes($type_id);
        $vetements = $this->vetementModel->getVetementsByCategories($type_id);

        $max = (int)$total['total'];
//        var_dump($max);die();
        for ($i = 0;$i < $max; $i++){
            $temp = $vetements[$i]['dateAchat'];
            $vetements[$i]['dateAchat'] = $vdate->convertUsToFr($temp);
        }

        return $app["twig"]->render('show.html.twig',['data'=>$vetements]);
    }

    public function detail(Application $app ,$id) {
        $this->typeModel = new TypeModel($app);
        $types = $this->typeModel->getAllType();
        $this->vetementModel = new VetementModel($app);

        $donnees = $this->vetementModel->getDetail($id);

        return $app["twig"]->render('frontOffice/Vetement/detail.html.twig',['types'=>$types,'donnees'=>$donnees]);
    }



    public function connect(Application $app)
    {

        $controllers = $app['controllers_factory'];

        $controllers->get('/', 'App\Controller\vetementController::index')->bind('vetement.index');
        $controllers->get('/show', 'App\Controller\vetementController::show')->bind('vetement.show');

        $controllers->get('/add', 'App\Controller\vetementController::add')->bind('vetement.add');
        $controllers->post('/add', 'App\Controller\vetementController::validFormAdd')->bind('vetement.validFormAdd');

        $controllers->get('/delete/{id}', 'App\Controller\vetementController::delete')->bind('vetement.delete')->assert('id_vetement', '\d+');
        $controllers->delete('/delete', 'App\Controller\vetementController::validFormDelete')->bind('vetement.validFormDelete');

        $controllers->get('/edit/{id}', 'App\Controller\vetementController::edit')->bind('vetement.edit')->assert('id_vetement', '\d+');
        $controllers->put('/edit', 'App\Controller\vetementController::validFormEdit')->bind('vetement.validFormEdit');

        $controllers->get('/detail/{id}', 'App\Controller\vetementController::detail')->bind('vetement.detail')->assert('id_vetement', '\d+');

        $controllers->get('/categories', 'App\Controller\vetementController::showCategories')->bind('vetement.categories');

        $controllers->post('/categories', 'App\Controller\vetementController::showbyCategories')->bind('vetement.categoriesDetail');

        $controllers->get('/detail/{id}', 'App\Controller\vetementController::detail')->bind('vetement.detail')->assert('id_vetement', '\d+');

        return $controllers;
    }
}