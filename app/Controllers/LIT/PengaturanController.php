<?php namespace App\Controllers\LIT;

/**
 * API Admin Modul
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
use App\Models\Pengaturan;
use App\Controllers\BaseController;
use Config\Services;
use PHP_ICO;

class PengaturanController extends BaseController
{
    public function update()
    {
        helper('text');
        
        $data = [
            'pgn_nama'      => $this->request->getPost('nama'),
            'pgn_tagline'   => $this->request->getPost('tagline'),
            'pgn_deskripsi' => $this->request->getPost('deskripsi'),

            'pgn_logo' => $this->request->getFile('logo'),
            'pgn_icon' => $this->request->getFile('icon'),
        ];

        // set rules
        $rules = [
            'pgn_nama'      => ['label' => 'Nama Website', 'rules' => 'required|max_length[64]'],
            'pgn_tagline'   => ['label' => 'Tagline', 'rules' => 'required|max_length[64]'],
            'pgn_deskripsi' => ['label' => 'Deskripsi', 'rules' => 'required|max_length[1000]']
        ];

        if (!empty($data['pgn_logo']) && $data['pgn_logo']->isValid())
        {
            $logoChange = TRUE;
            $rules['pgn_logo'] = ['label' => "Logo", 'rules' => 'uploaded[logo]|mime_in[logo,image/png]|max_size[logo,8096]'];
        }

        if (!empty($data['pgn_icon']) && $data['pgn_icon']->isValid())
        {
            $iconChange = TRUE;
            $rules['pgn_icon'] = ['label' => "Icon", 'rules' => 'uploaded[icon]|mime_in[icon,image/png]|max_size[icon,8096]'];
        }

        // validasi
        $validasi = Services::validation();
        $validasi->setRules($rules);

        if (!$validasi->run($data))
        {
            return $this->response->setStatusCode(400)->setJSON([
                'code'    => 400,
                'status'  => 'error',
                'title'   => 'Gagal Merubah Password',
                'message' => implode(', ', $validasi->getErrors())
            ]);
        }

        // get original data
        $pengaturan = new Pengaturan();
        $original   = $pengaturan->find(1);

        // set data
        $data['pgn_slug'] = ($original['pgn_nama'] !== $data['pgn_nama']) ? url_title($data['pgn_nama'], '-', TRUE) . '-' . random_string('alnum', 10) : $original['pgn_slug'];

        // upload logo
        if (isset($logoChange))
        {
            // create folder if not exist
            $folder = ROOTPATH . "{$_ENV['ASSET_PATH']}/dist/informasi";

            if (!file_exists($folder))
            {
                mkdir($folder, 0755, TRUE);
            }

            $old = glob($folder . '/logo-*');

            if (!empty($old))
            {
                foreach ($old as $item):

                    if (is_writable($item))
                    {
                        unlink($item);
                    }

                endforeach;
            }

            // upload new logo
            $nama = 'logo-' . $data['pgn_slug'] . '.' . $data['pgn_logo']->getClientExtension();
            $data['pgn_logo']->move($folder, $nama);
        }

        // upload icon
        if (isset($iconChange))
        {
            // create folder if not exist
            $folder = ROOTPATH . "{$_ENV['ASSET_PATH']}/dist/informasi";

            if (!file_exists($folder))
            {
                mkdir($folder, 0755, TRUE);
            }

            $old = glob($folder . '/icon-*');

            if (!empty($old))
            {
                foreach ($old as $item):

                    if (is_writable($item))
                    {
                        unlink($item);
                    }

                endforeach;
            }

            // upload new logo
            $nama = 'icon-' . $data['pgn_slug'] . '.' . $data['pgn_icon']->getClientExtension();
            $data['pgn_icon']->move($folder, $nama);

            // delete tmp
            $path    = WRITEPATH . "uploads/{$nama}";
            $favicon = ROOTPATH . "{$_ENV['FRONTEND_PATH']}/favicon.ico";

            // delete favicon
            if (file_exists($favicon) && is_writable($favicon))
            {
                unlink($favicon);
            }

            // get image
            $image = Services::image();
            $image->withFile("{$folder}/{$nama}")
                  ->resize(32, 32)
                  ->save($path);

            $ico_generator = new PHP_ICO($path);
            $ico_generator->save_ico($favicon);

            // delete
            unlink($path);
        }

        // change logo name
        if ($original['pgn_nama'] !== $data['pgn_nama'])
        {
            $old = $original['pgn_slug'];
            $new = $data['pgn_slug'];

            $logo_folder = glob(ROOTPATH . "{$_ENV['ASSET_PATH']}/dist/informasi/logo-{$old}*");

            if (!empty($logo_folder))
            {
                foreach ($logo_folder as $item):

                    $new_name = str_replace($old, $new, $item);
                    rename($item, $new_name);

                endforeach;
            }

            $icon_folder = glob(ROOTPATH . "{$_ENV['ASSET_PATH']}/dist/informasi/icon-{$old}*");

            if (!empty($icon_folder))
            {
                foreach ($icon_folder as $item):

                    $new_name = str_replace($old, $new, $item);
                    rename($item, $new_name);

                endforeach;
            }
        }

        // preview
        if (($data['pgn_nama'] != $original['pgn_nama']) OR ($data['pgn_tagline'] != $original['pgn_tagline']) OR ($data['pgn_deskripsi'] != $original['pgn_deskripsi']))
        {
            $version = TRUE;
        }

        // unset data
        unset($data['pgn_logo']);
        unset($data['pgn_icon']);

        // update now
        $update = $pengaturan->where('pgn_id', 1)->set($data);

        // if
        if (isset($version))
        {
            $update->increment('pgn_versi_web');
        }

        if (isset($logoChange))
        {
            $update->increment('pgn_versi_logo');   
        }

        if (isset($iconChange))
        {
            $update->increment('pgn_versi_icon');   
        }

        // update
        $update->update();

        // return
        return $this->response->setStatusCode(200)->setJSON([
            'code'    => 200,
            'status'  => 'success',
            'title'   => 'Data Disimpan',
            'message' => 'Informasi website sudah diperbaharui'
        ]);
    }

    //====================================================================================================
}