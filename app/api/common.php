<?php
// 应用公共文件
/**
 * User: 意象信息科技 lr
 * Desc: 下载文件
 * @param $url 文件url
 * @param $save_dir 保存目录
 * @param $file_name 文件名
 * @return string
 */
function download_file($url, $save_dir, $file_name)
{
    if (!file_exists($save_dir)) {
        mkdir($save_dir, 0775, true);
    }
    $file_src = $save_dir . $file_name;
    file_exists($file_src) && unlink($file_src);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
    $file = curl_exec($ch);
    curl_close($ch);
    $resource = fopen($file_src, 'a');
    fwrite($resource, $file);
    fclose($resource);
    if (filesize($file_src) == 0) {
        unlink($file_src);
        return '';
    }
    return $file_src;
}

///**
// * curl请求指定url (post)
// * @param $url
// * @param array $data
// * @return mixed
// */
//function curlPost($url, $data = [])
//{
//    $ch = curl_init();
//    curl_setopt($ch, CURLOPT_POST, 1);
//    curl_setopt($ch, CURLOPT_HEADER, 0);
//    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
//    curl_setopt($ch, CURLOPT_URL, $url);
//    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
//    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
//    $result = curl_exec($ch);
//    curl_close($ch);
//    return $result;
//}

///**
// * curl请求指定url (get)
// * @param $url
// * @param array $data
// * @return mixed
// */
//function curl($url, $data = [])
//{
//    // 处理get数据
//    if (!empty($data)) {
//        $url = $url . '?' . http_build_query($data);
//    }
//    $curl = curl_init();
//    curl_setopt($curl, CURLOPT_URL, $url);
//    curl_setopt($curl, CURLOPT_HEADER, false);
//    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
//    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);//这个是重点。
//    $result = curl_exec($curl);
//    curl_close($curl);
//    return $result;
//}

function send_post($url, $post_data, $header) {

    $postdata = http_build_query($post_data);

    $options = array(

        'http' => array(

            'method' => 'POST',

            'header' => $header,

            'content' => $postdata,

            'timeout' => 15 * 60 // 超时时间(单位:s)

        )

    );

    $context = stream_context_create($options);

    $result = file_get_contents($url, false, $context);

    return $result;

}


