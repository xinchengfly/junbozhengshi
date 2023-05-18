<?php

namespace app\shop\controller\product;

use app\shop\controller\Controller;
use app\shop\model\product\Comment as CommentModel;

/**
 * 商品评价控制器
 */
class Comment extends Controller
{
    /**
     * 评价列表
     */
    public function index()
    {
        $model = new CommentModel;
        //列表
        $list = $model->getList($this->postData());
        //重要数据
        $num = $model->getStatusNum(['status' => 0]);
        return $this->renderSuccess('', compact('list','num'));
    }

    /**
     * 评价详情
     */
    public function detail($comment_id)
    {
        // 评价详情
        $data = CommentModel::detail($comment_id);
        return $this->renderSuccess('', compact('data'));
    }

    /**
     * 更新商品评论
     */
    public function edit($comment_id)
    {
        $model = CommentModel::detail($comment_id);
        // 更新记录
        if ($model->edit($this->postData())) {
            return $this->renderSuccess('修改成功');
        }
        return $this->renderError('修改失败');
    }

    /**
     * 删除评价
     */
    public function delete($comment_id)
    {
        $model = new CommentModel();
        if (!$model->setDelete($comment_id)) {
            return $this->renderError('删除失败');
        }
        return $this->renderSuccess('删除成功');
    }

}