<?= $this->extend('main/layout') ?>

<?= $this->section('judul') ?>
Utility System
<?= $this->endSection() ?>

<?= $this->section('subjudul') ?>
Backup Database
<?= $this->endSection() ?>

<?= $this->section('isi') ?>
<?= session()->getFlashdata('pesan') ?>
<button type="button" class="btn btn-primary" onclick="location.href=('/utility/doBackup')">
    CLick To Backup Database
</button>
<?php if (!empty($backups) && is_array($backups)) : ?>
    <hr />
    <table class="table table-sm table-striped mt-2">
        <thead>
            <tr>
                <th>Nama File</th>
                <th>Waktu</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($backups as $b) : ?>
                <tr>
                    <td><?= esc($b['name']) ?></td>
                    <td><?= esc($b['time']) ?></td>
                    <td>
                        <a href="<?= site_url('utility/download/' . rawurlencode($b['name'])) ?>" class="btn btn-sm btn-success">Download</a>
                        <form method="post" action="<?= site_url('utility/delete') ?>" style="display:inline" onsubmit="return confirm('Hapus file backup <?= esc($b['name']) ?>?')">
                            <?= csrf_field() ?>
                            <input type="hidden" name="filename" value="<?= esc($b['name']) ?>">
                            <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else : ?>
    <p class="mt-2">Tidak ada file backup.</p>
<?php endif; ?>
<?= $this->endSection() ?>