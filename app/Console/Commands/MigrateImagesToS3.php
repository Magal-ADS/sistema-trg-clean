<?php

namespace App\Console\Commands;

use App\Models\Banner;
use App\Models\Category;
use App\Models\Product;
use App\Models\Service;
use App\Models\SubCategory;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MigrateImagesToS3 extends Command
{
    protected $signature = 'app:migrate-images-to-s3
        {--disk=s3 : Filesystem disk used as destination}
        {--prefix=imported-images : Destination folder inside the bucket}
        {--limit= : Maximum number of records to inspect}
        {--timeout=30 : Download timeout in seconds}
        {--dry-run : Show what would be migrated without downloading, uploading or saving}
        {--force : Reprocess URLs even when they already look like the destination bucket}';

    protected $description = 'Downloads remote image URLs from the database, uploads them to S3 and updates the records.';

    /** @var array<string, string|null> */
    private array $urlCache = [];

    private int $recordsSeen = 0;

    private int $recordsUpdated = 0;

    private int $imagesUploaded = 0;

    private int $imagesWouldUpload = 0;

    private int $imagesSkipped = 0;

    private int $imagesFailed = 0;

    public function handle(): int
    {
        $disk = (string) $this->option('disk');
        $prefix = trim((string) $this->option('prefix'), '/');
        $limit = $this->option('limit') !== null ? (int) $this->option('limit') : null;

        if ($prefix === '') {
            $this->error('The --prefix option cannot be empty.');

            return self::FAILURE;
        }

        config([
            "filesystems.disks.{$disk}.throw" => true,
            "filesystems.disks.{$disk}.report" => false,
        ]);

        foreach ($this->targets() as $target) {
            if ($limit !== null && $this->recordsSeen >= $limit) {
                break;
            }

            $this->processTarget($target, $disk, $prefix, $limit);
        }

        $this->newLine();
        $this->info("Records inspected: {$this->recordsSeen}");
        $this->info($this->option('dry-run') ? "Records that would be updated: {$this->recordsUpdated}" : "Records updated: {$this->recordsUpdated}");
        $this->info($this->option('dry-run') ? "Images that would be uploaded: {$this->imagesWouldUpload}" : "Images uploaded: {$this->imagesUploaded}");
        $this->info("Images skipped: {$this->imagesSkipped}");
        $this->info("Images failed: {$this->imagesFailed}");

        if ($this->option('dry-run')) {
            $this->warn('Dry run only: no files were uploaded and no database records were changed.');
        }

        return $this->imagesFailed > 0 ? self::FAILURE : self::SUCCESS;
    }

    /**
     * @return array<int, array{class: class-string<Model>, folder: string}>
     */
    private function targets(): array
    {
        return [
            ['class' => Product::class, 'folder' => 'products'],
            ['class' => Banner::class, 'folder' => 'banners'],
            ['class' => Category::class, 'folder' => 'categories'],
            ['class' => SubCategory::class, 'folder' => 'sub-categories'],
            ['class' => Service::class, 'folder' => 'services'],
        ];
    }

    /**
     * @param array{class: class-string<Model>, folder: string} $target
     */
    private function processTarget(array $target, string $disk, string $prefix, ?int $limit): void
    {
        $class = $target['class'];
        $folder = $target['folder'];
        $label = class_basename($class);

        $this->line("Inspecting {$label}...");

        foreach ($class::query()->lazyById(100) as $model) {
            if ($limit !== null && $this->recordsSeen >= $limit) {
                return;
            }

            $this->recordsSeen++;
            $changed = false;
            $originalUrls = $this->originalUrlsFromMetadata($model);

            if (is_string($model->getAttribute('image_url'))) {
                $newUrl = $this->migrateUrl(
                    url: $model->getAttribute('image_url'),
                    disk: $disk,
                    path: $this->destinationPath($prefix, $folder, $model, $model->getAttribute('image_url')),
                );

                if ($newUrl && $newUrl !== $model->getAttribute('image_url')) {
                    $originalUrls['image_url'] ??= $model->getAttribute('image_url');
                    $model->setAttribute('image_url', $newUrl);
                    $changed = true;
                }
            }

            if ($model instanceof Product && is_array($model->images)) {
                [$images, $imagesChanged] = $this->migrateImageArray($model->images, $disk, $prefix, $folder, $model);
                if ($imagesChanged) {
                    $originalUrls['images'] ??= $model->images;
                    $model->images = $images;
                    $changed = true;
                }
            }

            if (is_array($model->getAttribute('metadata'))) {
                [$metadata, $metadataChanged] = $this->migrateMetadata(
                    $model->getAttribute('metadata'),
                    $disk,
                    $prefix,
                    $folder,
                    $model
                );

                if ($metadataChanged) {
                    $metadata['_original_image_urls'] = array_replace(
                        $metadata['_original_image_urls'] ?? [],
                        $originalUrls
                    );
                    $model->setAttribute('metadata', $metadata);
                    $changed = true;
                } elseif ($changed && $originalUrls !== []) {
                    $metadata = $model->getAttribute('metadata') ?: [];
                    $metadata['_original_image_urls'] = array_replace(
                        $metadata['_original_image_urls'] ?? [],
                        $originalUrls
                    );
                    $model->setAttribute('metadata', $metadata);
                }
            } elseif ($changed && $originalUrls !== []) {
                $model->setAttribute('metadata', ['_original_image_urls' => $originalUrls]);
            }

            if (! $changed) {
                continue;
            }

            $this->recordsUpdated++;

            if (! $this->option('dry-run')) {
                $model->save();
            }

            $this->line("  {$label} #{$model->getKey()} updated");
        }
    }

    /**
     * @return array{0: array<mixed>, 1: bool}
     */
    private function migrateImageArray(array $images, string $disk, string $prefix, string $folder, Model $model): array
    {
        $changed = false;

        foreach ($images as $key => $value) {
            if (! is_string($value)) {
                continue;
            }

            $newUrl = $this->migrateUrl(
                url: $value,
                disk: $disk,
                path: $this->destinationPath($prefix, $folder, $model, $value),
            );

            if ($newUrl && $newUrl !== $value) {
                $images[$key] = $newUrl;
                $changed = true;
            }
        }

        return [$images, $changed];
    }

    /**
     * @return array{0: array<mixed>, 1: bool}
     */
    private function migrateMetadata(array $metadata, string $disk, string $prefix, string $folder, Model $model, array $keyPath = []): array
    {
        $changed = false;

        foreach ($metadata as $key => $value) {
            if ($key === '_original_image_urls') {
                continue;
            }

            $currentPath = [...$keyPath, (string) $key];

            if (is_array($value)) {
                [$newValue, $valueChanged] = $this->migrateMetadata($value, $disk, $prefix, $folder, $model, $currentPath);
                if ($valueChanged) {
                    $metadata[$key] = $newValue;
                    $changed = true;
                }

                continue;
            }

            if (! is_string($value) || ! $this->isImageCandidate($value, $currentPath)) {
                continue;
            }

            $newUrl = $this->migrateUrl(
                url: $value,
                disk: $disk,
                path: $this->destinationPath($prefix, $folder, $model, $value),
            );

            if ($newUrl && $newUrl !== $value) {
                $metadata[$key] = $newUrl;
                $changed = true;
            }
        }

        return [$metadata, $changed];
    }

    private function migrateUrl(string $url, string $disk, string $path): ?string
    {
        $url = trim($url);

        if (! $this->isRemoteUrl($url)) {
            $this->imagesSkipped++;

            return null;
        }

        if (! $this->option('force') && $this->isAlreadyOnDestination($url)) {
            $this->imagesSkipped++;

            return null;
        }

        if (array_key_exists($url, $this->urlCache)) {
            return $this->urlCache[$url];
        }

        if ($this->option('dry-run')) {
            $newUrl = Storage::disk($disk)->url($path);

            $this->imagesWouldUpload++;
            $this->line("  would migrate: {$url}");
            $this->line("        target: {$newUrl}");
            $this->urlCache[$url] = $newUrl;

            return $newUrl;
        }

        try {
            $response = Http::timeout((int) $this->option('timeout'))
                ->retry(2, 250)
                ->withHeaders(['User-Agent' => 'TRG-Clean image migration'])
                ->get($url);

            if (! $response->successful()) {
                $this->imagesFailed++;
                $this->warn("  download failed ({$response->status()}): {$url}");
                $this->urlCache[$url] = null;

                return null;
            }

            $contentType = Str::before((string) $response->header('Content-Type'), ';');

            if (! Str::startsWith($contentType, 'image/') && ! $this->hasImageExtension($url)) {
                $this->imagesSkipped++;
                $this->warn("  skipped non-image response: {$url}");
                $this->urlCache[$url] = null;

                return null;
            }

            $uploaded = Storage::disk($disk)->put($path, $response->body(), [
                'ContentType' => $contentType ?: $this->contentTypeFromPath($path),
            ]);

            if (! $uploaded) {
                $this->imagesFailed++;
                $this->warn("  upload failed: {$path}");
                $this->urlCache[$url] = null;

                return null;
            }

            $newUrl = Storage::disk($disk)->url($path);
            $this->imagesUploaded++;
            $this->urlCache[$url] = $newUrl;

            return $newUrl;
        } catch (\Throwable $exception) {
            $this->imagesFailed++;
            $this->warn("  migration failed: {$url} ({$exception->getMessage()})");
            $this->urlCache[$url] = null;

            return null;
        }
    }

    private function destinationPath(string $prefix, string $folder, Model $model, string $url): string
    {
        $extension = $this->extensionFromUrl($url) ?: 'jpg';
        $hash = substr(sha1($url), 0, 16);

        return "{$prefix}/{$folder}/{$model->getKey()}-{$hash}.{$extension}";
    }

    private function isRemoteUrl(string $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_URL)
            && in_array(parse_url($value, PHP_URL_SCHEME), ['http', 'https'], true);
    }

    private function isAlreadyOnDestination(string $url): bool
    {
        $bucket = (string) config('filesystems.disks.s3.bucket');
        $configuredUrl = (string) config('filesystems.disks.s3.url');

        if ($bucket !== '' && Str::contains($url, $bucket)) {
            return true;
        }

        return $configuredUrl !== '' && Str::startsWith($url, rtrim($configuredUrl, '/').'/');
    }

    private function isImageCandidate(string $value, array $keyPath): bool
    {
        if (! $this->isRemoteUrl($value)) {
            return false;
        }

        return $this->hasImageExtension($value) || $this->keyPathLooksLikeImage($keyPath);
    }

    private function keyPathLooksLikeImage(array $keyPath): bool
    {
        $path = Str::lower(implode(' ', $keyPath));

        return Str::contains($path, ['imagem', 'image', 'foto', 'banner', 'logo', 'icone', 'icon']);
    }

    private function hasImageExtension(string $url): bool
    {
        return $this->extensionFromUrl($url) !== null;
    }

    private function extensionFromUrl(string $url): ?string
    {
        $path = (string) parse_url($url, PHP_URL_PATH);
        $extension = Str::lower(pathinfo($path, PATHINFO_EXTENSION));

        return in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'avif', 'svg'], true)
            ? $extension
            : null;
    }

    private function contentTypeFromPath(string $path): string
    {
        return match (Str::lower(pathinfo($path, PATHINFO_EXTENSION))) {
            'png' => 'image/png',
            'webp' => 'image/webp',
            'gif' => 'image/gif',
            'avif' => 'image/avif',
            'svg' => 'image/svg+xml',
            default => 'image/jpeg',
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function originalUrlsFromMetadata(Model $model): array
    {
        $metadata = $model->getAttribute('metadata');

        if (! is_array($metadata)) {
            return [];
        }

        $originals = $metadata['_original_image_urls'] ?? [];

        return is_array($originals) ? $originals : [];
    }
}
