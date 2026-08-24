<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class CompressStorageImages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'storage:compress-images {--folder=bukti_laporan : The target folder in storage/app/public}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Compress existing images in storage and generate micro thumbnails for fast loading';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $folderName = $this->option('folder');
        $targetDir = storage_path("app/public/{$folderName}");

        if (!file_exists($targetDir)) {
            $this->error("Directory does not exist: {$targetDir}");
            return Command::FAILURE;
        }

        $this->info("Scanning directory: {$targetDir}");

        $files = array_diff(scandir($targetDir), ['.', '..', 'thumbs']);
        $imageFiles = [];

        foreach ($files as $file) {
            $filePath = $targetDir . '/' . $file;
            if (is_file($filePath)) {
                $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                    $imageFiles[] = $filePath;
                }
            }
        }

        $totalCount = count($imageFiles);
        if ($totalCount === 0) {
            $this->info("No images found to compress in {$folderName}.");
            return Command::SUCCESS;
        }

        $this->info("Found {$totalCount} images. Processing compression and thumbnail generation...");
        $bar = $this->output->createProgressBar($totalCount);
        $bar->start();

        $savedBytes = 0;
        $compressedCount = 0;

        $thumbDir = $targetDir . '/thumbs';
        if (!file_exists($thumbDir)) {
            mkdir($thumbDir, 0755, true);
        }

        foreach ($imageFiles as $filePath) {
            $filename = basename($filePath);
            $thumbPath = $thumbDir . '/' . $filename;
            $originalSize = filesize($filePath);

            try {
                $imageString = file_get_contents($filePath);
                $image = @imagecreatefromstring($imageString);

                if ($image) {
                    // Auto-rotate if EXIF orientation exists
                    $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
                    if (function_exists('exif_read_data') && in_array($ext, ['jpg', 'jpeg'])) {
                        try {
                            $exif = @exif_read_data($filePath);
                            if (!empty($exif['Orientation'])) {
                                switch ($exif['Orientation']) {
                                    case 3:
                                        $image = imagerotate($image, 180, 0);
                                        break;
                                    case 6:
                                        $image = imagerotate($image, -90, 0);
                                        break;
                                    case 8:
                                        $image = imagerotate($image, 90, 0);
                                        break;
                                }
                            }
                        } catch (\Throwable $e) {
                            // Ignore EXIF errors
                        }
                    }

                    $width = imagesx($image);
                    $height = imagesy($image);

                    // 1. Generate micro thumbnail (max 150px, 60% quality)
                    $maxThumbDim = 150;
                    if ($width > $maxThumbDim || $height > $maxThumbDim) {
                        if ($width > $height) {
                            $tWidth = $maxThumbDim;
                            $tHeight = (int) ($height * ($maxThumbDim / $width));
                        } else {
                            $tHeight = $maxThumbDim;
                            $tWidth = (int) ($width * ($maxThumbDim / $height));
                        }
                    } else {
                        $tWidth = $width;
                        $tHeight = $height;
                    }

                    $thumbImg = imagecreatetruecolor($tWidth, $tHeight);
                    if (in_array($ext, ['png', 'webp'])) {
                        imagealphablending($thumbImg, false);
                        imagesavealpha($thumbImg, true);
                    }
                    imagecopyresampled($thumbImg, $image, 0, 0, 0, 0, $tWidth, $tHeight, $width, $height);
                    imagejpeg($thumbImg, $thumbPath, 60);
                    imagedestroy($thumbImg);

                    // 2. Compress main image if file size > 250KB or width > 1200
                    $maxMainDim = 1200;
                    if ($originalSize > 250 * 1024 || $width > $maxMainDim || $height > $maxMainDim) {
                        if ($width > $maxMainDim || $height > $maxMainDim) {
                            if ($width > $height) {
                                $nWidth = $maxMainDim;
                                $nHeight = (int) ($height * ($maxMainDim / $width));
                            } else {
                                $nHeight = $maxMainDim;
                                $nWidth = (int) ($width * ($maxMainDim / $height));
                            }
                            $mainImg = imagecreatetruecolor($nWidth, $nHeight);
                            if (in_array($ext, ['png', 'webp'])) {
                                imagealphablending($mainImg, false);
                                imagesavealpha($mainImg, true);
                            }
                            imagecopyresampled($mainImg, $image, 0, 0, 0, 0, $nWidth, $nHeight, $width, $height);
                            imagejpeg($mainImg, $filePath, 75);
                            imagedestroy($mainImg);
                        } else {
                            imagejpeg($image, $filePath, 75);
                        }

                        clearstatcache();
                        $newSize = filesize($filePath);
                        if ($newSize < $originalSize) {
                            $savedBytes += ($originalSize - $newSize);
                            $compressedCount++;
                        }
                    }

                    imagedestroy($image);
                }
            } catch (\Throwable $e) {
                // Ignore single file error and continue
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();

        $savedMB = round($savedBytes / (1024 * 1024), 2);
        $this->info("SUCCESS: Compressed {$compressedCount} images. Total storage saved: {$savedMB} MB.");

        return Command::SUCCESS;
    }
}
