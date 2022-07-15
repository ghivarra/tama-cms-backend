<?php namespace App\Models;

/**
 * Pengaturan ORM
 *
 * "I treat my works as my own child, be careful with my childrens"
 *
 * Created with love and proud by Ghivarra Senandika Rushdie
 *
 * @package API GSS LIT
 *
 * @var https://github.com/ghivarra
 * @var https://facebook.com/bcvgr
 * @var https://twitter.com/ghivarra
 * @var https://instagram.com/ghivarra
 *
**/

use CodeIgniter\Model;
use Config\App;

class Pengaturan extends Model
{
    protected $table      = 'pengaturan';
    protected $primaryKey = 'pgn_id';

    protected $useAutoIncrement = TRUE;

    protected $returnType     = 'array';
    protected $useSoftDeletes = TRUE;

    protected $allowedFields = ['pgn_nama', 'pgn_slug', 'pgn_tagline', 'pgn_deskripsi', 'pgn_versi_logo', 'pgn_versi_icon', 'pgn_versi_web', 'pgn_updated_by', 'pgn_created_by'];

    protected $useTimestamps = TRUE;
    protected $createdField  = 'pgn_created_at';
    protected $updatedField  = 'pgn_updated_at';
    protected $deletedField  = 'pgn_deleted_at';

    protected $createdByField = '';
    protected $updatedByField = '';

    protected $validationRules    = [];
    protected $validationMessages = [];
    protected $skipValidation     = TRUE;

    protected $allowCallbacks = TRUE;

    protected $beforeInsert = ['beforeAdd'];
    protected $beforeUpdate = ['beforeSave'];
    protected $beforeDelete = ['whoDeleteThis'];

    //========================================================================================================

    public function __construct()
    {
        parent::__construct();
        $this->app = new App();
    }

    //========================================================================================================

    protected function whoDeleteThis(array $param)
    {
        $id = $param['id'];
        $this->update($id, [$this->updatedByField => $_SESSION['admin']['adm_id']]);

        return $param;
    }

    //========================================================================================================

    protected function beforeAdd($res)
    {
        return $this->getResponsibleAdmin('insert', $res);
    }

    //========================================================================================================

    protected function beforeSave($res)
    {
        return $this->getResponsibleAdmin('update', $res);
    }

    //========================================================================================================

    protected function getResponsibleAdmin($type, $res)
    {
        if (!isset($_SESSION['admin']['adm_id']))
        {
            return $res;
        }

        // check if one or more
        if (!isset($res['data']) OR empty($res['data']))
        {
            return $res;
        }

        // if multiple data or one row
        if ($type == 'insert')
        {
            if (isset($res['data'][0]))
            {
                foreach ($res['data'] as $n => $item):

                    $res['data'][$n][$this->createdByField] = $_SESSION['admin']['adm_id'];

                endforeach;

            } else {

                $res['data'][$this->createdByField] = $_SESSION['admin']['adm_id'];
            }
        }

        if (isset($res['data'][0]))
        {
            foreach ($res['data'] as $n => $item):

                $res['data'][$n][$this->updatedByField] = $_SESSION['admin']['adm_id'];

            endforeach;

        } else {

            $res['data'][$this->updatedByField] = $_SESSION['admin']['adm_id'];
        }

        // return
        return $res;
    }

    //========================================================================================================
}