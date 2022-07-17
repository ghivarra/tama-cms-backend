<?php namespace App\Controllers\LIT;

/**
 * API Admin Modul
 *
 * "I treat my works as my own child, be careful with my childrens"
 *
 * Created with love and proud by Ghivarra Senandika Rushdie
 *
 * @package API GSS LIT
 *
 * @var https://facebook.com/bcvgr
 * @var https://twitter.com/ghivarra
 * @var https://instagram.com/ghivarra
 *
**/

use App\Libraries\Akses;
use App\Models\AdminModul;
use App\Controllers\BaseController;
use Config\Services;

class ModulController extends BaseController
{
    public function select($id)
    {
        $adminModul = new AdminModul();

        $get = $adminModul->select('mod_id, mod_nama, mod_status, adm_create.adm_nama as kreator, adm_update.adm_nama as editor')
                          ->join('admin as adm_create', 'mod_created_by = adm_create.adm_id', 'left')
                          ->join('admin as adm_update', 'mod_updated_by = adm_update.adm_id', 'left')
                          ->where('mod_id', $id)
                          ->first();

        if (empty($get))
        {
            return $this->response->setStatusCode(404)->setJSON([
                'code'    => 404,
                'status'  => 'error',
                'title'   => 'Gagal Mengambil Data',
                'message' => 'Data modul tidak ditemukan'
            ]);
        }

        // return
        return $this->response->setStatusCode(200)->setJSON([
            'code'    => 200,
            'status'  => 'success',
            'title'   => 'Pengambilan Data Berhasil',
            'message' => "Data Modul {$get['mod_nama']} berhasil diambil pada ".date('Y-m-d H:i:s'),
            'data'    => $get
        ]);
    }

    //====================================================================================================

    public function get()
    {

    }

    //====================================================================================================

    public function create()
    {

    }

    //====================================================================================================

    public function update()
    {

    }

    //====================================================================================================

    public function delete()
    {

    }

    //====================================================================================================
}