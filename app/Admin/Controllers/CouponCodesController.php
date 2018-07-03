<?php

namespace App\Admin\Controllers;

use App\Models\CouponCode;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;

class CouponCodesController extends Controller
{
    use ModelForm;

    /**
     * Index interface.
     *
     * @return Content
     */
    public function index()
    {
        return Admin::content(function (Content $content) {

            $content->header('优惠券列表');

            $content->body($this->grid());
        });
    }

    /**
     * Edit interface.
     *
     * @param $id
     * @return Content
     */
    public function edit($id)
    {
        return Admin::content(function (Content $content) use ($id) {

            $content->header('编辑优惠券');
//            $content->description('description');

            $content->body($this->form()->edit($id));
        });
    }

    /**
     * Create interface.
     *
     * @return Content
     */
    public function create()
    {
        return Admin::content(function (Content $content) {

            $content->header('新增优惠券');

            $content->body($this->form());
        });
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Admin::grid(CouponCode::class, function (Grid $grid) {
// 默认按创建时间倒序排序
            $grid->model()->orderBy('created_at', 'desc');
            $grid->id('ID')->sortable();
            $grid->name('名称');
            $grid->code('优惠码');
//            $grid->type('类型')->display(function ($value) {
//                return CouponCode::$type_map[$value];
//            });
//            $grid->value('折扣')->display(function ($value) {
//                return $this->type === CouponCode::TYPE_FIXED ? '￥' . $value : $value . '%';
//            });
//            $grid->min_amount('最低金额');
            $grid->description('优惠详情');
//            $grid->total('总量');
//            $grid->used('已用');
            $grid->column('usage', '使用情况')->display(function ($value) {
                return "{$this->used} / {$this->total}";
            });
            $grid->enabled('是否启用')->display(function ($value) {
                return $value ? '是' : '否';
            });
            $grid->effected_at('生效时间');
            $grid->invalid_at('失效时间');
            $grid->created_at();
            $grid->updated_at();
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Admin::form(CouponCode::class, function (Form $form) {

            $form->display('id', 'ID');
            $form->text('name', '名称')->rules(['required',]);
            $form->text('code', '优惠码')->rules(function($form) {
                // 如果 $form->model()->id 不为空，代表是编辑操作
                if ($id = $form->model()->id) {
                    return 'nullable|unique:coupon_codes,id,'.$id;
                } else {
                    return 'nullable|unique:coupon_codes';
                }
            });
            $form->radio('type', '类型')->options(CouponCode::$type_map)->rules('required');
            $form->text('value', '折扣')->rules(function ($form) {
                if ($form->type === CouponCode::TYPE_PERCENT) {
                    // 如果选择了百分比折扣类型，那么折扣范围只能是 1 ~ 99
                    return 'required|numeric|between:1,99';
                } else {
                    // 否则只要大等于 0.01 即可
                    return 'required|numeric|min:0.01';
                }
            });
            $form->text('total', '总量')->rules('required|numeric|min:0');
            $form->text('min_amount', '最低金额')->rules('required|numeric|min:0');
            $form->datetime('effected_at', '生效时间');
            $form->datetime('invalid_at', '失效时间');
            $form->radio('enabled', '启用')->options(['1' => '是', '0' => '否']);

            $form->display('created_at', '创建时间');
            $form->display('updated_at', '更新时间');
            $form->saving(function (Form $form) {
                if (!$form->code) {
                    $form->code = CouponCode::getAvailableCode();
                }
            });
        });
    }
}
