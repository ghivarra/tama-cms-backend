<?php namespace App\Controllers;

/**
 * API Autentikasi
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
use Config\Services;

class AutentikasiController extends BaseController
{
	public function try()
	{
		$data = [
			'email'	   => $this->request->getPost('email'),
			'password' => $this->request->getPost('password')
		];

		// validasi
		$validation = Services::validation();
		$validation->setRules([
			'email'    => ['label' => 'Username', 'rules' => 'required'],
			'password' => ['label' => 'Username', 'rules' => 'required']
		]);

		if (!$validation->run($data))
		{
			return $this->response->setStatusCode(401)->setJSON([
				'code'    => 401,
				'status'  => 'warning',
				'title'	  => 'Autentikasi Gagal',
				'message' => 'Anda harus mengisi form username dan password untuk melakukan autentikasi'
			]);
		}

		// use orm superadmin
		$admin = new Admin();
		$auth  = $admin->auth($data['email'], $data['password'], ['adm_status' => 'aktif']);

		if (!$auth)
		{
			return $this->response->setStatusCode(401)->setJSON([
				'code'    => 401,
				'status'  => 'error',
				'title'	  => 'Autentikasi Gagal',
				'message' => 'Username dan password tidak cocok'
			]);
		}

		// return success
		return $this->response->setStatusCode(200)->setJSON([
			'code' 	  => 200,
			'status'  => 'success',
			'title'	  => 'Autentikasi Berhasil',
			'message' => 'Anda akan segera dialihkan ke halaman admin'
		]);
	}

	//===========================================================================================

	public function check()
	{
		$admin = new Admin();

		if ($admin->check())
		{
			return $this->response->setStatusCode(406)->setJSON([
				'code' 	   => 406,
				'status'   => 'OK',
				'loggedIn' => TRUE
			]);
		}

		return $this->response->setStatusCode(200)->setJSON([
			'code' 	   => 200,
			'status'   => 'OK',
			'loggedIn' => FALSE
		]);
	}

	//===========================================================================================

	public function forgotPassword()
	{
		helper('text');

		$emailAkun = $this->request->getPost('email');
		$admin 	   = new Admin();

		// check
		$get = $admin->select('adm_nama, adm_id')
					 ->where('adm_status', 'aktif')
					 ->where('adm_email', $emailAkun)
					 ->first();

		if (empty($get))
		{
			return $this->response->setStatusCode(401)->setJSON([
				'code'    => 401,
				'status'  => 'error',
				'title'	  => 'Akun Tidak Ditemukan',
				'message' => 'Periksa kembali penulisan email dan pastikan email yang diinput benar-benar terdaftar'
			]);
		}

		// load library
		$session = Services::session();
		$email   = Services::email();

		// update
		$token  = random_string('numeric', 10);
		$update = [
			'adm_token_lupa'  => password_hash($token, PASSWORD_DEFAULT),
			'adm_token_waktu' => date('Y-m-d H:i:s', strtotime('+6 minutes'))
		];

		// update
		$admin->update($get['adm_id'], $update);

		// simpan id akun ke session
		$_SESSION['forgotpass'] = $get['adm_id'];
		$session->markAsTempdata('forgotpass', 360);

		// data
		$data = [
			'nama'  => $get['adm_nama'],
			'view'  => 'email/v_email_forget',
			'token' => $token
		];

		// escaping data
		$data = esc($data);
		$view = view('email/v_email_template', $data);

		// kirim email
		$email->setTo($emailAkun);
		$email->setSubject('Kode OTP Perubahan Password');
		$email->setMessage($view);
		$email->send();

		// return ok
		return $this->response->setStatusCode(200)->setJSON([
			'code'    => 200,
			'status'  => 'success',
			'title'	  => 'Kode OTP Berhasil Dikirim',
			'message' => 'Periksa email anda, kode OTP hanya berlaku selama 5 menit'
		]);
	}

	//===========================================================================================
}