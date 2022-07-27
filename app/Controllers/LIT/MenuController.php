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

        $get = $menu->select('men_id, men_nama, men_jenis, men_parent, men_link, men_icon, men_urutan, men_status, men_created_at as date_create, men_updated_at as date_update, adm_create.adm_nama as creator, adm_update.adm_nama as updater')
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

    public function all()
    {
        $menu = new AdminMenu();

        return $this->response->setStatusCode(200)->setJSON([
            'code'    => 200,
            'status'  => 'success',
            'title'   => 'Pengambilan Data Berhasil',
            'message' => "Data menu berhasil diambil pada ".date('Y-m-d H:i:s'),
            'data'    => $menu->select('men_id, men_nama, men_status')->orderBy('men_nama', 'ASC')->findAll()
        ]);
    }

    //====================================================================================================

    public function get()
    {
        $menu    = new AdminMenu();
        $allMenu = $menu->select('men_id, men_nama, men_jenis, men_parent, men_link, men_icon, men_urutan, men_status, men_created_at as date_create, men_updated_at as date_update, adm_create.adm_nama as creator, adm_update.adm_nama as updater')
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
                $item['date_create'] = strtotime($item['date_create']);
                $item['date_update'] = strtotime($item['date_update']);
                $item['men_child']   = [];

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

                $item['date_create'] = strtotime($item['date_create']);
                $item['date_update'] = strtotime($item['date_update']);

                // find and push
                $key = array_search($item['men_parent'], $parent);

                if ($key === FALSE || $parent[$key] != $item['men_parent']) {
                    continue;
                }

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

    private function create($data)
    {
        // validasi
        $validation = Services::validation();
        $validation->setRules([
            'men_jenis' => ['label' => 'Jenis Menu', 'rules' => 'required|in_list[parent,submenu]'],
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
        $menu = new AdminMenu();
        $menu->insert($data);

        // return
        return $this->response->setStatusCode(200)->setJSON([
            'code'    => 200,
            'status'  => 'success',
            'title'   => 'Data Disimpan',
            'message' => "Menu {$data['men_nama']} sudah dibuat"
        ]);
    }

    //====================================================================================================

    public function createParent()
    {
        $data = [
            'men_nama'   => $this->request->getPost('men_nama'),
            'men_link'   => $this->request->getPost('men_link'),
            'men_icon'   => $this->request->getPost('men_icon'),
            'men_status' => $this->request->getPost('men_status')
        ];

        // validasi
        $validation = Services::validation();
        $validation->setRules([
            'men_nama'   => ['label' => 'Nama Menu', 'rules' => 'required|max_length[120]'],
            'men_status' => ['label' => 'Status Menu', 'rules' => 'required|in_list[aktif,nonaktif]']
        ]);

        if (!$validation->run($data))
        {
            return $this->response->setStatusCode(400)->setJSON([
                'code'    => 400,
                'status'  => 'error',
                'title'   => 'Gagal Menambah Menu',
                'message' => implode(', ', $validation->getErrors())
            ]);
        }

        // tarik angka urutan tertinggi
        $menu = new AdminMenu();
        $get  = $menu->select('men_urutan')
                     ->where('men_jenis', 'parent')
                     ->orderBy('men_urutan', 'DESC')
                     ->first();

        // create
        return $this->create([
            'men_urutan' => empty($get) ? 1 : $get['men_urutan'] + 1,
            'men_link'   => empty($data['men_link']) ? NULL : $data['men_link'],
            'men_icon'   => empty($data['men_icon']) ? NULL : $data['men_icon'],
            'men_nama'   => $data['men_nama'],
            'men_status' => $data['men_status'],
            'men_jenis'  => 'parent',
            'men_parent' => NULL,
        ]);
    }

    //====================================================================================================

    public function createChild()
    {
        $data = [
            'men_parent' => $this->request->getPost('men_parent'),
            'men_nama'   => $this->request->getPost('men_nama'),
            'men_link'   => $this->request->getPost('men_link'),
            'men_status' => $this->request->getPost('men_status')
        ];

        // validasi
        $validation = Services::validation();
        $validation->setRules([
            'men_nama'   => ['label' => 'Nama Menu', 'rules' => 'required|max_length[120]'],
            'men_link'   => ['label' => 'Link/URL', 'rules' => 'required|max_length[120]'],
            'men_status' => ['label' => 'Status Menu', 'rules' => 'required|in_list[aktif,nonaktif]']
        ]);

        if (!$validation->run($data))
        {
            return $this->response->setStatusCode(400)->setJSON([
                'code'    => 400,
                'status'  => 'error',
                'title'   => 'Gagal Menambah Sub Menu',
                'message' => implode(', ', $validation->getErrors())
            ]);
        }

        // tarik angka urutan tertinggi
        $menu = new AdminMenu();
        $get  = $menu->select('men_urutan')
                     ->where('men_jenis', 'submenu')
                     ->where('men_parent', $data['men_parent'])
                     ->orderBy('men_urutan', 'DESC')
                     ->first();

        // create
        return $this->create([
            'men_urutan' => empty($get) ? 1 : $get['men_urutan'] + 1,
            'men_link'   => empty($data['men_link']) ? NULL : $data['men_link'],
            'men_nama'   => $data['men_nama'],
            'men_status' => $data['men_status'],
            'men_parent' => $data['men_parent'],
            'men_jenis'  => 'submenu',
            'men_icon'   => NULL,
        ]);
    }

    //====================================================================================================

    private function update($data)
    {
        // now unset unused data
        $id = $data['men_id'];
        unset($data['men_id']);

        $menu = new AdminMenu();
        $menu->update($id, $data);

        // return
        return $this->response->setStatusCode(200)->setJSON([
            'code'    => 200,
            'status'  => 'success',
            'title'   => 'Data Disimpan',
            'message' => "Perubahan Menu {$data['men_nama']} sudah disimpan"
        ]);
    }

    //====================================================================================================

    public function updateParent()
    {
        $data = [
            'men_id'     => $this->request->getPost('men_id'),
            'men_nama'   => $this->request->getPost('men_nama'),
            'men_link'   => $this->request->getPost('men_link'),
            'men_icon'   => $this->request->getPost('men_icon'),
            'men_status' => $this->request->getPost('men_status')
        ];

        // validasi
        $validation = Services::validation();
        $validation->setRules([
            'men_id'     => ['label' => 'Menu', 'rules' => 'required|numeric|is_not_unique[admin_menu.men_id]'],
            'men_nama'   => ['label' => 'Nama Menu', 'rules' => 'required|max_length[120]'],
            'men_status' => ['label' => 'Status Menu', 'rules' => 'required|in_list[aktif,nonaktif]']
        ]);

        if (!$validation->run($data))
        {
            return $this->response->setStatusCode(400)->setJSON([
                'code'    => 400,
                'status'  => 'error',
                'title'   => 'Gagal Memperbaharui Menu',
                'message' => implode(', ', $validation->getErrors())
            ]);
        }

        // if empty then null
        $data['men_link'] = empty(trim($data['men_link'])) ? NULL : $data['men_link'];
        $data['men_icon'] = empty(trim($data['men_icon'])) ? NULL : $data['men_icon'];

        return $this->update($data);
    }

    //====================================================================================================

    public function updateChild()
    {
        $data = [
            'men_id'     => $this->request->getPost('men_id'),
            'men_parent' => $this->request->getPost('men_parent'),
            'men_nama'   => $this->request->getPost('men_nama'),
            'men_link'   => $this->request->getPost('men_link'),
            'men_status' => $this->request->getPost('men_status')
        ];

        // validasi
        $validation = Services::validation();
        $validation->setRules([
            'men_id'     => ['label' => 'Menu', 'rules' => 'required|numeric|is_not_unique[admin_menu.men_id]'],
            'men_parent' => ['label' => 'Parent', 'rules' => 'required|numeric|is_not_unique[admin_menu.men_id]'],
            'men_nama'   => ['label' => 'Nama Submenu', 'rules' => 'required|max_length[120]'],
            'men_status' => ['label' => 'Status Submenu', 'rules' => 'required|in_list[aktif,nonaktif]']
        ]);

        if (!$validation->run($data))
        {
            return $this->response->setStatusCode(400)->setJSON([
                'code'    => 400,
                'status'  => 'error',
                'title'   => 'Gagal Memperbaharui Menu',
                'message' => implode(', ', $validation->getErrors())
            ]);
        }

        // if empty then null
        $data['men_link'] = empty(trim($data['men_link'])) ? NULL : $data['men_link'];

        // update
        return $this->update($data);
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
                'message' => 'Menu Tidak Ditemukan'
            ]);
        }

        $menu = new AdminMenu();
        $get   = $menu->select('men_status, men_nama')->where('men_id', $id)->first();

        if (empty($get))
        {
            return $this->response->setStatusCode(400)->setJSON([
                'code'    => 400,
                'status'  => 'error',
                'title'   => 'Gagal Merubah Status',
                'message' => 'Menu Tidak Ditemukan'
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
            'message' => "Menu {$get['men_nama']} sudah {$status}"
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
                'message' => 'Menu Tidak Ditemukan'
            ]);
        }

        $menu = new AdminMenu();
        $get   = $menu->select('men_nama')->where('men_id', $id)->first();

        if (empty($get))
        {
            return $this->response->setStatusCode(400)->setJSON([
                'code'    => 400,
                'status'  => 'error',
                'title'   => 'Gagal Menghapus Data',
                'message' => 'Menu Tidak Ditemukan'
            ]);
        }

        // delete
        $menu->delete($id);

        // return
        return $this->response->setStatusCode(200)->setJSON([
            'code'    => 200,
            'status'  => 'success',
            'title'   => 'Data Dihapus',
            'message' => "Menu {$get['men_nama']} sudah dihapus"
        ]);
    }

    //====================================================================================================
}