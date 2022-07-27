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

    public function changePassword()
    {
        $data = [
            'password_lama' => $this->request->getPost('password_lama'),
            'password_baru' => $this->request->getPost('password_baru'),
            'password_konf' => $this->request->getPost('password_konf'),
        ];

        // data admin
        $admin = new Admin();
        $get   = $admin->select('adm_password')
                       ->where('adm_id', $_SESSION['admin']['adm_id'])
                       ->where('adm_status', 'aktif')
                       ->first();

        // verifikasi password
        if (empty($get) OR !password_verify($data['password_lama'], $get['adm_password']))
        {
            return $this->response->setStatusCode(400)->setJSON([
                'code'    => 400,
                'status'  => 'error',
                'title'   => 'Gagal Menyimpan Password',
                'message' => 'Password lama tidak sesuai'
            ]);
        }

        // validasi
        $validation = Services::validation();
        $validation->setRules([
            'password_lama' => ['label' => 'Password Lama', 'rules' => 'required'],
            'password_baru' => ['label' => 'Password Baru', 'rules' => 'required|matches[password_konf]|min_length[10]'],
            'password_konf' => ['label' => 'Password Konfirmasi', 'rules' => 'required'],
        ]);

        if (!$validation->run($data))
        {
            $errors = $validation->getErrors();

            if (!preg_match("#[0-9]+#", $data['password_baru']))
            {
                $errors['password_baru'] = 'Password harus menggunakan huruf dan angka';
            }

            return $this->response->setStatusCode(400)->setJSON([
                'code'    => 400,
                'status'  => 'error',
                'title'   => 'Gagal Menyimpan Password',
                'message' => implode(', ', $errors)
            ]);

        } elseif (!preg_match("#[0-9]+#", $data['password_baru'])) {

            return $this->response->setStatusCode(400)->setJSON([
                'code'    => 400,
                'status'  => 'error',
                'title'   => 'Gagal Menyimpan Password',
                'message' => 'Password harus menggunakan huruf dan angka'
            ]);
        }

        // simpan dan enkripsi password di sesi
        $encrypter = Services::encrypter();
        $session   = Services::session();
        $email     = Services::email();

        // enkripsi password
        $password = $encrypter->encrypt($data['password_baru']);
        $_SESSION['new_password'] = bin2hex($password);
        $session->markAsTempdata('new_password', 420);

        // load helper text
        helper('text');

        // buat token
        $otp = random_string('numeric', 6);
        $set = [
            'adm_otp'       => password_hash($otp, PASSWORD_DEFAULT),
            'adm_otp_waktu' => date('Y-m-d H:i:s', strtotime('+7 minutes'))
        ];

        // update
        $admin->update($_SESSION['admin']['adm_id'], $set);

        // data kirim via email
        $send = [
            'nama'  => $_SESSION['admin']['adm_nama'],
            'view'  => 'email/v_email_change',
            'token' => $otp
        ];

        // escaping data
        $data = esc($send);
        $view = view('email/v_email_template', $send);

        // kirim email
        $email->setTo($_SESSION['admin']['adm_email']);
        $email->setSubject('Kode OTP Perubahan Password');
        $email->setMessage($view);
        $email->send();

        // return ok
        return $this->response->setStatusCode(200)->setJSON([
            'code'    => 200,
            'status'  => 'success',
            'title'   => 'Kode OTP Sudah Dikirim',
            'message' => 'Periksa email anda, Kode OTP hanya berlaku selama 5 menit'
        ]);
    }

    //====================================================================================================

    public function confirmation()
    {
        $data['otp'] = $this->request->getPost('otp');

        // validasi
        $validation = Services::validation();
        $validation->setRules([
            'otp' => ['label' => 'Kode OTP', 'rules' => 'required|exact_length[6]|numeric']
        ]);

        if (!$validation->run($data))
        {
            return $this->response->setStatusCode(400)->setJSON([
                'code'    => 400,
                'status'  => 'error',
                'title'   => 'Kode Tidak valid',
                'message' => implode(', ', $validation->getErrors())
            ]);
        }

        // get
        $params = [
            'adm_id'     => $_SESSION['admin']['adm_id'],
            'adm_status' => 'aktif',
            'adm_otp !=' => NULL,
            'adm_otp_waktu >=' => date('Y-m-d H:i:s'),
        ];

        // admin
        $admin = new Admin();
        $get   = $admin->select('adm_otp')
                       ->where($params)
                       ->first();

        if (empty($get))
        {
            return $this->response->setStatusCode(400)->setJSON([
                'code'    => 400,
                'status'  => 'error',
                'title'   => 'Kode Tidak valid',
                'message' => 'Kode OTP Kadaluwarsa atau akun sudah dinonaktifkan'
            ]);
        }

        // verifikasi
        if (!password_verify($data['otp'], $get['adm_otp']))
        {
            return $this->response->setStatusCode(400)->setJSON([
                'code'    => 400,
                'status'  => 'error',
                'title'   => 'Kode Tidak valid',
                'message' => 'Kode OTP yang diinput tidak sesuai'
            ]);
        }

        // parse password
        $encrypter = Services::encrypter();
        $password  = hex2bin($_SESSION['new_password']);
        $password  = $encrypter->decrypt($password);

        // update
        $set = [
            'adm_otp'       => NULL,
            'adm_otp_waktu' => NULL,
            'adm_password'  => password_hash($password, PASSWORD_DEFAULT)
        ];

        // update
        $admin->update($_SESSION['admin']['adm_id'], $set);

        // unset session
        unset($_SESSION['new_password']);

        // return ok
        return $this->response->setStatusCode(200)->setJSON([
            'code'    => 200,
            'status'  => 'success',
            'title'   => 'Penyimpanan Berhasil',
            'message' => 'Password akun anda berhasil dirubah'
        ]);
    }

    //====================================================================================================
}