<?php

namespace App\Http\Controllers;

use App\Handlers\ImageUploadSftpHandler;
use Dingo\Api\Exception\ResourceException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\Sftp\SftpAdapter;
use League\Flysystem\Filesystem;

class UploadsController extends Controller
{
    public function image(Request $request, ImageUploadSftpHandler $uploader)
    {

        // 判断是否有上传文件，并赋值给 $file
        $file = $request->file('image');
        if (!$file) {
            throw new ResourceException('image不能为空');
        }
        $data = [];
        // 保存图片到本地
        $result = $uploader->save($file);
        // 图片保存成功的话
        if ($result) {
            $data[] = config('api.img_host') . $result;
        }
        return $data;


    }
}
