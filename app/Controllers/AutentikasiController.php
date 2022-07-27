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
		$token  = random_string('alnum', 120);
		$update = [
			'adm_token_lupa'  => $token,
			'adm_token_waktu' => date('Y-m-d H:i:s', strtotime('+6 minutes'))
		];

		// update
		$admin->update($get['adm_id'], $update);

		// data
		$data = [
			'nama'  => $get['adm_nama'],
			'view'  => 'email/v_email_forget',
			'token' => $_ENV['APP_BASE_URL'] . "{$_ENV['APP_LOGIN_PAGE']}/ubah-password?token={$token}"
		];

		// escaping data
		$data = esc($data);
		$view = view('email/v_email_template', $data);

		// kirim email
		$email->setTo($emailAkun);
		$email->setSubject('Link/URL Perubahan Password');
		$email->setMessage($view);
		$email->send();

		// return ok
		return $this->response->setStatusCode(200)->setJSON([
			'code'    => 200,
			'status'  => 'success',
			'title'	  => 'URL Berhasil Dikirim',
			'message' => 'Periksa email anda, Link/URL perubahan password hanya berlaku selama 5 menit'
		]);
	}

	//===========================================================================================

	public function changePasswordData()
	{
		$token = $this->request->getPost('token');

		if (!isset($token) OR empty($token))
		{
			return $this->response->setStatusCode(403)->setJSON([
				'code'    => 403,
				'status'  => 'error',
				'title'	  => 'Token Tidak Valid',
				'message' => 'Pastikan anda mengakses link/url perubahan yang valid'
			]);
		}

		// set parameter
		$params = [
			'adm_token_lupa' 	=> $token,
			'adm_status'		=> 'aktif',
			'adm_token_waktu>=' => date('Y-m-d H:i:s'),
		];

		// get data
		$admin = new Admin();
		$get   = $admin->select('adm_nama, adm_email')
					   ->where($params)
					   ->first();

		if (empty($get))
		{
			return $this->response->setStatusCode(403)->setJSON([
				'code'    => 403,
				'status'  => 'error',
				'title'	  => 'Sesi Perubahan Password Sudah Berakhir',
				'message' => 'Lakukan konfirmasi email akun anda terlebih dahulu'
			]);
		}

		// return
		return $this->response->setStatusCode(200)->setJSON([
			'code'    => 200,
			'status'  => 'success',
			'title'	  => 'Data Admin Berhasil Ditarik',
			'message' => 'Silahkan isi kolom password dan konfirmasi password',
			'data'	  => $get
		]);
	}
	
	//===========================================================================================

	public function changePasswordPost()
	{
		$data = [
			'token' 	 => $this->request->getPost('token'),
			'password'	 => $this->request->getPost('password'),
			'konfirmasi' => $this->request->getPost('konfirmasi')
		];

		// validasi
		$validation = Services::validation();
		$validation->setRules([
			'token' 	 => ['label' => 'Token', 'rules' => 'required'],
			'password'	 => ['label' => 'Password', 'rules' => 'required|matches[konfirmasi]|min_length[10]'],
			'konfirmasi' => ['label' => 'Konfirmasi Password', 'rules' => 'required'],
		]);

		// set
		if (!$validation->run($data))
		{
			$errors = $validation->getErrors();

			if (!preg_match("#[0-9]+#", $data['password']))
			{
				$errors['password'] = 'Password harus menggunakan huruf dan angka';
			}

			return $this->response->setStatusCode(400)->setJSON([
				'code'    => 400,
				'status'  => 'error',
				'title'	  => 'Gagal Merubah Password',
				'message' => implode(', ', $errors)
			]);

		} elseif (!preg_match("#[0-9]+#", $data['password'])) {

			return $this->response->setStatusCode(400)->setJSON([
				'code'    => 400,
				'status'  => 'error',
				'title'	  => 'Gagal Merubah Password',
				'message' => 'Password harus menggunakan huruf dan angka'
			]);
		}

		// get akun
		// set parameter
		$params = [
			'adm_token_lupa' 	=> $data['token'],
			'adm_status'		=> 'aktif',
			'adm_token_waktu>=' => date('Y-m-d H:i:s'),
		];

		// get data
		$admin = new Admin();
		$get   = $admin->select('adm_id')
					   ->where($params)
					   ->first();

		if (empty($get))
		{
			return $this->response->setStatusCode(403)->setJSON([
				'code'    => 403,
				'status'  => 'error',
				'title'	  => 'Sesi Perubahan Password Sudah Berakhir',
				'message' => 'Lakukan konfirmasi email akun anda terlebih dahulu'
			]);
		}

		// parse data
		$update = [
			'adm_password'    => password_hash($data['password'], PASSWORD_DEFAULT),
			'adm_token_lupa'  => NULL,
			'adm_token_waktu' => NULL
		];

		// update
		$admin->update($get['adm_id'], $update);

		// return
		return $this->response->setStatusCode(200)->setJSON([
			'code'    => 200,
			'status'  => 'success',
			'title'	  => 'Perubahan Berhasil Disimpan',
			'message' => 'Password akun anda berhasil dirubah, silahkan coba masuk kembali'
		]);
	}

	//===========================================================================================
}