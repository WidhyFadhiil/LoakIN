<?php

namespace App\Libraries;

use CodeIgniter\HTTP\Files\UploadedFile;

/**
 * Upload + kompres gambar. Foto listing & avatar disimpan di public/uploads (boleh diakses publik),
 * dokumen verifikasi disimpan di writable/uploads/verification (privat, hanya admin yang bisa lihat).
 */
class ImageUploader
{
    public const IMAGE_MIMES = ['image/jpeg', 'image/png', 'image/webp'];
    public const DOC_MIMES   = ['image/jpeg', 'image/png', 'application/pdf'];

    public function publicDir(string $folder): string
    {
        $dir = FCPATH . 'uploads/' . $folder . '/';
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        return $dir;
    }

    public function privateDir(string $folder): string
    {
        $dir = WRITEPATH . 'uploads/' . $folder . '/';
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        return $dir;
    }

    /**
     * Simpan beberapa foto listing. Mengembalikan ['saved' => [...nama file], 'errors' => [...pesan]].
     *
     * @param UploadedFile[] $files
     */
    public function saveListingImages(array $files, int $alreadyHave = 0): array
    {
        $cfg    = config('Loakin');
        $saved  = [];
        $errors = [];
        $room   = max(0, $cfg->maxImages - $alreadyHave);

        foreach ($files as $file) {
            if (! $file instanceof UploadedFile || $file->getError() === UPLOAD_ERR_NO_FILE) {
                continue;
            }
            if (count($saved) >= $room) {
                $errors[] = "Maksimal {$cfg->maxImages} foto per barang.";
                break;
            }
            $name = $this->saveImage($file, 'listings', 1200, $err);
            if ($name === null) {
                $errors[] = $err;
                continue;
            }
            $saved[] = $name;
        }

        return ['saved' => $saved, 'errors' => $errors];
    }

    /** Foto profil (kotak 400px). */
    public function saveAvatar(UploadedFile $file, ?string &$error = null): ?string
    {
        return $this->saveImage($file, 'avatars', 400, $error);
    }

    private function saveImage(UploadedFile $file, string $folder, int $maxSide, ?string &$error = null): ?string
    {
        $cfg = config('Loakin');

        if (! $file->isValid() || $file->hasMoved()) {
            $error = 'Foto "' . $file->getClientName() . '" gagal diunggah.';

            return null;
        }
        if ($file->getSizeByUnit('kb') > $cfg->maxImageKb) {
            $error = 'Foto "' . $file->getClientName() . '" lebih dari ' . ($cfg->maxImageKb / 1024) . ' MB.';

            return null;
        }
        if (! in_array($file->getMimeType(), self::IMAGE_MIMES, true)) {
            $error = 'Foto "' . $file->getClientName() . '" harus berformat JPG, PNG, atau WEBP.';

            return null;
        }

        $dir  = $this->publicDir($folder);
        $name = $file->getRandomName();

        try {
            service('image')
                ->withFile($file->getRealPath())
                ->resize($maxSide, $maxSide, true, 'width')
                ->save($dir . $name, 82);
        } catch (\Throwable $e) {
            // GD tidak tersedia / gagal kompres: simpan apa adanya
            $file->move($dir, $name);
        }

        return $name;
    }

    /** Dokumen verifikasi pendaur ulang (privat). */
    public function saveDocument(UploadedFile $file, ?string &$error = null): ?string
    {
        if (! $file->isValid() || $file->hasMoved()) {
            $error = 'Dokumen gagal diunggah.';

            return null;
        }
        if ($file->getSizeByUnit('mb') > 3) {
            $error = 'Dokumen maksimal 3 MB.';

            return null;
        }
        if (! in_array($file->getMimeType(), self::DOC_MIMES, true)) {
            $error = 'Dokumen harus berformat JPG, PNG, atau PDF.';

            return null;
        }
        $name = $file->getRandomName();
        $file->move($this->privateDir('verification'), $name);

        return $name;
    }

    public function deletePublic(string $folder, ?string $filename): void
    {
        if ($filename && preg_match('/^[\w.\-]+$/', $filename)) {
            $path = FCPATH . 'uploads/' . $folder . '/' . $filename;
            if (is_file($path)) {
                @unlink($path);
            }
        }
    }

    public function deletePrivate(string $folder, ?string $filename): void
    {
        if ($filename && preg_match('/^[\w.\-]+$/', $filename)) {
            $path = WRITEPATH . 'uploads/' . $folder . '/' . $filename;
            if (is_file($path)) {
                @unlink($path);
            }
        }
    }
}
