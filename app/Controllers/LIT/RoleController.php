<?php namespace App\Controllers\LIT;

/**
 * API Admin Role
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
use App\Models\AdminRole;
use App\Models\AdminModul;
use App\Models\AdminMenu;
use App\Controllers\BaseController;
use Config\Services;

class RoleController extends BaseController
{
    public function select($id)
    {
        $adminRoles = new AdminRole();

        $get = $adminRoles->select('rol_id, rol_nama, rol_status, rol_list_modul as rol_modul, rol_list_menu as rol_menu, adm_create.adm_nama as creator, adm_update.adm_nama as updater')
                          ->join('admin as adm_create', 'rol_created_by = adm_create.adm_id', 'left')
                          ->join('admin as adm_update', 'rol_updated_by = adm_update.adm_id', 'left')
                          ->where('rol_id', $id)
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

        // parse data
        if (!is_array(json_decode($get['rol_modul'], TRUE)))
        {
            $modul = new AdminModul();
            $modul = $modul->select('mod_id')->findAll();

            $get['rol_modul'] = json_encode(array_column($modul, 'mod_id'));
        }

        if (!is_array(json_decode($get['rol_menu'], TRUE)))
        {
            $menu = new AdminMenu();
            $menu = $menu->select('men_id')->findAll();

            $get['rol_menu'] = json_encode(array_column($menu, 'men_id'));
        }

        // return
        return $this->response->setStatusCode(200)->setJSON([
            'code'    => 200,
            'status'  => 'success',
            'title'   => 'Pengambilan Data Berhasil',
            'message' => "Data Role {$get['rol_nama']} berhasil diambil pada ".date('Y-m-d H:i:s'),
            'data'    => $get
        ]);
    }

    //====================================================================================================

    public function all()
    {
        $roles = new AdminRole();

        return $this->response->setStatusCode(200)->setJSON([
            'code'    => 200,
            'status'  => 'success',
            'title'   => 'Pengambilan Data Berhasil',
            'message' => "Data Role berhasil diambil pada ".date('Y-m-d H:i:s'),
            'data'    => $roles->select('rol_id, rol_nama')->where('rol_status', 'aktif')->orderBy('rol_nama', 'ASC')->findAll()
        ]);
    }

    //====================================================================================================

    public function datatable()
    {
        $roles = new AdminRole();

        // get request
        $order  = $this->request->getGet('order');
        $start  = $this->request->getGet('start');
        $length = $this->request->getGet('length');
        $search = $this->request->getGet('search');
        $column = $this->request->getGet('columns');
        $select = "rol_id, rol_nama, rol_status, rol_created_at as date_create, rol_updated_at as date_update, admin_update.adm_nama as updater, admin_create.adm_nama as creator";


        // order field
        $orderKey   = $order[0]['column'];
        $orderField = $column[$orderKey]['data'];
        $orderType  = strtoupper($order[0]['dir']);

        // get total data
        $total = $roles->where('rol_deleted_at', NULL)->countAllResults();

        // add search field
        $searchField = [];

        foreach ($column as $item):

            if (!empty($item['search']['value']))
            {
                array_push($searchField, [
                    'field' => $item['data'],
                    'value' =>$item['search']['value']
                ]);
            }

        endforeach;

        // if search
        if (empty($searchField))
        {
            $hasil = $roles->select($select)
                           ->join("admin as admin_update", "rol_updated_by = admin_update.adm_id")
                           ->join("admin as admin_create", "rol_created_by = admin_create.adm_id")
                           ->orderBy($orderField, $orderType)
                           ->orderBy('rol_nama', 'ASC')
                           ->limit($length, $start)
                           ->find();

            return $this->response->setJSON([
                'draw'            => $this->request->getGet('draw'),
                'recordsTotal'    => $total,
                'recordsFiltered' => $total,
                'data'            => $roles->datatable($hasil, $start)
            ]);
        }

        // get total filtered
        foreach ($searchField as $item):

            if ($item['field'] == 'rol_status')
            {
                $roles->where($item['field'], $item['value']);                

            } elseif ($item['field'] == 'rol_created_at') {

                $date = date('Y-m-d', strtotime($item['value']));
                $roles->like($item['field'], $date, 'after');         

            } else {

                $roles->like($item['field'], $item['value']);
            }

        endforeach;

        $filtered = $roles->where('rol_deleted_at', NULL)->countAllResults();

        // get all data
        foreach ($searchField as $item):

            if ($item['field'] == 'rol_status')
            {
                $roles->where($item['field'], $item['value']);                

            } elseif ($item['field'] == 'rol_created_at') {

                $date = date('Y-m-d', strtotime($item['value']));
                $roles->like($item['field'], $date, 'after');         

            } else {

                $roles->like($item['field'], $item['value']);
            }

        endforeach;

        $hasil = $roles->select($select)
                       ->join("admin as admin_update", "rol_updated_by = admin_update.adm_id")
                       ->join("admin as admin_create", "rol_created_by = admin_create.adm_id")
                       ->orderBy($orderField, $orderType)
                       ->orderBy('rol_nama', 'ASC')
                       ->limit($length, $start)
                       ->find();

        return $this->response->setJSON([
            'draw'            => $this->request->getGet('draw'),
            'recordsTotal'    => $total,
            'recordsFiltered' => $filtered,
            'data'            => $roles->datatable($hasil, $start)
        ]);
    }

    //====================================================================================================

    public function create()
    {
        $data = [
            'rol_nama'   => $this->request->getPost('rol_nama'),
            'rol_status' => $this->request->getPost('rol_status'),
            'rol_modul'  => json_decode($this->request->getPost('rol_modul'), TRUE),
            'rol_menu'   => json_decode($this->request->getPost('rol_menu'), TRUE),
        ];

        if (!is_array($data['rol_modul']) OR !is_array($data['rol_menu']))
        {
            return $this->response->setStatusCode(400)->setJSON([
                'code'    => 400,
                'status'  => 'error',
                'title'   => 'Gagal Menyimpan Data',
                'message' => 'Ada kesalahan dalam format data modul atau menu'
            ]);
        }

        // validasi
        $validation = Services::validation();
        $validation->setRules([
            'rol_nama'   => ['label' => 'Nama Role', 'rules' => 'required|max_length[120]'],
            'rol_status' => ['label' => 'Status', 'rules' => 'required|in_list[aktif,nonaktif]'],
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

        // parse data menu
        $menu  = new AdminMenu();
        $check = $menu->whereNotIn('men_id', $data['rol_menu'])->where('men_deleted_at', NULL)->countAllResults();
        $data['rol_list_menu'] = empty($check) ? json_encode('Semua Menu') : json_encode($data['rol_menu']);
        unset($data['rol_menu']);

        // parse data modul
        $modul  = new AdminModul();
        $check = $modul->whereNotIn('mod_id', $data['rol_modul'])->where('mod_deleted_at', NULL)->countAllResults();
        $data['rol_list_modul'] = empty($check) ? json_encode('Semua Modul') : json_encode($data['rol_modul']);
        unset($data['rol_modul']);

        // create data
        $roles = new AdminRole();
        $roles->insert($data);

        // return
        return $this->response->setStatusCode(200)->setJSON([
            'code'    => 200,
            'status'  => 'success',
            'title'   => 'Data Disimpan',
            'message' => "Role {$data['rol_nama']} sudah dibuat"
        ]);
    }

    //====================================================================================================

    public function update()
    {
        $data = [
            'rol_id'     => $this->request->getPost('rol_id'),
            'rol_nama'   => $this->request->getPost('rol_nama'),
            'rol_status' => $this->request->getPost('rol_status'),
            'rol_modul'  => json_decode($this->request->getPost('rol_modul'), TRUE),
            'rol_menu'   => json_decode($this->request->getPost('rol_menu'), TRUE),
        ];

        if (!is_array($data['rol_modul']) OR !is_array($data['rol_menu']))
        {
            return $this->response->setStatusCode(400)->setJSON([
                'code'    => 400,
                'status'  => 'error',
                'title'   => 'Gagal Menyimpan Data',
                'message' => 'Ada kesalahan dalam format data modul atau menu'
            ]);
        }

        // validasi
        $validation = Services::validation();
        $validation->setRules([
            'rol_id'     => ['label' => 'Menu', 'rules' => 'required|numeric|is_not_unique[admin_role.rol_id]'],
            'rol_nama'   => ['label' => 'Nama Role', 'rules' => 'required|max_length[120]'],
            'rol_status' => ['label' => 'Status', 'rules' => 'required|in_list[aktif,nonaktif]'],
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

        // parse data menu
        $menu  = new AdminMenu();
        $check = $menu->whereNotIn('men_id', $data['rol_menu'])->where('men_deleted_at', NULL)->countAllResults();
        $data['rol_list_menu'] = empty($check) ? json_encode('Semua Menu') : json_encode($data['rol_menu']);
        unset($data['rol_menu']);

        // parse data modul
        $modul  = new AdminModul();
        $check = $modul->whereNotIn('mod_id', $data['rol_modul'])->where('mod_deleted_at', NULL)->countAllResults();
        $data['rol_list_modul'] = empty($check) ? json_encode('Semua Modul') : json_encode($data['rol_modul']);
        unset($data['rol_modul']);

        // now unset unused data
        $id = $data['rol_id'];
        unset($data['rol_id']);

        $roles = new AdminRole();
        $roles->update($id, $data);

        // return
        return $this->response->setStatusCode(200)->setJSON([
            'code'    => 200,
            'status'  => 'success',
            'title'   => 'Data Disimpan',
            'message' => "Perubahan Role {$data['rol_nama']} sudah disimpan"
        ]);
    }

    //====================================================================================================

    public function updateStatus()
    {
        $id = $this->request->getPost('rol_id');

        if (empty($id))
        {
            return $this->response->setStatusCode(400)->setJSON([
                'code'    => 400,
                'status'  => 'error',
                'title'   => 'Gagal Merubah Status',
                'message' => 'Role Tidak Ditemukan'
            ]);
        }

        $roles = new AdminRole();
        $get   = $roles->select('rol_status, rol_nama')->where('rol_id', $id)->first();

        if (empty($get))
        {
            return $this->response->setStatusCode(400)->setJSON([
                'code'    => 400,
                'status'  => 'error',
                'title'   => 'Gagal Merubah Status',
                'message' => 'Role Tidak Ditemukan'
            ]);
        }

        // update
        $set['rol_status'] = ($get['rol_status'] == 'aktif') ? 'nonaktif' : 'aktif';
        $roles->update($id, $set);

        // set status
        $status = ($set['rol_status'] == 'aktif') ? 'diaktifkan' : 'dinonaktifkan';

        // return
        return $this->response->setStatusCode(200)->setJSON([
            'code'    => 200,
            'status'  => 'success',
            'title'   => 'Status Disimpan',
            'message' => "Role {$get['rol_nama']} sudah {$status}"
        ]);
    }

    //====================================================================================================

    public function delete()
    {
        $id = $this->request->getPost('rol_id');

        if (empty($id))
        {
            return $this->response->setStatusCode(400)->setJSON([
                'code'    => 400,
                'status'  => 'error',
                'title'   => 'Gagal Menghapus Data',
                'message' => 'Role Tidak Ditemukan'
            ]);
        }

        $roles = new AdminRole();
        $get   = $roles->select('rol_nama')->where('rol_id', $id)->first();

        if (empty($get))
        {
            return $this->response->setStatusCode(400)->setJSON([
                'code'    => 400,
                'status'  => 'error',
                'title'   => 'Gagal Menghapus Data',
                'message' => 'Role Tidak Ditemukan'
            ]);
        }

        // delete
        $roles->delete($id);

        // return
        return $this->response->setStatusCode(200)->setJSON([
            'code'    => 200,
            'status'  => 'success',
            'title'   => 'Data Dihapus',
            'message' => "Role {$get['rol_nama']} sudah dihapus"
        ]);
    }

    //====================================================================================================
}