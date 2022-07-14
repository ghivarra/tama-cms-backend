<?php namespace App\Models;

/**
 * Admin ORM
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

class Admin extends Model
{
    protected $table      = 'admin';
    protected $primaryKey = 'adm_id';

    protected $useAutoIncrement = TRUE;

    protected $returnType     = 'array';
    protected $useSoftDeletes = TRUE;

    protected $allowedFields = ['adm_password', 'adm_email', 'adm_status', 'adm_role', 'adm_foto', 'adm_token_lupa', 'adm_token_waktu', 'adm_updated_by', 'adm_created_by'];

    protected $useTimestamps = TRUE;
    protected $createdField  = 'adm_created_at';
    protected $updatedField  = 'adm_updated_at';
    protected $deletedField  = 'adm_deleted_at';


    protected $createdByField = 'adm_created_by';
    protected $updatedByField = 'adm_updated_by';
    protected $validationRules    = [];
    protected $validationMessages = [];
    protected $skipValidation     = TRUE;

    protected $allowCallbacks = TRUE;

    protected $afterFind    = ['timestamp'];
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

    protected function timestamp(array $res)
    {
        if (!isset($res['data']) OR empty($res['data']))
        {
            return $res;
        }

        // change
        $timestamp = [$this->createdField, $this->updatedField, $this->deletedField];

        // check
        if (isset($res['data'][0]))
        {
            foreach ($res['data'] as $n => $item):

                foreach ($timestamp as $value)
                {
                    if (isset($item[$value]))
                    {
                        $res['data'][$n][$value] = strtotime($item[$value]);
                    }
                }

            endforeach;

        } else {

            foreach ($timestamp as $value):

                if (isset($res['data'][$value]))
                {
                    $res['data'][$value] = strtotime($res['data'][$value]);
                }

            endforeach;
        }

        // return
        return $res;
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

            if (isset($item['adm_status']))
            {
                $data[$n]['adm_status'] = ucfirst($item['adm_status']);
            }

        endforeach;

        // return
        return $data;
    }

    //========================================================================================================

    public function auth(string $email, string $password, array $params = [])
    {
        $get = $this->where(['adm_email' => $email]);

        if (!empty($params))
        {
            $get = empty($params) ? $get->first() : $get->where($params)->first();
        }

        if (empty($get) OR !password_verify($password, $get['adm_password']))
        {
            return FALSE;
        }

        // unset data
        unset($get['adm_password']);
        unset($get['adm_token_lupa']);
        unset($get['adm_token_waktu']);

        // set session
        $_SESSION['admin'] = $get;

        // set secret
        $_SESSION[$_ENV['SESSION_LOGIN_KEY']] = md5($_ENV['SESSION_LOGIN_DATA']);

        // return
        return TRUE;
    }

    //========================================================================================================

    public function logout()
    {
        session_destroy();
        return TRUE;
    }

    //========================================================================================================

    public function check()
    {
        $key = $_ENV['SESSION_LOGIN_KEY'];

        if (!isset($_SESSION[$key]) OR !hash_equals($_SESSION[$key], md5($_ENV['SESSION_LOGIN_DATA'])))
        {
            return FALSE;
        }

        return TRUE;
    }

    //========================================================================================================

    public function info()
    {
        return $_SESSION['admin'];
    }

    //========================================================================================================
}