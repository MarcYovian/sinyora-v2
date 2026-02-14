<?php

namespace App\Repositories\Eloquent;

use App\Models\ContentSetting;
use App\Repositories\Contracts\ContentSettingRepositoryInterface;
use Illuminate\Support\Collection;

class EloquentContentSettingRepository implements ContentSettingRepositoryInterface
{
    /**
     * @var ContentSetting
     */
    protected $model;

    /**
     * EloquentContentSettingRepository constructor.
     *
     * @param ContentSetting $model
     */
    public function __construct(ContentSetting $model)
    {
        $this->model = $model;
    }

    public function find(int $id): ?ContentSetting
    {
        return $this->model->find($id);
    }

    /**
     * {@inheritDoc}
     */
    public function getByPage(string $page): Collection
    {
        return $this->model
            ->select('id', 'page', 'section', 'key', 'value', 'type')
            ->where('page', $page)
            ->get();
    }

    public function update(int $id, array $data): bool
    {
        $model = $this->find($id);
        if (!$model) {
            return false;
        }
        return $model->update($data);
    }

    /**
     * {@inheritDoc}
     */
    public function updateOrCreate(array $attributes, array $values): ContentSetting
    {
        return $this->model->updateOrCreate($attributes, $values);
    }
}
