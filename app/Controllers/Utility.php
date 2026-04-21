<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\Modeluser;
use Ifsnop\Mysqldump\Mysqldump;

class Utility extends BaseController
{
    public function index()
    {
        $targetDir = WRITEPATH . 'backup' . DIRECTORY_SEPARATOR;
        $backups = [];

        if (is_dir($targetDir)) {
            $files = glob($targetDir . '*.sql');
            if ($files !== false) {
                usort($files, function ($a, $b) {
                    return filemtime($b) <=> filemtime($a);
                });

                foreach ($files as $f) {
                    $backups[] = [
                        'name'  => basename($f),
                        'path'  => $f,
                        'mtime' => filemtime($f),
                        'time'  => date('Y-m-d H:i:s', filemtime($f)),
                    ];
                }
            }
        }

        return view('utility/index', ['backups' => $backups]);
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
            $targetDir = WRITEPATH . 'backup' . DIRECTORY_SEPARATOR;
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

    public function download($filename = null)
    {
        if ($filename === null) {
            session()->setFlashdata('pesan', 'Nama file tidak diberikan');
            return redirect()->to('/utility/index');
        }

        $basename = basename($filename);
        if ($basename !== $filename) {
            session()->setFlashdata('pesan', 'Nama file tidak valid');
            return redirect()->to('/utility/index');
        }

        $targetDir = realpath(WRITEPATH . 'backup');
        if ($targetDir === false) {
            session()->setFlashdata('pesan', 'Folder backup tidak tersedia');
            return redirect()->to('/utility/index');
        }

        // normalize prefix so prefix-check is reliable on Windows and Unix
        $targetDir = rtrim($targetDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

        $fullPath = $targetDir . $basename;
        $realFull = realpath($fullPath);

        if ($realFull === false || strpos($realFull, $targetDir) !== 0) {
            session()->setFlashdata('pesan', 'File tidak valid atau berada di luar folder backup');
            return redirect()->to('/utility/index');
        }

        $ext = strtolower(pathinfo($realFull, PATHINFO_EXTENSION));
        if ($ext !== 'sql') {
            session()->setFlashdata('pesan', 'File bukan file backup .sql');
            return redirect()->to('/utility/index');
        }

        return $this->response->download($realFull, null);
    }

    public function delete()
    {
        // Guard: accept POST regardless of case and provide explicit feedback for non-POST
        if (strtolower($this->request->getMethod() ?? '') !== 'post') {
            session()->setFlashdata('pesan', 'Metode request tidak diizinkan (harus POST)');
            return redirect()->to('/utility/index');
        }

        // Diagnostic exception removed; keep runtime logging for tracing.

        if (function_exists('log_message')) {
            log_message('debug', 'Utility::delete entered; method=' . $this->request->getMethod());
        }

        $filename = $this->request->getPost('filename');
        if (function_exists('log_message')) {
            log_message('debug', 'Utility::delete POST filename=' . ($filename ?? 'NULL'));
        }

        $basename = basename($filename);
        if ($basename !== $filename) {
            session()->setFlashdata('pesan', 'Nama file tidak valid');
            return redirect()->to('/utility/index');
        }

        $targetDir = realpath(WRITEPATH . 'backup');
        if ($targetDir === false) {
            session()->setFlashdata('pesan', 'Folder backup tidak tersedia');
            return redirect()->to('/utility/index');
        }

        // normalize prefix so prefix-check is reliable on Windows and Unix
        $targetDir = rtrim($targetDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

        $fullPath = $targetDir . $basename;
        if (function_exists('log_message')) {
            log_message('debug', 'Utility::delete fullPath=' . $fullPath);
        }
        $realFull = realpath($fullPath);
        if (function_exists('log_message')) {
            log_message('debug', 'Utility::delete realFull=' . ($realFull === false ? 'false' : $realFull));
        }

        if ($realFull === false || strpos($realFull, $targetDir) !== 0) {
            session()->setFlashdata('pesan', 'File tidak valid atau berada di luar folder backup');
            return redirect()->to('/utility/index');
        }

        $ext = strtolower(pathinfo($realFull, PATHINFO_EXTENSION));
        if ($ext !== 'sql') {
            session()->setFlashdata('pesan', 'File bukan file backup .sql');
            return redirect()->to('/utility/index');
        }

        // Check writable and attempt unlink with clearer feedback and logging
        if (function_exists('log_message')) {
            log_message('debug', 'Utility::delete is_writable=' . (is_writable($realFull) ? 'yes' : 'no'));
        }

        if (!is_writable($realFull)) {
            session()->setFlashdata('pesan', 'Gagal menghapus: file atau folder tidak writable');
            return redirect()->to('/utility/index');
        }

        if (function_exists('log_message')) {
            log_message('debug', 'Utility::delete attempting unlink: ' . $realFull);
        }

        $deleted = unlink($realFull);
        if ($deleted) {
            if (function_exists('log_message')) {
                log_message('info', 'Utility::delete succeeded unlink ' . $realFull);
            }
            session()->setFlashdata('pesan', 'File backup dihapus');
        } else {
            $err = error_get_last();
            if (function_exists('log_message')) {
                log_message('error', 'Utility::delete failed to unlink ' . $realFull . ' - ' . ($err['message'] ?? 'unknown'));
            }
            session()->setFlashdata('pesan', 'Gagal menghapus file backup');
        }

        return redirect()->to('/utility/index');
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
