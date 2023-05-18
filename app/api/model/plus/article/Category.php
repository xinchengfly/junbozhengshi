<?php

namespace app\api\model\plus\article;

use app\common\model\plus\article\Category as CategoryModel;

/**
 * 文章分类模型
 */
class Category extends CategoryModel
{
    /**
     * 隐藏字段
     */
    protected $hidden = [
        'app_id',
        'update_time'
    ];

    public static function getList() {

    }

}
