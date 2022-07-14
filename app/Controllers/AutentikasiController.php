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
		$session = Services::session();
		$admin   = new Admin();

		if ($admin->check())
		{
			return $this->response->setStatusCode(200)->setJSON([
				'code' 	   => 200,
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
}