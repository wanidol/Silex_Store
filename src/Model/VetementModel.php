<?php

namespace App\Model;

use Doctrine\DBAL\Query\QueryBuilder;
use Silex\Application;



class VetementModel{

	private $db;

    public function __construct(Application $app) {
        $this->db = $app['db'];
    }

	public function GetTotalRecords()
    {
        $queryBuilder = new QueryBuilder($this->db);
        $queryBuilder
            ->select('Count(*) as total')
            ->from('vetements', 'v')
            ->innerJoin('v', 'types', 't', 'v.id_type=t.id_Type');
        return $queryBuilder->execute()->fetch();
    }

    public function getAllVetements()
    {
        $queryBuilder = new QueryBuilder($this->db);
        $queryBuilder
            ->select('v.id_vetement','t.libelle', 'v.descriptif', 'v.prixDeBase', 'v.taille', 'v.photo','v.dateAchat')
            ->from('vetements', 'v')
            ->innerJoin('v', 'types', 't', 'v.id_type=t.id_Type')
            ->addOrderBy('v.id_vetement', 'ASC');

        $result = $queryBuilder->execute()->fetchAll();
        return $result;
    }

    public function GetTotalByTypes($type)
    {
        $queryBuilder = new QueryBuilder($this->db);
        $queryBuilder
            ->select('Count(*) as total')
            ->from('vetements', 'v')
            ->where('id_type= :Type')
            ->setParameter('Type',$type);
        return $queryBuilder->execute()->fetch();
    }

    public function getVetementByID($id){
        $queryBuilder = new QueryBuilder($this->db);
        $queryBuilder
            ->select('v.id_vetement', 'v.descriptif', 'v.prixDeBase', 'v.taille','v.dateAchat', 'v.photo')
            ->from('vetements', 'v')
            ->where('id_vetement= :id')
            ->setParameter('id', $id);
        return $queryBuilder->execute()->fetch();

    }

    public function deleteVetement($id) {
        $queryBuilder = new QueryBuilder($this->db);
        $queryBuilder
            ->delete('vetements')
            ->where('id_vetement = :id')
            ->setParameter('id',(int)$id)
        ;
        return $queryBuilder->execute();
    }

    public function insertVetement($donnees){

        $queryBuilder = new QueryBuilder($this->db);
        $queryBuilder->insert('Vetements')
            ->values([
                'descriptif' => '?',
                'id_type' => '?',
                'prixDeBase' => '?',
                'taille' => '?',
                'photo' => '?',
                'dateAchat' => '?'
            ])
            ->setParameter(0, $donnees['descriptif'])
            ->setParameter(1, $donnees['type_id'])
            ->setParameter(2, $donnees['prixDeBase'])
            ->setParameter(3, $donnees['taille'])
            ->setParameter(4, $donnees['photo'])
            ->setParameter(5, $donnees['dateAchat'])
        ;
        return $queryBuilder->execute();
    }

    public function updateVetement($donnees) {
        $queryBuilder = new QueryBuilder($this->db);
        $queryBuilder
            ->update('vetements')
            ->set('descriptif', '?')
            ->set('id_type','?')
            ->set('prixDeBase','?')
            ->set('taille','?')
            ->set('dateAchat','?')
            ->set('photo','?')
            ->where('id_vetement = ?')
            ->setParameter(0, $donnees['descriptif'])
            ->setParameter(1, $donnees['type_id'])
            ->setParameter(2, $donnees['prixDeBase'])
            ->setParameter(3, $donnees['taille'])
            ->setParameter(4, $donnees['dateAchat'])
            ->setParameter(5, $donnees['photo'])
            ->setParameter(6, $donnees['id_vetement']);
        return $queryBuilder->execute();
    }

    function getVetementsByCategories($id){

        $queryBuilder = new QueryBuilder($this->db);
        $queryBuilder
            ->select('v.id_vetement', 't.libelle', 'v.descriptif', 'v.prixDeBase','v.taille','v.dateAchat', 'v.photo')
            ->from('vetements', 'v')
            ->innerJoin('v', 'types', 't', 'v.id_type=t.id_Type')
            ->where('v.id_type = :id')
            ->setParameter('id', $id);

        return $queryBuilder->execute()->fetchAll();
    }

    function getDetail($id) {
        $queryBuilder = new QueryBuilder($this->db);
        $queryBuilder
            ->select('v.id_vetement', 't.libelle', 'v.descriptif', 'v.prixDeBase', 'v.taille', 'v.photo')
            ->from('vetements', 'v')
            ->innerJoin('v', 'types', 't', 'v.id_type=t.id_Type')
            ->where('v.id_vetement= :id')
            ->setParameter('id', $id);
        return $queryBuilder->execute()->fetch();
    }

}