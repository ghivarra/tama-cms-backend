<?php namespace App\Models;

/**
 * Admin Menu ORM
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

class AdminMenu extends Model
{
    protected $table      = 'admin_menu';
    protected $primaryKey = 'men_id';

    protected $useAutoIncrement = TRUE;

    protected $returnType     = 'array';
    protected $useSoftDeletes = TRUE;

    protected $allowedFields = ['men_nama', 'men_jenis', 'men_parent', 'men_link', 'men_icon', 'men_urutan', 'men_status', 'men_updated_by', 'men_created_by'];

    protected $useTimestamps = TRUE;
    protected $createdField  = 'men_created_at';
    protected $updatedField  = 'men_updated_at';
    protected $deletedField  = 'men_deleted_at';

    protected $createdByField = 'men_created_by';
    protected $updatedByField = 'men_updated_by';

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