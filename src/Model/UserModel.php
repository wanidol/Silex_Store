<?php
namespace App\Model;

use Silex\Application;
use Doctrine\DBAL\Query\QueryBuilder;;

class UserModel {

	private $db;

	public function __construct(Application $app) {
		$this->db = $app['db'];
	}

    public function show(){

        $queryBuilder = new QueryBuilder($this->db);
        $queryBuilder
            ->select('id', 'username', 'motdepasse','password', 'roles', 'email','isEnabled')
            ->from('users')
            ->addOrderBy('id', 'ASC');
        return $queryBuilder->execute()->fetchAll();

    }

	public function verif_login_mdp_Utilisateur($login,$mdp){
		$sql = "SELECT id,username,motdepasse,roles FROM users WHERE username = ? AND motdepasse = ?";
		$res=$this->db->executeQuery($sql,[$login,md5($mdp)]);   //md5($mdp);
		if($res->rowCount()==1)
			return $res->fetch();
		else
			return false;
	}

    public function coord($id){

        $queryBuilder = new QueryBuilder($this->db);
        $queryBuilder
            ->select('*')
            ->from('users')
            ->where('id = :idUser')
            ->setParameter('idUser', $id);
        return $queryBuilder->execute()->fetch();

    }

    public function addUser($donnees)
    {
        $queryBuilder = new QueryBuilder($this->db);
        $queryBuilder->insert('users')
            ->values([
                'username' => '?',
                'motdepasse' => '?',
                'password' => '?',
                'roles' => '?',
                'email' => '?',
                'isEnabled' => '?'
            ])
            ->setParameter(0, $donnees['username'])
            ->setParameter(1, md5($donnees['motdepasse']))
            ->setParameter(2, $donnees['motdepasse'])
            ->setParameter(3, 'ROLE_CLIENT')
            ->setParameter(4, $donnees['email'])
            ->setParameter(5, 1);

        return $queryBuilder->execute();
    }



	public function getUser($user_id) {
		$queryBuilder = new QueryBuilder($this->db);
		$queryBuilder
			->select('*')
			->from('users')
			->where('id = :idUser')
			->setParameter('idUser', $user_id);
		return $queryBuilder->execute()->fetch();

	}
}