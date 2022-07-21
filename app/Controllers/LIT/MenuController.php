<?php namespace App\Controllers\LIT;

/**
 * API Admin Menu
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
use App\Models\AdminMenu;
use App\Controllers\BaseController;
use Config\Services;

class MenuController extends BaseController
{
    public function select($id)
    {
        $menu = new AdminMenu();

        $get = $menu->select('men_id, men_nama, men_jenis, men_parent, men_link, men_icon, men_urutan, men_status, adm_create.adm_nama as kreator, adm_update.adm_nama as editor')
                    ->join('admin as adm_create', 'men_created_by = adm_create.adm_id', 'left')
                    ->join('admin as adm_update', 'men_updated_by = adm_update.adm_id', 'left')
                    ->where('men_id', $id)
                    ->first();

        if (empty($get))
        {
            return $this->response->setStatusCode(404)->setJSON([
                'code'    => 404,
                'status'  => 'error',
                'title'   => 'Gagal Mengambil Data',
                'message' => 'Data menu tidak ditemukan'
            ]);
        }

        // return
        return $this->response->setStatusCode(200)->setJSON([
            'code'    => 200,
            'status'  => 'success',
            'title'   => 'Pengambilan Data Berhasil',
            'message' => "Data menu {$get['men_nama']} berhasil diambil pada ".date('Y-m-d H:i:s'),
            'data'    => $get
        ]);
    }

    //====================================================================================================

    public function get()
    {
        $menu    = new AdminMenu();
        $allMenu = $menu->select('men_id, men_nama, men_jenis, men_parent, men_link, men_icon, men_urutan, men_status, men_created_at as date_create, men_updated_at as date_update, adm_create.adm_nama as kreator, adm_update.adm_nama as editor')
                        ->join('admin as adm_create', 'men_created_by = adm_create.adm_id', 'left')
                        ->join('admin as adm_update', 'men_updated_by = adm_update.adm_id', 'left')
                        ->orderBy('men_urutan', 'ASC')
                        ->findAll();

        // parse data
        if (empty($allMenu))
        {
            return $this->response->setStatusCode(200)->setJSON([
                'code'    => 200,
                'status'  => 'success',
                'title'   => 'Berhasil Menarik Data',
                'message' => 'Anda berhasil menarik list menu',
                'data'    => []
            ]);
        }

        $listMenu = [];

        // parse parent
        foreach ($allMenu as $n => $item):

            if ($item['men_jenis'] == 'parent')
            {
                $item['men_child'] = [];

                // push and delete parent
                array_push($listMenu, $item);
                unset($allMenu[$n]);
            }

        endforeach;

        if (!empty($listMenu))
        {
            $parent = array_column($listMenu, 'men_id');

            // parse child
            foreach ($allMenu as $n => $item):

                // find and push
                $key = array_search($item['men_parent'], $parent);
                array_push($listMenu[$key]['men_child'], $item);

            endforeach;
        }

        // return
        return $this->response->setStatusCode(200)->setJSON([
            'code'    => 200,
            'status'  => 'success',
            'title'   => 'Berhasil Menarik Data',
            'message' => 'Anda berhasil menarik list menu',
            'data'    => $listMenu
        ]);
    }

    //====================================================================================================

    public function create()
    {
        $data = [
            'men_nama'   => $this->request->getPost('men_nama'),
            'men_status' => $this->request->getPost('men_status')
        ];

        // validasi
        $validation = Services::validation();
        $validation->setRules([
            'men_nama'   => ['label' => 'Nama Modul', 'rules' => 'required|max_length[120]|is_unique[admin_modul.men_nama]'],
            'men_status' => ['label' => 'Status', 'rules' => 'required|in_list[aktif,nonaktif]'],
        ]);

        // run
        if (!$validation->run($data))
        {
            return $this->response->setStatusCode(400)->setJSON([
                'code'    => 400,
                'status'  => 'error',
                'title'   => 'Gagal Menyimpan Data',
                'message' => implode(', ', $validation->getErrors())
            ]);
        }

        // create data
        $menu = new AdminModul();
        $menu->insert($data);

        // return
        return $this->response->setStatusCode(200)->setJSON([
            'code'    => 200,
            'status'  => 'success',
            'title'   => 'Data Disimpan',
            'message' => "Modul {$data['men_nama']} sudah dibuat"
        ]);
    }

    //====================================================================================================

    public function update()
    {
        $data = [
            'men_id'     => $this->request->getPost('men_id'),
            'men_nama'   => $this->request->getPost('men_nama'),
            'men_status' => $this->request->getPost('men_status')
        ];

        // validasi
        $validation = Services::validation();
        $validation->setRules([
            'men_id'     => ['label' => 'Modul', 'rules' => 'required|numeric|is_not_unique[admin_modul.men_id]'],
            'men_nama'   => ['label' => 'Nama Modul', 'rules' => 'required|max_length[120]'],
            'men_status' => ['label' => 'Status', 'rules' => 'required|in_list[aktif,nonaktif]'],
        ]);

        // run
        if (!$validation->run($data))
        {
            return $this->response->setStatusCode(400)->setJSON([
                'code'    => 400,
                'status'  => 'error',
                'title'   => 'Gagal Menyimpan Data',
                'message' => implode(', ', $validation->getErrors())
            ]);
        }

        // now unset unused data
        $id = $data['men_id'];
        unset($data['men_id']);

        $menu = new AdminModul();
        $menu->update($id, $data);

        // return
        return $this->response->setStatusCode(200)->setJSON([
            'code'    => 200,
            'status'  => 'success',
            'title'   => 'Data Disimpan',
            'message' => "Perubahan Modul {$data['men_nama']} sudah disimpan"
        ]);
    }

    //====================================================================================================

    public function updateUrutan()
    {
        $data = $this->request->getPost('urutan');

        if (empty($data) && !is_array(json_decode($data, TRUE)) && (json_last_error() != JSON_ERROR_NONE))
        {
            return $this->response->setStatusCode(400)->setJSON([
                'code'    => 400,
                'status'  => 'error',
                'title'   => 'Gagal Menyimpan Urutan',
                'message' => 'Ada kesalahan dalam struktur data menu'
            ]);
        }

        $data = json_decode($data, TRUE);

        // validasi
        $validasi = Services::validation();
        $validasi->setRules([
            'men_id'     => ['label' => 'ID Menu', 'rules' => 'required|numeric|is_not_unique[admin_menu.men_id]'],
            'men_urutan' => ['label' => 'Urutan', 'rules' => 'required|numeric'],
        ]);

        // run
        foreach ($data as $item):

            if (!$validasi->run($item))
            {
                return $this->response->setStatusCode(400)->setJSON([
                    'code'    => 400,
                    'status'  => 'error',
                    'title'   => 'Gagal Menyimpan Urutan',
                    'message' => implode(', ', $validasi->getErrors())
                ]);
            }

        endforeach;

        // update Batch
        $menu = new AdminMenu();
        $menu->updateBatch($data, 'men_id');

        // return
        return $this->response->setStatusCode(200)->setJSON([
            'code'    => 200,
            'status'  => 'success',
            'title'   => 'Data Disimpan',
            'message' => "Urutan menu sudah disesuaikan"
        ]);
    }

    //====================================================================================================

    public function updateStatus()
    {
        $id = $this->request->getPost('men_id');

        if (empty($id))
        {
            return $this->response->setStatusCode(400)->setJSON([
                'code'    => 400,
                'status'  => 'error',
                'title'   => 'Gagal Merubah Status',
                'message' => 'Modul Tidak Ditemukan'
            ]);
        }

        $menu = new AdminModul();
        $get   = $menu->select('men_status, men_nama')->where('men_id', $id)->first();

        if (empty($get))
        {
            return $this->response->setStatusCode(400)->setJSON([
                'code'    => 400,
                'status'  => 'error',
                'title'   => 'Gagal Merubah Status',
                'message' => 'Modul Tidak Ditemukan'
            ]);
        }

        // update
        $set['men_status'] = ($get['men_status'] == 'aktif') ? 'nonaktif' : 'aktif';
        $menu->update($id, $set);

        // set status
        $status = ($set['men_status'] == 'aktif') ? 'diaktifkan' : 'dinonaktifkan';

        // return
        return $this->response->setStatusCode(200)->setJSON([
            'code'    => 200,
            'status'  => 'success',
            'title'   => 'Status Disimpan',
            'message' => "Modul {$get['men_nama']} sudah {$status}"
        ]);
    }

    //====================================================================================================

    public function delete()
    {
        $id = $this->request->getPost('men_id');

        if (empty($id))
        {
            return $this->response->setStatusCode(400)->setJSON([
                'code'    => 400,
                'status'  => 'error',
                'title'   => 'Gagal Menghapus Data',
                'message' => 'Modul Tidak Ditemukan'
            ]);
        }

        $menu = new AdminModul();
        $get   = $menu->select('men_nama')->where('men_id', $id)->first();

        if (empty($get))
        {
            return $this->response->setStatusCode(400)->setJSON([
                'code'    => 400,
                'status'  => 'error',
                'title'   => 'Gagal Menghapus Data',
                'message' => 'Modul Tidak Ditemukan'
            ]);
        }

        // delete
        $menu->delete($id);

        // return
        return $this->response->setStatusCode(200)->setJSON([
            'code'    => 200,
            'status'  => 'success',
            'title'   => 'Data Dihapus',
            'message' => "Modul {$get['men_nama']} sudah dihapus"
        ]);
    }

    //====================================================================================================
}