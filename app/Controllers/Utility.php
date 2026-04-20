<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\Modeluser;
use Ifsnop\Mysqldump\Mysqldump;

class Utility extends BaseController
{
    public function index()
    {
        return view('utility/index');
    }

    public function doBackup()
    {
        try {
            $tglSekarang = date('d-m-Y');
            // Ambil konfigurasi database aktif dari runtime aplikasi
            $dbConfig = new \Config\Database();
            $group = $dbConfig->defaultGroup;
            $conf = $dbConfig->{$group};

            $host = isset($conf['hostname']) ? $conf['hostname'] : 'localhost';
            $database = isset($conf['database']) ? $conf['database'] : '';
            $username = isset($conf['username']) ? $conf['username'] : '';
            $password = isset($conf['password']) ? $conf['password'] : '';
            $port = isset($conf['port']) ? $conf['port'] : 3306;

            // Cek/siapkan direktori tujuan backup minimal
            $targetDir = 'database/backup/';
            if (!is_dir($targetDir)) {
                if (!mkdir($targetDir, 0755, true)) {
                    throw new \Exception('Direktori tujuan backup tidak ada dan tidak bisa dibuat: ' . $targetDir);
                }
            }

            if (!is_writable($targetDir)) {
                throw new \Exception('Direktori tujuan backup tidak writable: ' . $targetDir);
            }

            $targetFile = $targetDir . 'dbgudang-' . $tglSekarang . '.sql';

            $dsn = 'mysql:host=' . $host . ';dbname=' . $database . ';port=' . $port;

            $dump = new Mysqldump($dsn, $username, $password);
            $dump->start($targetFile);

            $pesan = 'Backup Database Berhasil';
            session()->setFlashdata('pesan', $pesan);
            return redirect()->to('/utility/index');
        } catch (\Exception $e) {
            $pesan = "mysqldump-php error: " . $e->getMessage();
            session()->setFlashdata('pesan', $pesan);
            return redirect()->to('/utility/index');
        }
    }

    public function gantipassword()
    {
        return view('utility/formgantipassword');
    }

    public function updatepassword()
    {
        if ($this->request->isAjax()) {
            $iduser = session()->get('userid');
            $passlama = $this->request->getPost('passlama');
            $passbaru = $this->request->getPost('passbaru');
            $confirmpass = $this->request->getPost('confirmpass');

            $validation = \Config\Services::validation();

            $valid = $this->validate([
                'passlama' => [
                    'label' => 'Password Lama',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '{field} tidak boleh kosong',
                    ]
                ],
                'passbaru' => [
                    'label' => 'Password Baru',
                    'rules' => 'required',
                    'errors' => [
                        'required' => '{field} tidak boleh kosong',
                    ]
                ],
                'confirmpass' => [
                    'label' => 'Confirm Password Baru',
                    'rules' => 'required|matches[passbaru]',
                    'errors' => [
                        'required' => '{field} tidak boleh kosong',
                        'matches' => '{field} tidak sama dengan password baru',
                    ]
                ],
            ]);

            if (!$valid) {
                $json = [
                    'error' => [
                        'errorPassLama' => $validation->getError('passlama'),
                        'errorPassBaru' => $validation->getError('passbaru'),
                        'errorConfirmPass' => $validation->getError('confirmpass'),
                    ]
                ];
            } else {
                $modelUser = new Modeluser();
                $rowData = $modelUser->find($iduser);

                $passUser = '';
                if (is_array($rowData) && array_key_exists('userpassword', $rowData)) {
                    $passUser = (string) $rowData['userpassword'];
                } elseif (is_object($rowData) && property_exists($rowData, 'userpassword')) {
                    $passUser = (string) $rowData->userpassword;
                }

                if (password_verify((string) $passlama, $passUser)) {
                    $hash = password_hash($passbaru, PASSWORD_DEFAULT);
                    $modelUser->update($iduser, [
                        'userpassword' => $hash
                    ]);

                    $json = [
                        'sukses' => 'Password berhasil diubah'
                    ];
                } else {
                    $json = [
                        'error' => [
                            'errorPassLama' => 'Password Lama tidak sesuai',
                        ]
                    ];
                }
            }

            echo json_encode($json);
        }
    }
}
