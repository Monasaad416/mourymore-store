<?php

namespace App\Console\Commands;

use App\Models\SiteContent;
use App\Support\SiteContentLoader;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Throwable;

#[Signature('site-content:sync')]
#[Description('Sync storefront lang/store.php strings into site_contents without overwriting edited values')]
class SyncSiteContent extends Command
{
    public function handle(): int
    {
        $english = $this->loadStore('en');
        $arabic = $this->loadStore('ar');

        $flatEn = Arr::dot($english);
        $flatAr = Arr::dot($arabic);
        $keys = array_unique([...array_keys($flatEn), ...array_keys($flatAr)]);

        sort($keys);

        $created = 0;
        $updated = 0;
        $sort = 0;

        foreach ($keys as $key) {
            $en = $this->stringValue($flatEn[$key] ?? null);
            $ar = $this->stringValue($flatAr[$key] ?? null);
            $sample = $en ?? $ar;

            if ($sample === null) {
                continue;
            }

            $group = explode('.', $key, 2)[0];
            $input = $this->guessInput($sample);

            $content = SiteContent::query()->firstOrNew(['key' => $key]);
            $isNew = ! $content->exists;

            $content->group = $group;
            $content->input = $content->exists ? $content->input : $input;
            $content->sort = $content->exists ? $content->sort : $sort;

            $currentEn = trim((string) $content->getTranslation('value', 'en', false));
            $currentAr = trim((string) $content->getTranslation('value', 'ar', false));

            if ($currentEn === '' && $en !== null) {
                $content->setTranslation('value', 'en', $en);
            }

            if ($currentAr === '' && $ar !== null) {
                $content->setTranslation('value', 'ar', $ar);
            }

            if ($currentEn === '' && $currentAr === '' && $en === null) {
                $content->setTranslation('value', 'en', $sample);
            }

            $content->save();

            if ($isNew) {
                $created++;
            } else {
                $updated++;
            }

            $sort++;
        }

        $this->syncSettings();
        $this->syncPermissions();

        SiteContentLoader::forgetCache();

        $this->info("Synced site contents. Created: {$created}. Touched: {$updated}.");

        return self::SUCCESS;
    }

    /**
     * @return array<string, mixed>
     */
    private function loadStore(string $locale): array
    {
        $path = lang_path($locale.'/store.php');

        if (! is_file($path)) {
            return [];
        }

        /** @var array<string, mixed> $lines */
        $lines = require $path;

        return $lines;
    }

    private function stringValue(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $trimmed = trim($value);

        return $trimmed !== '' ? $trimmed : null;
    }

    private function guessInput(string $value): string
    {
        return mb_strlen($value) > 80 || str_contains($value, "\n")
            ? 'textarea'
            : 'text';
    }

    private function syncSettings(): void
    {
        $settings = [
            'settings.name' => [
                'en' => (string) config('store.name', 'mourymore_store'),
                'ar' => (string) config('store.name', 'mourymore_store'),
            ],
            'settings.tagline' => [
                'en' => (string) config('store.tagline', ''),
                'ar' => (string) config('store.tagline', ''),
            ],
        ];

        foreach ($settings as $key => $values) {
            $content = SiteContent::query()->firstOrNew(['key' => $key]);

            $content->group = 'settings';
            $content->input = 'text';
            $content->sort = $content->exists ? $content->sort : 0;

            foreach (['en', 'ar'] as $locale) {
                $current = trim((string) $content->getTranslation('value', $locale, false));

                if ($current === '' && filled($values[$locale])) {
                    $content->setTranslation('value', $locale, $values[$locale]);
                }
            }

            $content->save();
        }
    }

    private function syncPermissions(): void
    {
        try {
            $guard = config('auth.defaults.guard', 'web');
            $actions = [
                'ViewAny', 'View', 'Create', 'Update', 'Delete', 'DeleteAny',
                'Restore', 'ForceDelete', 'ForceDeleteAny', 'RestoreAny', 'Replicate', 'Reorder',
            ];

            foreach (['SiteContent', 'Page'] as $model) {
                foreach ($actions as $action) {
                    Permission::findOrCreate($action.':'.$model, $guard);
                }
            }

            $admin = Role::findByName('admin', $guard);
            $admin->givePermissionTo(
                Permission::query()
                    ->where('guard_name', $guard)
                    ->where(function ($query): void {
                        $query->where('name', 'like', '%:SiteContent')
                            ->orWhere('name', 'like', '%:Page');
                    })
                    ->get(),
            );

            app(PermissionRegistrar::class)->forgetCachedPermissions();
        } catch (Throwable $exception) {
            $this->warn('Skipped SiteContent permission sync: '.$exception->getMessage());
        }
    }
}
