<?php namespace App\Controllers\LIT;

/**
 * API Admin Session
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

class SessionController extends BaseController
{
    public function logout()
    {
        session_destroy();

        // return
        return $this->response->setStatusCode(200)->setJSON([
            'code'    => 200,
            'status'  => 'success',
            'title'   => 'Berhasil Melakukan Logout',
            'message' => 'Anda sudah keluar dari panel admin'
        ]);
    }

    //======================================================================================================

    public function getAdminInfo()
    {
        $admin = new Admin();

        // return
        return $this->response->setStatusCode(200)->setJSON([
            'code'    => 200,
            'status'  => 'success',
            'title'   => 'Berhasil Menarik Data',
            'message' => 'Anda berhasil menarik data akun anda',
            'data'    => $admin->info()
        ]);
    }
    
    //======================================================================================================
}