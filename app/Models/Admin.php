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

    protected $allowedFields = ['adm_nama', 'adm_password', 'adm_email', 'adm_status', 'adm_role', 'adm_foto', 'adm_token_lupa', 'adm_token_waktu', 'adm_updated_by', 'adm_created_by'];

    protected $useTimestamps = TRUE;
    protected $createdField  = 'adm_created_at';
    protected $updatedField  = 'adm_updated_at';
    protected $deletedField  = 'adm_deleted_at';


    protected $validationRules    = [];
    protected $validationMessages = [];
    protected $skipValidation     = TRUE;

    protected $allowCallbacks = TRUE;

    //========================================================================================================

    public function __construct()
    {
        parent::__construct();
        $this->app = new App();
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
        $get = $this->select('admin.*, rol_nama, rol_list_modul, rol_list_menu')
                    ->join('admin_role', 'adm_role = rol_id', 'left')
                    ->where(['adm_email' => $email]);

        if (!empty($params))
        {
            $get = empty($params) ? $get->first() : $get->where($params)->first();
        }

        if (empty($get) OR !password_verify($password, $get['adm_password']))
        {
            return FALSE;
        }

        // set to non json
        $get['rol_list_menu'] = json_decode($get['rol_list_menu'], TRUE);
        $get['rol_list_modul'] = json_decode($get['rol_list_modul'], TRUE);

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

        // check akun
        $get = $this->select('admin.*, rol_nama, rol_list_modul, rol_list_menu')
                    ->join('admin_role', 'adm_role = rol_id', 'left')
                    ->where('adm_id', $_SESSION['admin']['adm_id'])
                    ->where('adm_status', 'aktif')
                    ->first();

        if (empty($get))
        {
            return FALSE;
        }

        // set to non json
        $get['rol_list_menu'] = json_decode($get['rol_list_menu'], TRUE);
        $get['rol_list_modul'] = json_decode($get['rol_list_modul'], TRUE);

        // set new session
        unset($get['adm_password']);
        unset($get['adm_token_lupa']);
        unset($get['adm_token_waktu']);

        $_SESSION['admin'] = $get;

        // return
        return TRUE;
    }

    //========================================================================================================

    public function info()
    {
        return $_SESSION['admin'];
    }

    //========================================================================================================
}