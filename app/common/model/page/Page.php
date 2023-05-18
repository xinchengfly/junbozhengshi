<?php

namespace app\common\model\page;

use app\common\model\BaseModel;

/**
 * diy页面模型
 */
class Page extends BaseModel
{
    protected $pk = 'page_id';
    protected $name = 'page';

    /**
     * 页面标题栏默认数据
     * @return array
     */
    public function getDefaultPage()
    {
        static $defaultPage = [];
        if (!empty($defaultPage)) return $defaultPage;
        return [
            'type' => 'page',
            'name' => '页面设置',
            'params' => [
                'name' => '页面名称',
                'title' => '页面标题',
                'share_title' => '分享标题'
            ],
            'style' => [
                'titleTextColor' => 'black',
                'titleBackgroundColor' => '#ffffff',
                'toplogo' => 'http://wx-cdn.jiujiuyunhui.com/20210618184614807d88060.png'
            ],
            'category' => [
                'open' => 0,
                'color' => '#000000',
            ]
        ];
    }

    /**
     * 页面diy元素默认数据
     * @return array[]
     */
    public function getDefaultItems()
    {
        return [
            'banner' => [
                'name' => '图片轮播',
                'type' => 'banner',
                'group' => 'media',
                'style' => [
                    'btnColor' => '#ffffff',
                    'background' => '#ffffff',
                    'btnShape' => 'round',//rectangle 长方形，round圆形, square正方形
                    'imgShape' => 'round', //square 正方形，round圆形
                    'height' => 200,
                ],
                'params' => [
                    'interval' => '2800'
                ],
                'data' => [
                    [
                        'imgUrl' => self::$base_url . 'image/diy/banner/01.png',
                        'linkUrl' => ''
                    ],
                    [
                        'imgUrl' => self::$base_url . 'image/diy/banner/01.png',
                        'linkUrl' => ''
                    ]
                ]
            ],
            'imageSingle' => [
                'name' => '单图组',
                'type' => 'imageSingle',
                'group' => 'media',
                'style' => [
                    'paddingTop' => 0,
                    'paddingLeft' => 0,
                    'background' => '#ffffff'
                ],
                'data' => [
                    [
                        'imgUrl' => self::$base_url . 'image/diy/banner/01.png',
                        'imgName' => 'image-1.jpg',
                        'linkUrl' => ''
                    ],
                    [
                        'imgUrl' => self::$base_url . 'image/diy/banner/01.png',
                        'imgName' => 'banner-2.jpg',
                        'linkUrl' => ''
                    ]
                ]
            ],
            'navBar' => [
                'name' => '导航组',
                'type' => 'navBar',
                'group' => 'media',
                'style' => ['background' => '#ffffff', 'rowsNum' => '4'],
                'data' => [
                    [
                        'imgUrl' => self::$base_url . 'image/diy/navbar/01.png',
                        'imgName' => 'icon-1.png',
                        'linkUrl' => '',
                        'text' => '按钮文字1',
                        'color' => '#666666'
                    ],
                    [
                        'imgUrl' => self::$base_url . 'image/diy/navbar/02.png',
                        'imgName' => 'icon-2.jpg',
                        'linkUrl' => '',
                        'text' => '按钮文字2',
                        'color' => '#666666'
                    ],
                    [
                        'imgUrl' => self::$base_url . 'image/diy/navbar/03.png',
                        'imgName' => 'icon-3.jpg',
                        'linkUrl' => '',
                        'text' => '按钮文字3',
                        'color' => '#666666'
                    ],
                    [
                        'imgUrl' => self::$base_url . 'image/diy/navbar/04.png',
                        'imgName' => 'icon-4.jpg',
                        'linkUrl' => '',
                        'text' => '按钮文字4',
                        'color' => '#666666'
                    ]
                ]
            ],
            'blank' => [
                'name' => '辅助空白',
                'type' => 'blank',
                'group' => 'tools',
                'style' => [
                    'height' => 20,
                    'background' => '#ffffff'
                ]
            ],
            'guide' => [
                'name' => '辅助线',
                'type' => 'guide',
                'group' => 'tools',
                'style' => [
                    'background' => '#ffffff',
                    'lineStyle' => 'solid',
                    'lineHeight' => '1',
                    'lineColor' => "#000000",
                    'paddingTop' => 10
                ]
            ],
            'video' => [
                'name' => '视频组',
                'type' => 'video',
                'group' => 'media',
                'params' => [
                    'videoUrl' => 'http://wxsnsdy.tc.qq.com/105/20210/snsdyvideodownload?filekey=30280201010421301f0201690402534804102ca905ce620b1241b726bc41dcff44e00204012882540400',
                    'poster' => self::$base_url . 'image/diy/video_poster.png',
                    'autoplay' => 0
                ],
                'style' => [
                    'paddingTop' => 0,
                    'height' => 190
                ]
            ],
            'article' => [
                'name' => '文章组',
                'type' => 'article',
                'group' => 'media',
                'params' => [
                    'source' => 'auto', // choice; auto
                    'auto' => [
                        'category' => '0',
                        'showNum' => 6
                    ]
                ],
                'style' => [
                    'display' => '10'
                ],
                // '自动获取' => 默认数据
                'defaultData' => [
                    [
                        'article_title' => '此处显示文章标题',
                        'show_type' => 10,
                        'image' => self::$base_url . 'image/diy/article/01.png',
                        'views_num' => 309
                    ],
                    [
                        'article_title' => '此处显示文章标题',
                        'show_type' => 10,
                        'image' => self::$base_url . 'image/diy/article/01.png',
                        'views_num' => 309
                    ]
                ],
                // '手动选择' => 默认数据
                'data' => []
            ],
            'special' => [
                'name' => '头条快报',
                'type' => 'special',
                'group' => 'media',
                'params' => [
                    'source' => 'auto', // choice; auto
                    'auto' => [
                        'category' => 0,
                        'showNum' => 6
                    ]
                ],
                'style' => [
                    'display' => 1,
                    'image' => self::$base_url . 'image/diy/special.png'
                ],
                // '自动获取' => 默认数据
                'defaultData' => [
                    [
                        'article_title' => '此处显示头条快报标题'
                    ]
                ],
                // '手动选择' => 默认数据
                'data' => []
            ],
            'notice' => [
                'name' => '公告组',
                'type' => 'notice',
                'group' => 'media',
                'params' => [
                    'text' => '这里是第一条自定义公告的标题',
                    'icon' => self::$base_url . 'image/diy/notice.png'
                ],
                'style' => [
                    'paddingTop' => 4,
                    'background' => '#ffffff',
                    'textColor' => '#000000'
                ]
            ],
            'richText' => [
                'name' => '富文本',
                'type' => 'richText',
                'group' => 'tools',
                'params' => [
                    'content' => '<p>这里是文本的内容</p>'
                ],
                'style' => [
                    'paddingTop' => 0,
                    'paddingLeft' => 0,
                    'background' => '#ffffff'
                ]
            ],
            'window' => [
                'name' => '图片橱窗',
                'type' => 'window',
                'group' => 'media',
                'style' => [
                    'paddingTop' => 0,
                    'paddingLeft' => 0,
                    'background' => '#ffffff',
                    'layout' => '2'
                ],
                'data' => [
                    [
                        'imgUrl' => self::$base_url . 'image/diy/window/01.jpg',
                        'linkUrl' => ''
                    ],
                    [
                        'imgUrl' => self::$base_url . 'image/diy/window/02.jpg',
                        'linkUrl' => ''
                    ],
                    [
                        'imgUrl' => self::$base_url . 'image/diy/window/03.jpg',
                        'linkUrl' => ''
                    ],
                    [
                        'imgUrl' => self::$base_url . 'image/diy/window/04.jpg',
                        'linkUrl' => ''
                    ]
                ],
                'dataNum' => 4
            ],
            'product' => [
                'name' => '商品组',
                'type' => 'product',
                'group' => 'shop',
                'params' => [
                    'source' => 'auto', // choice; auto
                    'auto' => [
                        'category' => 0,
                        'productSort' => 'all', // all; sales; price
                        'showNum' => 6
                    ]
                ],
                'style' => [
                    'background' => '#F6F6F6',
                    'display' => 'list', // list; slide
                    'column' => '2',
                    'show' => [
                        'productName' => 1,
                        'productPrice' => 1,
                        'linePrice' => 1,
                        'sellingPoint' => 0,
                        'productSales' => 0,
                    ]
                ],
                // '自动获取' => 默认数据
                'defaultData' => [
                    [
                        'product_name' => '此处显示商品名称',
                        'image' => self::$base_url . 'image/diy/product/01.png',
                        'product_price' => '99.00',
                        'line_price' => '139.00',
                        'selling_point' => '此款商品美观大方 不容错过',
                        'product_sales' => '100',
                    ],
                    [
                        'product_name' => '此处显示商品名称',
                        'image' => self::$base_url . 'image/diy/product/01.png',
                        'product_price' => '99.00',
                        'line_price' => '139.00',
                        'selling_point' => '此款商品美观大方 不容错过',
                        'product_sales' => '100',
                    ],
                    [
                        'product_name' => '此处显示商品名称',
                        'image' => self::$base_url . 'image/diy/product/01.png',
                        'product_price' => '99.00',
                        'line_price' => '139.00',
                        'selling_point' => '此款商品美观大方 不容错过',
                        'product_sales' => '100',
                    ],
                    [
                        'product_name' => '此处显示商品名称',
                        'image' => self::$base_url . 'image/diy/product/01.png',
                        'product_price' => '99.00',
                        'line_price' => '139.00',
                        'selling_point' => '此款商品美观大方 不容错过',
                        'product_sales' => '100',
                    ]
                ],
                // '手动选择' => 默认数据
                'data' => [
                    [
                        'product_name' => '此处显示商品名称',
                        'image' => self::$base_url . 'image/diy/product/01.png',
                        'product_price' => '99.00',
                        'line_price' => '139.00',
                        'selling_point' => '此款商品美观大方 不容错过',
                        'product_sales' => '100',
                        'is_default' => true
                    ],
                    [
                        'product_name' => '此处显示商品名称',
                        'image' => self::$base_url . 'image/diy/product/01.png',
                        'product_price' => '99.00',
                        'line_price' => '139.00',
                        'selling_point' => '此款商品美观大方 不容错过',
                        'product_sales' => '100',
                        'is_default' => true
                    ]
                ]
            ],
            'coupon' => [
                'name' => '优惠券组',
                'type' => 'coupon',
                'group' => 'shop',
                'style' => [
                    'paddingTop' => 10,
                    'background' => '#ffffff'
                ],
                'params' => [
                    'limit' => 5
                ],
                'data' => [
                    [
                        'color' => 'red',
                        'reduce_price' => '10',
                        'min_price' => '100.00'
                    ],
                    [
                        'color' => 'violet',
                        'reduce_price' => '10',
                        'min_price' => '100.00'
                    ]
                ]
            ],
            'assembleProduct' => [
                'name' => '拼团商品组',
                'type' => 'assembleProduct',
                'group' => 'shop',
                'params' => [
                    'source' => 'auto', // choice; auto
                    'showNum' => 6,
                    'auto' => [
                        'category' => 0,
                        'productSort' => 'all', // all; sales; price
                    ]
                ],
                'style' => [
                    'color' => '#C9C9C9',
                    'background_image' => self::$base_url . 'image/diy/active/assemble.png',
                    'column' => 1,
                    'show' => [
                        'productName' => true,
                        'sellingPoint' => true,
                        'assemblePrice' => true,
                        'linePrice' => true
                    ]
                ],
                // '自动获取' => 默认数据
                'defaultData' => [
                    [
                        'product_name' => '此处是拼团商品',
                        'image' => self::$base_url . 'image/diy/product/01.png',
                        'selling_point' => '此款商品美观大方 性价比较高 不容错过',
                        'assemble_price' => '99.00',
                        'line_price' => '139.00',
                    ],
                    [
                        'product_name' => '此处是拼团商品',
                        'image' => self::$base_url . 'image/diy/product/01.png',
                        'selling_point' => '此款商品美观大方 性价比较高 不容错过',
                        'assemble_price' => '99.00',
                        'line_price' => '139.00',
                    ],
                    [
                        'product_name' => '此处是拼团商品',
                        'image' => self::$base_url . 'image/diy/product/01.png',
                        'selling_point' => '此款商品美观大方 性价比较高 不容错过',
                        'assemble_price' => '99.00',
                        'line_price' => '139.00',
                    ],
                    [
                        'product_name' => '此处是拼团商品',
                        'image' => self::$base_url . 'image/diy/product/01.png',
                        'selling_point' => '此款商品美观大方 性价比较高 不容错过',
                        'assemble_price' => '99.00',
                        'line_price' => '139.00',
                    ]
                ],
                // '手动选择' => 默认数据
                'data' => [
                    [
                        'product_name' => '此处是拼团商品',
                        'image' => self::$base_url . 'image/diy/product/01.png',
                        'selling_point' => '此款商品美观大方 性价比较高 不容错过',
                        'assemble_price' => '99.00',
                        'line_price' => '139.00',
                        'is_default' => true
                    ],
                    [
                        'product_name' => '此处是拼团商品',
                        'image' => self::$base_url . 'image/diy/product/01.png',
                        'selling_point' => '此款商品美观大方 性价比较高 不容错过',
                        'assemble_price' => '99.00',
                        'line_price' => '139.00',
                        'is_default' => true
                    ]
                ]
            ],
            'bargainProduct' => [
                'name' => '砍价商品组',
                'type' => 'bargainProduct',
                'group' => 'shop',
                'params' => [
                    'source' => 'auto', // choice; auto
                    'showNum' => 6,
                    'auto' => [
                        'category' => 0,
                        'productSort' => 'all', // all; sales; price
                        'showNum' => 6
                    ]
                ],
                'style' => [
                    'color' => '#ffffff',
                    'countdown_color' => '#FF02A8',
                    'countdown_back_color' => '#FEE24F',
                    'background_image' => self::$base_url . 'image/diy/active/bargain.png',
                    'column' => 1,
                    'show' => [
                        'productName' => 1,
                        'peoples' => 1,
                        'floorPrice' => 1,
                        'originalPrice' => 1
                    ]
                ],
                'demo' => [
                    'helps_count' => 2,
                    'helps' => [
                        ['avatarUrl' => 'http://tva1.sinaimg.cn/large/0060lm7Tly1g4c7zrytvvj30dw0dwwes.jpg'],
                        ['avatarUrl' => 'http://tva1.sinaimg.cn/large/0060lm7Tly1g4c7zs2u5ej30b40b4dfx.jpg'],
                    ]
                ],
                // '自动获取' => 默认数据
                'defaultData' => [
                    [
                        'product_name' => '此处是砍价商品',
                        'product_image' => self::$base_url . 'image/diy/product/01.png',
                        'floor_price' => '0.01',
                        'original_price' => '139.00',
                    ],
                    [
                        'product_name' => '此处是砍价商品',
                        'product_image' => self::$base_url . 'image/diy/product/01.png',
                        'floor_price' => '0.01',
                        'original_price' => '139.00',
                    ],
                ],
                // '手动选择' => 默认数据
                'data' => [
                    [
                        'product_name' => '此处是砍价商品',
                        'product_image' => self::$base_url . 'image/diy/product/01.png',
                        'floor_price' => '0.01',
                        'original_price' => '139.00',
                    ],
                    [
                        'product_name' => '此处是砍价商品',
                        'product_image' => self::$base_url . 'image/diy/product/01.png',
                        'floor_price' => '0.01',
                        'original_price' => '139.00',
                    ],
                ]
            ],
            'seckillProduct' => [
                'name' => '秒杀商品组',
                'type' => 'seckillProduct',
                'group' => 'shop',
                'params' => [
                    'showNum' => 6
                ],
                'style' => [
                    'color' => '#ffffff',
                    'countdown_color' => '#FF302F',
                    'countdown_back_color' => '#FEE250',
                    'background_image' => self::$base_url . 'image/diy/active/seckill.png',
                    'column' => 3,
                    'show' => [
                        'productName' => true,
                        'seckillPrice' => true,
                        'linePrice' => true
                    ]
                ],
                // '手动选择' => 默认数据
                'data' => [
                    [
                        'product_name' => '此处是秒杀商品',
                        'product_image' => self::$base_url . 'image/diy/product/01.png',
                        'seckill_price' => '69.00',
                        'original_price' => '139.00',
                    ],
                    [
                        'product_name' => '此处是秒杀商品',
                        'product_image' => self::$base_url . 'image/diy/product/01.png',
                        'seckill_price' => '69.00',
                        'original_price' => '139.00',
                    ],
                    [
                        'product_name' => '此处是秒杀商品',
                        'product_image' => self::$base_url . 'image/diy/product/01.png',
                        'seckill_price' => '69.00',
                        'original_price' => '139.00',
                    ],
                ]
            ],
            'live' => [
                'name' => '热门直播',
                'type' => 'live',
                'group' => 'shop',
                'params' => [
                    'source' => 'auto', // choice; auto
                    'showNum' => 6
                ],
                'style' => [
                    'background_image' => self::$base_url . 'image/diy/active/live.png',
                    'color' => '#000000'
                ],
                // '自动获取' => 默认数据
                'defaultData' => [
                    [
                        'shop_name' => '直播间名称',
                        'logo_image' => self::$base_url . 'image/diy/circular.png',
                        'name' => '主播昵称',
                    ],
                    [
                        'shop_name' => '直播间名称',
                        'logo_image' => self::$base_url . 'image/diy/circular.png',
                        'name' => '主播昵称',
                    ],
                ],
                // '手动选择' => 默认数据
                'data' => [
                    [
                        'name' => '直播间名称',
                        'logo_image' => self::$base_url . 'image/diy/circular.png',
                        'anchor_name' => '主播昵称',
                    ],
                    [
                        'name' => '直播间名称',
                        'logo_image' => self::$base_url . 'image/diy/circular.png',
                        'anchor_name' => '主播昵称',
                    ],
                ]
            ],
            'service' => [
                'name' => '在线客服',
                'type' => 'service',
                'group' => 'tools',
                'params' => [
                    'type' => 'chat',     // '客服类型' => chat在线聊天，phone拨打电话
                    'image' => self::$base_url . 'image/diy/service.png',
                    'phone_num' => ''
                ],
                'style' => [
                    'right' => '1',
                    'bottom' => '10',
                    'opacity' => '100'
                ]
            ],
            'title' => [
                'name' => '标题',
                'type' => 'title',
                'group' => 'media',
                'style' => [
                    'paddingTop' => 0,
                    'background' => '#F5F5F5',
                    'textColor' => '#FF0000'
                ],
                'params' => [
                    'title' => '标题名称',
                    'show_icon' => 'yes',
                    'icon' => ''
                ]
            ]
        ];
    }

    /**
     * 格式化页面数据
     * @param $json
     * @return mixed
     */
    public function getPageDataAttr($json)
    {
        // 旧版数据转义
        $array = $this->_transferToNewData($json);
        // 合并默认数据
        return $this->_mergeDefaultData($array);
    }

    /**
     * 自动转换data为json格式
     * @param $value
     * @return false|string
     */
    public function setPageDataAttr($value)
    {
        return json_encode($value ?: ['items' => []]);
    }

    /**
     * diy页面详情
     */
    public static function detail($page_id)
    {
        return (new static())->find($page_id);
    }

    /**
     * diy页面详情
     */
    public static function getHomePage()
    {
        return (new static())->where('page_type', '10')->find();
    }

    /**
     * 旧版数据转义为新版格式
     */
    private function _transferToNewData($json)
    {
        $array = json_decode($json, true);
        $items = $array['items'];
        if (isset($items['page'])) {
            unset($items['page']);
        }
        foreach ($items as &$item) {
            isset($item['data']) && $item['data'] = array_values($item['data']);
        }
        return [
            'page' => isset($array['page']) ? $array['page'] : $array['items']['page'],
            'items' => array_values(array_filter($items))
        ];
    }

    /**
     * 合并默认数据
     */
    private function _mergeDefaultData($array)
    {
        $array['page'] = array_merge_multiple($this->getDefaultPage(), $array['page']);
        $defaultItems = $this->getDefaultItems();
        foreach ($array['items'] as &$item) {
            if (isset($defaultItems[$item['type']])) {
                array_key_exists('data', $item) && $defaultItems[$item['type']]['data'] = [];
                $item = array_merge_multiple($defaultItems[$item['type']], $item);
            }
        }
        return $array;
    }

    /**
     * 首页默认设置
     */
    public static function getDefault()
    {
        return (new static())->where('page_type', '10')->order('is_default desc,page_id desc')->find();
    }
}
