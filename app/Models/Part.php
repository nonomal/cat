<?php

namespace App\Models;

use App\Services\PartService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Part extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'parts';

    /**
     * 一对一，配件属于一个分类.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(PartCategory::class, 'category_id', 'id');
    }

    /**
     * 一对一，配件有一个品牌.
     */
    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class, 'brand_id', 'id');
    }

    /**
     * 一对一，配件有一个资产追踪.
     */
    public function assetNumberTrack(): HasOne
    {
        return $this->hasOne(AssetNumberTrack::class, 'asset_number', 'asset_number');
    }

    /**
     * 模型到服务.
     */
    public function service(): PartService
    {
        return new PartService($this);
    }

    /**
     * 一对多，配件有很多配件管理记录.
     */
    public function hasParts(): HasMany
    {
        return $this->hasMany(DeviceHasPart::class, 'part_id', 'id');
    }

    /**
     * 远程一对多，配件有很多个设备.
     */
    public function devices(): HasManyThrough
    {
        return $this->hasManyThrough(
            Device::class,  // 远程表
            DeviceHasPart::class,   // 中间表
            'part_id',    // 中间表对主表的关联字段
            'id',   // 远程表对中间表的关联字段
            'id',   // 主表对中间表的关联字段
            'device_id' // 中间表对远程表的关联字段
        );
    }
}
