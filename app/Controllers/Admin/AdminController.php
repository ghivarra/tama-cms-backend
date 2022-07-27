<?php namespace App\Controllers\Admin;

/**
 * API Admin
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

use App\Libraries\Akses;
use App\Models\Admin;
use App\Controllers\BaseController;
use Config\Services;

class AdminController extends BaseController
{
    public function select($id)
    {
        $admin = new Admin();

        $get = $admin->select('adm_id, adm_nama, adm_email, adm_status, adm_role')
                     ->where('adm_id', $id)
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
            'message' => "Data Admin {$get['adm_nama']} berhasil diambil pada ".date('Y-m-d H:i:s'),
            'data'    => $get
        ]);
    }

    //====================================================================================================

    public function all()
    {
        $admin = new Admin();

        // return
        return $this->response->setStatusCode(200)->setJSON([
            'code'    => 200,
            'status'  => 'success',
            'title'   => 'Pengambilan Data Berhasil',
            'message' => "Data Admin berhasil diambil pada ".date('Y-m-d H:i:s'),
            'data'    => $admin->select('adm_id, adm_nama, adm_status')->orderBy('adm_nama', 'ASC')->findAll()
        ]);
    }

    //====================================================================================================

    public function datatable()
    {
        $admin = new Admin();

        // get request
        $order  = $this->request->getGet('order');
        $start  = $this->request->getGet('start');
        $length = $this->request->getGet('length');
        $search = $this->request->getGet('search');
        $column = $this->request->getGet('columns');
        $select = "adm_id, adm_nama, adm_email, adm_foto, adm_status, adm_role, rol_nama, adm_created_at as date_create, adm_updated_at as date_update";


        // order field
        $orderKey   = $order[0]['column'];
        $orderField = $column[$orderKey]['data'];
        $orderType  = strtoupper($order[0]['dir']);

        // get total data
        $total = $admin->where('adm_deleted_at', NULL)->countAllResults();

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
            $hasil = $admin->select($select)
                           ->join('admin_role', 'adm_role = rol_id')
                           ->orderBy($orderField, $orderType)
                           ->orderBy('adm_nama', 'ASC')
                           ->limit($length, $start)
                           ->find();

            return $this->response->setJSON([
                'draw'            => $this->request->getGet('draw'),
                'recordsTotal'    => $total,
                'recordsFiltered' => $total,
                'data'            => $admin->datatable($hasil, $start)
            ]);
        }

        // get total filtered
        foreach ($searchField as $item):

            switch ($item['field']) {
                case 'adm_status':
                    $admin->where($item['field'], $item['value']);
                    break;

                case 'adm_role':
                    $admin->where('adm_role', $item['value']);
                    break;

                case 'adm_created_at':
                    $date = date('Y-m-d', strtotime($item['value']));
                    $admin->like($item['field'], $date, 'after');  
                    break;

                case 'adm_nama':
                    $admin->like($item['field'], $item['value']);
                    $again = true;
                    $againVal = $item['value'];
                    break;
                
                default:
                    $admin->like($item['field'], $item['value']);
                    break;
            }

        endforeach;

        if (isset($again) && isset($againVal))
        {
            $admin->orLike('adm_email', $againVal);

            foreach ($searchField as $item):

                switch ($item['field']) {
                    case 'adm_status':
                        $admin->where($item['field'], $item['value']);
                        break;

                    case 'adm_role':
                        $admin->where('adm_role', $item['value']);
                        break;

                    case 'adm_created_at':
                        $date = date('Y-m-d', strtotime($item['value']));
                        $admin->like($item['field'], $date, 'after');  
                        break;

                    case 'adm_nama':
                        // do nothing
                        break;
                    
                    default:
                        $admin->like($item['field'], $item['value']);
                        break;
                }

            endforeach;
        }

        $filtered = $admin->where('adm_deleted_at', NULL)->countAllResults();

        foreach ($searchField as $item):

            switch ($item['field']) {
                case 'adm_status':
                    $admin->where($item['field'], $item['value']);
                    break;

                case 'adm_role':
                    $admin->where('adm_role', $item['value']);
                    break;

                case 'adm_created_at':
                    $date = date('Y-m-d', strtotime($item['value']));
                    $admin->like($item['field'], $date, 'after');  
                    break;

                case 'adm_nama':
                    $admin->like($item['field'], $item['value']);
                    $again = true;
                    $againVal = $item['value'];
                    break;
                
                default:
                    $admin->like($item['field'], $item['value']);
                    break;
            }

        endforeach;

        if (isset($again) && isset($againVal))
        {
            $admin->orLike('adm_email', $againVal);

            foreach ($searchField as $item):

                switch ($item['field']) {
                    case 'adm_status':
                        $admin->where($item['field'], $item['value']);
                        break;

                    case 'adm_role':
                        $admin->where('adm_role', $item['value']);
                        break;

                    case 'adm_created_at':
                        $date = date('Y-m-d', strtotime($item['value']));
                        $admin->like($item['field'], $date, 'after');  
                        break;

                    case 'adm_nama':
                        // do nothing
                        break;
                    
                    default:
                        $admin->like($item['field'], $item['value']);
                        break;
                }

            endforeach;
        }

        $hasil = $admin->select($select)
                       ->join('admin_role', 'adm_role = rol_id')
                       ->orderBy($orderField, $orderType)
                       ->orderBy('adm_nama', 'ASC')
                       ->limit($length, $start)
                       ->find();

        return $this->response->setJSON([
            'draw'            => $this->request->getGet('draw'),
            'recordsTotal'    => $total,
            'recordsFiltered' => $filtered,
            'data'            => $admin->datatable($hasil, $start)
        ]);
    }

    //====================================================================================================

    public function create()
    {
        $data = [
            'adm_email'    => $this->request->getPost('adm_email'),
            'adm_nama'     => $this->request->getPost('adm_nama'),
            'adm_role'     => $this->request->getPost('adm_role'),
            'adm_status'   => $this->request->getPost('adm_status'),
            'adm_password' => $this->request->getPost('adm_password')
        ];

        // validasi
        $validation = Services::validation();
        $validation->setRules([
            'adm_email'    => ['label' => 'Email', 'rules' => 'required|max_length[120]|is_unique[admin.adm_email]|valid_email'],
            'adm_nama'     => ['label' => 'Nama Admin', 'rules' => 'required|max_length[120]'],
            'adm_role'     => ['label' => 'Role', 'rules' => 'required|is_not_unique[admin_role.rol_id]'],
            'adm_status'   => ['label' => 'Status', 'rules' => 'required|in_list[aktif,nonaktif]'],
            'adm_password' => ['label' => 'Password', 'rules' => 'required|min_length[10]'],
        ]);

        // run
        if (!$validation->run($data))
        {
            $errors = $validation->getErrors();

            if (!preg_match("#[0-9]+#", $data['adm_password']))
            {
                $errors['adm_password'] = 'Password harus menggunakan huruf dan angka';
            }

            return $this->response->setStatusCode(400)->setJSON([
                'code'    => 400,
                'status'  => 'error',
                'title'   => 'Gagal Menyimpan Data',
                'message' => implode(', ', $errors)
            ]);

        } elseif(!preg_match("#[0-9]+#", $data['adm_password'])) {

            return $this->response->setStatusCode(400)->setJSON([
                'code'    => 400,
                'status'  => 'error',
                'title'   => 'Gagal Menyimpan Data',
                'message' => 'Password harus menggunakan huruf dan angka'
            ]);

        }

        // set data
        $data['adm_password'] = password_hash($data['adm_password'], PASSWORD_DEFAULT);

        // create data
        $admin = new Admin();
        $admin->insert($data);

        // return
        return $this->response->setStatusCode(200)->setJSON([
            'code'    => 200,
            'status'  => 'success',
            'title'   => 'Data Disimpan',
            'message' => "Admin {$data['adm_nama']} sudah dibuat"
        ]);
    }

    //====================================================================================================

    public function update()
    {
        $data = [
            'adm_id'       => $this->request->getPost('adm_id'),
            'adm_nama'     => $this->request->getPost('adm_nama'),
            'adm_status'   => $this->request->getPost('adm_status'),
            'adm_email'    => $this->request->getPost('adm_email'),
            'adm_password' => $this->request->getPost('adm_password'),
            'adm_role'     => $this->request->getPost('adm_role'),
        ];

        // get admin
        $admin = new Admin();
        $old   = $admin->where('adm_id', $data['adm_id'])->first();

        if (empty($old))
        {
            return $this->response->setStatusCode(400)->setJSON([
                'code'    => 400,
                'status'  => 'error',
                'title'   => 'Gagal Menyimpan Data',
                'message' => 'Admin tidak ditemukan'
            ]);
        }

        // set rules
        $rules = [
            'adm_id'     => ['label' => 'Admin', 'rules' => 'required|numeric'],
            'adm_nama'   => ['label' => 'Nama Admin', 'rules' => 'required|max_length[120]'],
            'adm_status' => ['label' => 'Status', 'rules' => 'required|in_list[aktif,nonaktif]'],
            'adm_role'     => ['label' => 'Role', 'rules' => 'required|numeric|is_not_unique[admin_role.rol_id]'],
        ];

        if ($data['adm_email'] != $old['adm_email'])
        {
            $rules['adm_email'] = ['label' => 'Email', 'rules' => 'required|valid_email|is_unique[admin.adm_email]'];

        } else {

            $rules['adm_email'] = ['label' => 'Email', 'rules' => 'required|valid_email'];
        }

        // change password
        if (!empty($data['adm_password']))
        {
            $rules['adm_password'] = ['label' => 'Password', 'rules' => 'required|min_length[10]'];
        }

        // validasi
        $validation = Services::validation();
        $validation->setRules($rules);

        // run
        if (!$validation->run($data))
        {
            $errors = $validation->getErrors();

            if (isset($rules['adm_password']))
            {
                if (!preg_match("#[0-9]+#", $data['adm_password']))
                {
                    $errors['adm_password'] = 'Password harus menggunakan huruf dan angka';
                }
            }

            return $this->response->setStatusCode(400)->setJSON([
                'code'    => 400,
                'status'  => 'error',
                'title'   => 'Gagal Menyimpan Data',
                'message' => implode(', ', $errors)
            ]);

        } elseif (isset($rules['adm_password'])) {

            if (!preg_match("#[0-9]+#", $data['adm_password']))
            { 
                return $this->response->setStatusCode(400)->setJSON([
                    'code'    => 400,
                    'status'  => 'error',
                    'title'   => 'Gagal Menyimpan Data',
                    'message' => 'Password harus menggunakan huruf dan angka'
                ]);
            }
        }

        // now unset unused data
        $id = $data['adm_id'];
        unset($data['adm_id']);

        // parse password
        if (isset($rules['adm_password']))
        {
            $data['adm_password'] = password_hash($data['adm_password'], PASSWORD_DEFAULT);

        } else {

            unset($data['adm_password']);
        }

        $admin = new Admin();
        $admin->update($id, $data);

        // return
        return $this->response->setStatusCode(200)->setJSON([
            'code'    => 200,
            'status'  => 'success',
            'title'   => 'Data Disimpan',
            'message' => "Perubahan Admin {$data['adm_nama']} sudah disimpan"
        ]);
    }

    //====================================================================================================

    public function updateStatus()
    {
        $id = $this->request->getPost('adm_id');

        if (empty($id))
        {
            return $this->response->setStatusCode(400)->setJSON([
                'code'    => 400,
                'status'  => 'error',
                'title'   => 'Gagal Merubah Status',
                'message' => 'Admin Tidak Ditemukan'
            ]);
        }

        $admin = new Admin();
        $get   = $admin->select('adm_status, adm_nama')->where('adm_id', $id)->first();

        if (empty($get))
        {
            return $this->response->setStatusCode(400)->setJSON([
                'code'    => 400,
                'status'  => 'error',
                'title'   => 'Gagal Merubah Status',
                'message' => 'Admin Tidak Ditemukan'
            ]);
        }

        // update
        $set['adm_status'] = ($get['adm_status'] == 'aktif') ? 'nonaktif' : 'aktif';
        $admin->update($id, $set);

        // set status
        $status = ($set['adm_status'] == 'aktif') ? 'diaktifkan' : 'dinonaktifkan';

        // return
        return $this->response->setStatusCode(200)->setJSON([
            'code'    => 200,
            'status'  => 'success',
            'title'   => 'Status Disimpan',
            'message' => "Admin {$get['adm_nama']} sudah {$status}"
        ]);
    }

    //====================================================================================================

    public function delete()
    {
        $id = $this->request->getPost('adm_id');

        if (empty($id))
        {
            return $this->response->setStatusCode(400)->setJSON([
                'code'    => 400,
                'status'  => 'error',
                'title'   => 'Gagal Menghapus Data',
                'message' => 'Admin Tidak Ditemukan'
            ]);
        }

        $admin = new Admin();
        $get   = $admin->select('adm_nama')->where('adm_id', $id)->first();

        if (empty($get))
        {
            return $this->response->setStatusCode(400)->setJSON([
                'code'    => 400,
                'status'  => 'error',
                'title'   => 'Gagal Menghapus Data',
                'message' => 'Admin Tidak Ditemukan'
            ]);
        }

        // delete
        $admin->delete($id);

        // return
        return $this->response->setStatusCode(200)->setJSON([
            'code'    => 200,
            'status'  => 'success',
            'title'   => 'Data Dihapus',
            'message' => "Admin {$get['adm_nama']} sudah dihapus"
        ]);
    }

    //====================================================================================================
}