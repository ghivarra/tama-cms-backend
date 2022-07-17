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

use App\Models\Admin;
use App\Controllers\BaseController;
use Config\Services;

class AkunController extends BaseController
{
    public function update()
    {
        $data = [
            'nama' => $this->request->getPost('nama'),
            'foto' => $this->request->getFile('foto'),
        ];

        // validasi
        $rules['nama'] = ['label' => 'Nama Akun', 'rules' => 'required|max_length[120]'];

        if (!empty($data['foto']) && $data['foto']->isValid())
        {
            $rules['foto'] = ['label' => 'Foto Profil Akun', 'rules' => 'required|is_image[foto]|mime_in[foto,image/jpeg,image/png,image/gif]|max_size[foto,8192]'];
        }

        // validation
        $validation = Services::validation();
        $validation->setRules($rules);

        if (!$validation->run($data))
        {
            return $this->response->setStatusCode(400)->setJSON([
                'code'    => 400,
                'status'  => 'error',
                'title'   => 'Gagal Menyimpan Perubahan',
                'message' => implode(', ', $validation->getErrors())
            ]);
        }

        // upload foto bila valid
        if (!empty($data['foto']) && $data['foto']->isValid())
        {
            $uploaded_image  = $data['foto'];
            $set['adm_foto'] = $uploaded_image->getRandomName();

            $uploaded_image->move(ROOTPATH . "{$_ENV['ASSET_PATH']}/dist/photo/admin", $set['adm_foto']);
        }

        // set nama
        $set['adm_nama'] = $data['nama'];

        // update
        $admin = new Admin();
        $admin->update($_SESSION['admin']['adm_id'], $set);

        // return 
        return $this->response->setStatusCode(200)->setJSON([
            'code'    => 200,
            'status'  => 'success',
            'title'   => 'Perubahan Disimpan',
            'message' => 'OK'
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

    public function delete()
    {

    }

    //====================================================================================================
}