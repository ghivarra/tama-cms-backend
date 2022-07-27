<?php namespace App\Controllers\Admin;

/**
 * API Admin Modul
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

    public function all()
    {
        $modul = new AdminModul();

        // return
        return $this->response->setStatusCode(200)->setJSON([
            'code'    => 200,
            'status'  => 'success',
            'title'   => 'Pengambilan Data Berhasil',
            'message' => "Data Modul berhasil diambil pada ".date('Y-m-d H:i:s'),
            'data'    => $modul->select('mod_id, mod_nama, mod_status')->orderBy('mod_nama', 'ASC')->findAll()
        ]);
    }

    //====================================================================================================

    public function datatable()
    {
        $modul = new AdminModul();

        // get request
        $order  = $this->request->getGet('order');
        $start  = $this->request->getGet('start');
        $length = $this->request->getGet('length');
        $search = $this->request->getGet('search');
        $column = $this->request->getGet('columns');
        $select = "mod_id, mod_nama, mod_status, mod_created_at as date_create, mod_updated_at as date_update, admin_update.adm_nama as updater, admin_create.adm_nama as creator";


        // order field
        $orderKey   = $order[0]['column'];
        $orderField = $column[$orderKey]['data'];
        $orderType  = strtoupper($order[0]['dir']);

        // get total data
        $total = $modul->where('mod_deleted_at', NULL)->countAllResults();

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
            $hasil = $modul->select($select)
                           ->join("admin as admin_update", "mod_updated_by = admin_update.adm_id")
                           ->join("admin as admin_create", "mod_created_by = admin_create.adm_id")
                           ->orderBy($orderField, $orderType)
                           ->orderBy('mod_nama', 'ASC')
                           ->limit($length, $start)
                           ->find();

            return $this->response->setJSON([
                'draw'            => $this->request->getGet('draw'),
                'recordsTotal'    => $total,
                'recordsFiltered' => $total,
                'data'            => $modul->datatable($hasil, $start)
            ]);
        }

        // get total filtered
        foreach ($searchField as $item):

            if ($item['field'] == 'mod_status')
            {
                $modul->where($item['field'], $item['value']);                

            } elseif ($item['field'] == 'mod_created_at') {

                $date = date('Y-m-d', strtotime($item['value']));
                $modul->like($item['field'], $date, 'after');         

            } else {

                $modul->like($item['field'], $item['value']);
            }

        endforeach;

        $filtered = $modul->where('mod_deleted_at', NULL)->countAllResults();

        // get all data
        foreach ($searchField as $item):

            if ($item['field'] == 'mod_status')
            {
                $modul->where($item['field'], $item['value']);                

            } elseif ($item['field'] == 'mod_created_at') {

                $date = date('Y-m-d', strtotime($item['value']));
                $modul->like($item['field'], $date, 'after');         

            } else {

                $modul->like($item['field'], $item['value']);
            }

        endforeach;

        $hasil = $modul->select($select)
                       ->join("admin as admin_update", "mod_updated_by = admin_update.adm_id")
                       ->join("admin as admin_create", "mod_created_by = admin_create.adm_id")
                       ->orderBy($orderField, $orderType)
                       ->orderBy('mod_nama', 'ASC')
                       ->limit($length, $start)
                       ->find();

        return $this->response->setJSON([
            'draw'            => $this->request->getGet('draw'),
            'recordsTotal'    => $total,
            'recordsFiltered' => $filtered,
            'data'            => $modul->datatable($hasil, $start)
        ]);
    }

    //====================================================================================================

    public function create()
    {
        $data = [
            'mod_nama'   => $this->request->getPost('mod_nama'),
            'mod_status' => $this->request->getPost('mod_status')
        ];

        // validasi
        $validation = Services::validation();
        $validation->setRules([
            'mod_nama'   => ['label' => 'Nama Modul', 'rules' => 'required|max_length[120]|is_unique[admin_modul.mod_nama]'],
            'mod_status' => ['label' => 'Status', 'rules' => 'required|in_list[aktif,nonaktif]'],
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
        $modul = new AdminModul();
        $modul->insert($data);

        // return
        return $this->response->setStatusCode(200)->setJSON([
            'code'    => 200,
            'status'  => 'success',
            'title'   => 'Data Disimpan',
            'message' => "Modul {$data['mod_nama']} sudah dibuat"
        ]);
    }

    //====================================================================================================

    public function update()
    {
        $data = [
            'mod_id'     => $this->request->getPost('mod_id'),
            'mod_nama'   => $this->request->getPost('mod_nama'),
            'mod_status' => $this->request->getPost('mod_status')
        ];

        // validasi
        $validation = Services::validation();
        $validation->setRules([
            'mod_id'     => ['label' => 'Modul', 'rules' => 'required|numeric|is_not_unique[admin_modul.mod_id]'],
            'mod_nama'   => ['label' => 'Nama Modul', 'rules' => 'required|max_length[120]'],
            'mod_status' => ['label' => 'Status', 'rules' => 'required|in_list[aktif,nonaktif]'],
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
        $id = $data['mod_id'];
        unset($data['mod_id']);

        $modul = new AdminModul();
        $modul->update($id, $data);

        // return
        return $this->response->setStatusCode(200)->setJSON([
            'code'    => 200,
            'status'  => 'success',
            'title'   => 'Data Disimpan',
            'message' => "Perubahan Modul {$data['mod_nama']} sudah disimpan"
        ]);
    }

    //====================================================================================================

    public function updateStatus()
    {
        $id = $this->request->getPost('mod_id');

        if (empty($id))
        {
            return $this->response->setStatusCode(400)->setJSON([
                'code'    => 400,
                'status'  => 'error',
                'title'   => 'Gagal Merubah Status',
                'message' => 'Modul Tidak Ditemukan'
            ]);
        }

        $modul = new AdminModul();
        $get   = $modul->select('mod_status, mod_nama')->where('mod_id', $id)->first();

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
        $set['mod_status'] = ($get['mod_status'] == 'aktif') ? 'nonaktif' : 'aktif';
        $modul->update($id, $set);

        // set status
        $status = ($set['mod_status'] == 'aktif') ? 'diaktifkan' : 'dinonaktifkan';

        // return
        return $this->response->setStatusCode(200)->setJSON([
            'code'    => 200,
            'status'  => 'success',
            'title'   => 'Status Disimpan',
            'message' => "Modul {$get['mod_nama']} sudah {$status}"
        ]);
    }

    //====================================================================================================

    public function delete()
    {
        $id = $this->request->getPost('mod_id');

        if (empty($id))
        {
            return $this->response->setStatusCode(400)->setJSON([
                'code'    => 400,
                'status'  => 'error',
                'title'   => 'Gagal Menghapus Data',
                'message' => 'Modul Tidak Ditemukan'
            ]);
        }

        $modul = new AdminModul();
        $get   = $modul->select('mod_nama')->where('mod_id', $id)->first();

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
        $modul->delete($id);

        // return
        return $this->response->setStatusCode(200)->setJSON([
            'code'    => 200,
            'status'  => 'success',
            'title'   => 'Data Dihapus',
            'message' => "Modul {$get['mod_nama']} sudah dihapus"
        ]);
    }

    //====================================================================================================
}