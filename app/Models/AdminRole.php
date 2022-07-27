<?php namespace App\Models;

/**
 * Admin Role ORM
 *
 * "I treat my works as my own child, be careful with my childrens"
 *
 * Created with love and proud by Ghivarra Senandika Rushdie
 *
 * @package API TAMA CMS
 *
 * @var https://facebook.com/bcvgr
 * @var https://twitter.com/ghivarra
 * @var https://github.com/ghivarra
 *
**/

use CodeIgniter\Model;
use Config\App;

class AdminRole extends Model
{
    protected $table      = 'admin_role';
    protected $primaryKey = 'rol_id';

    protected $useAutoIncrement = TRUE;

    protected $returnType     = 'array';
    protected $useSoftDeletes = TRUE;

    protected $allowedFields = ['rol_nama', 'rol_list_modul', 'rol_list_menu', 'rol_status', 'rol_created_by', 'rol_updated_by'];

    protected $useTimestamps = TRUE;
    protected $createdField  = 'rol_created_at';
    protected $updatedField  = 'rol_updated_at';
    protected $deletedField  = 'rol_deleted_at';

    protected $createdByField = 'rol_created_by';
    protected $updatedByField = 'rol_updated_by';

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

    public function datatable(array $data, int $offset = 0)
    {
        if (empty($data))
        {
            return $data;
        }

        foreach ($data as $n => $item):

            if(isset($offset))
            {
                $data[$n]['no'] = $offset + $n + 1;
            }

            if (isset($item['date_update']))
            {
                $data[$n]['date_update'] = strtotime($item['date_update']);
            }

            if (isset($item['date_create']))
            {
                $data[$n]['date_create'] = strtotime($item['date_create']);
            }

        endforeach;

        // return
        return $data;
    }

    //========================================================================================================
}