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
use App\Models\AdminMenu;
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

    public function getAllowedMenu()
    {
        if (empty($_SESSION['admin']['rol_list_menu']))
        {
            return $this->logout();
        }

        // get list menu
        $list = $_SESSION['admin']['rol_list_menu'];

        // menu
        $menu = new AdminMenu();
        $get  = $menu->select('men_id, men_nama, men_jenis, men_parent, men_link, men_icon')
                     ->where('men_status', 'aktif')
                     ->orderBy('men_urutan', 'ASC');


        $get  = is_array($list) ? $get->find($list) : $get->findAll();

        // parse data
        if (empty($get))
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
        foreach ($get as $n => $item):

            if ($item['men_jenis'] == 'parent')
            {
                $item['men_child'] = [];

                // push and delete parent
                array_push($listMenu, $item);
                unset($get[$n]);
            }

        endforeach;

        if (!empty($listMenu))
        {
            $parent = array_column($listMenu, 'men_id');

            // parse child
            foreach ($get as $n => $item):

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

    //======================================================================================================
}