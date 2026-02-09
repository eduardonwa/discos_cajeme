<?php

namespace Database\Seeders;

use App\Models\Collection;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class CollectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $catalog = config('catalog');

        foreach (($catalog['collections'] ?? []) as $c) {

            /** @var Collection $collection */
            $collection = Collection::updateOrCreate(
                ['slug' => $c['slug']],
                [
                    'name'        => $c['name'],
                    'description' => $c['description']
                ]
            );

            if (empty($c['image'])) {
                continue;
            }

            $full = public_path($c['image']);

            if (!is_file($full)) {
                continue;
            }

            // obtener media actual, si existe
            $currentMedia = $collection->getFirstMedia('col_thumbnail');
            // nombre del archivo nuevo
            $newSourcePath = $c['image'];

            $shouldReplace = !$currentMedia
                || $currentMedia->getCustomProperty('source_path') !== $newSourcePath;

            if ($shouldReplace) {
                $collection->clearMediaCollection('col_thumbnail');
                
                $collection->addMedia($full)
                    ->preservingOriginal()
                    ->withCustomProperties(['source_path' => $newSourcePath])
                    ->toMediaCollection('col_thumbnail');
            }
        }
    }
}
