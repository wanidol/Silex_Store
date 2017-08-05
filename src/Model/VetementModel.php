<?php

namespace App\Model;

use Doctrine\DBAL\Query\QueryBuilder;
use Silex\Application;

use App\Helper\Helper_Date;

class VetementModel{

	private $db;

    public function __construct(Application $app) {
        $this->db = $app['db'];
    }

	public function getAllVetements()
    {

        $queryBuilder = new QueryBuilder($this->db);
        $queryBuilder2 = new QueryBuilder($this->db);
        $queryBuilder

            ->select('Count(*) as total')
            ->from('vetements', 'v')
            ->innerJoin('v', 'types', 't', 'v.id_type=t.id_Type');
        $total = $queryBuilder->execute()->fetch();

        $queryBuilder2
            ->select('v.id_vetement','t.libelle', 'v.descriptif', 'v.prixDeBase', 'v.taille', 'v.photo','v.dateAchat')
            ->from('vetements', 'v')
            ->innerJoin('v', 'types', 't', 'v.id_type=t.id_Type')
            ->addOrderBy('v.id_vetement', 'ASC');

        $result = $queryBuilder2->execute()->fetchAll();

        $vdate = new Helper_Date();
        $max = (int)$total['total'];

        for ($i = 0;$i < $max; $i++){
            $temp = $result[$i]['dateAchat'];
            $result[$i]['dateAchat'] = $vdate->UsToFr($temp);
        }

        return $result;
    }

    public function getVetementByID($id){
        $queryBuilder = new QueryBuilder($this->db);
        $queryBuilder
            ->select('v.id_vetement', 'v.descriptif', 'v.prixDeBase', 'v.taille', 'v.photo')
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
        $vdate = new Helper_Date();
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
            ->setParameter(5, $vdate->FrToUs())
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
            ->set('photo','?')
            ->where('id_vetement = ?')
            ->setParameter(0, $donnees['descriptif'])
            ->setParameter(1, $donnees['type_id'])
            ->setParameter(2, $donnees['prix'])
            ->setParameter(3, $donnees['taille'])
            ->setParameter(4, $donnees['photo'])
            ->setParameter(5, $donnees['id_vetement']);
        return $queryBuilder->execute();
    }

    function getVetementsByCategories($id){

        $queryBuilder = new QueryBuilder($this->db);
        $queryBuilder
            ->select('v.id_vetement', 't.libelle', 'v.descriptif', 'v.prixDeBase','v.taille', 'v.photo')
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